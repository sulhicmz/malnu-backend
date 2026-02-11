<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Compliance\ComplianceAudit;
use App\Models\Compliance\CompliancePolicy;
use App\Models\Compliance\CompliancePolicyAcknowledgment;
use App\Models\Compliance\ComplianceReport;
use App\Models\Compliance\ComplianceRisk;
use App\Models\Compliance\ComplianceTraining;
use App\Models\Compliance\ComplianceTrainingCompletion;
use App\Models\Compliance\DataBreachIncident;
use App\Models\User;
use Exception;
use Hyperf\Context\ApplicationContext;
use Ramsey\Uuid\Uuid;

/**
 * ComplianceService.
 *
 * Core service for compliance management functionality.
 * Handles policies, training, audits, risks, incidents, and reports.
 */
class ComplianceService
{
    protected $container;

    public function __construct()
    {
        $this->container = ApplicationContext::getContainer();
    }

    public function createPolicy(array $data): CompliancePolicy
    {
        $data['id'] = Uuid::uuid4()->toString();
        $data['version'] = 1;
        $data['status'] = 'active';
        $data['created_by'] = $this->getCurrentUserId();

        return CompliancePolicy::create($data);
    }

    public function updatePolicy(string $policyId, array $data): CompliancePolicy
    {
        $policy = CompliancePolicy::findOrFail($policyId);

        if (isset($data['status']) && $data['status'] === 'superseded') {
            $data['superseded_by'] = $this->getCurrentUserId();
            $data['superseded_at'] = now();
        }

        $policy->update($data);
        return $policy->fresh();
    }

    public function acknowledgePolicy(string $policyId, string $userId, ?string $ip = null, ?string $device = null): CompliancePolicyAcknowledgment
    {
        $policy = CompliancePolicy::findOrFail($policyId);

        return CompliancePolicyAcknowledgment::create([
            'id' => Uuid::uuid4()->toString(),
            'policy_id' => $policyId,
            'user_id' => $userId,
            'acknowledgment_ip' => $ip,
            'acknowledgment_device' => $device,
        ]);
    }

    public function createTraining(array $data): ComplianceTraining
    {
        $data['id'] = Uuid::uuid4()->toString();
        $data['status'] = 'active';
        $data['created_by'] = $this->getCurrentUserId();

        return ComplianceTraining::create($data);
    }

    public function completeTraining(string $trainingId, string $userId, array $data): ComplianceTrainingCompletion
    {
        $training = ComplianceTraining::findOrFail($trainingId);

        return ComplianceTrainingCompletion::create([
            'id' => Uuid::uuid4()->toString(),
            'training_id' => $trainingId,
            'user_id' => $userId,
            'score' => $data['score'] ?? null,
            'passed' => $data['passed'] ?? true,
            'completion_ip' => $data['ip'] ?? null,
            'completion_device' => $data['device'] ?? null,
        ]);
    }

    public function createAudit(array $data): ComplianceAudit
    {
        return ComplianceAudit::create([
            'id' => Uuid::uuid4()->toString(),
            'user_id' => $data['user_id'] ?? $this->getCurrentUserId(),
            'action_type' => $data['action_type'],
            'entity_type' => $data['entity_type'] ?? null,
            'entity_id' => $data['entity_id'] ?? null,
            'description' => $data['description'] ?? null,
            'old_values' => $data['old_values'] ?? null,
            'new_values' => $data['new_values'] ?? null,
            'ip_address' => $data['ip'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
            'request_method' => $data['request_method'] ?? null,
            'request_path' => $data['request_path'] ?? null,
            'compliance_tags' => $data['compliance_tags'] ?? [],
            'severity' => $data['severity'] ?? 'low',
        ]);
    }

    public function createReport(array $data): ComplianceReport
    {
        $data['id'] = Uuid::uuid4()->toString();
        $data['status'] = 'draft';
        $data['generated_by'] = $this->getCurrentUserId();

        return ComplianceReport::create($data);
    }

    public function submitReport(string $reportId, string $submittedTo): ComplianceReport
    {
        $report = ComplianceReport::findOrFail($reportId);

        $report->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'submitted_to' => $submittedTo,
        ]);

