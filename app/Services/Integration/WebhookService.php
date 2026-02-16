<?php

declare(strict_types=1);

namespace App\Services\Integration;

use App\Models\Integration\Webhook;
use App\Models\Integration\WebhookDelivery;
use App\Services\ResilientHttpClientService;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Hypervel\Support\Annotation\Inject;
use Hypervel\Support\Facades\Log;

class WebhookService
{
    #[Inject]
    private Webhook $webhookModel;

    #[Inject]
    private WebhookDelivery $webhookDeliveryModel;

    #[Inject]
    private ResilientHttpClientService $httpClient;

    public function create(array $data): Webhook
    {
        $data['secret'] = $this->generateSecret();
        $data['status'] = 'active';
        $data['failure_count'] = 0;

        return $this->webhookModel::create($data);
    }

    public function update(string $id, array $data): ?Webhook
    {
        $webhook = $this->webhookModel::find($id);
        if (! $webhook) {
            return null;
        }

        $webhook->update($data);
        return $webhook;
    }

    public function delete(string $id): bool
    {
        $webhook = $this->webhookModel::find($id);
        if (! $webhook) {
            return false;
        }

        return $webhook->delete();
    }

    public function find(string $id): ?Webhook
    {
        return $this->webhookModel::find($id);
    }

    public function getAll(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $query = $this->webhookModel::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }

        $total = $query->count();
        $items = $query->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }

    public function dispatch(string $event, array $payload, ?string $webhookId = null): void
    {
        $webhooks = $webhookId
            ? $this->webhookModel::where('id', $webhookId)->get()
            : $this->webhookModel::where('status', 'active')->get();

        foreach ($webhooks as $webhook) {
            if (! $webhook->shouldTrigger($event)) {
                continue;
            }

            $this->sendWebhook($webhook, $event, $payload);
        }
    }

    public function sendWebhook(Webhook $webhook, string $event, array $payload): WebhookDelivery
    {
        $delivery = $this->webhookDeliveryModel::create([
            'webhook_id' => $webhook->id,
            'event' => $event,
            'payload' => $payload,
            'status' => 'pending',
            'attempt' => 1,
        ]);

        $webhook->update(['last_triggered_at' => now()]);

        $this->executeDelivery($delivery, $webhook);

        return $delivery;
    }

    private function executeDelivery(WebhookDelivery $delivery, Webhook $webhook): void
    {
        $startTime = microtime(true);
        $delivery->markAsSent();

        try {
            $signature = $this->generateSignature($delivery->payload, $webhook->secret);

            $headers = array_merge(
                [
                    'Content-Type' => 'application/json',
                    'X-Webhook-Signature' => $signature,
                    'X-Webhook-Event' => $delivery->event,
                    'X-Webhook-ID' => $delivery->id,
                    'User-Agent' => 'Malnu-Webhook/1.0',
                ],
                $webhook->headers ?? []
            );

            $response = $this->httpClient->post($webhook->url, [
                'headers' => $headers,
                'json' => $delivery->payload,
                'timeout' => $webhook->timeout ?? 30,
            ]);

            $duration = (microtime(true) - $startTime) * 1000;

            $delivery->markAsDelivered(
                $response->getStatusCode(),
                (string) $response->getBody()
            );
            $delivery->setDuration($duration);

            $webhook->markAsSuccess();

            Log::info('Webhook delivered successfully', [
                'webhook_id' => $webhook->id,
                'delivery_id' => $delivery->id,
                'event' => $delivery->event,
                'duration_ms' => $duration,
            ]);
        } catch (RequestException $e) {
            $duration = (microtime(true) - $startTime) * 1000;
            $delivery->setDuration($duration);

            $errorMessage = $e->getResponse()
                ? 'HTTP ' . $e->getResponse()->getStatusCode() . ': ' . $e->getMessage()
                : $e->getMessage();

            $delivery->markAsFailed($errorMessage);

            if ($e->getResponse()) {
                $delivery->update(['http_status_code' => $e->getResponse()->getStatusCode()]);
            }

            $this->handleDeliveryFailure($delivery, $webhook, $errorMessage);
        } catch (Exception $e) {
            $duration = (microtime(true) - $startTime) * 1000;
            $delivery->setDuration($duration);
            $delivery->markAsFailed($e->getMessage());
            $this->handleDeliveryFailure($delivery, $webhook, $e->getMessage());
        }
    }

    private function handleDeliveryFailure(WebhookDelivery $delivery, Webhook $webhook, string $errorMessage): void
    {
        Log::warning('Webhook delivery failed', [
            'webhook_id' => $webhook->id,
            'delivery_id' => $delivery->id,
            'event' => $delivery->event,
            'attempt' => $delivery->attempt,
            'error' => $errorMessage,
        ]);

        if ($delivery->attempt < $webhook->retry_count) {
            $delivery->incrementAttempt();
        } else {
            $webhook->markAsFailed();
            Log::error('Webhook failed after max retries', [
                'webhook_id' => $webhook->id,
                'delivery_id' => $delivery->id,
                'attempts' => $delivery->attempt,
            ]);
        }
    }

    public function retryDelivery(string $deliveryId): ?WebhookDelivery
    {
        $delivery = $this->webhookDeliveryModel->find($deliveryId);
        if (! $delivery || ! $delivery->isFailed()) {
            return null;
        }

        $webhook = $this->webhookModel->find($delivery->webhook_id);
        if (! $webhook) {
            return null;
        }

        $newDelivery = $this->webhookDeliveryModel->create([
            'webhook_id' => $webhook->id,
            'event' => $delivery->event,
            'payload' => $delivery->payload,
            'status' => 'pending',
            'attempt' => 1,
        ]);

        $this->executeDelivery($newDelivery, $webhook);

        return $newDelivery;
    }

    public function getDeliveries(string $webhookId, int $limit = 20): array
    {
        return $this->webhookDeliveryModel::where('webhook_id', $webhookId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getDeliveryStats(string $webhookId, int $hours = 24): array
    {
        $since = now()->subHours($hours);

        $total = $this->webhookDeliveryModel::where('webhook_id', $webhookId)
            ->where('created_at', '>=', $since)
            ->count();

        $successful = $this->webhookDeliveryModel::where('webhook_id', $webhookId)
            ->where('status', 'success')
            ->where('created_at', '>=', $since)
            ->count();

        $failed = $this->webhookDeliveryModel::where('webhook_id', $webhookId)
            ->where('status', 'failed')
            ->where('created_at', '>=', $since)
            ->count();

        $avgDuration = $this->webhookDeliveryModel::where('webhook_id', $webhookId)
            ->where('created_at', '>=', $since)
            ->whereNotNull('duration_ms')
            ->avg('duration_ms') ?? 0;

        return [
            'total' => $total,
            'successful' => $successful,
            'failed' => $failed,
            'success_rate' => $total > 0 ? round(($successful / $total) * 100, 2) : 0,
            'average_duration_ms' => round($avgDuration, 2),
            'period_hours' => $hours,
        ];
    }

    private function generateSecret(): string
    {
        return bin2hex(random_bytes(32));
    }

    private function generateSignature(array $payload, string $secret): string
    {
        $jsonPayload = json_encode($payload);
        return hash_hmac('sha256', $jsonPayload, $secret);
    }

    public function verifySignature(string $payload, string $signature, string $secret): bool
    {
        $expected = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expected, $signature);
    }
}
