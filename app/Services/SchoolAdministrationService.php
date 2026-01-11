<?php

namespace App\Services;

use App\Models\ComplianceRequirement;
use App\Models\AccreditationStandard;
use App\Models\PolicyAndProcedure;
use App\Models\StaffEvaluation;
use App\Models\ProfessionalDevelopment;
use App\Models\BudgetAllocation;
use App\Models\Expense;
use App\Models\InventoryItem;
use App\Models\VendorContract;
use App\Models\InstitutionalMetric;
use App\Models\Staff;

class SchoolAdministrationService
{
    public function getComplianceRequirements(array $filters = [])
    {
        $query = ComplianceRequirement::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        return $query->with('responsibleStaff')->get();
    }

    public function createComplianceRequirement(array $data): ComplianceRequirement
    {
        return ComplianceRequirement::create($data);
    }

    public function updateComplianceRequirement(string $id, array $data): ?ComplianceRequirement
    {
        $requirement = ComplianceRequirement::find($id);
        if (!$requirement) {
            return null;
        }

        $requirement->update($data);
        return $requirement;
    }

    public function deleteComplianceRequirement(string $id): bool
    {
        $requirement = ComplianceRequirement::find($id);
        if (!$requirement) {
            return false;
        }

        return $requirement->delete();
    }

    public function getOverdueComplianceRequirements()
    {
        return ComplianceRequirement::overdue()->highPriority()->get();
    }

