<?php

namespace App\Http\Controllers\Api\SchoolManagement;

use App\Http\Controllers\Api\BaseController;
use App\Models\Grading\Grade;
use App\Models\Grading\Report;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\Subject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class StudentAnalyticsController extends BaseController
{
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }

    /**
     * Get student performance analytics
     */
    public function getStudentPerformanceAnalytics(string $studentId)
    {
        try {
            $student = Student::with([
                'grades' => function($query) {
                    $query->with(['subject', 'class']);
                },
                'reports',
                'class'
            ])->find($studentId);

            if (!$student) {
                return $this->notFoundResponse('Student not found');
            }

            $analytics = [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'nisn' => $student->nisn,
                    'class' => $student->class ? $student->class->name : null
                ],
                'performance_metrics' => $this->calculatePerformanceMetrics($student),
                'trend_analysis' => $this->getTrendAnalysis($student),
                'comparative_analysis' => $this->getComparativeAnalysis($student),
                'recommendations' => $this->generateRecommendations($student)
            ];

            return $this->successResponse($analytics, 'Student performance analytics retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    /**
     * Calculate performance metrics for a student
     */
    private function calculatePerformanceMetrics($student)
    {
        $grades = $student->grades;
        
        if ($grades->isEmpty()) {
            return [
                'cumulative_gpa' => 0.00,
                'average_grade' => 0,
                'total_subjects' => 0,
                'highest_grade' => 0,
                'lowest_grade' => 0,
                'grade_distribution' => []
            ];
        }

        $totalGrade = 0;
        $highestGrade = 0;
        $lowestGrade = 100;
        $gradeDistribution = [
            'excellent' => 0, // 90-100
            'very_good' => 0, // 80-89
            'good' => 0,      // 70-79
            'average' => 0,   // 60-69
            'below_average' => 0 // < 60
        ];

        foreach ($grades as $grade) {
            $totalGrade += $grade->grade;
            
            if ($grade->grade > $highestGrade) {
                $highestGrade = $grade->grade;
            }
            
            if ($grade->grade < $lowestGrade) {
                $lowestGrade = $grade->grade;
            }

            // Categorize grade
            if ($grade->grade >= 90) {
                $gradeDistribution['excellent']++;
            } elseif ($grade->grade >= 80) {
                $gradeDistribution['very_good']++;
            } elseif ($grade->grade >= 70) {
                $gradeDistribution['good']++;
            } elseif ($grade->grade >= 60) {
                $gradeDistribution['average']++;
            } else {
                $gradeDistribution['below_average']++;
            }
        }

        $averageGrade = $grades->count() > 0 ? $totalGrade / $grades->count() : 0;
        $cumulativeGPA = $this->calculateCumulativeGPA($grades);

        return [
            'cumulative_gpa' => $cumulativeGPA,
            'average_grade' => round($averageGrade, 2),
            'total_subjects' => $grades->count(),
            'highest_grade' => $highestGrade,
            'lowest_grade' => $lowestGrade,
            'grade_distribution' => $gradeDistribution
        ];
    }

    /**
     * Calculate cumulative GPA
     */
    private function calculateCumulativeGPA($grades)
    {
        if ($grades->isEmpty()) {
            return 0.00;
        }

        $totalGradePoints = 0;
        $totalCredits = 0;

        foreach ($grades as $grade) {
            $gradeValue = $this->convertGradeToPoints($grade->grade);
            $credits = $grade->subject->credit_hours ?? 1; // Default to 1 credit if not specified
            
            $totalGradePoints += $gradeValue * $credits;
            $totalCredits += $credits;
        }

        return $totalCredits > 0 ? round($totalGradePoints / $totalCredits, 2) : 0.00;
    }

    /**
     * Convert numeric grade to grade points
     */
    private function convertGradeToPoints($grade)
    {
        // Standard 4.0 GPA scale
        if ($grade >= 97) return 4.0;
        if ($grade >= 93) return 3.7;
        if ($grade >= 90) return 3.3;
        if ($grade >= 87) return 3.0;
        if ($grade >= 83) return 2.7;
        if ($grade >= 80) return 2.3;
        if ($grade >= 77) return 2.0;
        if ($grade >= 73) return 1.7;
        if ($grade >= 70) return 1.3;
        if ($grade >= 67) return 1.0;
        if ($grade >= 65) return 0.7;
        return 0.0;
    }

    /**
     * Get trend analysis for a student
     */
    private function getTrendAnalysis($student)
    {
        $grades = $student->grades->sortBy('semester');

        $trendData = [];
        $semesterGrades = $grades->groupBy('semester');

        foreach ($semesterGrades as $semester => $semesterGrades) {
            $semesterAverage = $semesterGrades->avg('grade');
            $semesterGPA = $this->calculateSemesterGPA($semesterGrades);
            
            $trendData[] = [
                'semester' => $semester,
                'average_grade' => round($semesterAverage, 2),
                'gpa' => $semesterGPA,
                'total_subjects' => $semesterGrades->count()
            ];
        }

        // Determine trend direction
        $trendDirection = 'stable';
        if (count($trendData) >= 2) {
            $lastIndex = count($trendData) - 1;
            $secondLastIndex = $lastIndex - 1;
            
            if ($trendData[$lastIndex]['average_grade'] > $trendData[$secondLastIndex]['average_grade']) {
                $trendDirection = 'improving';
            } elseif ($trendData[$lastIndex]['average_grade'] < $trendData[$secondLastIndex]['average_grade']) {
                $trendDirection = 'declining';
            }
        }

        return [
            'trend_direction' => $trendDirection,
            'semester_breakdown' => $trendData,
            'overall_trend' => count($trendData) > 1
        ];
    }

    /**
     * Calculate GPA for a specific semester
     */
    private function calculateSemesterGPA($semesterGrades)
    {
        if ($semesterGrades->isEmpty()) {
            return 0.00;
        }

        $totalGradePoints = 0;
        $totalCredits = 0;

        foreach ($semesterGrades as $grade) {
            $gradeValue = $this->convertGradeToPoints($grade->grade);
            $credits = $grade->subject->credit_hours ?? 1;
            
            $totalGradePoints += $gradeValue * $credits;
            $totalCredits += $credits;
        }

        return $totalCredits > 0 ? round($totalGradePoints / $totalCredits, 2) : 0.00;
    }

    /**
     * Get comparative analysis with class
     */
    private function getComparativeAnalysis($student)
    {
        if (!$student->class_id) {
            return [
                'class_average' => 0,
                'class_rank' => null,
                'performance_vs_class' => 'N/A'
            ];
        }

        // Get all students in the same class
        $classStudents = Student::where('class_id', $student->class_id)
            ->with(['grades'])
            ->get();

        $studentGPAs = [];
        foreach ($classStudents as $classStudent) {
            $gpa = $this->calculateCumulativeGPA($classStudent->grades);
            $studentGPAs[] = $gpa;
        }

        if (empty($studentGPAs)) {
            return [
                'class_average' => 0,
                'class_rank' => null,
                'performance_vs_class' => 'N/A'
            ];
        }

        $classAverage = array_sum($studentGPAs) / count($studentGPAs);
        $studentGPA = $this->calculateCumulativeGPA($student->grades);

        // Calculate rank
        rsort($studentGPAs);
        $rank = array_search($studentGPA, $studentGPAs);
        $rank = $rank !== false ? $rank + 1 : null;

        $performanceVsClass = 'average';
        if ($studentGPA > $classAverage) {
            $performanceVsClass = 'above_average';
        } elseif ($studentGPA < $classAverage) {
            $performanceVsClass = 'below_average';
        }

        return [
            'class_average' => round($classAverage, 2),
            'class_rank' => $rank,
            'performance_vs_class' => $performanceVsClass,
            'class_size' => count($studentGPAs)
        ];
    }

    /**
     * Generate recommendations based on performance
     */
    private function generateRecommendations($student)
    {
        $recommendations = [];
        $metrics = $this->calculatePerformanceMetrics($student);

        // GPA-based recommendations
        if ($metrics['cumulative_gpa'] < 2.0) {
            $recommendations[] = 'Student needs academic support and tutoring';
        } elseif ($metrics['cumulative_gpa'] < 3.0) {
            $recommendations[] = 'Consider additional academic resources to improve performance';
        } elseif ($metrics['cumulative_gpa'] >= 3.5) {
            $recommendations[] = 'Excellent performance - consider advanced coursework or leadership opportunities';
        }

        // Grade distribution recommendations
        if ($metrics['grade_distribution']['below_average'] > 0) {
            $recommendations[] = 'Focus on subjects with grades below 60%';
        }

        if ($metrics['grade_distribution']['excellent'] == 0 && $metrics['grade_distribution']['very_good'] == 0) {
            $recommendations[] = 'Student needs to work on achieving higher grades';
        }

        // Trend-based recommendations
        $trend = $this->getTrendAnalysis($student);
        if ($trend['trend_direction'] === 'declining') {
            $recommendations[] = 'Performance is declining - immediate intervention needed';
        } elseif ($trend['trend_direction'] === 'improving') {
            $recommendations[] = 'Positive trend - continue current study habits';
        }

        return $recommendations;
    }

    /**
     * Get class performance analytics
     */
    public function getClassPerformanceAnalytics(string $classId)
    {
        try {
            $class = ClassModel::find($classId);
            if (!$class) {
                return $this->notFoundResponse('Class not found');
            }

            $students = Student::with(['grades', 'reports'])
                ->where('class_id', $classId)
                ->get();

            $analytics = [
                'class' => $class,
                'total_students' => $students->count(),
                'average_class_gpa' => $this->calculateClassAverageGPA($students),
                'grade_distribution' => $this->getClassGradeDistribution($students),
                'top_performers' => $this->getTopPerformers($students),
                'students_needing_support' => $this->getStudentsNeedingSupport($students)
            ];

            return $this->successResponse($analytics, 'Class performance analytics retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    /**
     * Calculate class average GPA
     */
    private function calculateClassAverageGPA($students)
    {
        $totalGPA = 0;
        $studentCount = 0;

        foreach ($students as $student) {
            $gpa = $this->calculateCumulativeGPA($student->grades);
            if ($gpa > 0) {
                $totalGPA += $gpa;
                $studentCount++;
            }
        }

        return $studentCount > 0 ? round($totalGPA / $studentCount, 2) : 0;
    }

    /**
     * Get class grade distribution
     */
    private function getClassGradeDistribution($students)
    {
        $gradeDistribution = [
            'excellent' => 0, // 90-100
            'very_good' => 0, // 80-89
            'good' => 0,      // 70-79
            'average' => 0,   // 60-69
            'below_average' => 0 // < 60
        ];

        foreach ($students as $student) {
            foreach ($student->grades as $grade) {
                if ($grade->grade >= 90) {
                    $gradeDistribution['excellent']++;
                } elseif ($grade->grade >= 80) {
                    $gradeDistribution['very_good']++;
                } elseif ($grade->grade >= 70) {
                    $gradeDistribution['good']++;
                } elseif ($grade->grade >= 60) {
                    $gradeDistribution['average']++;
                } else {
                    $gradeDistribution['below_average']++;
                }
            }
        }

        return $gradeDistribution;
    }

    /**
     * Get top performers in the class
     */
    private function getTopPerformers($students)
    {
        $studentGPAs = [];
        foreach ($students as $student) {
            $gpa = $this->calculateCumulativeGPA($student->grades);
            if ($gpa > 0) {
                $studentGPAs[] = [
                    'student' => [
                        'id' => $student->id,
                        'name' => $student->name,
                        'nisn' => $student->nisn
                    ],
                    'gpa' => $gpa
                ];
            }
        }

        // Sort by GPA descending
        usort($studentGPAs, function($a, $b) {
            return $b['gpa'] <=> $a['gpa'];
        });

        // Return top 5
        return array_slice($studentGPAs, 0, 5);
    }

    /**
     * Get students needing academic support
     */
    private function getStudentsNeedingSupport($students)
    {
        $studentsNeedingSupport = [];
        foreach ($students as $student) {
            $gpa = $this->calculateCumulativeGPA($student->grades);
            if ($gpa < 2.0) { // GPA below 2.0 indicates need for support
                $studentsNeedingSupport[] = [
                    'student' => [
                        'id' => $student->id,
                        'name' => $student->name,
                        'nisn' => $student->nisn
                    ],
                    'gpa' => $gpa,
                    'total_subjects' => $student->grades->count()
                ];
            }
        }

        return $studentsNeedingSupport;
    }
}