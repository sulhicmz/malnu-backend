<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Services\BehaviorManagementService;
use App\Http\Controllers\Api\BaseController;
use Psr\Http\Message\ResponseInterface as Response;

class BehaviorManagementController extends BaseController
{
    private BehaviorManagementService $behaviorService;

    public function __construct(BehaviorManagementService $behaviorService)
    {
        $this->behaviorService = $behaviorService;
    }

    public function createIncident(Response $response): Response
    {
        $data = $response->getParsedBody();

        $validated = $this->validate($data, [
            'student_id' => 'required|string',
            'category_id' => 'required|string',
            'title' => 'required|string|max:200',
            'description' => 'required|string',
            'incident_date' => 'required|date',
            'incident_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:100',
            'severity' => 'required|in:low,medium,high',
            'evidence' => 'nullable|string',
        ]);

        $incident = $this->behaviorService->createIncident($validated);

        return $this->successResponse($incident->load(['student', 'category', 'reportedBy']), 'Incident reported successfully', 201);
    }

    public function getIncidents(Response $response, string $studentId = null): Response
    {
        $filters = [];

        if ($studentId) {
            $filters['student_id'] = $studentId;
        }

        if ($response->hasQueryParam('category_id')) {
            $filters['category_id'] = $response->getQueryParam('category_id');
        }

        if ($response->hasQueryParam('date_from')) {
            $filters['date_from'] = $response->getQueryParam('date_from');
        }

        if ($response->hasQueryParam('date_to')) {
            $filters['date_to'] = $response->getQueryParam('date_to');
        }

        if ($response->hasQueryParam('severity')) {
            $filters['severity'] = $response->getQueryParam('severity');
        }

        if ($response->hasQueryParam('status')) {
            $filters['status'] = $response->getQueryParam('status');
        }

        $incidents = $this->behaviorService->getIncidents($filters);

        return $this->successResponse($incidents, 'Incidents retrieved successfully');
    }

    public function getIncident(Response $response, string $id): Response
    {
        $incident = Incident::findOrFail($id);

        return $this->successResponse($incident->load(['student', 'category', 'reportedBy', 'disciplineActions']), 'Incident retrieved successfully');
    }

    public function updateIncident(Response $response, string $id): Response
    {
        $data = $response->getParsedBody();

        $validated = $this->validate($data, [
            'title' => 'nullable|string|max:200',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:100',
            'severity' => 'nullable|in:low,medium,high',
            'status' => 'nullable|in:reported,under_investigation,resolved,closed',
            'evidence' => 'nullable|string',
        ]);

        $incident = $this->behaviorService->updateIncident($id, $validated);

        return $this->successResponse($incident->load(['student', 'category', 'reportedBy']), 'Incident updated successfully');
    }

    public function createDisciplineAction(Response $response, string $incidentId): Response
    {
        $data = $response->getParsedBody();

        $validated = $this->validate($data, [
            'assigned_to' => 'required|string',
            'action_type' => 'required|string|max:100',
            'description' => 'required|string',
            'action_date' => 'required|date',
        ]);

        $data['incident_id'] = $incidentId;
        $data['assigned_by'] = $this->auth->id();

        $action = $this->behaviorService->createDisciplineAction($validated);

        return $this->successResponse($action->load(['incident', 'assignedBy']), 'Disciplinary action created successfully', 201);
    }

    public function getDisciplineActions(Response $response, string $incidentId): Response
    {
        $actions = DisciplineAction::byIncident($incidentId)->with(['incident', 'assignedBy'])->get();

        return $this->successResponse($actions, 'Disciplinary actions retrieved successfully');
    }