    public function getAccreditationStandards(array $filters = [])
    {
        $query = AccreditationStandard::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['accreditation_body'])) {
            $query->where('accreditation_body', 'like', '%' . $filters['accreditation_body'] . '%');
        }

        return $query->with('coordinator')->get();
    }

    public function createAccreditationStandard(array $data): AccreditationStandard
    {
        return AccreditationStandard::create($data);
    }

    public function updateAccreditationStandard(string $id, array $data): ?AccreditationStandard
    {
        $standard = AccreditationStandard::find($id);
        if (!$standard) {
            return null;
        }

        $standard->update($data);
        return $standard;
    }

    public function getPolicies(array $filters = [])
    {
        $query = PolicyAndProcedure::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        return $query->with(['author', 'approver'])->get();
    }

    public function createPolicy(array $data): PolicyAndProcedure
    {
        return PolicyAndProcedure::create($data);
    }

    public function updatePolicy(string $id, array $data): ?PolicyAndProcedure
    {
        $policy = PolicyAndProcedure::find($id);
        if (!$policy) {
            return null;
        }

        $policy->update($data);
        return $policy;
    }

    public function getStaffEvaluations(array $filters = [])
    {
        $query = StaffEvaluation::query();

        if (isset($filters['staff_id'])) {
            $query->where('staff_id', $filters['staff_id']);
        }
        if (isset($filters['evaluation_type'])) {
            $query->where('evaluation_type', $filters['evaluation_type']);
        }
        if (isset($filters['academic_year'])) {
            $query->where('academic_year', $filters['academic_year']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->with(['staff', 'evaluator', 'reviewer'])->get();
    }

    public function createStaffEvaluation(array $data): StaffEvaluation
    {
        return StaffEvaluation::create($data);
    }

    public function updateStaffEvaluation(string $id, array $data): ?StaffEvaluation
    {
        $evaluation = StaffEvaluation::find($id);
        if (!$evaluation) {
            return null;
        }

        $evaluation->update($data);
        return $evaluation;
    }

    public function getProfessionalDevelopment(array $filters = [])
    {
        $query = ProfessionalDevelopment::query();

        if (isset($filters['staff_id'])) {
            $query->where('staff_id', $filters['staff_id']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['internal'])) {
            if ($filters['internal']) {
                $query->internal();
            } else {
                $query->external();
            }
        }

        return $query->with('staff')->get();
    }

    public function createProfessionalDevelopment(array $data): ProfessionalDevelopment
    {
        return ProfessionalDevelopment::create($data);
    }

    public function updateProfessionalDevelopment(string $id, array $data): ?ProfessionalDevelopment
    {
        $development = ProfessionalDevelopment::find($id);
        if (!$development) {
            return null;
        }

        $development->update($data);
        return $development;
    }

    public function getBudgetAllocations(array $filters = [])
    {
        $query = BudgetAllocation::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['academic_year'])) {
            $query->where('academic_year', $filters['academic_year']);
        }
        if (isset($filters['department'])) {
            $query->where('department', $filters['department']);
        }

        return $query->with(['manager', 'expenses'])->get();
    }

    public function createBudgetAllocation(array $data): BudgetAllocation
    {
        $data['remaining_amount'] = $data['allocated_amount'];
        return BudgetAllocation::create($data);
    }

    public function updateBudgetAllocation(string $id, array $data): ?BudgetAllocation
    {
        $budget = BudgetAllocation::find($id);
        if (!$budget) {
            return null;
        }

        $budget->update($data);
        return $budget;
    }

    public function getExpenses(array $filters = [])
    {
        $query = Expense::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['budget_allocation_id'])) {
            $query->where('budget_allocation_id', $filters['budget_allocation_id']);
        }
        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        return $query->with(['budgetAllocation', 'requester', 'approver'])->get();
    }

    public function createExpense(array $data): Expense
    {
        return Expense::create($data);
    }

    public function approveExpense(string $id, string $approverId): ?Expense
    {
        $expense = Expense::find($id);
        if (!$expense) {
            return null;
        }

        $expense->update([
            'status' => 'approved',
            'approver_id' => $approverId,
        ]);

        $budget = $expense->budgetAllocation;
        if ($budget) {
            $budget->update([
                'spent_amount' => $budget->spent_amount + $expense->amount,
                'remaining_amount' => $budget->remaining_amount - $expense->amount,
            ]);
        }

        return $expense;
    }

    public function rejectExpense(string $id, string $approverId, string $reason = ''): ?Expense
    {
        $expense = Expense::find($id);
        if (!$expense) {
            return null;
        }

        $expense->update([
            'status' => 'rejected',
            'approver_id' => $approverId,
            'justification' => $reason,
        ]);

        return $expense;
    }

    public function getInventoryItems(array $filters = [])
    {
        $query = InventoryItem::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->with('responsibleStaff')->get();
    }

    public function createInventoryItem(array $data): InventoryItem
    {
        return InventoryItem::create($data);
    }

    public function updateInventoryItem(string $id, array $data): ?InventoryItem
    {
        $item = InventoryItem::find($id);
        if (!$item) {
            return null;
        }

        $item->update($data);
        return $item;
    }

    public function getLowStockItems()
    {
        return InventoryItem::lowStock()->get();
    }

    public function getItemsNeedingMaintenance()
    {
        return InventoryItem::needsMaintenance()->get();
    }

    public function getVendorContracts(array $filters = [])
    {
        $query = VendorContract::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['service_type'])) {
            $query->where('service_type', $filters['service_type']);
        }

        return $query->with('manager')->get();
    }

    public function createVendorContract(array $data): VendorContract
    {
        return VendorContract::create($data);
    }

    public function updateVendorContract(string $id, array $data): ?VendorContract
    {
        $contract = VendorContract::find($id);
        if (!$contract) {
            return null;
        }

        $contract->update($data);
        return $contract;
    }

    public function getExpiringContracts()
    {
        return VendorContract::active()->expiringSoon()->get();
    }

    public function getInstitutionalMetrics(array $filters = [])
    {
        $query = InstitutionalMetric::query();

        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        if (isset($filters['metric_type'])) {
            $query->where('metric_type', $filters['metric_type']);
        }
        if (isset($filters['academic_year'])) {
            $query->where('academic_year', $filters['academic_year']);
        }

        return $query->with('dataSourceStaff')->get();
    }

    public function createInstitutionalMetric(array $data): InstitutionalMetric
    {
        $metric = new InstitutionalMetric($data);
        $metric->trend = $metric->getTrend();
        $metric->save();
        return $metric;
    }

    public function updateInstitutionalMetric(string $id, array $data): ?InstitutionalMetric
    {
        $metric = InstitutionalMetric::find($id);
        if (!$metric) {
            return null;
        }

        $metric->update($data);
        $metric->trend = $metric->getTrend();
        $metric->save();
        return $metric;
    }

    public function getComplianceReport(array $filters = []): array
    {
        $requirements = $this->getComplianceRequirements($filters);
        $overdue = $this->getOverdueComplianceRequirements();

        return [
            'total' => $requirements->count(),
            'completed' => $requirements->where('status', 'completed')->count(),
            'in_progress' => $requirements->where('status', 'in_progress')->count(),
            'pending' => $requirements->where('status', 'pending')->count(),
            'overdue' => $overdue->count(),
            'high_priority' => $requirements->where('priority', 'high')->count(),
        ];
    }

    public function getBudgetReport(string $academicYear): array
    {
        $budgets = $this->getBudgetAllocations(['academic_year' => $academicYear]);

        return [
            'total_allocated' => $budgets->sum('allocated_amount'),
            'total_spent' => $budgets->sum('spent_amount'),
            'total_remaining' => $budgets->sum('remaining_amount'),
            'by_department' => $budgets->groupBy('department')->map(function ($group) {
                return [
                    'allocated' => $group->sum('allocated_amount'),
                    'spent' => $group->sum('spent_amount'),
                    'remaining' => $group->sum('remaining_amount'),
                ];
            }),
        ];
    }

    public function getPerformanceMetrics(string $academicYear): array
    {
        $evaluations = StaffEvaluation::where('academic_year', $academicYear)->get();
        $pdHours = ProfessionalDevelopment::completed()->where('start_date', '>=', date('Y') . '-01-01')->get()->sum('duration_hours');

        return [
            'total_evaluations' => $evaluations->count(),
            'average_score' => $evaluations->avg('overall_score'),
            'pd_hours_completed' => $pdHours,
            'pd_completion_rate' => $pdHours > 0 ? round(($evaluations->count() / $pdHours) * 100, 2) : 0,
        ];
    }
}
