<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Integration;

use App\Http\Controllers\Api\BaseController;
use App\Services\Integration\IntegrationService;

class IntegrationController extends BaseController
{
    protected IntegrationService $integrationService;

    public function __construct(IntegrationService $integrationService)
    {
        $this->integrationService = $integrationService;
    }

    public function index()
    {
        $page = (int) $this->request->input('page', 1);
        $perPage = (int) $this->request->input('per_page', 20);
        $provider = $this->request->input('provider');
        $type = $this->request->input('type');
        $status = $this->request->input('status');

        $filters = [];
        if ($provider) {
            $filters['provider'] = $provider;
        }
        if ($type) {
            $filters['type'] = $type;
        }
        if ($status) {
            $filters['status'] = $status;
        }

        $result = $this->integrationService->getAll($filters, $page, $perPage);

        return $this->successResponse($result, 'Integrations retrieved successfully');
    }

    public function store()
    {
        $name = $this->request->input('name');
        $provider = $this->request->input('provider');
        $type = $this->request->input('type');

        if (! $name || ! $provider || ! $type) {
            return $this->errorResponse('Name, provider, and type are required', 'MISSING_FIELDS');
        }

        $data = [
            'name' => $name,
            'description' => $this->request->input('description'),
            'credentials' => $this->request->input('credentials', []),
            'settings' => $this->request->input('settings', []),
            'sync_rules' => $this->request->input('sync_rules', []),
            'created_by' => $this->request->getAttribute('user')?->id,
        ];

        $integration = $this->integrationService->create($data);

        return $this->successResponse($integration, 'Integration created successfully', 201);
    }

    public function show($id)
    {
        $integration = $this->integrationService->find($id);

        if (! $integration) {
            return $this->notFoundResponse('Integration not found');
        }

        return $this->successResponse($integration, 'Integration retrieved successfully');
    }

    public function update($id)
    {
        $integration = $this->integrationService->find($id);

        if (! $integration) {
            return $this->notFoundResponse('Integration not found');
        }

        $data = [];

        if ($this->request->has('name')) {
            $data['name'] = $this->request->input('name');
        }

        if ($this->request->has('description')) {
            $data['description'] = $this->request->input('description');
        }

        if ($this->request->has('credentials')) {
            $data['credentials'] = $this->request->input('credentials');
        }

        if ($this->request->has('settings')) {
            $data['settings'] = $this->request->input('settings');
        }

        if ($this->request->has('sync_rules')) {
            $data['sync_rules'] = $this->request->input('sync_rules');
        }

        $integration = $this->integrationService->update($id, $data);

        return $this->successResponse($integration, 'Integration updated successfully');
    }

    public function destroy($id)
    {
        $integration = $this->integrationService->find($id);

        if (! $integration) {
            return $this->notFoundResponse('Integration not found');
        }

        $this->integrationService->delete($id);

        return $this->successResponse(null, 'Integration deleted successfully');
    }

    public function activate($id)
    {
        $integration = $this->integrationService->activate($id);

        if (! $integration) {
            return $this->notFoundResponse('Integration not found');
        }

        if ($integration->status !== 'active') {
            return $this->errorResponse(
                'Failed to activate integration: ' . $integration->error_message,
                'ACTIVATION_FAILED'
            );
        }

        return $this->successResponse($integration, 'Integration activated successfully');
    }

    public function deactivate($id)
    {
        $integration = $this->integrationService->deactivate($id);

        if (! $integration) {
            return $this->notFoundResponse('Integration not found');
        }

        return $this->successResponse($integration, 'Integration deactivated successfully');
    }

    public function test($id)
    {
        $integration = $this->integrationService->find($id);

        if (! $integration) {
            return $this->notFoundResponse('Integration not found');
        }

        try {
            $success = $this->integrationService->testConnection($integration);

            if ($success) {
                return $this->successResponse(['connected' => true], 'Connection test successful');
            }

            return $this->errorResponse('Connection test failed', 'CONNECTION_FAILED');
        } catch (\Exception $e) {
            return $this->errorResponse('Connection test failed: ' . $e->getMessage(), 'CONNECTION_FAILED');
        }
    }

    public function sync($id)
    {
        $integration = $this->integrationService->find($id);

        if (! $integration) {
            return $this->notFoundResponse('Integration not found');
        }

        $operation = $this->request->input('operation', 'full');
        $options = $this->request->input('options', []);

        $syncLog = $this->integrationService->executeSync($id, $operation, $options);

        if (! $syncLog) {
            return $this->errorResponse('Sync failed or integration is not active', 'SYNC_FAILED');
        }

        return $this->successResponse($syncLog, 'Sync initiated successfully');
    }

    public function getSyncLogs($id)
    {
        $integration = $this->integrationService->find($id);

        if (! $integration) {
            return $this->notFoundResponse('Integration not found');
        }

        $limit = (int) $this->request->input('limit', 20);
        $logs = $this->integrationService->getSyncLogs($id, $limit);

        return $this->successResponse($logs, 'Sync logs retrieved successfully');
    }

    public function getStats($id)
    {
        $integration = $this->integrationService->find($id);

        if (! $integration) {
            return $this->notFoundResponse('Integration not found');
        }

        $days = (int) $this->request->input('days', 30);
        $stats = $this->integrationService->getSyncStats($id, $days);

        return $this->successResponse($stats, 'Integration statistics retrieved successfully');
    }
}
