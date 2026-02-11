<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Assessment\Analytics;
use App\Models\Assessment\Assessment;
use App\Models\Assessment\Rubric;
use App\Models\Assessment\RubricCriterion;
use App\Models\Assessment\Submission;
use App\Models\Grading\Grade;
use App\Models\OnlineExam\Exam;
use App\Models\OnlineExam\ExamQuestion;
use App\Models\OnlineExam\ExamResult;
use App\Models\OnlineExam\QuestionBank;
use App\Models\SchoolManagement\Student;
use App\Models\User;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;

class AssessmentService
{
    public function createAssessment(array $data, User $creator): Assessment
    {
        $data['created_by'] = $creator->id;
        
        return Assessment::create($data);
    }

    public function updateAssessment(Assessment $assessment, array $data): Assessment
    {
        $assessment->update($data);
        return $assessment->fresh();
    }

    public function publishAssessment(Assessment $assessment): Assessment
    {
        $assessment->update(['is_published' => true]);
        return $assessment->fresh();
    }

    public function getStudentAssessments(Student $student): \Hyperf\Database\Model\Collection
    {
        return Assessment::where('class_id', $student->class_id)
            ->active()
            ->with(['subject', 'rubric'])
            ->get();
    }

    public function startAssessment(Assessment $assessment, Student $student): Submission
    {
        if (!$assessment->isAccessibleBy($student->user)) {
            throw new \Exception('Assessment not accessible');
        }

        $attemptCount = Submission::where('assessment_id', $assessment->id)
            ->where('student_id', $student->id)
            ->count();

        if ($attemptCount >= $assessment->max_attempts) {
            throw new \Exception('Maximum attempts reached');
        }

        return Submission::create([
            'assessment_id' => $assessment->id,
            'student_id' => $student->id,
            'started_at' => Carbon::now(),
            'attempt_number' => $attemptCount + 1,
            'status' => 'in_progress',
        ]);
    }

    public function submitAssessment(Submission $submission, array $answers, int $timeSpentMinutes = null): Submission
    {
        $submission->update([
            'submitted_at' => Carbon::now(),
            'answers' => $answers,
            'time_spent_minutes' => $timeSpentMinutes ?? $submission->started_at->diffInMinutes(Carbon::now()),
            'status' => 'submitted',
        ]);

        return $submission;
    }

    public function gradeSubmission(Submission $submission): array
    {
        $assessment = $submission->assessment;
        $totalScore = 0;
        $questionsCorrect = 0;
        $totalQuestions = count($submission->answers ?? []);

        foreach ($submission->answers as $questionId => $answer) {
            $question = ExamQuestion::find($questionId);
            
            if ($question) {
                $score = $this->gradeQuestion($question, $answer);
                $totalScore += $score;
                
                if ($score > 0) {
                    $questionsCorrect++;
                }
            }
        }

        $percentage = $assessment->total_points > 0 
            ? ($totalScore / $assessment->total_points) * 100 
            : 0;
        
        $passed = $percentage >= $assessment->passing_grade;

        $submission->update([
            'score' => $totalScore,
            'percentage' => $percentage,
            'passed' => $passed,
            'status' => 'graded',
        ]);

        $this->createGradeFromSubmission($submission);

        return [
            'score' => $totalScore,
            'percentage' => round($percentage, 2),
            'passed' => $passed,
            'questions_correct' => $questionsCorrect,
            'total_questions' => $totalQuestions,
        ];
    }

    protected function gradeQuestion(ExamQuestion $question, $answer): float
    {
        $correctAnswer = ExamQuestion::find($question->id)?->correct_answer;

        if (!$correctAnswer) {
            return 0;
        }

        switch ($question->question_type) {
            case 'multiple_choice':
            case 'true_false':
                return $answer == $correctAnswer ? $question->points : 0;
                
            case 'fill_in_blank':
                return strtolower(trim($answer)) === strtolower(trim($correctAnswer)) 
                    ? $question->points 
                    : 0;
                
            default:
                return 0;
        }
    }

    protected function createGradeFromSubmission(Submission $submission): void
    {
        Grade::create([
            'student_id' => $submission->student_id,
            'subject_id' => $submission->assessment->subject_id,
            'class_id' => $submission->assessment->class_id,
            'grade' => $submission->score,
            'grade_type' => 'assessment',
            'notes' => 'Assessment: ' . $submission->assessment->title,
            'created_by' => $submission->assessment->created_by,
        ]);
    }

    public function calculateAssessmentAnalytics(Assessment $assessment): Analytics
    {
        $submissions = $submission::where('assessment_id', $assessment->id)
            ->where('status', 'graded')
            ->get();

        $totalParticipants = $assessment->class()->first()?->students()->count() ?? 0;
        $completedCount = $submissions->count();

        $stats = [
            'average_score' => $submissions->avg('score'),
            'highest_score' => $submissions->max('score'),
            'lowest_score' => $submissions->min('score'),
            'average_time_minutes' => $submissions->avg('time_spent_minutes'),
            'pass_rate' => $completedCount > 0 
                ? round(($submissions->where('passed', true)->count() / $completedCount) * 100)
                : 0,
        ];

        return Analytics::updateOrCreate(
            [
                'assessment_id' => $assessment->id,
                'student_id' => null,
            ],
            array_merge([
                'total_participants' => $totalParticipants,
                'completed_count' => $completedCount,
            ], $stats)
        );
    }

    public function getStudentPerformance(Student $student, ?string $subjectId = null): array
    {
        $query = Analytics::where('student_id', $student->id);

        if ($subjectId) {
            $query->whereHas('assessment', function ($q) use ($subjectId) {
                $q->where('subject_id', $subjectId);
            });
        }

        $analytics = $query->get();

        return [
            'average_score' => $analytics->avg('average_score'),
            'total_assessments' => $analytics->count(),
            'pass_rate' => $analytics->avg('pass_rate'),
            'recent_performance' => $analytics->take(10)->values(),
        ];
    }
}