    public function updateDisciplineAction(Response $response, string $id): Response
    {
        $data = $response->getParsedBody();

        $validated = $this->validate($data, [
            'action_type' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'action_date' => 'nullable|date',
            'status' => 'nullable|in:pending,in_progress,completed,cancelled',
            'outcome' => 'nullable|string',
        ]);

        $action = $this->behaviorService->updateDisciplineAction($id, $validated);

        return $this->successResponse($action->load(['incident', 'assignedBy']), 'Disciplinary action updated successfully');
    }

    public function createInterventionPlan(Response $response): Response
    {
        $data = $response->getParsedBody();

        $validated = $this->validate($data, [
            'student_id' => 'required|string',
            'incident_id' => 'nullable|string',
            'goals' => 'required|string',
            'strategies' => 'required|string',
            'timeline' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        $data['created_by'] = $this->auth->id();

        $plan = $this->behaviorService->createInterventionPlan($validated);

        return $this->successResponse($plan->load(['student', 'incident', 'createdBy']), 'Intervention plan created successfully', 201);
    }

    public function getInterventionPlans(Response $response, string $studentId = null): Response
    {
        $plans = $studentId 
            ? InterventionPlan::byStudent($studentId)->get()
            : InterventionPlan::with(['student', 'incident'])->get();

        return $this->successResponse($plans, 'Intervention plans retrieved successfully');
    }

    public function updateInterventionPlan(Response $response, string $id): Response
    {
        $data = $response->getParsedBody();

        $validated = $this->validate($data, [
            'goals' => 'nullable|string',
            'strategies' => 'nullable|string',
            'timeline' => 'nullable|string',
            'end_date' => 'nullable|date',
            'status' => 'nullable|in:active,completed,cancelled',
            'evaluation' => 'nullable|string',
        ]);

        $plan = $this->behaviorService->updateInterventionPlan($id, $validated);

        return $this->successResponse($plan->load(['student', 'incident', 'createdBy']), 'Intervention plan updated successfully');
    }

    public function createBehaviorNote(Response $response): Response
    {
        $data = $response->getParsedBody();

        $validated = $this->validate($data, [
            'student_id' => 'required|string',
            'note_type' => 'nullable|in:observation,positive_incident,improvement',
            'content' => 'required|string',
            'note_date' => 'required|date',
            'is_positive' => 'nullable|boolean',
        ]);

        $data['noted_by'] = $this->auth->id();

        $note = $this->behaviorService->createBehaviorNote($validated);

        return $this->successResponse($note->load(['student', 'notedBy']), 'Behavior note created successfully', 201);
    }

    public function getBehaviorNotes(Response $response, string $studentId = null): Response
    {
        $notes = $studentId 
            ? BehaviorNote::byStudent($studentId)->with(['student', 'notedBy'])->get()
            : BehaviorNote::with(['student', 'notedBy'])->get();

        return $this->successResponse($notes, 'Behavior notes retrieved successfully');
    }

    public function generateBehaviorReport(Response $response): Response
    {
        $filters = [];

        if ($response->hasQueryParam('student_id')) {
            $filters['student_id'] = $response->getQueryParam('student_id');
        }

        if ($response->hasQueryParam('date_from')) {
            $filters['date_from'] = $response->getQueryParam('date_from');
        }

        if ($response->hasQueryParam('date_to')) {
            $filters['date_to'] = $response->getQueryParam('date_to');
        }

        if ($response->hasQueryParam('category_id')) {
            $filters['category_id'] = $response->getQueryParam('category_id');
        }

        $report = $this->behaviorService->generateBehaviorReport($filters);

        return $this->successResponse($report, 'Behavior report generated successfully');
    }

    public function getBehaviorCategories(Response $response): Response
    {
        $categories = BehaviorCategory::orderBy('name')->get();

        return $this->successResponse($categories, 'Behavior categories retrieved successfully');
    }

    public function getStudentBehaviorHistory(Response $response, string $studentId): Response
    {
        $history = $this->behaviorService->getStudentBehaviorHistory($studentId);

        return $this->successResponse($history, 'Student behavior history retrieved successfully');
    }
}
