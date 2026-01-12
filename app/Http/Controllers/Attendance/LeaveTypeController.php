<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Api\BaseController;
use App\Models\Attendance\LeaveType;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class LeaveTypeController extends BaseController
{
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }

    /**
     * Display a listing of the leave types.
     */
    public function index()
    {
        $query = LeaveType::query();

        if ($this->request->has('is_active')) {
            $query->where('is_active', $this->request->input('is_active'));
        }

        if ($this->request->has('is_paid')) {
            $query->where('is_paid', $this->request->input('is_paid'));
        }

        $leaveTypes = $query->orderBy('name')->paginate(15);
        return $this->successResponse($leaveTypes);
    }

    /**
     * Store a newly created leave type.
     */
    public function store()
    {
        $data = $this->request->all();

        if (empty($data['name']) || empty($data['code'])) {
            return $this->errorResponse('Name and code are required', null, null, 400);
        }

        $leaveType = LeaveType::create($data);
        return $this->successResponse($leaveType, 'Leave type created successfully', 201);
    }

    /**
     * Display the specified leave type.
     */
    public function show(string $id)
    {
        $leaveType = LeaveType::find($id);

        if (!$leaveType) {
            return $this->notFoundResponse('Leave type not found');
        }

        return $this->successResponse($leaveType);
    }

    /**
     * Update the specified leave type.
     */
    public function update(string $id)
    {
        $leaveType = LeaveType::find($id);

        if (!$leaveType) {
            return $this->notFoundResponse('Leave type not found');
        }

        $data = $this->request->all();
        $leaveType->update($data);
        return $this->successResponse($leaveType, 'Leave type updated successfully');
    }

    /**
     * Remove the specified leave type.
     */
    public function destroy(string $id)
    {
        $leaveType = LeaveType::find($id);

        if (!$leaveType) {
            return $this->notFoundResponse('Leave type not found');
        }

        if ($leaveType->leaveRequests()->count() > 0) {
            return $this->errorResponse('Cannot delete leave type with associated leave requests', 'LEAVE_TYPE_IN_USE', null, 400);
        }

        $leaveType->delete();
        return $this->successResponse(null, 'Leave type deleted successfully');
    }
}
