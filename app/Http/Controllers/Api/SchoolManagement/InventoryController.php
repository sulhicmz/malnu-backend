<?php

namespace App\Http\Controllers\Api\SchoolManagement;

use App\Http\Controllers\Api\BaseController;
use App\Models\SchoolManagement\SchoolInventory;
use App\Models\SchoolManagement\AssetCategory;
use App\Models\SchoolManagement\AssetAssignment;
use App\Models\SchoolManagement\AssetMaintenance;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class InventoryController extends BaseController
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
            $query = SchoolInventory::with(['category', 'assignedTo']);

            $categoryId = $this->request->query('category_id');
            $status = $this->request->query('status');
            $search = $this->request->query('search');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }

            if ($status) {
                $query->where('status', $status);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('asset_code', 'like', "%{$search}%")
                      ->orWhere('serial_number', 'like', "%{$search}%");
                });
            }

            $inventory = $query->orderBy('name', 'asc')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($inventory, 'Inventory items retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function store()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['name', 'category', 'quantity'];
            $errors = [];

            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            if (isset($data['category_id'])) {
                $category = AssetCategory::find($data['category_id']);
                if (!$category) {
                    return $this->validationErrorResponse(['category_id' => ['Category not found.']]);
                }
            }

            $data['status'] = $data['status'] ?? 'available';

            $item = SchoolInventory::create($data);

            return $this->successResponse($item, 'Inventory item created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'INVENTORY_CREATION_ERROR', null, 400);
        }
    }

    public function show(string $id)
    {
        try {
            $item = SchoolInventory::with(['category', 'assignedTo', 'maintenanceRecords', 'assignments'])->find($id);

            if (!$item) {
                return $this->notFoundResponse('Inventory item not found');
            }

            return $this->successResponse($item, 'Inventory item retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function update(string $id)
    {
        try {
            $item = SchoolInventory::find($id);

            if (!$item) {
                return $this->notFoundResponse('Inventory item not found');
            }

            $data = $this->request->all();

            if (isset($data['category_id'])) {
                $category = AssetCategory::find($data['category_id']);
                if (!$category) {
                    return $this->validationErrorResponse(['category_id' => ['Category not found.']]);
                }
            }

            if (isset($data['status']) && $data['status'] === 'assigned' && empty($data['assigned_to'])) {
                return $this->validationErrorResponse(['assigned_to' => ['assigned_to is required when status is assigned.']]);
            }

            $item->update($data);

            return $this->successResponse($item, 'Inventory item updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'INVENTORY_UPDATE_ERROR', null, 400);
        }
    }

    public function destroy(string $id)
    {
        try {
            $item = SchoolInventory::find($id);

            if (!$item) {
                return $this->notFoundResponse('Inventory item not found');
            }

            if ($item->status === 'assigned') {
                return $this->errorResponse('Cannot delete an assigned item', 'ASSIGNED_ITEM_DELETION_ERROR', null, 400);
            }

            $item->delete();

            return $this->successResponse(null, 'Inventory item deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'INVENTORY_DELETION_ERROR', null, 400);
        }
    }

    public function assign(string $id)
    {
        try {
            $item = SchoolInventory::find($id);

            if (!$item) {
                return $this->notFoundResponse('Inventory item not found');
            }

            if (!$item->isAvailable()) {
                return $this->errorResponse('Item is not available for assignment', 'ITEM_NOT_AVAILABLE', null, 400);
            }

            $data = $this->request->all();

            if (empty($data['assigned_to'])) {
                return $this->validationErrorResponse(['assigned_to' => ['assigned_to field is required.']]);
            }

            $assignment = new AssetAssignment([
                'asset_id' => $id,
                'assigned_to' => $data['assigned_to'],
                'assigned_to_type' => $data['assigned_to_type'] ?? 'user',
                'assigned_date' => now()->toDateString(),
                'status' => 'active',
                'notes' => $data['notes'] ?? null,
            ]);

            $assignment->save();

            $item->update([
                'status' => 'assigned',
                'assigned_to' => $data['assigned_to'],
                'assigned_date' => now()->toDateString(),
            ]);

            return $this->successResponse($assignment, 'Item assigned successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ITEM_ASSIGNMENT_ERROR', null, 400);
        }
    }

    public function returnItem(string $id)
    {
        try {
            $item = SchoolInventory::find($id);

            if (!$item) {
                return $this->notFoundResponse('Inventory item not found');
            }

            if (!$item->isAssigned()) {
                return $this->errorResponse('Item is not assigned', 'ITEM_NOT_ASSIGNED', null, 400);
            }

            $activeAssignment = $item->assignments()->where('status', 'active')->first();

            if ($activeAssignment) {
                $activeAssignment->update([
                    'status' => 'returned',
                    'returned_date' => now()->toDateString(),
                ]);
            }

            $item->update([
                'status' => 'available',
                'assigned_to' => null,
                'assigned_date' => null,
            ]);

            return $this->successResponse(null, 'Item returned successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ITEM_RETURN_ERROR', null, 400);
        }
    }

    public function maintenance(string $id)
    {
        try {
            $item = SchoolInventory::find($id);

            if (!$item) {
                return $this->notFoundResponse('Inventory item not found');
            }

            $data = $this->request->all();

            $requiredFields = ['maintenance_date', 'maintenance_type'];
            $errors = [];

            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $maintenance = new AssetMaintenance([
                'asset_id' => $id,
                'maintenance_date' => $data['maintenance_date'],
                'maintenance_type' => $data['maintenance_type'],
                'description' => $data['description'] ?? null,
                'cost' => $data['cost'] ?? null,
                'performed_by' => $data['performed_by'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $maintenance->save();

            $item->update([
                'last_maintenance' => $data['maintenance_date'],
                'status' => $item->status === 'assigned' ? 'assigned' : 'maintenance',
            ]);

            return $this->successResponse($maintenance, 'Maintenance record created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'MAINTENANCE_RECORD_ERROR', null, 400);
        }
    }

    public function getAssignments(string $id)
    {
        try {
            $item = SchoolInventory::find($id);

            if (!$item) {
                return $this->notFoundResponse('Inventory item not found');
            }

            $assignments = $item->assignments()->with('assignedTo')->orderBy('created_at', 'desc')->get();

            return $this->successResponse($assignments, 'Assignment history retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getMaintenanceRecords(string $id)
    {
        try {
            $item = SchoolInventory::find($id);

            if (!$item) {
                return $this->notFoundResponse('Inventory item not found');
            }

            $records = $item->maintenanceRecords()->orderBy('maintenance_date', 'desc')->get();

            return $this->successResponse($records, 'Maintenance records retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}
