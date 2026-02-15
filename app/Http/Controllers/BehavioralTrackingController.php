<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Hypervel\Http\Request;
use App\Services\BehavioralTrackingService;

class BehavioralTrackingController extends AbstractController
{
    private BehavioralTrackingService $behavioralTrackingService;

    public function __construct(
        BehavioralTrackingService $behavioralTrackingService
    ) {
        $this->behavioralTrackingService = $behavioralTrackingService;
    }

    public function logIncident(Request $request): array
    {
        $data = $request->input();

        $incident = $this->behavioralTrackingService->logIncident($data);

        return $this->json([
            'success' => true,
            'message'  => 'Behavioral incident logged successfully',
            'data'     => $incident,
        ], 201);
    }

    public function getIncidents(Request $request): array
    {
        $studentId = $request->input('student_id');
        $limit = $request->input('limit', 20);

        $incidents = $this->behavioralTrackingService->getStudentHistory($studentId, $limit);

        return $this->json([
            'success'   => true,
            'incidents' => $incidents['incidents'] ?? [],
        ]);
    }

    public function createAssessment(Request $request): array
    {
        $data = $request->input();

        $assessment = $this->behavioralTrackingService->createAssessment($data);

        return $this->json([
            'success'    => true,
            'message'     => 'Psychological assessment created successfully',
            'assessment'  => $assessment,
        ], 201);
    }

    public function getAssessments(Request $request): array
    {
        $studentId = $request->input('student_id');
        $limit = $request->input('limit', 20);

        $history = $this->behavioralTrackingService->getStudentHistory($studentId, $limit);

        return $this->json([
            'success'     => true,
            'assessments' => $history['assessments'] ?? [],
        ]);
    }

    public function scheduleSession(Request $request): array
    {
        $data = $request->input();

        $session = $this->behavioralTrackingService->scheduleSession($data);

        return $this->json([
            'success' => true,
            'message' => 'Counselor session scheduled successfully',
            'session' => $session,
        ], 201);
    }

    public function getSessions(Request $request): array
    {
        $studentId = $request->input('student_id');
        $limit = $request->input('limit', 20);

        $history = $this->behavioralTrackingService->getStudentHistory($studentId, $limit);

        return $this->json([
            'success'  => true,
            'sessions' => $history['sessions'] ?? [],
        ]);
    }

    public function recordIntervention(Request $request): array
    {
        $data = $request->input();

        $intervention = $this->behavioralTrackingService->recordIntervention($data);

        return $this->json([
            'success'     => true,
            'message'      => 'Behavioral intervention recorded successfully',
            'intervention' => $intervention,
        ], 201);
    }

    public function getInterventions(Request $request): array
    {
        $studentId = $request->input('student_id');
        $limit = $request->input('limit', 20);

        $history = $this->behavioralTrackingService->getStudentHistory($studentId, $limit);

        return $this->json([
            'success'      => true,
            'interventions' => $history['interventions'] ?? [],
        ]);
    }

    public function getTrends(Request $request): array
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $trends = $this->behavioralTrackingService->analyzeTrends($startDate, $endDate);

        return $this->json([
            'success' => true,
            'trends'  => $trends,
        ]);
    }

    public function getAtRiskStudents(Request $request): array
    {
        $thresholdDays = (int) $request->input('threshold_days', 30);

        $students = $this->behavioralTrackingService->getAtRiskStudents($thresholdDays);

        return $this->json([
            'success'  => true,
            'students' => $students,
        ]);
    }

    public function resolveIncident(Request $request): array
    {
        $incidentId = $request->input('incident_id');
        $outcome = $request->input('outcome');
        $resolvedBy = auth()->id();

        $this->behavioralTrackingService->resolveIncident($incidentId, $resolvedBy, $outcome);

        return $this->json([
            'success' => true,
            'message' => 'Incident resolved successfully',
        ]);
    }

    public function getStudentHistory(Request $request): array
    {
        $studentId = $request->input('student_id');
        $limit = (int) $request->input('limit', 20);

        $history = $this->behavioralTrackingService->getStudentHistory($studentId, $limit);

        return $this->json([
            'success'      => true,
            'incidents'    => $history['incidents'] ?? [],
            'assessments'  => $history['assessments'] ?? [],
            'sessions'      => $history['sessions'] ?? [],
            'interventions' => $history['interventions'] ?? [],
        ]);
    }
}