        return $report->fresh();
    }

    public function createIncident(array $data): DataBreachIncident
    {
        $data['id'] = Uuid::uuid4()->toString();
        $data['status'] = 'open';
        $data['reported_at'] = now();
        $data['reported_by'] = $this->getCurrentUserId();

        return DataBreachIncident::create($data);
    }

    public function updateIncident(string $incidentId, array $data): DataBreachIncident
    {
        $incident = DataBreachIncident::findOrFail($incidentId);
        $incident->update($data);
        return $incident->fresh();
    }

    public function createRisk(array $data): ComplianceRisk
    {
        $data['id'] = Uuid::uuid4()->toString();
        $data['status'] = 'open';
        $data['mitigation_status'] = 'not_started';
        $data['identified_by'] = $this->getCurrentUserId();

        if (isset($data['likelihood'], $data['impact'])) {
            $risk = new ComplianceRisk();
            $risk->likelihood = $data['likelihood'];
            $risk->impact = $data['impact'];
            $risk->calculateRiskScore();
            $data['risk_score'] = $risk->risk_score;
        }

        return ComplianceRisk::create($data);
    }

    public function updateRisk(string $riskId, array $data): ComplianceRisk
    {
        $risk = ComplianceRisk::findOrFail($riskId);

        if (isset($data['likelihood']) || isset($data['impact'])) {
            $likelihood = $data['likelihood'] ?? $risk->likelihood;
            $impact = $data['impact'] ?? $risk->impact;

            $risk->likelihood = $likelihood;
            $risk->impact = $impact;
            $risk->calculateRiskScore();
            $data['risk_score'] = $risk->risk_score;
        }

        $risk->update($data);
        return $risk->fresh();
    }

    public function getUserPendingPolicies(string $userId): array
    {
        $activePolicies = CompliancePolicy::active()->get();

        return $activePolicies->filter(function ($policy) use ($userId) {
            return ! $policy->isAcknowledgedByUser($userId);
        })->values()->toArray();
    }

    public function getUserPendingTraining(string $userId, ?User $user = null): array
    {
        $activeTraining = ComplianceTraining::active()->get();

        return $activeTraining->filter(function ($training) use ($userId, $user) {
            $existing = ComplianceTrainingCompletion::where('training_id', $training->id)
                ->where('user_id', $userId)
                ->exists();

            return ! $existing && $training->isRequiredForUser($user);
        })->values()->toArray();
    }

    public function getDashboardData(): array
    {
        return [
            'active_policies' => CompliancePolicy::active()->count(),
            'pending_acknowledgments' => CompliancePolicy::active()->get()->sum(function ($policy) {
                return max(0, $policy->completionCount() - $policy->acknowledgmentCount());
            }),
            'active_training' => ComplianceTraining::active()->count(),
            'recent_audits' => ComplianceAudit::recent(7)->count(),
            'open_risks' => ComplianceRisk::open()->count(),
            'high_priority_risks' => ComplianceRisk::highPriority()->count(),
            'open_incidents' => DataBreachIncident::open()->count(),
            'critical_incidents' => DataBreachIncident::where('severity', 'critical')
                ->where('status', '!=', 'closed')->count(),
            'reports_submitted_this_month' => ComplianceReport::where('status', 'submitted')
                ->where('submitted_at', '>=', now()->startOfMonth())
                ->count(),
        ];
    }

    public function getComplianceScore(): array
    {
        $totalPolicies = CompliancePolicy::active()->count();
        $totalAcknowledgments = CompliancePolicyAcknowledgment::count();

        $totalTraining = ComplianceTraining::active()->count();
        $totalCompletions = ComplianceTrainingCompletion::count();

        $policyCompliance = $totalPolicies > 0
            ? ($totalAcknowledgments / $totalPolicies) * 100
            : 100;

        $trainingCompliance = $totalTraining > 0
            ? ($totalCompletions / $totalTraining) * 100
            : 100;

        $openRisks = ComplianceRisk::open()->count();
        $criticalRisks = ComplianceRisk::highPriority()
            ->where('mitigation_status', '!=', 'completed')
            ->count();

        $riskDeduction = min(25, ($criticalRisks * 5) + ($openRisks * 2));

        $overallScore = (($policyCompliance * 0.4) + ($trainingCompliance * 0.4))
            - $riskDeduction;

        return [
            'overall_score' => max(0, min(100, $overallScore)),
            'policy_compliance' => $policyCompliance,
            'training_compliance' => $trainingCompliance,
            'open_risks_count' => $openRisks,
            'critical_risks_count' => $criticalRisks,
            'total_policies' => $totalPolicies,
            'total_training' => $totalTraining,
        ];
    }

    protected function getCurrentUserId(): ?string
    {
        try {
            $request = ApplicationContext::getContainer()->get('request');
            return $request->getAttribute('user_id');
        } catch (Exception $e) {
            return null;
        }
    }
}
