<?php

declare(strict_types=1);

namespace App\Services\Integration;

use App\Models\Integration\Integration;
use App\Models\Integration\IntegrationSyncLog;
use Exception;
use Hypervel\Support\Annotation\Inject;
use Hypervel\Support\Facades\Log;

/**
 * Integration Manager Service
 *
 * This service manages all integration providers and delegates operations
 * to the appropriate concrete implementation based on provider type.
 */
class IntegrationManagerService extends IntegrationService
{
    #[Inject]
    protected Integration $integrationModel;

    #[Inject]
    protected IntegrationSyncLog $syncLogModel;

    /**
     * Provider-specific service implementations.
     */
    protected array $providers = [];

    public function __construct()
    {
        // Initialize provider mappings
        $this->providers = [
            'stripe' => PaymentIntegrationService::class,
            'paypal' => PaymentIntegrationService::class,
            'google' => CalendarIntegrationService::class,
            'microsoft' => CalendarIntegrationService::class,
            's3' => StorageIntegrationService::class,
            'slack' => CommunicationIntegrationService::class,
            'saml' => SsoIntegrationService::class,
            'oauth' => SsoIntegrationService::class,
            'lti' => LmsIntegrationService::class,
        ];
    }

    public function getProvider(): string
    {
        return 'manager';
    }

    public function getType(): string
    {
        return 'manager';
    }

    /**
     * Test connection for an integration.
     * Delegates to the appropriate provider implementation.
     */
    public function testConnection(Integration $integration): bool
    {
        $providerService = $this->getProviderService($integration->provider);

        if ($providerService) {
            return $providerService->testConnection($integration);
        }

        // Default implementation for unknown providers
        return $this->defaultTestConnection($integration);
    }

    /**
     * Execute sync for an integration.
     * Delegates to the appropriate provider implementation.
     */
    public function sync(Integration $integration, string $operation, array $options = []): IntegrationSyncLog
    {
        $providerService = $this->getProviderService($integration->provider);

        if ($providerService) {
            return $providerService->sync($integration, $operation, $options);
        }

        // Default implementation for unknown providers
        return $this->defaultSync($integration, $operation, $options);
    }

    /**
     * Get the appropriate service for a provider.
     */
    protected function getProviderService(string $provider): ?IntegrationService
    {
        $providerKey = strtolower($provider);

        if (isset($this->providers[$providerKey])) {
            $className = $this->providers[$providerKey];
            return new $className();
        }

        // Try to match partial provider names
        foreach ($this->providers as $key => $className) {
            if (str_contains($providerKey, $key) || str_contains($key, $providerKey)) {
                return new $className();
            }
        }

        return null;
    }

    /**
     * Default connection test for unknown providers.
     */
    protected function defaultTestConnection(Integration $integration): bool
    {
        try {
            $credentials = $integration->credentials ?? [];

            // Basic validation - check if required credentials exist
            if (empty($credentials)) {
                Log::warning('Integration test failed: No credentials provided', [
                    'integration_id' => $integration->id,
                    'provider' => $integration->provider,
                ]);
                return false;
            }

            // Log the test attempt
            Log::info('Integration connection test', [
                'integration_id' => $integration->id,
                'provider' => $integration->provider,
                'type' => $integration->type,
            ]);

            // For now, assume connection is valid if credentials exist
            // Specific provider implementations should override this
            return true;
        } catch (Exception $e) {
            Log::error('Integration test connection failed', [
                'integration_id' => $integration->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Default sync implementation for unknown providers.
     */
    protected function defaultSync(Integration $integration, string $operation, array $options = []): IntegrationSyncLog
    {
        $syncLog = $this->createSyncLog($integration->id, $operation);

        try {
            Log::info('Starting integration sync', [
                'integration_id' => $integration->id,
                'provider' => $integration->provider,
                'operation' => $operation,
            ]);

            // Default sync logic - mark as completed with no records
            // Specific provider implementations should override this with actual sync logic
            $syncLog->records_processed = 0;
            $syncLog->records_created = 0;
            $syncLog->records_updated = 0;
            $syncLog->records_failed = 0;
            $syncLog->status = 'success';
            $syncLog->completed_at = now();
            $syncLog->duration_ms = 0;
            $syncLog->save();

            Log::info('Integration sync completed', [
                'integration_id' => $integration->id,
                'sync_log_id' => $syncLog->id,
            ]);

            return $syncLog;
        } catch (Exception $e) {
            $syncLog->status = 'failed';
            $syncLog->error_message = $e->getMessage();
            $syncLog->completed_at = now();
            $syncLog->save();

            Log::error('Integration sync failed', [
                'integration_id' => $integration->id,
                'error' => $e->getMessage(),
            ]);

            return $syncLog;
        }
    }

    /**
     * Register a custom provider service.
     */
    public function registerProvider(string $provider, string $serviceClass): void
    {
        $this->providers[strtolower($provider)] = $serviceClass;
    }

    /**
     * Get available provider types.
     */
    public function getAvailableProviders(): array
    {
        return array_keys($this->providers);
    }
}
