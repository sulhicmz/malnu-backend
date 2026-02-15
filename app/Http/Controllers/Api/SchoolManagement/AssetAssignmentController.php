<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SchoolManagement;

use App\Http\Controllers\Api\BaseController;
use App\Models\SchoolManagement\AssetAssignment;
use App\Models\SchoolManagement\SchoolInventory;
use Exception;
use Hypervel\Http\Request;
use Hypervel\Http\Response;
use Psr\Container\ContainerInterface;

class AssetAssignmentController extends BaseController
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
            $query = AssetAssignment::query()->with(['asset', 'assignedTo']);

            $status = $this->request->query('status');
            $assetId = $this->request->query('asset_id');
            $assignedToId = $this->request->query('assigned_to');
            $search = $this->request->query('search');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($status) {
                $query->where('status', $status);
            }

            if ($assetId) {
                $query->where('asset_id', $assetId);
            }

            if ($assignedToId) {
                $query->where('assigned_to', $assignedToId);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('asset', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    })->orWhereHas('assignedTo', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    });
                });
            }

            $assignments = $query->orderBy('created_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($assignments, 'Asset assignments retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function show(string $id)
    {
        try {
            $assignment = AssetAssignment::with(['asset', 'assignedTo'])->find($id);

            if (!$assignment) {
                return $this->notFoundResponse('Asset assignment not found');
            }

            return $this->successResponse($assignment, 'Asset assignment retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function store()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['asset_id', 'assigned_to'];
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

            if (!$asset->isAvailable()) {
                return $this->validationErrorResponse(['asset_id' => ['Asset is not available for assignment.']]);
            }

            $assignment = AssetAssignment::create([
                'asset_id' => $data['asset_id'],
                'assigned_to' => $data['assigned_to'],
                'assigned_to_type' => $data['assigned_to_type'] ?? 'user',
                'assigned_date' => now()->toDateString(),
                'status' => 'active',
                'notes' => $data['notes'] ?? null,
            ]);

            $asset->update([
                'status' => 'assigned',
                'assigned_to' => $data['assigned_to'],
                'assigned_date' => now()->toDateString(),
            ]);

            return $this->successResponse($assignment, 'Asset assignment created successfully', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ASSIGNMENT_CREATION_ERROR', null, 400);
        }
    }

    public function update(string $id)
    {
        try {
            $assignment = AssetAssignment::find($id);

            if (!$assignment) {
                return $this->notFoundResponse('Asset assignment not found');
            }

            $data = $this->request->all();

            $assignment->update($data);

            return $this->successResponse($assignment, 'Asset assignment updated successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ASSIGNMENT_UPDATE_ERROR', null, 400);
        }
    }

    public function destroy(string $id)
    {
        try {
            $assignment = AssetAssignment::find($id);

            if (!$assignment) {
                return $this->notFoundResponse('Asset assignment not found');
            }

            $asset = $assignment->asset;

            $assignment->delete();

            $asset->update([
                'status' => 'available',
                'assigned_to' => null,
                'assigned_date' => null,
            ]);

            return $this->successResponse(null, 'Asset assignment deleted successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ASSIGNMENT_DELETION_ERROR', null, 400);
        }
    }

    public function complete(string $id)
    {
        try {
            $assignment = AssetAssignment::find($id);

            if (!$assignment) {
                return $this->notFoundResponse('Asset assignment not found');
            }

            $assignment->update([
                'status' => 'completed',
                'returned_date' => now()->toDateString(),
            ]);

            $asset = $assignment->asset;
            $asset->update([
                'status' => 'available',
                'assigned_to' => null,
                'assigned_date' => null,
            ]);

            return $this->successResponse($assignment, 'Assignment marked as completed successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ASSIGNMENT_COMPLETION_ERROR', null, 400);
        }
    }
}
