<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Integration;

use App\Http\Controllers\Api\BaseController;
use App\Services\Integration\WebhookService;

class WebhookController extends BaseController
{
    protected WebhookService $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    public function index()
    {
        $page = (int) $this->request->input('page', 1);
        $perPage = (int) $this->request->input('per_page', 20);
        $status = $this->request->input('status');

        $filters = [];
        if ($status) {
            $filters['status'] = $status;
        }

        $result = $this->webhookService->getAll($filters, $page, $perPage);

        return $this->successResponse($result, 'Webhooks retrieved successfully');
    }

    public function store()
    {
        $name = $this->request->input('name');
        $url = $this->request->input('url');
        $events = $this->request->input('events');

        if (! $name || ! $url || ! $events) {
            return $this->errorResponse('Name, URL, and events are required', 'MISSING_FIELDS');
        }

        if (! is_array($events) || empty($events)) {
            return $this->errorResponse('Events must be a non-empty array', 'INVALID_EVENTS');
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return $this->errorResponse('Invalid URL format', 'INVALID_URL');
        }

        $data = [
            'name' => $name,
            'description' => $this->request->input('description'),
            'url' => $url,
            'events' => $events,
            'headers' => $this->request->input('headers'),
            'retry_count' => $this->request->input('retry_count', 3),
            'timeout' => $this->request->input('timeout', 30),
            'created_by' => $this->request->getAttribute('user')?->id,
        ];

        $webhook = $this->webhookService->create($data);

        return $this->successResponse($webhook, 'Webhook created successfully', 201);
    }

    public function show($id)
    {
        $webhook = $this->webhookService->find($id);

        if (! $webhook) {
            return $this->notFoundResponse('Webhook not found');
        }

        return $this->successResponse($webhook, 'Webhook retrieved successfully');
    }

    public function update($id)
    {
        $webhook = $this->webhookService->find($id);

        if (! $webhook) {
            return $this->notFoundResponse('Webhook not found');
        }

        $data = [];

        if ($this->request->has('name')) {
            $data['name'] = $this->request->input('name');
        }

        if ($this->request->has('url')) {
            $url = $this->request->input('url');
            if (! filter_var($url, FILTER_VALIDATE_URL)) {
                return $this->errorResponse('Invalid URL format', 'INVALID_URL');
            }
            $data['url'] = $url;
        }

        if ($this->request->has('events')) {
            $events = $this->request->input('events');
            if (! is_array($events) || empty($events)) {
                return $this->errorResponse('Events must be a non-empty array', 'INVALID_EVENTS');
            }
            $data['events'] = $events;
        }

        if ($this->request->has('description')) {
            $data['description'] = $this->request->input('description');
        }

        if ($this->request->has('headers')) {
            $data['headers'] = $this->request->input('headers');
        }

        if ($this->request->has('retry_count')) {
            $data['retry_count'] = $this->request->input('retry_count');
        }

        if ($this->request->has('timeout')) {
            $data['timeout'] = $this->request->input('timeout');
        }

        if ($this->request->has('status')) {
            $data['status'] = $this->request->input('status');
        }

        $webhook = $this->webhookService->update($id, $data);

        return $this->successResponse($webhook, 'Webhook updated successfully');
    }

    public function destroy($id)
    {
        $webhook = $this->webhookService->find($id);

        if (! $webhook) {
            return $this->notFoundResponse('Webhook not found');
        }

        $this->webhookService->delete($id);

        return $this->successResponse(null, 'Webhook deleted successfully');
    }

    public function getDeliveries($id)
    {
        $webhook = $this->webhookService->find($id);

        if (! $webhook) {
            return $this->notFoundResponse('Webhook not found');
        }

        $limit = (int) $this->request->input('limit', 20);
        $deliveries = $this->webhookService->getDeliveries($id, $limit);

        return $this->successResponse($deliveries, 'Deliveries retrieved successfully');
    }

    public function getStats($id)
    {
        $webhook = $this->webhookService->find($id);

        if (! $webhook) {
            return $this->notFoundResponse('Webhook not found');
        }

        $hours = (int) $this->request->input('hours', 24);
        $stats = $this->webhookService->getDeliveryStats($id, $hours);

        return $this->successResponse($stats, 'Webhook statistics retrieved successfully');
    }

    public function retryDelivery($deliveryId)
    {
        $delivery = $this->webhookService->retryDelivery($deliveryId);

        if (! $delivery) {
            return $this->errorResponse('Delivery not found or cannot be retried', 'INVALID_DELIVERY');
        }

        return $this->successResponse($delivery, 'Delivery retry initiated');
    }

    public function test($id)
    {
        $webhook = $this->webhookService->find($id);

        if (! $webhook) {
            return $this->notFoundResponse('Webhook not found');
        }

        $testPayload = [
            'event' => 'test.webhook',
            'timestamp' => now()->toIso8601String(),
            'data' => [
                'message' => 'This is a test webhook delivery',
                'webhook_id' => $id,
            ],
        ];

        $delivery = $this->webhookService->sendWebhook($webhook, 'test.webhook', $testPayload);

        return $this->successResponse($delivery, 'Test webhook sent');
    }
}
