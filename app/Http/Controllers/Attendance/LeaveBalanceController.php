<?php

declare(strict_types=1);

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Api\BaseController;
use App\Models\Attendance\LeaveBalance;
use App\Traits\CrudOperationsTrait;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;
use OpenApi\Annotations as OA;

class LeaveBalanceController extends BaseController
{
    use CrudOperationsTrait;

    protected string $model = LeaveBalance::class;
    protected string $resourceName = 'Leave Balance';
    protected array $relationships = ['staff', 'leaveType'];
    protected array $allowedFilters = ['staff_id', 'leave_type_id', 'year'];
    protected array $searchFields = [];
    protected array $validationRules = [
        'required' => ['staff_id', 'leave_type_id', 'year'],
        'integer' => ['current_balance', 'used_days', 'allocated_days', 'carry_forward_days', 'year'],
    ];

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }

    public function getByStaff(string $staffId)
    {
        try {
            $balances = LeaveBalance::with('leaveType')
                ->where('staff_id', $staffId)
                ->orderBy('year', 'desc')
                ->paginate(15);

            return $this->successResponse($balances, 'Leave balances retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve leave balances');
        }
    }

    public function getByYear(int $year)
    {
        try {
            $balances = LeaveBalance::with(['staff', 'leaveType'])
                ->where('year', $year)
                ->paginate(15);

            return $this->successResponse($balances, "Leave balances for year {$year} retrieved successfully");
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve leave balances');
        }
    }

    public function adjustBalance(string $id)
    {
        try {
            $balance = LeaveBalance::with(['staff', 'leaveType'])->find($id);

            if (!$balance) {
                return $this->notFoundResponse('Leave balance not found');
            }

            $data = $this->request->all();

            if (empty($data['adjustment_type'])) {
                return $this->errorResponse('Adjustment type is required', 'MISSING_ADJUSTMENT_TYPE', null, 400);
            }

            if (!in_array($data['adjustment_type'], ['add', 'subtract'])) {
                return $this->errorResponse('Adjustment type must be either add or subtract', 'INVALID_ADJUSTMENT_TYPE', null, 400);
            }

            if (empty($data['amount'])) {
                return $this->errorResponse('Amount is required', 'MISSING_AMOUNT', null, 400);
            }

            $amount = (int) $data['amount'];

            if ($amount <= 0) {
                return $this->errorResponse('Amount must be greater than 0', 'INVALID_AMOUNT', null, 400);
            }

            if ($data['adjustment_type'] === 'add') {
                $balance->increment('current_balance', $amount);
                $balance->increment('allocated_days', $amount);
            } else {
                if ($balance->current_balance < $amount) {
                    return $this->errorResponse('Insufficient balance for adjustment', 'INSUFFICIENT_BALANCE', null, 400);
                }
                $balance->decrement('current_balance', $amount);
                $balance->increment('used_days', $amount);
            }

            return $this->successResponse($balance, 'Leave balance adjusted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to adjust leave balance');
        }
    }

    public function carryForward(string $id)
    {
        try {
            $balance = LeaveBalance::with(['staff', 'leaveType'])->find($id);

            if (!$balance) {
                return $this->notFoundResponse('Leave balance not found');
            }

            if ($balance->current_balance <= 0) {
                return $this->errorResponse('No balance to carry forward', 'NO_BALANCE_TO_CARRY', null, 400);
            }

            $nextYear = $balance->year + 1;

            $existingBalance = LeaveBalance::where('staff_id', $balance->staff_id)
                ->where('leave_type_id', $balance->leave_type_id)
                ->where('year', $nextYear)
                ->first();

            if ($existingBalance) {
                $existingBalance->increment('carry_forward_days', $balance->current_balance);
                $existingBalance->increment('allocated_days', $balance->current_balance);
                $existingBalance->increment('current_balance', $balance->current_balance);
            } else {
                LeaveBalance::create([
                    'staff_id' => $balance->staff_id,
                    'leave_type_id' => $balance->leave_type_id,
                    'current_balance' => $balance->current_balance,
                    'used_days' => 0,
                    'allocated_days' => $balance->current_balance,
                    'carry_forward_days' => $balance->current_balance,
                    'year' => $nextYear,
                ]);
            }

            $balance->update(['current_balance' => 0]);

            return $this->successResponse(null, 'Leave balance carried forward successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to carry forward leave balance');
        }
    }
}
