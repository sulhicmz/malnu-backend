<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Services\ComplianceService;
use Hypervel\Support\Annotation\Inject;

/**
 * ComplianceController.
 *
 * API controller for compliance management.
 * Provides endpoints for policies, training, audits, risks, incidents, and reports.
 */
class ComplianceController extends BaseController
{
    #[Inject]
    protected ComplianceService $complianceService;

    /**
     * Get compliance dashboard data.
     */
    public function dashboard()
    {
        return $this->successResponse($this->complianceService->getDashboardData());
    }

    /**
     * Get compliance score.
     */
    public function getComplianceScore()
    {
        return $this->successResponse($this->complianceService->getComplianceScore());
    }

    /**
     * Get all policies.
     */
    public function getPolicies()
    {
        $request = $this->request();
        $category = $request->input('category');
        $status = $request->input('status', 'active');

        $query = \App\Models\Compliance\CompliancePolicy::query();

        if ($category) {
            $query->where('category', $category);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $policies = $query->orderBy('created_at', 'desc')->paginate(15);

        return $this->successResponse($policies);
    }

    /**
     * Get single policy.
     */
    public function getPolicy(string $id)
    {
        $policy = \App\Models\Compliance\CompliancePolicy::findOrFail($id);
        return $this->successResponse($policy);
    }

    /**
     * Create new policy.
     */
    public function createPolicy()
    {
        $data = $this->validate($this->request(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'required|string',
            'category' => 'required|string|in:FERPA,GDPR,CCPA,CIPA,IDEA,General',
            'effective_date' => 'required|date',
            'expiry_date' => 'nullable|date',
        ]);

        $policy = $this->complianceService->createPolicy($data);

        return $this->successResponse($policy, 'Policy created successfully', 201);
    }

    /**
     * Update policy.
     */
    public function updatePolicy(string $id)
    {
        $data = $this->validate($this->request(), [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'effective_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'status' => 'nullable|in:active,superseded,retired',
        ]);

        $policy = $this->complianceService->updatePolicy($id, $data);

        return $this->successResponse($policy, 'Policy updated successfully');
    }

    /**
     * Acknowledge policy.
     */
    public function acknowledgePolicy(string $id)
    {
        $userId = $this->getCurrentUserId();

        $acknowledgment = $this->complianceService->acknowledgePolicy(
            $id,
            $userId,
            $this->request()->ip(),
            $this->request()->header('User-Agent')
        );

        return $this->successResponse($acknowledgment, 'Policy acknowledged successfully');
    }

    /**
     * Get my pending policies.
     */
    public function getMyPendingPolicies()
    {
        $userId = $this->getCurrentUserId();
        $pendingPolicies = $this->complianceService->getUserPendingPolicies($userId);

        return $this->successResponse($pendingPolicies);
    }

    /**
     * Get all training.
     */
    public function getTraining()
    {
        $request = $this->request();
        $type = $request->input('training_type');
        $status = $request->input('status', 'active');

        $query = \App\Models\Compliance\ComplianceTraining::query();

        if ($type) {
            $query->where('training_type', $type);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $training = $query->orderBy('created_at', 'desc')->paginate(15);

        return $this->successResponse($training);
    }

    /**
     * Get single training.
     */
    public function getTrainingItem(string $id)
    {
        $training = \App\Models\Compliance\ComplianceTraining::findOrFail($id);
        return $this->successResponse($training);
    }

    /**
     * Create new training.
     */
    public function createTraining()
    {
        $data = $this->validate($this->request(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'required|string',
            'training_type' => 'required|string|in:FERPA,GDPR,Security,Privacy,General',
            'duration_minutes' => 'required|integer|min:1',
            'category' => 'required|string',
            'required_for_roles' => 'nullable|array',
            'required_for_all' => 'nullable|boolean',
            'valid_from' => 'required|date',
            'valid_until' => 'nullable|date',
        ]);

        $training = $this->complianceService->createTraining($data);

        return $this->successResponse($training, 'Training created successfully', 201);
    }

    /**
     * Update training.
     */
    public function updateTraining(string $id)
    {
        $data = $this->validate($this->request(), [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'duration_minutes' => 'nullable|integer|min:1',
            'category' => 'nullable|string',
            'required_for_roles' => 'nullable|array',
            'required_for_all' => 'nullable|boolean',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date',
            'status' => 'nullable|in:active,inactive,archived',
        ]);

        $training = \App\Models\Compliance\ComplianceTraining::findOrFail($id);
        $training->update($data);

        return $this->successResponse($training->fresh(), 'Training updated successfully');
    }

    /**
     * Complete training.
     */
    public function completeTraining(string $id)
    {
        $data = $this->validate($this->request(), [
            'score' => 'nullable|numeric|between:0,100',
            'passed' => 'nullable|boolean',
        ]);

        $userId = $this->getCurrentUserId();
        $completion = $this->complianceService->completeTraining($id, $userId, [
            'ip' => $this->request()->ip(),
            'device' => $this->request()->header('User-Agent'),
            'score' => $data['score'] ?? null,
            'passed' => $data['passed'] ?? true,
        ]);

        return $this->successResponse($completion, 'Training completed successfully');
    }

    /**
     * Get my pending training.
     */
    public function getMyPendingTraining()
    {
        $userId = $this->getCurrentUserId();
        $user = \App\Models\User::find($userId);
        $pendingTraining = $this->complianceService->getUserPendingTraining($userId, $user);

        return $this->successResponse($pendingTraining);
    }

    /**
     * Get audits.
     */
    public function getAudits()
    {
        $request = $this->request();
        $actionType = $request->input('action_type');
        $entityType = $request->input('entity_type');
        $severity = $request->input('severity');
        $days = $request->input('days', 30);

        $query = \App\Models\Compliance\ComplianceAudit::query();

        if ($actionType) {
            $query->where('action_type', $actionType);
        }

        if ($entityType) {
            $query->where('entity_type', $entityType);
        }

        if ($severity) {
            $query->where('severity', $severity);
        }

        $audits = $query->recent((int) $days)->orderBy('created_at', 'desc')->paginate(50);

        return $this->successResponse($audits);
    }

    /**
     * Get risks.
     */
    public function getRisks()
    {
        $request = $this->request();
        $category = $request->input('risk_category');
        $priority = $request->input('mitigation_priority');
        $status = $request->input('status');

        $query = \App\Models\Compliance\ComplianceRisk::query();

        if ($category) {
            $query->where('risk_category', $category);
        }

        if ($priority) {
            $query->where('mitigation_priority', $priority);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $risks = $query->orderBy('risk_score', 'desc')->paginate(15);

        return $this->successResponse($risks);
    }

    /**
     * Get single risk.
     */
    public function getRisk(string $id)
    {
        $risk = \App\Models\Compliance\ComplianceRisk::findOrFail($id);
        return $this->successResponse($risk);
    }

    /**
     * Create new risk.
     */
    public function createRisk()
    {
        $data = $this->validate($this->request(), [
            'risk_title' => 'required|string|max:255',
            'description' => 'required|string',
            'risk_category' => 'required|string',
            'likelihood' => 'required|in:rare,unlikely,possible,likely,almost_certain',
            'impact' => 'required|in:negligible,minor,moderate,major,catastrophic',
            'affected_systems' => 'nullable|array',
            'applicable_regulations' => 'nullable|array',
            'mitigation_plan' => 'nullable|string',
            'mitigation_priority' => 'required|in:low,medium,high,critical',
            'target_mitigation_date' => 'nullable|date',
        ]);

        $risk = $this->complianceService->createRisk($data);

        return $this->successResponse($risk, 'Risk created successfully', 201);
    }

    /**
     * Update risk.
     */
    public function updateRisk(string $id)
    {
        $data = $this->validate($this->request(), [
            'risk_title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'likelihood' => 'nullable|in:rare,unlikely,possible,likely,almost_certain',
            'impact' => 'nullable|in:negligible,minor,moderate,major,catastrophic',
            'affected_systems' => 'nullable|array',
            'applicable_regulations' => 'nullable|array',
            'mitigation_plan' => 'nullable|string',
            'mitigation_priority' => 'nullable|in:low,medium,high,critical',
            'mitigation_status' => 'nullable|in:not_started,in_progress,completed,deferred',
            'target_mitigation_date' => 'nullable|date',
            'actual_mitigation_date' => 'nullable|date',
            'status' => 'nullable|in:open,in_review,mitigating,mitigated,accepted',
            'assigned_to' => 'nullable|string',
        ]);

        $risk = $this->complianceService->updateRisk($id, $data);

        return $this->successResponse($risk, 'Risk updated successfully');
    }

    /**
     * Get incidents.
     */
    public function getIncidents()
    {
        $request = $this->request();
        $severity = $request->input('severity');
        $status = $request->input('status');

        $query = \App\Models\Compliance\DataBreachIncident::query();

        if ($severity) {
            $query->where('severity', $severity);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $incidents = $query->orderBy('discovered_at', 'desc')->paginate(15);

        return $this->successResponse($incidents);
    }

    /**
     * Get single incident.
     */
    public function getIncident(string $id)
    {
        $incident = \App\Models\Compliance\DataBreachIncident::findOrFail($id);
        return $this->successResponse($incident);
    }

    /**
     * Create new incident.
     */
    public function createIncident()
    {
        $data = $this->validate($this->request(), [
            'incident_type' => 'required|string|max:100',
            'severity' => 'required|in:low,medium,high,critical',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'affected_records' => 'nullable|integer',
            'data_types_affected' => 'nullable|array',
        ]);

        $incident = $this->complianceService->createIncident($data);

        return $this->successResponse($incident, 'Incident created successfully', 201);
    }

    /**
     * Update incident.
     */
    public function updateIncident(string $id)
    {
        $data = $this->validate($this->request(), [
            'severity' => 'nullable|in:low,medium,high,critical',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'affected_records' => 'nullable|integer',
            'data_types_affected' => 'nullable|array',
            'root_cause' => 'nullable|string',
            'mitigation_actions' => 'nullable|string',
            'status' => 'nullable|in:open,investigating,mitigating,resolved,closed',
            'assigned_to' => 'nullable|string',
            'regulatory_report_required' => 'nullable|boolean',
            'regulatory_report_submitted' => 'nullable|boolean',
            'regulatory_submission_date' => 'nullable|date',
        ]);

        $incident = $this->complianceService->updateIncident($id, $data);

        return $this->successResponse($incident, 'Incident updated successfully');
    }

    /**
     * Get reports.
     */
    public function getReports()
    {
        $request = $this->request();
        $type = $request->input('report_type');
        $status = $request->input('status');

        $query = \App\Models\Compliance\ComplianceReport::query();

        if ($type) {
            $query->where('report_type', $type);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $reports = $query->orderBy('created_at', 'desc')->paginate(15);

        return $this->successResponse($reports);
    }

    /**
     * Get single report.
     */
    public function getReport(string $id)
    {
        $report = \App\Models\Compliance\ComplianceReport::findOrFail($id);
        return $this->successResponse($report);
    }

    /**
     * Create new report.
     */
    public function createReport()
    {
        $data = $this->validate($this->request(), [
            'report_type' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'report_period_start' => 'nullable|date',
            'report_period_end' => 'nullable|date',
            'report_data' => 'nullable|array',
        ]);

        $report = $this->complianceService->createReport($data);

        return $this->successResponse($report, 'Report created successfully', 201);
    }

    /**
     * Submit report.
     */
    public function submitReport(string $id)
    {
        $data = $this->validate($this->request(), [
            'submitted_to' => 'required|string|max:255',
        ]);

        $report = $this->complianceService->submitReport($id, $data['submitted_to']);

        return $this->successResponse($report, 'Report submitted successfully');
    }

    protected function getCurrentUserId(): string
    {
        $request = $this->request();
        return $request->getAttribute('user_id');
    }
}
