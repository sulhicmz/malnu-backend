<?php

declare(strict_types=1);

namespace App\Http\Controllers\Attendance;

use App\Models\Attendance\LeaveRequest;
use App\Models\Attendance\LeaveType;
use App\Models\Attendance\LeaveBalance;
use App\Models\SchoolManagement\Staff;
use App\Services\FormValidationHelper;
use App\Services\NotificationService;

class LeaveRequestController extends BaseController
{
    private NotificationService $notificationService;

    public function __construct(
        \Hyperf\HttpServer\Contract\RequestInterface $request,
        \Hyperf\HttpServer\Contract\ResponseInterface $response,
        \Psr\Container\ContainerInterface $container,
        NotificationService $notificationService
    ) {
        parent::__construct($request, $response, $container);
        $this->notificationService = $notificationService;
    }
    
    /**
     * Display a listing of the leave requests.
     */
    public function index()
    {
        try {
            $query = LeaveRequest::with(['staff', 'leaveType', 'approvedBy']);

            // Filter by staff ID if provided
            if ($this->request->has('staff_id')) {
                $query->where('staff_id', $this->request->input('staff_id'));
            }

            // Filter by status if provided
            if ($this->request->has('status')) {
                $query->where('status', $this->request->input('status'));
            }

            // Filter by leave type if provided
            if ($this->request->has('leave_type_id')) {
                $query->where('leave_type_id', $this->request->input('leave_type_id'));
            }

            // Filter by date range if provided
            if ($this->request->has('start_date') && $this->request->has('end_date')) {
                $query->whereBetween('start_date', [$this->request->input('start_date'), $this->request->input('end_date')])
                      ->orWhereBetween('end_date', [$this->request->input('start_date'), $this->request->input('end_date')]);
            }

            $leaveRequests = $query->orderBy('created_at', 'desc')->paginate(15);

            return $this->successResponse($leaveRequests);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve leave requests');
        }
    }

