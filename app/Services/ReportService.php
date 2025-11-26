<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Grading\Report;
use App\Models\Grading\Grade;
use App\Models\Grading\Competency;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\ClassModel;
use Hyperf\Utils\Str;

class ReportService
{
    /**
     * Generate a comprehensive report card for a student
     */
    public function generateReportCard(string $studentId, string $classId, int $semester, string $academicYear): array
    {
        $student = Student::find($studentId);
        if (!$student) {
            throw new \Exception("Student not found");
        }
        
        $class = ClassModel::find($classId);
        if (!$class) {
            throw new \Exception("Class not found");
        }
        
        // Get grades for the student in the specified class, semester, and academic year
        $grades = Grade::where('student_id', $studentId)
            ->where('class_id', $classId)
            ->where('semester', $semester)
            ->with(['subject', 'assignment', 'quiz', 'exam'])
            ->get();
        
        // Calculate average grade
        $averageGrade = $grades->avg('grade') ?? 0;
        
        // Get competencies for the student
        $competencies = Competency::where('student_id', $studentId)
            ->where('semester', $semester)
            ->with(['subject'])
            ->get();
        
        // Calculate rank in class
        $rankInClass = $this->calculateRankInClass($studentId, $classId, $semester, $academicYear);
        
        return [
            'student' => $student,
            'class' => $class,
            'semester' => $semester,
            'academic_year' => $academicYear,
            'grades' => $grades,
            'competencies' => $competencies,
            'average_grade' => number_format($averageGrade, 2),
            'rank_in_class' => $rankInClass,
            'generated_at' => date('Y-m-d H:i:s'),
        ];
    }
    
    /**
     * Generate a comprehensive academic transcript for a student
     */
    public function generateTranscript(string $studentId): array
    {
        $student = Student::find($studentId);
        if (!$student) {
            throw new \Exception("Student not found");
        }
        
        // Get all grades for the student across all semesters and years
        $allGrades = Grade::where('student_id', $studentId)
            ->with(['subject', 'class', 'assignment', 'quiz', 'exam'])
            ->orderBy('academic_year')
            ->orderBy('semester')
            ->get();
        
        // Group grades by academic year and semester
        $gradesByYear = $allGrades->groupBy(['academic_year', 'semester']);
        
        // Calculate cumulative GPA
        $cumulativeGPA = $this->calculateCumulativeGPA(collect($allGrades));
        
        // Calculate total credits
        $totalCredits = $this->calculateTotalCredits(collect($allGrades));
        
        return [
            'student' => $student,
            'grades_by_year' => $gradesByYear,
            'cumulative_gpa' => number_format($cumulativeGPA, 2),
            'total_credits' => $totalCredits,
            'generated_at' => date('Y-m-d H:i:s'),
        ];
    }
    
    /**
     * Calculate rank of student in class
     */
    private function calculateRankInClass(string $studentId, string $classId, int $semester, string $academicYear): int
    {
        // Get all students in the class with their average grades
        $classGrades = Grade::where('class_id', $classId)
            ->where('semester', $semester)
            ->select(['student_id', 'grade'])
            ->get()
            ->groupBy('student_id');
        
        $studentAverages = [];
        
        foreach ($classGrades as $studentIdInClass => $studentGrades) {
            $grades = collect($studentGrades);
            $average = $grades->avg('grade') ?? 0;
            $studentAverages[$studentIdInClass] = $average;
        }
        
        // Sort by average grade descending
        arsort($studentAverages);
        
        // Find the rank of the requested student
        $rank = 1;
        foreach ($studentAverages as $id => $average) {
            if ($id === $studentId) {
                return $rank;
            }
            $rank++;
        }
        
        return 0; // Student not found in class
    }
    
    /**
     * Calculate cumulative GPA
     */
    private function calculateCumulativeGPA($grades): float
    {
        if ($grades->isEmpty()) {
            return 0.0;
        }
        
        $totalGradePoints = $grades->sum('grade');
        $count = $grades->count();
        
        return $count > 0 ? $totalGradePoints / $count : 0.0;
    }
    
    /**
     * Calculate total credits
     */
    private function calculateTotalCredits($grades): int
    {
        // Assuming each subject has 1 credit for simplicity
        // In a real system, you would get credits from the subject model
        return $grades->unique('subject_id')->count();
    }
    
    /**
     * Extract academic year from format like "2023/2024"
     */
    private function extractAcademicYear(string $academicYear): string
    {
        // Extract the first part of academic year like "2023" from "2023/2024"
        $parts = explode('/', $academicYear);
        return $parts[0] ?? $academicYear;
    }
    
    /**
     * Save generated report to database
     */
    public function saveReport(array $reportData): Report
    {
        $report = new Report();
        $report->id = $this->generateUuid();
        $report->student_id = $reportData['student']->id;
        $report->class_id = $reportData['class']->id ?? null;
        $report->semester = $reportData['semester'];
        $report->academic_year = $reportData['academic_year'];
        $report->average_grade = $reportData['average_grade'];
        $report->rank_in_class = $reportData['rank_in_class'];
        $report->homeroom_notes = $reportData['homeroom_notes'] ?? null;
        $report->principal_notes = $reportData['principal_notes'] ?? null;
        $report->is_published = false;
        $report->created_by = $reportData['created_by'] ?? null;
        $report->save();
        
        return $report;
    }
    
    /**
     * Generate UUID for new records
     */
    private function generateUuid(): string
    {
        return (string) Str::uuid();
    }
}