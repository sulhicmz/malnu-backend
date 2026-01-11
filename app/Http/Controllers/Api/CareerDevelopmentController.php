<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\CareerDevelopment\CareerAssessment;
use App\Models\CareerDevelopment\CounselingSession;
use App\Models\CareerDevelopment\IndustryPartner;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class CareerDevelopmentController extends BaseController
{
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }

    public function indexAssessments()
    {
        try {
            $query = CareerAssessment::query();

            $studentId = $this->request->query('student_id');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($studentId) {
                $query->where('student_id', $studentId);
            }

            $assessments = $query->orderBy('created_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($assessments, 'Career assessments retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeAssessment()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['student_id', 'assessment_type', 'results'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $assessment = CareerAssessment::create($data);

            return $this->successResponse($assessment, 'Career assessment created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'CAREER_ASSESSMENT_CREATION_ERROR', null, 400);
        }
    }

    public function showAssessment(string $id)
    {
        try {
            $assessment = CareerAssessment::with(['student'])->find($id);

            if (!$assessment) {
                return $this->notFoundResponse('Career assessment not found');
            }

            return $this->successResponse($assessment, 'Career assessment retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function updateAssessment(string $id)
    {
        try {
            $assessment = CareerAssessment::find($id);

            if (!$assessment) {
                return $this->notFoundResponse('Career assessment not found');
            }

            $data = $this->request->all();
            $assessment->update($data);

            return $this->successResponse($assessment, 'Career assessment updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'CAREER_ASSESSMENT_UPDATE_ERROR', null, 400);
        }
    }

    public function destroyAssessment(string $id)
    {
        try {
            $assessment = CareerAssessment::find($id);

            if (!$assessment) {
                return $this->notFoundResponse('Career assessment not found');
            }

            $assessment->delete();

            return $this->successResponse(null, 'Career assessment deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'CAREER_ASSESSMENT_DELETION_ERROR', null, 400);
        }
    }

    public function indexSessions()
    {
        try {
            $query = CounselingSession::query();

            $studentId = $this->request->query('student_id');
            $status = $this->request->query('status');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($studentId) {
                $query->where('student_id', $studentId);
            }

            if ($status) {
                $query->where('status', $status);
            }

            $sessions = $query->orderBy('scheduled_date', 'desc')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($sessions, 'Counseling sessions retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeSession()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['student_id', 'counselor_id', 'scheduled_date', 'topic'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $session = CounselingSession::create($data);

            return $this->successResponse($session, 'Counseling session created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'COUNSELING_SESSION_CREATION_ERROR', null, 400);
        }
    }

    public function showSession(string $id)
    {
        try {
            $session = CounselingSession::with(['student', 'counselor'])->find($id);

            if (!$session) {
                return $this->notFoundResponse('Counseling session not found');
            }

            return $this->successResponse($session, 'Counseling session retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function updateSession(string $id)
    {
        try {
            $session = CounselingSession::find($id);

            if (!$session) {
                return $this->notFoundResponse('Counseling session not found');
            }

            $data = $this->request->all();
            $session->update($data);

            return $this->successResponse($session, 'Counseling session updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'COUNSELING_SESSION_UPDATE_ERROR', null, 400);
        }
    }

    public function destroySession(string $id)
    {
        try {
            $session = CounselingSession::find($id);

            if (!$session) {
                return $this->notFoundResponse('Counseling session not found');
            }

            $session->delete();

            return $this->successResponse(null, 'Counseling session deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'COUNSELING_SESSION_DELETION_ERROR', null, 400);
        }
    }

    public function indexPartners()
    {
        try {
            $partners = IndustryPartner::orderBy('company_name', 'asc')->paginate(15, ['*'], 'page', $this->request->query('page', 1));

            return $this->successResponse($partners, 'Industry partners retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storePartner()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['company_name', 'contact_person', 'email', 'phone'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = ['The email must be a valid email address.'];
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $partner = IndustryPartner::create($data);

            return $this->successResponse($partner, 'Industry partner created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'INDUSTRY_PARTNER_CREATION_ERROR', null, 400);
        }
    }

    public function showPartner(string $id)
    {
        try {
            $partner = IndustryPartner::find($id);

            if (!$partner) {
                return $this->notFoundResponse('Industry partner not found');
            }

            return $this->successResponse($partner, 'Industry partner retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function updatePartner(string $id)
    {
        try {
            $partner = IndustryPartner::find($id);

            if (!$partner) {
                return $this->notFoundResponse('Industry partner not found');
            }

            $data = $this->request->all();
            $partner->update($data);

            return $this->successResponse($partner, 'Industry partner updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'INDUSTRY_PARTNER_UPDATE_ERROR', null, 400);
        }
    }

    public function destroyPartner(string $id)
    {
        try {
            $partner = IndustryPartner::find($id);

            if (!$partner) {
                return $this->notFoundResponse('Industry partner not found');
            }

            $partner->delete();

            return $this->successResponse(null, 'Industry partner deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'INDUSTRY_PARTNER_DELETION_ERROR', null, 400);
        }
    }
}
