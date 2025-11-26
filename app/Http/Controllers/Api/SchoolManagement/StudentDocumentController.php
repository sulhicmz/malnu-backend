<?php

namespace App\Http\Controllers\Api\SchoolManagement;

use App\Http\Controllers\Api\BaseController;
use App\Models\Grading\StudentPortfolio;
use App\Models\SchoolManagement\Student;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class StudentDocumentController extends BaseController
{
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }

    /**
     * Get all documents/portfolios for a student
     */
    public function getStudentDocuments(string $studentId)
    {
        try {
            $student = Student::find($studentId);
            if (!$student) {
                return $this->notFoundResponse('Student not found');
            }

            $portfolios = StudentPortfolio::where('student_id', $studentId)
                ->orderBy('date_added', 'desc')
                ->get();

            $documents = [
                'student' => $student,
                'portfolios' => $portfolios,
                'document_count' => $portfolios->count(),
                'document_types' => $this->getDocumentTypes($portfolios)
            ];

            return $this->successResponse($documents, 'Student documents retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    /**
     * Get document types distribution
     */
    private function getDocumentTypes($portfolios)
    {
        $types = [];
        foreach ($portfolios as $portfolio) {
            $type = $portfolio->portfolio_type;
            if (isset($types[$type])) {
                $types[$type]++;
            } else {
                $types[$type] = 1;
            }
        }
        return $types;
    }

    /**
     * Add a new document/portfolio for a student
     */
    public function addStudentDocument(string $studentId)
    {
        try {
            $student = Student::find($studentId);
            if (!$student) {
                return $this->notFoundResponse('Student not found');
            }

            $data = $this->request->all();

            // Validate required fields
            $requiredFields = ['title', 'portfolio_type'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            // Validate portfolio type
            $validTypes = ['assignment', 'project', 'certificate', 'achievement', 'portfolio', 'other'];
            if (!in_array($data['portfolio_type'], $validTypes)) {
                return $this->validationErrorResponse([
                    'portfolio_type' => ['Portfolio type must be one of: ' . implode(', ', $validTypes)]
                ]);
            }

            $data['student_id'] = $studentId;
            $data['date_added'] = $data['date_added'] ?? date('Y-m-d');

            $portfolio = StudentPortfolio::create($data);

            return $this->successResponse($portfolio, 'Student document added successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'DOCUMENT_CREATION_ERROR', null, 400);
        }
    }

    /**
     * Update a student document
     */
    public function updateStudentDocument(string $documentId)
    {
        try {
            $portfolio = StudentPortfolio::find($documentId);
            if (!$portfolio) {
                return $this->notFoundResponse('Document not found');
            }

            $data = $this->request->all();

            // Validate portfolio type if provided
            if (isset($data['portfolio_type'])) {
                $validTypes = ['assignment', 'project', 'certificate', 'achievement', 'portfolio', 'other'];
                if (!in_array($data['portfolio_type'], $validTypes)) {
                    return $this->validationErrorResponse([
                        'portfolio_type' => ['Portfolio type must be one of: ' . implode(', ', $validTypes)]
                    ]);
                }
            }

            $portfolio->update($data);

            return $this->successResponse($portfolio, 'Student document updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'DOCUMENT_UPDATE_ERROR', null, 400);
        }
    }

    /**
     * Delete a student document
     */
    public function deleteStudentDocument(string $documentId)
    {
        try {
            $portfolio = StudentPortfolio::find($documentId);
            if (!$portfolio) {
                return $this->notFoundResponse('Document not found');
            }

            $portfolio->delete();

            return $this->successResponse(null, 'Student document deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'DOCUMENT_DELETION_ERROR', null, 400);
        }
    }

    /**
     * Get documents by type for a student
     */
    public function getDocumentsByType(string $studentId, string $type)
    {
        try {
            $student = Student::find($studentId);
            if (!$student) {
                return $this->notFoundResponse('Student not found');
            }

            $portfolios = StudentPortfolio::where('student_id', $studentId)
                ->where('portfolio_type', $type)
                ->orderBy('date_added', 'desc')
                ->get();

            return $this->successResponse($portfolios, 'Documents retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    /**
     * Generate certificate for student
     */
    public function generateCertificate(string $studentId)
    {
        try {
            $student = Student::with(['class', 'grades'])->find($studentId);
            if (!$student) {
                return $this->notFoundResponse('Student not found');
            }

            // Calculate GPA for certificate
            $gpa = 0;
            if ($student->grades->count() > 0) {
                $totalGradePoints = 0;
                $totalCredits = 0;
                
                foreach ($student->grades as $grade) {
                    $gradeValue = $this->convertGradeToPoints($grade->grade);
                    $credits = $grade->subject->credit_hours ?? 1;
                    
                    $totalGradePoints += $gradeValue * $credits;
                    $totalCredits += $credits;
                }
                
                $gpa = $totalCredits > 0 ? round($totalGradePoints / $totalCredits, 2) : 0;
            }

            $certificate = [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'nisn' => $student->nisn,
                    'class' => $student->class ? $student->class->name : null
                ],
                'certificate_type' => 'academic_achievement',
                'issue_date' => date('Y-m-d'),
                'academic_info' => [
                    'cumulative_gpa' => $gpa,
                    'total_subjects' => $student->grades->count(),
                    'enrollment_date' => $student->enrollment_date,
                    'status' => $student->status
                ],
                'achievement_level' => $this->getAchievementLevel($gpa),
                'certificate_number' => $this->generateCertificateNumber($student->id)
            ];

            return $this->successResponse($certificate, 'Certificate generated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
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
     * Get achievement level based on GPA
     */
    private function getAchievementLevel($gpa)
    {
        if ($gpa >= 3.7) return 'Summa Cum Laude';
        if ($gpa >= 3.5) return 'Magna Cum Laude';
        if ($gpa >= 3.3) return 'Cum Laude';
        if ($gpa >= 2.7) return 'With Honors';
        if ($gpa >= 2.0) return 'Satisfactory';
        return 'Needs Improvement';
    }

    /**
     * Generate certificate number
     */
    private function generateCertificateNumber($studentId)
    {
        return 'CERT-' . strtoupper(substr($studentId, 0, 8)) . '-' . date('Y');
    }
}