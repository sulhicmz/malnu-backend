<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Behavior\BehaviorIncident;
use App\Models\Behavior\BehaviorCategory;
use App\Models\Behavior\DisciplineAction;
use App\Models\Behavior\InterventionPlan;
use App\Models\Behavior\BehaviorNote;
use App\Models\SchoolManagement\Student;
use App\Services\NotificationService;
use Hyperf\Di\Annotation\Inject;

class BehaviorManagementService
{
    #[Inject]
    private BehaviorIncident $incidentModel;

    #[Inject]
    private BehaviorCategory $categoryModel;

    #[Inject]
    private DisciplineAction $disciplineActionModel;

    #[Inject]
    private InterventionPlan $interventionPlanModel;

    #[Inject]
    private BehaviorNote $behaviorNoteModel;

    #[Inject]
    private Student $studentModel;

    #[Inject]
    private NotificationService $notificationService;

    public function createIncident(array $data): BehaviorIncident
    {
        $incident = $this->incidentModel::create($data);

        if ($incident->severity === 'critical' || $incident->severity === 'severe') {
            $this->notifyParents($incident);
        }

        return $incident;
    }

    public function updateIncident(string $id, array $data): BehaviorIncident
    {
        $incident = $this->incidentModel::findOrFail($id);
        $incident->update($data);
        return $incident;
    }

    public function resolveIncident(string $id, string $resolvedBy, ?string $resolutionNotes = null): BehaviorIncident
    {
        $incident = $this->incidentModel::findOrFail($id);

        $incident->update([
            'is_resolved' => true,
            'resolved_by' => $resolvedBy,
            'resolved_at' => date('Y-m-d H:i:s'),
            'resolution_notes' => $resolutionNotes,
        ]);

        return $incident;
    }

    public function createDisciplineAction(array $data): DisciplineAction
    {
        return $this->disciplineActionModel::create($data);
    }

    public function completeDisciplineAction(string $id, string $completedBy): DisciplineAction
    {
        $action = $this->disciplineActionModel::findOrFail($id);

        $action->update([
            'is_completed' => true,
            'completed_at' => date('Y-m-d H:i:s'),
        ]);

        return $action;
    }

    public function createInterventionPlan(array $data): InterventionPlan
    {
        return $this->interventionPlanModel::create($data);
    }

    public function updateInterventionPlan(string $id, array $data): InterventionPlan
    {
        $plan = $this->interventionPlanModel::findOrFail($id);
        $plan->update($data);
        return $plan;
    }

    public function activateInterventionPlan(string $id): InterventionPlan
    {
        $plan = $this->interventionPlanModel::findOrFail($id);

        $plan->update([
            'status' => 'active',
            'start_date' => date('Y-m-d'),
        ]);

        return $plan;
    }

    public function completeInterventionPlan(string $id, bool $isSuccessful, ?string $notes = null): InterventionPlan
    {
        $plan = $this->interventionPlanModel::findOrFail($id);

        $plan->update([
            'status' => 'completed',
            'is_successful' => $isSuccessful,
            'notes' => $notes,
            'end_date' => date('Y-m-d'),
        ]);

        return $plan;
    }

    public function createBehaviorNote(array $data): BehaviorNote
    {
        return $this->behaviorNoteModel::create($data);
    }

    public function createBehaviorCategory(array $data): BehaviorCategory
    {
        return $this->categoryModel::create($data);
    }

    public function getStudentBehaviorHistory(string $studentId, ?int $limit = 50): array
    {
        $incidents = $this->incidentModel::with('category', 'reportedBy')
            ->where('student_id', $studentId)
            ->orderBy('incident_date', 'desc')
            ->limit($limit)
            ->get();

        $actions = $this->disciplineActionModel::with('incident', 'assignedTo')
            ->whereHas('incident', fn ($q) => $q->where('student_id', $studentId))
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        $plans = $this->interventionPlanModel::with('student', 'assignedTo')
            ->where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return [
            'incidents' => $incidents,
            'discipline_actions' => $actions,
            'intervention_plans' => $plans,
        ];
    }

    public function getBehaviorStatistics(array $filters = []): array
    {
        $startDate = $filters['start_date'] ?? date('Y-m-01');
        $endDate = $filters['end_date'] ?? date('Y-m-t');

        $incidents = $this->incidentModel::whereBetween('incident_date', [$startDate, $endDate]);

        if (isset($filters['severity'])) {
            $incidents->where('severity', $filters['severity']);
        }

        if (isset($filters['category_id'])) {
            $incidents->where('behavior_category_id', $filters['category_id']);
        }

        $totalIncidents = $incidents->count();
        $resolvedIncidents = (clone $incidents)->where('is_resolved', true)->count();
        $severeIncidents = (clone $incidents)->whereIn('severity', ['severe', 'critical'])->count();
        $minorIncidents = (clone $incidents)->where('severity', 'minor')->count();

        $actions = $this->disciplineActionModel::whereBetween('created_at', [$startDate, $endDate]);
        $totalActions = $actions->count();
        $completedActions = (clone $actions)->where('is_completed', true)->count();

        return [
            'total_incidents' => $totalIncidents,
            'resolved_incidents' => $resolvedIncidents,
            'resolution_rate' => $totalIncidents > 0 ? round(($resolvedIncidents / $totalIncidents) * 100, 2) : 0,
            'severe_incidents' => $severeIncidents,
            'minor_incidents' => $minorIncidents,
            'total_actions' => $totalActions,
            'completed_actions' => $completedActions,
            'action_completion_rate' => $totalActions > 0 ? round(($completedActions / $totalActions) * 100, 2) : 0,
        ];
    }

    private function notifyParents(BehaviorIncident $incident): void
    {
        $student = $this->studentModel::find($incident->student_id);

        if (!$student) {
            return;
        }

        $this->notificationService->create([
            'title' => 'Behavior Incident Reported',
            'message' => sprintf(
                'A behavior incident has been reported for student %s on %s',
                $student->name ?? 'Unknown',
                $incident->incident_date
            ),
            'type' => 'behavior',
            'priority' => $incident->severity === 'critical' ? 'critical' : 'high',
            'data' => json_encode([
                'incident_id' => $incident->id,
                'student_id' => $incident->student_id,
                'severity' => $incident->severity,
            ]),
        ]);
    }
}
