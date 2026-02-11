<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BehavioralTracking\BehavioralIncident;
use App\Models\BehavioralTracking\PsychologicalAssessment;
use App\Models\BehavioralTracking\CounselorSession;
use App\Models\BehavioralTracking\BehavioralIntervention;
use App\Models\SchoolManagement\Student;
use App\Models\User;

class BehavioralTrackingService
{
    public function logIncident(array $data): array
    {
        $incident = BehavioralIncident::create([
            'student_id'    => $data['student_id'],
            'reported_by'    => $data['reported_by'] ?? auth()->id(),
            'incident_type' => $data['incident_type'],
            'severity'      => $data['severity'],
            'description'    => $data['description'],
            'action_taken'   => $data['action_taken'] ?? null,
            'incident_date'  => $data['incident_date'] ?? now(),
        ]);

        return $incident->toArray();
    }

    public function createAssessment(array $data): array
    {
        $assessment = PsychologicalAssessment::create([
            'student_id'      => $data['student_id'],
            'assessed_by'     => $data['assessed_by'] ?? auth()->id(),
            'assessment_type' => $data['assessment_type'],
            'assessment_data' => $data['assessment_data'] ?? null,
            'score'           => $data['score'] ?? null,
            'max_score'       => $data['max_score'] ?? null,
            'notes'           => $data['notes'] ?? null,
            'recommendations' => $data['recommendations'] ?? null,
            'is_confidential' => $data['is_confidential'] ?? true,
            'assessment_date' => $data['assessment_date'] ?? now(),
        ]);

        return $assessment->toArray();
    }

    public function scheduleSession(array $data): array
    {
        $session = CounselorSession::create([
            'student_id'        => $data['student_id'],
            'counselor_id'       => $data['counselor_id'] ?? auth()->id(),
            'session_date'       => $data['session_date'],
            'duration_minutes'    => $data['duration_minutes'] ?? 60,
            'session_type'       => $data['session_type'],
            'session_notes'      => $data['session_notes'] ?? null,
            'observations'        => $data['observations'] ?? null,
            'follow_up_required' => $data['follow_up_required'] ?? 'pending',
            'follow_up_date'     => $data['follow_up_date'] ?? null,
            'is_private'         => $data['is_private'] ?? true,
        ]);

        return $session->toArray();
    }

    public function recordIntervention(array $data): array
    {
        $intervention = BehavioralIntervention::create([
            'incident_id'      => $data['incident_id'] ?? null,
            'student_id'       => $data['student_id'],
            'intervention_by'  => $data['intervention_by'] ?? auth()->id(),
            'intervention_type' => $data['intervention_type'],
            'description'       => $data['description'],
            'status'           => $data['status'] ?? 'planned',
            'planned_date'     => $data['planned_date'] ?? null,
            'completed_date'    => $data['completed_date'] ?? null,
            'outcome'           => $data['outcome'] ?? null,
            'parent_notified'  => $data['parent_notified'] ?? null,
            'is_effective'     => $data['is_effective'] ?? null,
        ]);

        return $intervention->toArray();
    }

    public function analyzeTrends(?string $startDate = null, ?string $endDate = null): array
    {
        $query = BehavioralIncident::query();

        if ($startDate) {
            $query->where('incident_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('incident_date', '<=', $endDate);
        }

        $incidents = $query->get();

        $severityCounts = [
            'low'      => $incidents->where('severity', 'low')->count(),
            'medium'   => $incidents->where('severity', 'medium')->count(),
            'high'     => $incidents->where('severity', 'high')->count(),
            'critical'  => $incidents->where('severity', 'critical')->count(),
        ];

        $incidentTypes = $incidents->groupBy('incident_type')
            ->map(fn ($group) => $group->count())
            ->toArray();

        return [
            'total_incidents'      => $incidents->count(),
            'resolved_incidents'   => $incidents->where('is_resolved', true)->count(),
            'unresolved_incidents' => $incidents->where('is_resolved', false)->count(),
            'severity_counts'       => $severityCounts,
            'incident_types'        => $incidentTypes,
            'period' => [
                'start' => $startDate,
                'end'   => $endDate,
            ],
        ];
    }

    public function getAtRiskStudents(int $thresholdDays = 30): array
    {
        $cutoffDate = now()->subDays($thresholdDays);

        $students = Student::with(['behavioralIncidents', 'psychologicalAssessments'])
            ->get()
            ->filter(function ($student) use ($cutoffDate) {
                $recentIncidents = $student->behavioralIncidents
                    ->where('incident_date', '>=', $cutoffDate)
                    ->where('severity', '!=', 'low')
                    ->count();

                $lowAssessments = $student->psychologicalAssessments
                    ->where('assessment_date', '>=', $cutoffDate)
                    ->where('score', '<=', function ($assessment) {
                        return $assessment->max_score * 0.4;
                    })
                    ->count();

                return $recentIncidents >= 2 || $lowAssessments >= 1;
            });

        return $students->map(function ($student) {
            $incidentCount = $student->behavioralIncidents
                ->where('incident_date', '>=', now()->subDays(30))
                ->count();

            $lastAssessment = $student->psychologicalAssessments
                ->orderBy('assessment_date', 'desc')
                ->first();

            return [
                'student_id'        => $student->id,
                'student_name'       => $student->user->name ?? null,
                'student_email'      => $student->user->email ?? null,
                'recent_incidents'  => $incidentCount,
                'last_assessment'   => $lastAssessment ? [
                    'type'   => $lastAssessment->assessment_type,
                    'date'   => $lastAssessment->assessment_date,
                    'score'  => $lastAssessment->score,
                ] : null,
                'risk_level'         => $incidentCount >= 2 ? 'high' : ($incidentCount >= 1 ? 'medium' : 'low'),
            ];
        })->toArray();
    }

    public function resolveIncident(string $incidentId, string $resolvedBy, string $outcome): bool
    {
        $incident = BehavioralIncident::find($incidentId);

        if (!$incident) {
            throw new \Exception('Incident not found');
        }

        $incident->update([
            'is_resolved' => true,
            'resolved_at'  => now(),
            'resolved_by'  => $resolvedBy,
            'action_taken' => $outcome,
        ]);

        return true;
    }

    public function getStudentHistory(string $studentId, ?int $limit = 20): array
    {
        $incidents = BehavioralIncident::where('student_id', $studentId)
            ->orderBy('incident_date', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();

        $assessments = PsychologicalAssessment::where('student_id', $studentId)
            ->orderBy('assessment_date', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();

        $sessions = CounselorSession::where('student_id', $studentId)
            ->orderBy('session_date', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();

        $interventions = BehavioralIntervention::where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();

        return [
            'incidents'    => $incidents,
            'assessments'  => $assessments,
            'sessions'      => $sessions,
            'interventions' => $interventions,
        ];
    }
}
