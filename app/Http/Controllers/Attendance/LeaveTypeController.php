<?php

declare(strict_types=1);

namespace App\Http\Controllers\Attendance;

use App\Enums\ErrorCode;
use App\Http\Controllers\Api\BaseController;
use App\Models\Attendance\LeaveType;
use Exception;
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

            return $this->successResponse($leaveTypes);
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve leave types');
        }
    }

    public function store()
    {
        try {
            $data = $this->request->all();

            $errors = [];
            if (empty($data['name'])) {
                $errors['name'] = ['The name field is required.'];
            }

            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $leaveType = LeaveType::create($data);

            return $this->successResponse($leaveType, 'Leave type created successfully', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), ErrorCode::SUBJECT_CREATION_ERROR, null, ErrorCode::getStatusCode(ErrorCode::SUBJECT_CREATION_ERROR));
        }
    }

    public function show(int $id)
    {
        try {
            $leaveType = LeaveType::find($id);

            if (! $leaveType) {
                return $this->notFoundResponse('Leave type not found');
            }

            return $this->successResponse($leaveType);
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve leave type');
        }
    }

    public function update(int $id)
    {
        try {
            $leaveType = LeaveType::find($id);

            if (! $leaveType) {
                return $this->notFoundResponse('Leave type not found');
            }

            $leaveType->update($this->request->all());

            return $this->successResponse($leaveType, 'Leave type updated successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), ErrorCode::SUBJECT_CREATION_ERROR, null, ErrorCode::getStatusCode(ErrorCode::SUBJECT_CREATION_ERROR));
        }
    }

    public function destroy(int $id)
    {
        try {
            $leaveType = LeaveType::find($id);

            if (! $leaveType) {
                return $this->notFoundResponse('Leave type not found');
            }

            $leaveType->delete();

            return $this->successResponse(null, 'Leave type deleted successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to delete leave type');
        }
    }
}
