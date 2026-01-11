<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\OnlineExam\Exam;
use App\Models\OnlineExam\QuestionBank;
use App\Models\OnlineExam\ExamQuestion;
use App\Models\OnlineExam\ExamAnswer;
use App\Models\OnlineExam\ExamResult;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class OnlineExamController extends BaseController
{
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }

    public function indexExams()
    {
        try {
            $query = Exam::query();

            $subject = $this->request->query('subject');
            $status = $this->request->query('status');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($subject) {
                $query->where('subject', $subject);
            }

            if ($status) {
                $query->where('status', $status);
            }

            $exams = $query->orderBy('created_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($exams, 'Exams retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeExam()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['title', 'subject', 'duration_minutes'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $exam = Exam::create($data);

            return $this->successResponse($exam, 'Exam created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'EXAM_CREATION_ERROR', null, 400);
        }
    }

    public function showExam(string $id)
    {
        try {
            $exam = Exam::with(['questions', 'results'])->find($id);

            if (!$exam) {
                return $this->notFoundResponse('Exam not found');
            }

            return $this->successResponse($exam, 'Exam retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function updateExam(string $id)
    {
        try {
            $exam = Exam::find($id);

            if (!$exam) {
                return $this->notFoundResponse('Exam not found');
            }

            $data = $this->request->all();
            $exam->update($data);

            return $this->successResponse($exam, 'Exam updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'EXAM_UPDATE_ERROR', null, 400);
        }
    }

    public function destroyExam(string $id)
    {
        try {
            $exam = Exam::find($id);

            if (!$exam) {
                return $this->notFoundResponse('Exam not found');
            }

            $exam->delete();

            return $this->successResponse(null, 'Exam deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'EXAM_DELETION_ERROR', null, 400);
        }
    }

    public function indexQuestions()
    {
        try {
            $query = QuestionBank::query();

            $category = $this->request->query('category');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($category) {
                $query->where('category', $category);
            }

            $questions = $query->orderBy('created_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($questions, 'Questions retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeQuestion()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['question_text', 'question_type', 'correct_answer'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $question = QuestionBank::create($data);

            return $this->successResponse($question, 'Question created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'QUESTION_CREATION_ERROR', null, 400);
        }
    }

    public function indexExamQuestions(string $examId)
    {
        try {
            $questions = ExamQuestion::with('question')->where('exam_id', $examId)
                ->paginate(15, ['*'], 'page', $this->request->query('page', 1));

            return $this->successResponse($questions, 'Exam questions retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeExamQuestion()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['exam_id', 'question_id'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $examQuestion = ExamQuestion::create($data);

            return $this->successResponse($examQuestion, 'Exam question created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'EXAM_QUESTION_CREATION_ERROR', null, 400);
        }
    }

    public function indexResults()
    {
        try {
            $query = ExamResult::with(['exam', 'student']);

            $examId = $this->request->query('exam_id');
            $studentId = $this->request->query('student_id');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($examId) {
                $query->where('exam_id', $examId);
            }

            if ($studentId) {
                $query->where('student_id', $studentId);
            }

            $results = $query->orderBy('submitted_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($results, 'Exam results retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeResult()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['exam_id', 'student_id', 'score', 'total_score'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $result = ExamResult::create($data);

            return $this->successResponse($result, 'Exam result created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'EXAM_RESULT_CREATION_ERROR', null, 400);
        }
    }

    public function indexAnswers(string $resultId)
    {
        try {
            $answers = ExamAnswer::where('exam_result_id', $resultId)->get();

            return $this->successResponse($answers, 'Exam answers retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeAnswer()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['exam_result_id', 'exam_question_id', 'answer'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $answer = ExamAnswer::create($data);

            return $this->successResponse($answer, 'Exam answer created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'EXAM_ANSWER_CREATION_ERROR', null, 400);
        }
    }
}
