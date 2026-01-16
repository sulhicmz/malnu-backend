<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SchoolManagement;

use App\Http\Controllers\Api\BaseController;
use App\Models\SchoolManagement\AssetMaintenance;
use App\Models\SchoolManagement\SchoolInventory;
use Exception;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class AssetMaintenanceController extends BaseController
{
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }

    public function index()
    {
        try {
            $query = AssetMaintenance::query()->with(['asset', 'performedBy']);

            $status = $this->request->query('status');
            $assetId = $this->request->query('asset_id');
            $type = $this->request->query('maintenance_type');
            $search = $this->request->query('search');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($status) {
                $query->where('status', $status);
            }

            if ($assetId) {
                $query->where('asset_id', $assetId);
            }

            if ($type) {
                $query->where('maintenance_type', $type);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                        ->orWhere('maintenance_type', 'like', "%{$search}%");
                });
            }

            $maintenanceRecords = $query->orderBy('maintenance_date', 'desc')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($maintenanceRecords, 'Maintenance records retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function show(string $id)
    {
        try {
            $record = AssetMaintenance::with(['asset', 'performedBy'])->find($id);

            if (!$record) {
                return $this->notFoundResponse('Maintenance record not found');
            }

            return $this->successResponse($record, 'Maintenance record retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function store()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['asset_id', 'maintenance_type', 'maintenance_date'];
            $errors = [];

            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $asset = SchoolInventory::find($data['asset_id']);
            if (!$asset) {
                return $this->validationErrorResponse(['asset_id' => ['Asset not found.']]);
            }

            $maintenance = AssetMaintenance::create([
                'asset_id' => $data['asset_id'],
                'maintenance_date' => $data['maintenance_date'],
                'maintenance_type' => $data['maintenance_type'],
                'description' => $data['description'] ?? null,
                'cost' => $data['cost'] ?? null,
                'performed_by' => $data['performed_by'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => $data['status'] ?? 'pending',
            ]);

            $asset->update([
                'last_maintenance' => $data['maintenance_date'],
                'status' => $asset->status === 'assigned' ? 'assigned' : 'maintenance',
            ]);

            return $this->successResponse($maintenance, 'Maintenance record created successfully', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 'MAINTENANCE_CREATION_ERROR', null, 400);
        }
    }

    public function update(string $id)
    {
        try {
            $record = AssetMaintenance::find($id);

            if (!$record) {
                return $this->notFoundResponse('Maintenance record not found');
            }

            $data = $this->request->all();

            if (isset($data['asset_id'])) {
                $asset = SchoolInventory::find($data['asset_id']);
                if (!$asset) {
                    return $this->validationErrorResponse(['asset_id' => ['Asset not found.']]);
                }
            }

            $record->update($data);

            if (isset($data['asset_id'])) {
                $asset->update([
                    'last_maintenance' => $data['maintenance_date'] ?? $record->maintenance_date,
                ]);
            }

            return $this->successResponse($record, 'Maintenance record updated successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 'MAINTENANCE_UPDATE_ERROR', null, 400);
        }
    }

    public function destroy(string $id)
    {
        try {
            $record = AssetMaintenance::find($id);

            if (!$record) {
                return $this->notFoundResponse('Maintenance record not found');
            }

            $record->delete();

            return $this->successResponse(null, 'Maintenance record deleted successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 'MAINTENANCE_DELETION_ERROR', null, 400);
        }
    }

    public function complete(string $id)
    {
        try {
            $record = AssetMaintenance::find($id);

            if (!$record) {
                return $this->notFoundResponse('Maintenance record not found');
            }

            $record->update([
                'status' => 'completed',
            ]);

            $asset = $record->asset;
            if ($asset) {
                $asset->update([
                    'status' => $asset->status === 'maintenance' ? 'available' : $asset->status,
                ]);
            }

            return $this->successResponse($record, 'Maintenance marked as completed successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 'MAINTENANCE_COMPLETION_ERROR', null, 400);
        }
    }

    public function getAssetHistory(string $assetId)
    {
        try {
            $asset = SchoolInventory::find($assetId);

            if (!$asset) {
                return $this->notFoundResponse('Asset not found');
            }

            $history = $asset->maintenanceRecords()->orderBy('maintenance_date', 'desc')->get();

            return $this->successResponse($history, 'Maintenance history retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}
