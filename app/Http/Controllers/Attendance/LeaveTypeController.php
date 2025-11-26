<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance\LeaveType;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

class LeaveTypeController extends Controller
{
    /**
     * Display a listing of the leave types.
     */
    public function index(RequestInterface $request): ResponseInterface
    {
        $query = LeaveType::query();

        // Filter by active status if provided
        $isActive = $request->input('is_active');
        if ($isActive !== null) {
            $query->where('is_active', $isActive);
        }

        // Filter by paid status if provided
        $isPaid = $request->input('is_paid');
        if ($isPaid !== null) {
            $query->where('is_paid', $isPaid);
        }

        $leaveTypes = $query->orderBy('name')->paginate(15);

        return $this->response->json([
            'success' => true,
            'data' => $leaveTypes
        ]);
    }

    /**
     * Store a newly created leave type.
     */
    public function store(RequestInterface $request): ResponseInterface
    {
        $requestData = $request->all();
        
        // Basic validation
        if (empty($requestData['name'])) {
            return $this->response->json([
                'success' => false,
                'message' => 'Name is required'
            ], 422);
        }
        
        if (empty($requestData['code'])) {
            return $this->response->json([
                'success' => false,
                'message' => 'Code is required'
            ], 422);
        }
        
        // Check if code already exists
        $existing = LeaveType::where('code', $requestData['code'])->first();
        if ($existing) {
            return $this->response->json([
                'success' => false,
                'message' => 'Code already exists'
            ], 422);
        }

        $leaveType = LeaveType::create($requestData);

        return $this->response->json([
            'success' => true,
            'message' => 'Leave type created successfully',
            'data' => $leaveType
        ], 201);
    }

    /**
     * Display the specified leave type.
     */
    public function show(string $id): ResponseInterface
    {
        $leaveType = LeaveType::find($id);

        if (!$leaveType) {
            return $this->response->json([
                'success' => false,
                'message' => 'Leave type not found'
            ], 404);
        }

        return $this->response->json([
            'success' => true,
            'data' => $leaveType
        ]);
    }

    /**
     * Update the specified leave type.
     */
    public function update(RequestInterface $request, string $id): ResponseInterface
    {
        $leaveType = LeaveType::find($id);

        if (!$leaveType) {
            return $this->response->json([
                'success' => false,
                'message' => 'Leave type not found'
            ], 404);
        }

        $requestData = $request->all();
        
        // Check if code already exists for other records
        if (isset($requestData['code'])) {
            $existing = LeaveType::where('code', $requestData['code'])
                ->where('id', '!=', $id)
                ->first();
            if ($existing) {
                return $this->response->json([
                    'success' => false,
                    'message' => 'Code already exists'
                ], 422);
            }
        }

        $leaveType->update($requestData);

        return $this->response->json([
            'success' => true,
            'message' => 'Leave type updated successfully',
            'data' => $leaveType
        ]);
    }

    /**
     * Remove the specified leave type.
     */
    public function destroy(string $id): ResponseInterface
    {
        $leaveType = LeaveType::find($id);

        if (!$leaveType) {
            return $this->response->json([
                'success' => false,
                'message' => 'Leave type not found'
            ], 404);
        }

        // Check if there are any leave requests associated with this type
        if ($leaveType->leaveRequests()->count() > 0) {
            return $this->response->json([
                'success' => false,
                'message' => 'Cannot delete leave type with associated leave requests'
            ], 400);
        }

        $leaveType->delete();

        return $this->response->json([
            'success' => true,
            'message' => 'Leave type deleted successfully'
        ]);
    }
}