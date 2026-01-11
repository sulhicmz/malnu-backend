<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Assessment\Assessment;
use App\Models\Assessment\Submission;
use App\Models\Assessment\Analytics;
use App\Services\AssessmentService;
use App\Models\SchoolManagement\Student;

class AssessmentController extends BaseController
{
    protected $assessmentService;

    public function __construct(AssessmentService $assessmentService)
    {
        $this->assessmentService = $assessmentService;
    }

    public function index()
    {
        $classId = $this->request->input('class_id');
        $subjectId = $this->request->input('subject_id');
        $type = $this->request->input('assessment_type');

        $query = Assessment::with(['subject', 'class', 'rubric']);

        if ($classId) {
            $query->where('class_id', $classId);
        }

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        if ($type) {
            $query->where('assessment_type', $type);
        }

        $assessments = $query->paginate(15);

        return $this->successResponse($assessments, 'Assessments retrieved successfully');
    }

    public function store()
    {
        $title = $this->request->input('title');
        $type = $this->request->input('assessment_type');
        $subjectId = $this->request->input('subject_id');
        $classId = $this->request->input('class_id');

        if (!$title || !$type || !$subjectId || !$classId) {
            return $this->errorResponse('Required fields missing', 'MISSING_FIELDS');
        }

        $data = [
            'title' => $title,
            'assessment_type' => $type,
            'subject_id' => $subjectId,
            'class_id' => $classId,
            'description' => $this->request->input('description'),
            'start_time' => $this->request->input('start_time'),
            'end_time' => $this->request->input('end_time'),
            'duration_minutes' => $this->request->input('duration_minutes'),
            'total_points' => $this->request->input('total_points', 100),
            'passing_grade' => $this->request->input('passing_grade', 60),
            'allow_retakes' => $this->request->input('allow_retakes', false),
            'max_attempts' => $this->request->input('max_attempts', 1),
            'shuffle_questions' => $this->request->input('shuffle_questions', false),
            'show_results_immediately' => $this->request->input('show_results_immediately', true),
            'proctoring_enabled' => $this->request->input('proctoring_enabled', false),
            'rubric_id' => $this->request->input('rubric_id'),
        ];

        $assessment = $this->assessmentService->createAssessment($data, $this->request->getAttribute('user'));

        return $this->successResponse($assessment, 'Assessment created successfully', 201);
    }

    public function show($id)
    {
        $assessment = Assessment::with(['subject', 'class', 'rubric.criteria'])
            ->where('id', $id)
            ->first();

        if (!$assessment) {
            return $this->errorResponse('Assessment not found', 'NOT_FOUND', null, 404);
        }

        return $this->successResponse($assessment, 'Assessment retrieved successfully');
    }

    public function update($id)
    {
        $assessment = Assessment::where('id', $id)->first();

        if (!$assessment) {
            return $this->errorResponse('Assessment not found', 'NOT_FOUND', null, 404);
        }

        $data = $this->request->all();
        $assessment = $this->assessmentService->updateAssessment($assessment, $data);

        return $this->successResponse($assessment, 'Assessment updated successfully');
    }

    public function destroy($id)
    {
        $assessment = Assessment::where('id', $id)->first();

        if (!$assessment) {
            return $this->errorResponse('Assessment not found', 'NOT_FOUND', null, 404);
        }

        $assessment->delete();

        return $this->successResponse(null, 'Assessment deleted successfully');
    }

    public function publish($id)
    {
        $assessment = Assessment::where('id', $id)->first();

        if (!$assessment) {
            return $this->errorResponse('Assessment not found', 'NOT_FOUND', null, 404);
        }

        $assessment = $this->assessmentService->publishAssessment($assessment);

        return $this->successResponse($assessment, 'Assessment published successfully');
    }

    public function myAssessments()
    {
        $user = $this->request->getAttribute('user');
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return $this->errorResponse('Student not found', 'NOT_FOUND', null, 404);
        }

        $assessments = $this->assessmentService->getStudentAssessments($student);

        return $this->successResponse($assessments, 'Student assessments retrieved successfully');
    }

    public function start($id)
    {
        $assessment = Assessment::where('id', $id)->first();
        $user = $this->request->getAttribute('user');
        $student = Student::where('user_id', $user->id)->first();

        if (!$assessment || !$student) {
            return $this->errorResponse('Assessment or student not found', 'NOT_FOUND', null, 404);
        }

        try {
            $submission = $this->assessmentService->startAssessment($assessment, $student);
            return $this->successResponse($submission, 'Assessment started successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ASSESSMENT_ERROR');
        }
    }

    public function submit($id)
    {
        $submission = Submission::where('id', $id)->first();

        if (!$submission) {
            return $this->errorResponse('Submission not found', 'NOT_FOUND', null, 404);
        }

        $answers = $this->request->input('answers');
        $timeSpent = $this->request->input('time_spent_minutes');

        if (!$answers) {
            return $this->errorResponse('Answers are required', 'MISSING_ANSWERS');
        }

        $submission = $this->assessmentService->submitAssessment(
            $submission,
            $answers,
            $timeSpent
        );

        return $this->successResponse($submission, 'Assessment submitted successfully');
    }

    public function grade($id)
    {
        $submission = Submission::where('id', $id)->first();

        if (!$submission) {
            return $this->errorResponse('Submission not found', 'NOT_FOUND', null, 404);
        }

        $result = $this->assessmentService->gradeSubmission($submission);

        return $this->successResponse($result, 'Assessment graded successfully');
    }

    public function analytics($id)
    {
        $assessment = Assessment::where('id', $id)->first();

        if (!$assessment) {
            return $this->errorResponse('Assessment not found', 'NOT_FOUND', null, 404);
        }

        $analytics = $this->assessmentService->calculateAssessmentAnalytics($assessment);

        return $this->successResponse($analytics, 'Assessment analytics retrieved successfully');
    }

    public function studentPerformance()
    {
        $user = $this->request->getAttribute('user');
        $student = Student::where('user_id', $user->id)->first();
        $subjectId = $this->request->input('subject_id');

        if (!$student) {
            return $this->errorResponse('Student not found', 'NOT_FOUND', null, 404);
        }

        $performance = $this->assessmentService->getStudentPerformance($student, $subjectId);

        return $this->successResponse($performance, 'Student performance retrieved successfully');
    }
}
