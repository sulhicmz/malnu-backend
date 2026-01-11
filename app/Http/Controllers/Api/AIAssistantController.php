<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\AIAssistant\AiTutorSession;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class AIAssistantController extends BaseController
{
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }

    public function index()
    {
        try {
            $query = AiTutorSession::query();

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

            $sessions = $query->orderBy('created_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($sessions, 'AI tutor sessions retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function store()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['student_id', 'subject', 'question', 'answer'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $session = AiTutorSession::create($data);

            return $this->successResponse($session, 'AI tutor session created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'AI_SESSION_CREATION_ERROR', null, 400);
        }
    }

    public function show(string $id)
    {
        try {
            $session = AiTutorSession::with(['student'])->find($id);

            if (!$session) {
                return $this->notFoundResponse('AI tutor session not found');
            }

            return $this->successResponse($session, 'AI tutor session retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function update(string $id)
    {
        try {
            $session = AiTutorSession::find($id);

            if (!$session) {
                return $this->notFoundResponse('AI tutor session not found');
            }

            $data = $this->request->all();
            $session->update($data);

            return $this->successResponse($session, 'AI tutor session updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'AI_SESSION_UPDATE_ERROR', null, 400);
        }
    }

    public function destroy(string $id)
    {
        try {
            $session = AiTutorSession::find($id);

            if (!$session) {
                return $this->notFoundResponse('AI tutor session not found');
            }

            $session->delete();

            return $this->successResponse(null, 'AI tutor session deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'AI_SESSION_DELETION_ERROR', null, 400);
        }
    }
}
