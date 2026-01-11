<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Grading\Grade;
use App\Models\Grading\Competency;
use App\Models\Grading\Report;
use App\Models\Grading\StudentPortfolio;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class GradingController extends BaseController
{
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }

    public function indexGrades()
    {
        try {
            $query = Grade::query();

            $studentId = $this->request->query('student_id');
            $subject = $this->request->query('subject');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($studentId) {
                $query->where('student_id', $studentId);
            }

            if ($subject) {
                $query->where('subject', $subject);
            }

            $grades = $query->orderBy('created_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($grades, 'Grades retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeGrade()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['student_id', 'subject', 'score', 'max_score'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $grade = Grade::create($data);

            return $this->successResponse($grade, 'Grade created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'GRADE_CREATION_ERROR', null, 400);
        }
    }

    public function showGrade(string $id)
    {
        try {
            $grade = Grade::with(['student'])->find($id);

            if (!$grade) {
                return $this->notFoundResponse('Grade not found');
            }

            return $this->successResponse($grade, 'Grade retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function updateGrade(string $id)
    {
        try {
            $grade = Grade::find($id);

            if (!$grade) {
                return $this->notFoundResponse('Grade not found');
            }

            $data = $this->request->all();
            $grade->update($data);

            return $this->successResponse($grade, 'Grade updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'GRADE_UPDATE_ERROR', null, 400);
        }
    }

    public function destroyGrade(string $id)
    {
        try {
            $grade = Grade::find($id);

            if (!$grade) {
                return $this->notFoundResponse('Grade not found');
            }

            $grade->delete();

            return $this->successResponse(null, 'Grade deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'GRADE_DELETION_ERROR', null, 400);
        }
    }

    public function indexCompetencies()
    {
        try {
            $competencies = Competency::orderBy('name', 'asc')->paginate(15, ['*'], 'page', $this->request->query('page', 1));

            return $this->successResponse($competencies, 'Competencies retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeCompetency()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['name', 'description'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $competency = Competency::create($data);

            return $this->successResponse($competency, 'Competency created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'COMPETENCY_CREATION_ERROR', null, 400);
        }
    }

    public function indexReports()
    {
        try {
            $query = Report::query();

            $studentId = $this->request->query('student_id');
            $reportType = $this->request->query('report_type');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($studentId) {
                $query->where('student_id', $studentId);
            }

            if ($reportType) {
                $query->where('report_type', $reportType);
            }

            $reports = $query->orderBy('created_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($reports, 'Reports retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeReport()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['student_id', 'report_type', 'term'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $report = Report::create($data);

            return $this->successResponse($report, 'Report created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'REPORT_CREATION_ERROR', null, 400);
        }
    }

    public function indexPortfolios()
    {
        try {
            $query = StudentPortfolio::query();

            $studentId = $this->request->query('student_id');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($studentId) {
                $query->where('student_id', $studentId);
            }

            $portfolios = $query->orderBy('created_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($portfolios, 'Student portfolios retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storePortfolio()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['student_id', 'title', 'description'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $portfolio = StudentPortfolio::create($data);

            return $this->successResponse($portfolio, 'Student portfolio created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'PORTFOLIO_CREATION_ERROR', null, 400);
        }
    }
}
