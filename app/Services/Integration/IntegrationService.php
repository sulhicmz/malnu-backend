<?php

declare(strict_types=1);

namespace App\Services\Integration;

use App\Models\Integration\Integration;
use App\Models\Integration\IntegrationSyncLog;
use Exception;
use Hypervel\Support\Annotation\Inject;
use Hypervel\Support\Facades\Log;

abstract class IntegrationService
{
    #[Inject]
    protected Integration $integrationModel;

    #[Inject]
    protected IntegrationSyncLog $syncLogModel;

    abstract public function getProvider(): string;

    abstract public function getType(): string;

    abstract public function testConnection(Integration $integration): bool;

    abstract public function sync(Integration $integration, string $operation, array $options = []): IntegrationSyncLog;

    public function create(array $data): Integration
    {
        $data['provider'] = $this->getProvider();
        $data['type'] = $this->getType();
        $data['status'] = 'inactive';
        $data['sync_count'] = 0;
        $data['error_count'] = 0;

        return $this->integrationModel::create($data);
    }

    public function update(string $id, array $data): ?Integration
    {
        $integration = $this->integrationModel::find($id);
        if (! $integration) {
            return null;
        }

        if (isset($data['credentials'])) {
            $existingCredentials = $integration->credentials ?? [];
            $data['credentials'] = array_merge($existingCredentials, $data['credentials']);
        }

        $integration->update($data);
        return $integration;
    }

    public function delete(string $id): bool
    {
        $integration = $this->integrationModel::find($id);
        if (! $integration) {
            return false;
        }

        return $integration->delete();
    }

    public function find(string $id): ?Integration
    {
        return $this->integrationModel::find($id);
    }

    public function getAll(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $query = $this->integrationModel::query();

        if (isset($filters['provider'])) {
            $query->where('provider', $filters['provider']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
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

    public function activate(string $id): ?Integration
    {
        $integration = $this->integrationModel::find($id);
        if (! $integration) {
            return null;
        }

        try {
            if ($this->testConnection($integration)) {
                $integration->activate();
                Log::info('Integration activated', [
                    'integration_id' => $id,
                    'provider' => $integration->provider,
                ]);
                return $integration;
            }

            $integration->markError('Connection test failed');
            return $integration;
        } catch (Exception $e) {
            $integration->markError($e->getMessage());
            Log::error('Failed to activate integration', [
                'integration_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return $integration;
        }
    }

    public function deactivate(string $id): ?Integration
    {
        $integration = $this->integrationModel::find($id);
        if (! $integration) {
            return null;
        }

        $integration->deactivate();
        return $integration;
    }

    public function executeSync(string $id, string $operation, array $options = []): ?IntegrationSyncLog
    {
        $integration = $this->integrationModel::find($id);
        if (! $integration) {
            return null;
        }

        if (! $integration->isActive()) {
            return null;
        }

        try {
            $syncLog = $this->sync($integration, $operation, $options);
            $integration->markSuccess();
            return $syncLog;
        } catch (Exception $e) {
            $integration->markError($e->getMessage());
            Log::error('Sync failed', [
                'integration_id' => $id,
                'operation' => $operation,
                'error' => $e->getMessage(),
            ]);

            $syncLog = $this->syncLogModel::create([
                'integration_id' => $id,
                'operation' => $operation,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'started_at' => now(),
                'completed_at' => now(),
            ]);

            return $syncLog;
        }
    }

    public function getSyncLogs(string $integrationId, int $limit = 20): array
    {
        return $this->syncLogModel::where('integration_id', $integrationId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getSyncStats(string $integrationId, int $days = 30): array
    {
        $since = now()->subDays($days);

        $total = $this->syncLogModel::where('integration_id', $integrationId)
            ->where('created_at', '>=', $since)
            ->count();

        $successful = $this->syncLogModel::where('integration_id', $integrationId)
            ->where('status', 'success')
            ->where('created_at', '>=', $since)
            ->count();

        $failed = $this->syncLogModel::where('integration_id', $integrationId)
            ->where('status', 'failed')
            ->where('created_at', '>=', $since)
            ->count();

        $recordsCreated = $this->syncLogModel::where('integration_id', $integrationId)
            ->where('created_at', '>=', $since)
            ->sum('records_created') ?? 0;

        $recordsUpdated = $this->syncLogModel::where('integration_id', $integrationId)
            ->where('created_at', '>=', $since)
            ->sum('records_updated') ?? 0;

        return [
            'total' => $total,
            'successful' => $successful,
            'failed' => $failed,
            'success_rate' => $total > 0 ? round(($successful / $total) * 100, 2) : 0,
            'records_created' => $recordsCreated,
            'records_updated' => $recordsUpdated,
            'period_days' => $days,
        ];
    }

    protected function createSyncLog(string $integrationId, string $operation): IntegrationSyncLog
    {
        return $this->syncLogModel->create([
            'integration_id' => $integrationId,
            'operation' => $operation,
            'status' => 'pending',
            'records_processed' => 0,
            'records_created' => 0,
            'records_updated' => 0,
            'records_failed' => 0,
            'started_at' => now(),
        ]);
    }

    protected function completeSyncLog(IntegrationSyncLog $syncLog): void
    {
        $syncLog->complete();
    }

    protected function failSyncLog(IntegrationSyncLog $syncLog, string $errorMessage): void
    {
        $syncLog->fail($errorMessage);
    }
}
