<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SchoolAdministrationService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class SchoolAdministrationController extends Controller
{
    private SchoolAdministrationService $service;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container,
        SchoolAdministrationService $service
    ) {
        parent::__construct($request, $response, $container);
        $this->service = $service;
    }

    public function index()
    {
        $type = $this->request->input('type', 'all');
        $category = $this->request->input('category');
        $status = $this->request->input('status');
        $academicYear = $this->request->input('academic_year');

        $data = [];

        switch ($type) {
            case 'compliance':
                $data['requirements'] = $this->service->getComplianceRequirements(['status' => $status, 'category' => $category]);
                $data['overdue'] = $this->service->getOverdueComplianceRequirements();
                break;
            case 'accreditation':
                $data['standards'] = $this->service->getAccreditationStandards(['status' => $status]);
                $data['expiring'] = $this->service->getAccreditationStandards(['status' => 'in_progress'])->expiringSoon();
                break;
            case 'policies':
                $data['policies'] = $this->service->getPolicies(['status' => $status, 'category' => $category]);
                break;
            case 'evaluations':
                $data['evaluations'] = $this->service->getStaffEvaluations(['academic_year' => $academicYear, 'status' => $status]);
                break;
            case 'professional_development':
                $data['professional_development'] = $this->service->getProfessionalDevelopment(['status' => $status]);
                break;
            case 'budget':
                $data['budgets'] = $this->service->getBudgetAllocations(['academic_year' => $academicYear, 'status' => $status]);
                break;
            case 'expenses':
                $data['expenses'] = $this->service->getExpenses(['status' => $status, 'category' => $category]);
                break;
            case 'inventory':
                $data['inventory'] = $this->service->getInventoryItems(['status' => $status, 'category' => $category]);
                $data['low_stock'] = $this->service->getLowStockItems();
                $data['needs_maintenance'] = $this->service->getItemsNeedingMaintenance();
                break;
            case 'vendors':
                $data['contracts'] = $this->service->getVendorContracts(['status' => $status]);
                $data['expiring'] = $this->service->getExpiringContracts();
                break;
            case 'metrics':
                $data['metrics'] = $this->service->getInstitutionalMetrics(['academic_year' => $academicYear, 'category' => $category]);
                break;
            default:
                $data['compliance_report'] = $this->service->getComplianceReport();
                $data['budget_report'] = $this->service->getBudgetReport(date('Y'));
                $data['performance'] = $this->service->getPerformanceMetrics(date('Y'));
        }

        return $this->successResponse($data);
    }

    public function createComplianceRequirement()
    {
        $data = $this->request->all();
        $requirement = $this->service->createComplianceRequirement($data);

        return $this->successResponse($requirement, 'Compliance requirement created successfully');
    }

    public function updateComplianceRequirement(string $id)
    {
        $data = $this->request->all();
        $requirement = $this->service->updateComplianceRequirement($id, $data);

        if (!$requirement) {
            return $this->notFoundResponse('Compliance requirement not found');
        }

        return $this->successResponse($requirement, 'Compliance requirement updated successfully');
    }

    public function deleteComplianceRequirement(string $id)
    {
        $success = $this->service->deleteComplianceRequirement($id);

        if (!$success) {
            return $this->notFoundResponse('Compliance requirement not found');
        }

        return $this->successResponse(null, 'Compliance requirement deleted successfully');
    }

    public function createPolicy()
    {
        $data = $this->request->all();
        $policy = $this->service->createPolicy($data);

        return $this->successResponse($policy, 'Policy created successfully');
    }

    public function updatePolicy(string $id)
    {
        $data = $this->request->all();
        $policy = $this->service->updatePolicy($id, $data);

        if (!$policy) {
            return $this->notFoundResponse('Policy not found');
        }

        return $this->successResponse($policy, 'Policy updated successfully');
    }

    public function createEvaluation()
    {
        $data = $this->request->all();
        $evaluation = $this->service->createStaffEvaluation($data);

        return $this->successResponse($evaluation, 'Staff evaluation created successfully');
    }

    public function updateEvaluation(string $id)
    {
        $data = $this->request->all();
        $evaluation = $this->service->updateStaffEvaluation($id, $data);

        if (!$evaluation) {
            return $this->notFoundResponse('Staff evaluation not found');
        }

        return $this->successResponse($evaluation, 'Staff evaluation updated successfully');
    }

    public function createProfessionalDevelopment()
    {
        $data = $this->request->all();
        $pd = $this->service->createProfessionalDevelopment($data);

        return $this->successResponse($pd, 'Professional development created successfully');
    }

    public function updateProfessionalDevelopment(string $id)
    {
        $data = $this->request->all();
        $pd = $this->service->updateProfessionalDevelopment($id, $data);

        if (!$pd) {
            return $this->notFoundResponse('Professional development not found');
        }

        return $this->successResponse($pd, 'Professional development updated successfully');
    }

    public function createBudgetAllocation()
    {
        $data = $this->request->all();
        $budget = $this->service->createBudgetAllocation($data);

        return $this->successResponse($budget, 'Budget allocation created successfully');
    }

    public function updateBudgetAllocation(string $id)
    {
        $data = $this->request->all();
        $budget = $this->service->updateBudgetAllocation($id, $data);

        if (!$budget) {
            return $this->notFoundResponse('Budget allocation not found');
        }

        return $this->successResponse($budget, 'Budget allocation updated successfully');
    }

    public function createExpense()
    {
        $data = $this->request->all();
        $expense = $this->service->createExpense($data);

        return $this->successResponse($expense, 'Expense created successfully');
    }

    public function approveExpense(string $id)
    {
        $approverId = auth()->id();
        $expense = $this->service->approveExpense($id, $approverId);

        if (!$expense) {
            return $this->notFoundResponse('Expense not found');
        }

        return $this->successResponse($expense, 'Expense approved successfully');
    }

    public function rejectExpense(string $id)
    {
        $approverId = auth()->id();
        $reason = $this->request->input('reason', '');

        $expense = $this->service->rejectExpense($id, $approverId, $reason);

        if (!$expense) {
            return $this->notFoundResponse('Expense not found');
        }

        return $this->successResponse($expense, 'Expense rejected successfully');
    }

    public function createInventoryItem()
    {
        $data = $this->request->all();
        $item = $this->service->createInventoryItem($data);

        return $this->successResponse($item, 'Inventory item created successfully');
    }

    public function updateInventoryItem(string $id)
    {
        $data = $this->request->all();
        $item = $this->service->updateInventoryItem($id, $data);

        if (!$item) {
            return $this->notFoundResponse('Inventory item not found');
        }

        return $this->successResponse($item, 'Inventory item updated successfully');
    }

    public function createVendorContract()
    {
        $data = $this->request->all();
        $contract = $this->service->createVendorContract($data);

        return $this->successResponse($contract, 'Vendor contract created successfully');
    }

    public function updateVendorContract(string $id)
    {
        $data = $this->request->all();
        $contract = $this->service->updateVendorContract($id, $data);

        if (!$contract) {
            return $this->notFoundResponse('Vendor contract not found');
        }

        return $this->successResponse($contract, 'Vendor contract updated successfully');
    }

    public function createMetric()
    {
        $data = $this->request->all();
        $metric = $this->service->createInstitutionalMetric($data);

        return $this->successResponse($metric, 'Institutional metric created successfully');
    }

    public function updateMetric(string $id)
    {
        $data = $this->request->all();
        $metric = $this->service->updateInstitutionalMetric($id, $data);

        if (!$metric) {
            return $this->notFoundResponse('Institutional metric not found');
        }

        return $this->successResponse($metric, 'Institutional metric updated successfully');
    }

    public function getReports()
    {
        $type = $this->request->input('type', 'all');
        $academicYear = $this->request->input('academic_year', date('Y'));

        $reports = [];

        if ($type === 'all' || $type === 'compliance') {
            $reports['compliance'] = $this->service->getComplianceReport();
        }
        if ($type === 'all' || $type === 'budget') {
            $reports['budget'] = $this->service->getBudgetReport($academicYear);
        }
        if ($type === 'all' || $type === 'performance') {
            $reports['performance'] = $this->service->getPerformanceMetrics($academicYear);
        }

        return $this->successResponse($reports, 'Reports generated successfully');
    }
}
