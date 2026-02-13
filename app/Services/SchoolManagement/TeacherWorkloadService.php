<?php

declare(strict_types=1);

namespace App\Services\SchoolManagement;

use App\Models\SchoolManagement\ClassSubject;
use App\Models\SchoolManagement\Schedule;
use App\Models\SchoolManagement\Teacher;
use App\Models\SchoolManagement\TeacherWorkload;

class TeacherWorkloadService
{
    public function createWorkload(array $data): TeacherWorkload
    {
        $workload = new TeacherWorkload();
        $workload->teacher_id = $data['teacher_id'];
        $workload->academic_year = $data['academic_year'];
        $workload->semester = $data['semester'];
        $workload->max_hours_per_week = $data['max_hours_per_week'] ?? 40;
        $workload->teaching_hours = $data['teaching_hours'] ?? 0;
        $workload->administrative_hours = $data['administrative_hours'] ?? 0;
        $workload->extracurricular_hours = $data['extracurricular_hours'] ?? 0;
        $workload->preparation_hours = $data['preparation_hours'] ?? 0;
        $workload->grading_hours = $data['grading_hours'] ?? 0;
        $workload->other_duties_hours = $data['other_duties_hours'] ?? 0;
        $workload->notes = $data['notes'] ?? null;

        $workload->total_hours_per_week = $this->calculateTotalHours($workload);
        $workload->workload_status = $this->determineWorkloadStatus($workload);

        $workload->save();

        return $workload;
    }

    public function updateWorkload(TeacherWorkload $workload, array $data): TeacherWorkload
    {
        if (isset($data['max_hours_per_week'])) {
            $workload->max_hours_per_week = $data['max_hours_per_week'];
        }
        if (isset($data['teaching_hours'])) {
            $workload->teaching_hours = $data['teaching_hours'];
        }
        if (isset($data['administrative_hours'])) {
            $workload->administrative_hours = $data['administrative_hours'];
        }
        if (isset($data['extracurricular_hours'])) {
            $workload->extracurricular_hours = $data['extracurricular_hours'];
        }
        if (isset($data['preparation_hours'])) {
            $workload->preparation_hours = $data['preparation_hours'];
        }
        if (isset($data['grading_hours'])) {
            $workload->grading_hours = $data['grading_hours'];
        }
        if (isset($data['other_duties_hours'])) {
            $workload->other_duties_hours = $data['other_duties_hours'];
        }
        if (isset($data['notes'])) {
            $workload->notes = $data['notes'];
        }

        $workload->total_hours_per_week = $this->calculateTotalHours($workload);
        $workload->workload_status = $this->determineWorkloadStatus($workload);

        $workload->save();

        return $workload;
    }

    public function calculateFromSchedule(string $teacherId, string $academicYear, string $semester): TeacherWorkload
    {
        $teacher = Teacher::find($teacherId);
        if (! $teacher) {
            throw new \InvalidArgumentException('Teacher not found');
        }

        $classSubjects = ClassSubject::where('teacher_id', $teacherId)->get();
        $classSubjectIds = $classSubjects->pluck('id')->toArray();

        $schedules = Schedule::whereIn('class_subject_id', $classSubjectIds)->get();

        $totalTeachingMinutes = 0;
        foreach ($schedules as $schedule) {
            $startTime = strtotime($schedule->start_time);
            $endTime = strtotime($schedule->end_time);
            $durationMinutes = ($endTime - $startTime) / 60;
            $totalTeachingMinutes += $durationMinutes;
        }

        $teachingHours = round($totalTeachingMinutes / 60, 2);

        $workload = TeacherWorkload::where('teacher_id', $teacherId)
            ->where('academic_year', $academicYear)
            ->where('semester', $semester)
            ->first();

        if (! $workload) {
            $workload = new TeacherWorkload();
            $workload->teacher_id = $teacherId;
            $workload->academic_year = $academicYear;
            $workload->semester = $semester;
            $workload->max_hours_per_week = 40;
        }

        $workload->teaching_hours = $teachingHours;
        $workload->preparation_hours = round($teachingHours * 0.5, 2);
        $workload->grading_hours = round($teachingHours * 0.3, 2);

        $workload->total_hours_per_week = $this->calculateTotalHours($workload);
        $workload->workload_status = $this->determineWorkloadStatus($workload);

        $workload->save();

        return $workload;
    }

    public function getWorkloadSummary(string $academicYear, string $semester): array
    {
        $workloads = TeacherWorkload::where('academic_year', $academicYear)
            ->where('semester', $semester)
            ->get();

        $totalTeachers = $workloads->count();
        $overloadedCount = $workloads->filter(fn ($w) => $w->isOverloaded())->count();
        $underloadedCount = $workloads->filter(fn ($w) => $w->isUnderloaded())->count();
        $normalCount = $totalTeachers - $overloadedCount - $underloadedCount;

        $averageHours = $totalTeachers > 0 ? round($workloads->avg('total_hours_per_week'), 2) : 0;
        $averageMaxHours = $totalTeachers > 0 ? round($workloads->avg('max_hours_per_week'), 2) : 0;
        $averageUtilization = $averageMaxHours > 0 ? round(($averageHours / $averageMaxHours) * 100, 2) : 0;

        return [
            'academic_year' => $academicYear,
            'semester' => $semester,
            'total_teachers' => $totalTeachers,
            'overloaded_teachers' => $overloadedCount,
            'underloaded_teachers' => $underloadedCount,
            'normal_workload_teachers' => $normalCount,
            'average_hours_per_week' => $averageHours,
            'average_max_hours' => $averageMaxHours,
            'average_utilization_percentage' => $averageUtilization,
            'workload_distribution' => [
                'overloaded_percentage' => $totalTeachers > 0 ? round(($overloadedCount / $totalTeachers) * 100, 2) : 0,
                'underloaded_percentage' => $totalTeachers > 0 ? round(($underloadedCount / $totalTeachers) * 100, 2) : 0,
                'normal_percentage' => $totalTeachers > 0 ? round(($normalCount / $totalTeachers) * 100, 2) : 0,
            ],
        ];
    }

    private function calculateTotalHours(TeacherWorkload $workload): float
    {
        return round(
            $workload->teaching_hours +
            $workload->administrative_hours +
            $workload->extracurricular_hours +
            $workload->preparation_hours +
            $workload->grading_hours +
            $workload->other_duties_hours,
            2
        );
    }

    private function determineWorkloadStatus(TeacherWorkload $workload): string
    {
        if ($workload->isOverloaded()) {
            return 'overloaded';
        }

        if ($workload->isUnderloaded()) {
            return 'underloaded';
        }

        return 'normal';
    }
}
