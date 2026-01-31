<?php

declare(strict_types=1);

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Api\BaseController;
use App\Models\Attendance\SubstituteAssignment;
use App\Traits\CrudOperationsTrait;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;
use OpenApi\Annotations as OA;

class SubstituteAssignmentController extends BaseController
{
    use CrudOperationsTrait;

    protected string $model = SubstituteAssignment::class;
    protected string $resourceName = 'Substitute Assignment';
    protected array $relationships = ['leaveRequest', 'substituteTeacher', 'classSubject'];
    protected array $allowedFilters = ['leave_request_id', 'substitute_teacher_id', 'status'];
    protected array $searchFields = [];
    protected array $validationRules = [
        'required' => ['leave_request_id', 'substitute_teacher_id', 'assignment_date'],
    ];

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }

    public function updateStatus(string $id)
    {
        try {
            $assignment = SubstituteAssignment::with(['leaveRequest', 'substituteTeacher'])->find($id);

            if (!$assignment) {
                return $this->notFoundResponse('Substitute assignment not found');
            }

            $data = $this->request->all();

            if (empty($data['status'])) {
                return $this->errorResponse('Status is required', 'MISSING_STATUS', null, 400);
            }

            $validStatuses = ['pending', 'accepted', 'completed', 'cancelled'];
            if (!in_array($data['status'], $validStatuses)) {
                return $this->errorResponse('Invalid status. Must be one of: pending, accepted, completed, cancelled', 'INVALID_STATUS', null, 400);
            }

            $assignment->update([
                'status' => $data['status'],
                'assignment_notes' => $data['assignment_notes'] ?? $assignment->assignment_notes,
                'payment_amount' => $data['payment_amount'] ?? $assignment->payment_amount,
            ]);

            return $this->successResponse($assignment, 'Substitute assignment status updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update substitute assignment status');
        }
    }

    public function getByLeaveRequest(string $leaveRequestId)
    {
        try {
            $assignments = SubstituteAssignment::with(['substituteTeacher', 'classSubject'])
                ->where('leave_request_id', $leaveRequestId)
                ->orderBy('assignment_date', 'asc')
                ->paginate(15);

            return $this->successResponse($assignments, 'Substitute assignments retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve substitute assignments');
        }
    }
}