    /**
     * Store a newly created leave request.
     */
     public function store(StoreLeaveRequest $request)
    {
        try {
            $validated = $request->validated();
            
            // Calculate total days
            $startDate = new \DateTime($validated['start_date']);
            $endDate = new \DateTime($validated['end_date']);
            $totalDays = $startDate->diff($endDate)->days + 1;
            
            // Check if staff has sufficient leave balance
            $leaveType = LeaveType::find($validated['leave_type_id']);
            if ($leaveType && $leaveType->requires_approval) {
                $leaveBalance = LeaveBalance::where('staff_id', $validated['staff_id'])
                    ->where('leave_type_id', $validated['leave_type_id'])
                    ->where('year', date('Y'))
                    ->first();
                
                if ($leaveBalance && $leaveBalance->current_balance < $totalDays) {
                    return $this->errorResponse('Insufficient leave balance for this request', 'INSUFFICIENT_BALANCE');
                }
            }
            
            $leaveRequest = LeaveRequest::create(array_merge(
                $validated,
                ['total_days' => $totalDays, 'status' => 'pending']
            ));
            
            return $this->successResponse($leaveRequest, 'Leave request submitted successfully', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create leave request');
        }
    }

    /**
     * Display the specified leave request.
     */
    public function show(string $id)
    {
        try {
            $leaveRequest = LeaveRequest::with(['staff', 'leaveType', 'approvedBy', 'substituteAssignments'])->find($id);

            if (!$leaveRequest) {
                return $this->notFoundResponse('Leave request not found');
            }

            return $this->successResponse($leaveRequest);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve leave request');
        }
    }

    /**
     * Update the specified leave request.
     */
    public function update(string $id)
    {
        try {
            $leaveRequest = LeaveRequest::find($id);

            if (!$leaveRequest) {
                return $this->notFoundResponse('Leave request not found');
            }

            // Only allow updates to comments and status if not approved/rejected yet
            if ($leaveRequest->status !== 'pending') {
                return $this->errorResponse('Cannot update leave request that is already processed', 'UPDATE_ERROR');
            }

            $input = $this->request->all();
            
            // Sanitize input data
            $input = $this->sanitizeInput($input);
            
            // Validate comments if provided
            $errors = [];
            if (isset($input['comments']) && !is_string($input['comments'])) {
                $errors['comments'] = ["Comments must be a string"];
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $leaveRequest->update($input); // Only update provided fields

            return $this->successResponse($leaveRequest, 'Leave request updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update leave request');
        }
    }

    /**
     * Remove the specified leave request.
     */
    public function destroy(string $id)
    {
        try {
            $leaveRequest = LeaveRequest::find($id);

            if (!$leaveRequest) {
                return $this->notFoundResponse('Leave request not found');
            }

            // Only allow deletion if status is pending
            if ($leaveRequest->status !== 'pending') {
                return $this->errorResponse('Cannot delete leave request that is already processed', 'DELETE_ERROR');
            }

            $leaveRequest->delete();

            return $this->successResponse(null, 'Leave request deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete leave request');
        }
    }

    /**
     * Approve a leave request.
     */
    public function approve(string $id)
    {
        try {
            $leaveRequest = LeaveRequest::find($id);

            if (!$leaveRequest) {
                return $this->notFoundResponse('Leave request not found');
            }

            if ($leaveRequest->status !== 'pending') {
                return $this->errorResponse('Leave request is not in pending status', 'APPROVAL_ERROR');
            }

            $input = $this->request->all();
            
            // Sanitize input data
            $input = $this->sanitizeInput($input);
            
            $leaveRequest->update([
                'status' => 'approved',
                'approved_by' => null, // Assuming user authentication is not implemented yet
                'approved_at' => date('Y-m-d H:i:s'),
                'approval_comments' => $input['approval_comments'] ?? null
            ]);

            // Update leave balance if applicable
            $leaveBalance = LeaveBalance::where('staff_id', $leaveRequest->staff_id)
                ->where('leave_type_id', $leaveRequest->leave_type_id)
                ->where('year', date('Y'))
                ->first();

            if ($leaveBalance) {
                $leaveBalance->decrement('current_balance', $leaveRequest->total_days);
                $leaveBalance->increment('used_days', $leaveRequest->total_days);
            }

            $this->sendLeaveNotification($leaveRequest, 'approved');

            return $this->successResponse($leaveRequest, 'Leave request approved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to approve leave request');
        }
    }

    private function sendLeaveNotification(LeaveRequest $leaveRequest, string $status): void
    {
        try {
            $title = $status === 'approved'
                ? 'Leave Request Approved'
                : 'Leave Request Rejected';

            $message = $status === 'approved'
                ? "Your leave request from {$leaveRequest->start_date->format('Y-m-d')} to {$leaveRequest->end_date->format('Y-m-d')} has been approved."
                : "Your leave request from {$leaveRequest->start_date->format('Y-m-d')} to {$leaveRequest->end_date->format('Y-m-d')} has been rejected.";

            $notification = $this->notificationService->create([
                'title' => $title,
                'message' => $message,
                'type' => $status === 'approved' ? 'success' : 'warning',
                'priority' => 'high',
            ]);

            $this->notificationService->send($notification->id, [$leaveRequest->staff_id]);
        } catch (\Exception $e) {
        }
    }

    /**
     * Reject a leave request.
     */
    public function reject(string $id)
    {
        try {
            $leaveRequest = LeaveRequest::find($id);

            if (!$leaveRequest) {
                return $this->notFoundResponse('Leave request not found');
            }

            if ($leaveRequest->status !== 'pending') {
                return $this->errorResponse('Leave request is not in pending status', 'REJECTION_ERROR');
            }

            $input = $this->request->all();
            
            // Sanitize input data
            $input = $this->sanitizeInput($input);
            
            $leaveRequest->update([
                'status' => 'rejected',
                'approved_by' => null, // Assuming user authentication is not implemented yet
                'approved_at' => date('Y-m-d H:i:s'),
                'approval_comments' => $input['approval_comments'] ?? null
            ]);

            $this->sendLeaveNotification($leaveRequest, 'rejected');

            return $this->successResponse($leaveRequest, 'Leave request rejected successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to reject leave request');
        }
    }
}