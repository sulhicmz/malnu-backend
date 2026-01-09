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

    public function index()
    {
        try {
            $query = LeaveType::query();

            if ($this->request->has('is_active')) {
                $query->where('is_active', $this->request->input('is_active'));
            }

            if ($this->request->has('is_paid')) {
                $query->where('is_paid', $this->request->input('is_paid'));
            }

            $leaveTypes = $query->orderBy('name')->paginate(15);

            return $this->successResponse($leaveTypes, 'Leave types retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve leave types');
        }
    }

    public function store()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['name', 'code'];
            $errors = [];

            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (isset($data['name']) && strlen($data['name']) > 100) {
                $errors['name'] = ['The name must not exceed 100 characters.'];
            }

            if (isset($data['code']) && strlen($data['code']) > 20) {
                $errors['code'] = ['The code must not exceed 20 characters.'];
            }

            if (isset($data['code'])) {
                $existingLeaveType = LeaveType::where('code', $data['code'])->first();
                if ($existingLeaveType) {
                    $errors['code'] = ['The code has already been taken.'];
                }
            }

            if (isset($data['max_days_per_year']) && (!is_numeric($data['max_days_per_year']) || $data['max_days_per_year'] < 0)) {
                $errors['max_days_per_year'] = ['The max days per year must be a positive number.'];
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $leaveType = LeaveType::create($data);

            return $this->successResponse($leaveType, 'Leave type created successfully', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create leave type');
        }
    }

    public function show(string $id)
    {
        try {
            $leaveType = LeaveType::find($id);

            if (!$leaveType) {
                return $this->notFoundResponse('Leave type not found');
            }

            return $this->successResponse($leaveType, 'Leave type retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve leave type');
        }
    }

    public function update(string $id)
    {
        try {
            $leaveType = LeaveType::find($id);

            if (!$leaveType) {
                return $this->notFoundResponse('Leave type not found');
            }

            $data = $this->request->all();

            $errors = [];

            if (isset($data['name']) && strlen($data['name']) > 100) {
                $errors['name'] = ['The name must not exceed 100 characters.'];
            }

            if (isset($data['code']) && strlen($data['code']) > 20) {
                $errors['code'] = ['The code must not exceed 20 characters.'];
            }

            if (isset($data['code']) && $data['code'] !== $leaveType->code) {
                $existingLeaveType = LeaveType::where('code', $data['code'])->first();
                if ($existingLeaveType) {
                    $errors['code'] = ['The code has already been taken.'];
                }
            }

            if (isset($data['max_days_per_year']) && (!is_numeric($data['max_days_per_year']) || $data['max_days_per_year'] < 0)) {
                $errors['max_days_per_year'] = ['The max days per year must be a positive number.'];
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $leaveType->update($data);

            return $this->successResponse($leaveType, 'Leave type updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update leave type');
        }
    }

    public function destroy(string $id)
    {
        try {
            $leaveType = LeaveType::find($id);

            if (!$leaveType) {
                return $this->notFoundResponse('Leave type not found');
            }

            $leaveRequestCount = $leaveType->leaveRequests()->count();

            if ($leaveRequestCount > 0) {
                return $this->errorResponse('Cannot delete leave type with associated leave requests', 'DELETE_ERROR');
            }

            $leaveType->delete();

            return $this->successResponse(null, 'Leave type deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete leave type');
        }
    }
}
