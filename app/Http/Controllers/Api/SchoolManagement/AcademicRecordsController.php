<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SchoolManagement;

use App\Http\Controllers\Api\BaseController;
use App\Models\SchoolManagement\Student;
use App\Services\GPACalculationService;
use App\Services\TranscriptGenerationService;
use Exception;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class AcademicRecordsController extends BaseController
{
    private GPACalculationService $gpaService;

    private TranscriptGenerationService $transcriptService;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container,
        GPACalculationService $gpaService,
        TranscriptGenerationService $transcriptService
    ) {
        parent::__construct($request, $response, $container);
        $this->gpaService = $gpaService;
        $this->transcriptService = $transcriptService;
    }

    public function calculateGPA(string $studentId)
    {
        try {
            $student = Student::find($studentId);

            if (! $student) {
                return $this->notFoundResponse('Student not found');
            }

            $academicYear = $this->request->query('academic_year');
            $semester = $this->request->query('semester') ? (int) $this->request->query('semester') : null;

            $gpa = $this->gpaService->calculateStudentGPA($studentId, $academicYear, $semester);

            $response = [
                'student_id' => $studentId,
                'gpa' => $gpa,
                'gpa_scale' => '4.0',
                'academic_year' => $academicYear ?: 'All Years',
                'semester' => $semester ?: 'All Semesters',
                'letter_grade' => $this->gpaService->convertNumericToLetter($gpa),
            ];

            return $this->successResponse($response, 'GPA calculated successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getAcademicPerformance(string $studentId)
    {
        try {
            $student = Student::find($studentId);

            if (! $student) {
                return $this->notFoundResponse('Student not found');
            }

            $summary = $this->gpaService->getAcademicPerformanceSummary($studentId);

            return $this->successResponse($summary, 'Academic performance retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getClassRank(string $studentId, string $classId)
    {
        try {
            $student = Student::find($studentId);

            if (! $student) {
                return $this->notFoundResponse('Student not found');
            }

            $semester = $this->request->query('semester') ? (int) $this->request->query('semester') : null;
            $academicYear = $this->request->query('academic_year');

            $rank = $this->gpaService->getClassRank($studentId, $classId, $semester, $academicYear);

            if ($rank === null) {
                return $this->errorResponse('Unable to calculate class rank', 'RANK_CALCULATION_ERROR');
            }

            return $this->successResponse([
                'student_id' => $studentId,
                'class_id' => $classId,
                'rank' => $rank,
                'semester' => $semester ?? 'All Semesters',
                'academic_year' => $academicYear ?? 'All Years',
            ], 'Class rank retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function generateTranscript(string $studentId)
    {
        try {
            $student = Student::find($studentId);

            if (! $student) {
                return $this->notFoundResponse('Student not found');
            }

            $academicYear = $this->request->query('academic_year');

            $transcript = $this->transcriptService->generateTranscript($studentId, $academicYear);

            return $this->successResponse($transcript, 'Transcript generated successfully');
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                'TRANSCRIPT_GENERATION_ERROR',
                null,
                400
            );
        }
    }

    public function generateReportCard(string $studentId, int $semester, string $academicYear)
    {
        try {
            $student = Student::find($studentId);

            if (! $student) {
                return $this->notFoundResponse('Student not found');
            }

            $reportCard = $this->transcriptService->generateReportCard($studentId, $semester, $academicYear);

            return $this->successResponse($reportCard, 'Report card generated successfully');
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                'REPORT_CARD_GENERATION_ERROR',
                null,
                400
            );
        }
    }

    public function getSubjectGrades(string $studentId, string $subjectId)
    {
        try {
            $student = Student::find($studentId);

            if (! $student) {
                return $this->notFoundResponse('Student not found');
            }

            $academicYear = $this->request->query('academic_year');
            $subjectGPA = $this->gpaService->getSubjectGPA($studentId, $subjectId, $academicYear);

            return $this->successResponse([
                'student_id' => $studentId,
                'subject_id' => $subjectId,
                'subject_gpa' => $subjectGPA,
                'academic_year' => $academicYear ?: 'All Years',
            ], 'Subject GPA retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getGradesHistory(string $studentId)
    {
        try {
            $student = Student::find($studentId);

            if (! $student) {
                return $this->notFoundResponse('Student not found');
            }

            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 20);

            $grades = $student->grades()
                ->with(['subject', 'class'])
                ->orderBy('created_at', 'desc')
                ->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($grades, 'Grades history retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}
