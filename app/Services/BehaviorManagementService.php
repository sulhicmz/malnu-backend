<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Behavior\Incident;
use App\Models\Behavior\DisciplineAction;
use App\Models\Behavior\InterventionPlan;
use App\Models\Behavior\BehaviorNote;
use App\Models\Behavior\BehaviorCategory;
use App\Models\SchoolManagement\Student;
use App\Models\User;

class BehaviorManagementService
{
    public function createIncident(array $data): Incident
    {
        return Incident::create([
            'student_id' => $data['student_id'],
            'category_id' => $data['category_id'],
            'reported_by' => $data['reported_by'],
            'title' => $data['title'],
            'description' => $data['description'],
            'incident_date' => $data['incident_date'],
            'incident_time' => $data['incident_time'],
            'location' => $data['location'],
            'severity' => $data['severity'] ?? 'low',
            'status' => 'reported',
            'evidence' => $data['evidence'] ?? null,
        ]);
    }

    public function updateIncident(string $incidentId, array $data): Incident
    {
        $incident = Incident::findOrFail($incidentId);

        $incident->update([
            'title' => $data['title'] ?? $incident->title,
            'description' => $data['description'] ?? $incident->description,
            'location' => $data['location'] ?? $incident->location,
            'severity' => $data['severity'] ?? $incident->severity,
            'status' => $data['status'] ?? $incident->status,
            'evidence' => $data['evidence'] ?? $incident->evidence,
        ]);

        return $incident;
    }

    public function createDisciplineAction(array $data): DisciplineAction
    {
        return DisciplineAction::create([
            'incident_id' => $data['incident_id'],
            'assigned_by' => $data['assigned_by'],
            'action_type' => $data['action_type'],
            'description' => $data['description'],
            'action_date' => $data['action_date'],
            'status' => 'pending',
            'outcome' => null,
        ]);
    }

    public function updateDisciplineAction(string $actionId, array $data): DisciplineAction
    {
        $action = DisciplineAction::findOrFail($actionId);

        $action->update([
            'action_type' => $data['action_type'] ?? $action->action_type,
            'description' => $data['description'] ?? $action->description,
            'action_date' => $data['action_date'] ?? $action->action_date,
            'status' => $data['status'] ?? $action->status,
            'outcome' => $data['outcome'] ?? $action->outcome,
        ]);

        return $action;
    }

    public function createInterventionPlan(array $data): InterventionPlan
    {
        return InterventionPlan::create([
            'student_id' => $data['student_id'],
            'incident_id' => $data['incident_id'] ?? null,
            'created_by' => $data['created_by'],
            'goals' => $data['goals'],
            'strategies' => $data['strategies'],
            'timeline' => $data['timeline'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => 'active',
            'evaluation' => null,
        ]);
    }

    public function updateInterventionPlan(string $planId, array $data): InterventionPlan
    {
        $plan = InterventionPlan::findOrFail($planId);

        $plan->update([
            'goals' => $data['goals'] ?? $plan->goals,
            'strategies' => $data['strategies'] ?? $plan->strategies,
            'timeline' => $data['timeline'] ?? $plan->timeline,
            'end_date' => $data['end_date'] ?? $plan->end_date,
            'status' => $data['status'] ?? $plan->status,
            'evaluation' => $data['evaluation'] ?? $plan->evaluation,
        ]);

        return $plan;
    }

    public function createBehaviorNote(array $data): BehaviorNote
    {
        return BehaviorNote::create([
            'student_id' => $data['student_id'],
            'noted_by' => $data['noted_by'],
            'note_type' => $data['note_type'] ?? 'observation',
            'content' => $data['content'],
            'note_date' => $data['note_date'],
            'is_positive' => $data['is_positive'] ?? false,
        ]);
    }

    public function getStudentBehaviorHistory(string $studentId): array
    {
        $incidents = Incident::byStudent($studentId)->get();
        $disciplineActions = DisciplineAction::with('incident')->whereIn('incident_id', $incidents->pluck('id'))->get();
        $interventionPlans = InterventionPlan::byStudent($studentId)->get();
        $behaviorNotes = BehaviorNote::byStudent($studentId)->get();

        return [
            'incidents' => $incidents,
            'discipline_actions' => $disciplineActions,
            'intervention_plans' => $interventionPlans,
            'behavior_notes' => $behaviorNotes,
        ];
    }

    public function getIncidents(array $filters = []): array
    {
        $query = Incident::query();

        if (isset($filters['student_id'])) {
            $query = $query->byStudent($filters['student_id']);
        }

        if (isset($filters['category_id'])) {
            $query = $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['date_from'])) {
            $query = $query->whereDate('incident_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query = $query->whereDate('incident_date', '<=', $filters['date_to']);
        }

        if (isset($filters['severity'])) {
            $query = $query->bySeverity($filters['severity']);
        }

        if (isset($filters['status'])) {
            $query = $query->byStatus($filters['status']);
        }

        return $query->with(['student', 'category', 'reportedBy'])->orderBy('incident_date', 'desc')->get();
    }

    public function generateBehaviorReport(array $filters = []): array
    {
        $incidents = $this->getIncidents($filters);

        $byCategory = [];
        foreach ($incidents as $incident) {
            $category = $incident->category->name ?? 'uncategorized';
            if (!isset($byCategory[$category])) {
                $byCategory[$category] = 0;
            }
            $byCategory[$category]++;
        }

        $bySeverity = [];
        foreach ($incidents as $incident) {
            $severity = $incident->severity;
            if (!isset($bySeverity[$severity])) {
                $bySeverity[$severity] = 0;
            }
            $bySeverity[$severity]++;
        }

        $byStatus = [];
        foreach ($incidents as $incident) {
            $status = $incident->status;
            if (!isset($byStatus[$status])) {
                $byStatus[$status] = 0;
            }
            $byStatus[$status]++;
        }

        return [
            'total_incidents' => count($incidents),
            'by_category' => $byCategory,
            'by_severity' => $bySeverity,
            'by_status' => $byStatus,
            'recent_incidents' => $incidents->take(10),
        ];
    }
}
