<?php

declare(strict_types=1);

namespace App\Services\Integration;

use App\Models\Integration\Integration;
use App\Models\Integration\IntegrationSyncLog;

/**
 * Payment Integration Service
 *
 * Handles integrations with payment gateways like Stripe, PayPal, Square.
 */
class PaymentIntegrationService extends IntegrationService
{
    public function getProvider(): string
    {
        return 'payment';
    }

    public function getType(): string
    {
        return 'payment';
    }

    public function testConnection(Integration $integration): bool
    {
        $credentials = $integration->credentials ?? [];

        // Validate required credentials based on provider
        $provider = strtolower($integration->provider);

        return match ($provider) {
            'stripe' => $this->testStripeConnection($credentials),
            'paypal' => $this->testPayPalConnection($credentials),
            default => $this->testGenericPaymentConnection($credentials),
        };
    }

    public function sync(Integration $integration, string $operation, array $options = []): IntegrationSyncLog
    {
        $syncLog = $this->createSyncLog($integration->id, $operation);

        try {
            $result = match ($operation) {
                'import_transactions' => $this->syncTransactions($integration, $options),
                'import_customers' => $this->syncCustomers($integration, $options),
                'export_invoices' => $this->exportInvoices($integration, $options),
                default => ['processed' => 0, 'created' => 0, 'updated' => 0, 'failed' => 0],
            };

            $syncLog->records_processed = $result['processed'] ?? 0;
            $syncLog->records_created = $result['created'] ?? 0;
            $syncLog->records_updated = $result['updated'] ?? 0;
            $syncLog->records_failed = $result['failed'] ?? 0;
            $syncLog->status = $result['failed'] > 0 ? 'partial' : 'success';
            $syncLog->completed_at = now();
            $syncLog->save();
        } catch (\Exception $e) {
            $syncLog->status = 'failed';
            $syncLog->error_message = $e->getMessage();
            $syncLog->completed_at = now();
            $syncLog->save();
        }

        return $syncLog;
    }

    protected function testStripeConnection(array $credentials): bool
    {
        return ! empty($credentials['api_key']) || ! empty($credentials['secret_key']);
    }

    protected function testPayPalConnection(array $credentials): bool
    {
        return ! empty($credentials['client_id']) && ! empty($credentials['client_secret']);
    }

    protected function testGenericPaymentConnection(array $credentials): bool
    {
        return ! empty($credentials);
    }

    protected function syncTransactions(Integration $integration, array $options): array
    {
        // Placeholder for transaction sync logic
        return ['processed' => 0, 'created' => 0, 'updated' => 0, 'failed' => 0];
    }

    protected function syncCustomers(Integration $integration, array $options): array
    {
        // Placeholder for customer sync logic
        return ['processed' => 0, 'created' => 0, 'updated' => 0, 'failed' => 0];
    }

    protected function exportInvoices(Integration $integration, array $options): array
    {
        // Placeholder for invoice export logic
        return ['processed' => 0, 'created' => 0, 'updated' => 0, 'failed' => 0];
    }

    protected function createSyncLog(string $integrationId, string $operation): IntegrationSyncLog
    {
        return IntegrationSyncLog::create([
            'integration_id' => $integrationId,
            'operation' => $operation,
            'status' => 'pending',
            'started_at' => now(),
        ]);
    }
}
