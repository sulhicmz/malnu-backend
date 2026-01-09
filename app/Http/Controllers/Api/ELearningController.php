<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\ELearning\Assignment;
use App\Models\ELearning\Quiz;
use App\Models\ELearning\LearningMaterial;
use App\Models\ELearning\VirtualClass;
use App\Models\ELearning\VideoConference;
use App\Models\ELearning\Discussion;
use App\Models\ELearning\DiscussionReply;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class ELearningController extends BaseController
{
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }

    public function indexVirtualClasses()
    {
        try {
            $query = VirtualClass::query();

            $classId = $this->request->query('class_id');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($classId) {
                $query->where('class_id', $classId);
            }

            $virtualClasses = $query->orderBy('created_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($virtualClasses, 'Virtual classes retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeVirtualClass()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['name', 'class_id'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $virtualClass = VirtualClass::create($data);

            return $this->successResponse($virtualClass, 'Virtual class created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'VIRTUAL_CLASS_CREATION_ERROR', null, 400);
        }
    }

    public function showVirtualClass(string $id)
    {
        try {
            $virtualClass = VirtualClass::find($id);

            if (!$virtualClass) {
                return $this->notFoundResponse('Virtual class not found');
            }

            return $this->successResponse($virtualClass, 'Virtual class retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function updateVirtualClass(string $id)
    {
        try {
            $virtualClass = VirtualClass::find($id);

            if (!$virtualClass) {
                return $this->notFoundResponse('Virtual class not found');
            }

            $data = $this->request->all();
            $virtualClass->update($data);

            return $this->successResponse($virtualClass, 'Virtual class updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'VIRTUAL_CLASS_UPDATE_ERROR', null, 400);
        }
    }

    public function destroyVirtualClass(string $id)
    {
        try {
            $virtualClass = VirtualClass::find($id);

            if (!$virtualClass) {
                return $this->notFoundResponse('Virtual class not found');
            }

            $virtualClass->delete();

            return $this->successResponse(null, 'Virtual class deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'VIRTUAL_CLASS_DELETION_ERROR', null, 400);
        }
    }

    public function indexLearningMaterials()
    {
        try {
            $query = LearningMaterial::query();

            $virtualClassId = $this->request->query('virtual_class_id');
            $materialType = $this->request->query('material_type');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($virtualClassId) {
                $query->where('virtual_class_id', $virtualClassId);
            }

            if ($materialType) {
                $query->where('material_type', $materialType);
            }

            $materials = $query->orderBy('created_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($materials, 'Learning materials retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeLearningMaterial()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['virtual_class_id', 'title', 'content'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $material = LearningMaterial::create($data);

            return $this->successResponse($material, 'Learning material created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'LEARNING_MATERIAL_CREATION_ERROR', null, 400);
        }
    }

    public function showLearningMaterial(string $id)
    {
        try {
            $material = LearningMaterial::find($id);

            if (!$material) {
                return $this->notFoundResponse('Learning material not found');
            }

            return $this->successResponse($material, 'Learning material retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function updateLearningMaterial(string $id)
    {
        try {
            $material = LearningMaterial::find($id);

            if (!$material) {
                return $this->notFoundResponse('Learning material not found');
            }

            $data = $this->request->all();
            $material->update($data);

            return $this->successResponse($material, 'Learning material updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'LEARNING_MATERIAL_UPDATE_ERROR', null, 400);
        }
    }

    public function destroyLearningMaterial(string $id)
    {
        try {
            $material = LearningMaterial::find($id);

            if (!$material) {
                return $this->notFoundResponse('Learning material not found');
            }

            $material->delete();

            return $this->successResponse(null, 'Learning material deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'LEARNING_MATERIAL_DELETION_ERROR', null, 400);
        }
    }

    public function indexAssignments()
    {
        try {
            $query = Assignment::query();

            $virtualClassId = $this->request->query('virtual_class_id');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($virtualClassId) {
                $query->where('virtual_class_id', $virtualClassId);
            }

            $assignments = $query->orderBy('created_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($assignments, 'Assignments retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeAssignment()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['virtual_class_id', 'title', 'due_date'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $assignment = Assignment::create($data);

            return $this->successResponse($assignment, 'Assignment created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ASSIGNMENT_CREATION_ERROR', null, 400);
        }
    }

    public function showAssignment(string $id)
    {
        try {
            $assignment = Assignment::find($id);

            if (!$assignment) {
                return $this->notFoundResponse('Assignment not found');
            }

            return $this->successResponse($assignment, 'Assignment retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function updateAssignment(string $id)
    {
        try {
            $assignment = Assignment::find($id);

            if (!$assignment) {
                return $this->notFoundResponse('Assignment not found');
            }

            $data = $this->request->all();
            $assignment->update($data);

            return $this->successResponse($assignment, 'Assignment updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ASSIGNMENT_UPDATE_ERROR', null, 400);
        }
    }

    public function destroyAssignment(string $id)
    {
        try {
            $assignment = Assignment::find($id);

            if (!$assignment) {
                return $this->notFoundResponse('Assignment not found');
            }

            $assignment->delete();

            return $this->successResponse(null, 'Assignment deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ASSIGNMENT_DELETION_ERROR', null, 400);
        }
    }

    public function indexQuizzes()
    {
        try {
            $query = Quiz::query();

            $virtualClassId = $this->request->query('virtual_class_id');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($virtualClassId) {
                $query->where('virtual_class_id', $virtualClassId);
            }

            $quizzes = $query->orderBy('created_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($quizzes, 'Quizzes retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeQuiz()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['virtual_class_id', 'title', 'quiz_date'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $quiz = Quiz::create($data);

            return $this->successResponse($quiz, 'Quiz created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'QUIZ_CREATION_ERROR', null, 400);
        }
    }

    public function showQuiz(string $id)
    {
        try {
            $quiz = Quiz::find($id);

            if (!$quiz) {
                return $this->notFoundResponse('Quiz not found');
            }

            return $this->successResponse($quiz, 'Quiz retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function updateQuiz(string $id)
    {
        try {
            $quiz = Quiz::find($id);

            if (!$quiz) {
                return $this->notFoundResponse('Quiz not found');
            }

            $data = $this->request->all();
            $quiz->update($data);

            return $this->successResponse($quiz, 'Quiz updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'QUIZ_UPDATE_ERROR', null, 400);
        }
    }

    public function destroyQuiz(string $id)
    {
        try {
            $quiz = Quiz::find($id);

            if (!$quiz) {
                return $this->notFoundResponse('Quiz not found');
            }

            $quiz->delete();

            return $this->successResponse(null, 'Quiz deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'QUIZ_DELETION_ERROR', null, 400);
        }
    }

    public function indexDiscussions()
    {
        try {
            $query = Discussion::query();

            $virtualClassId = $this->request->query('virtual_class_id');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($virtualClassId) {
                $query->where('virtual_class_id', $virtualClassId);
            }

            $discussions = $query->orderBy('created_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($discussions, 'Discussions retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeDiscussion()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['virtual_class_id', 'user_id', 'content'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $discussion = Discussion::create($data);

            return $this->successResponse($discussion, 'Discussion created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'DISCUSSION_CREATION_ERROR', null, 400);
        }
    }

    public function showDiscussion(string $id)
    {
        try {
            $discussion = Discussion::with(['replies'])->find($id);

            if (!$discussion) {
                return $this->notFoundResponse('Discussion not found');
            }

            return $this->successResponse($discussion, 'Discussion retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function updateDiscussion(string $id)
    {
        try {
            $discussion = Discussion::find($id);

            if (!$discussion) {
                return $this->notFoundResponse('Discussion not found');
            }

            $data = $this->request->all();
            $discussion->update($data);

            return $this->successResponse($discussion, 'Discussion updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'DISCUSSION_UPDATE_ERROR', null, 400);
        }
    }

    public function destroyDiscussion(string $id)
    {
        try {
            $discussion = Discussion::find($id);

            if (!$discussion) {
                return $this->notFoundResponse('Discussion not found');
            }

            $discussion->delete();

            return $this->successResponse(null, 'Discussion deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'DISCUSSION_DELETION_ERROR', null, 400);
        }
    }

    public function indexDiscussionReplies(string $discussionId)
    {
        try {
            $replies = DiscussionReply::where('discussion_id', $discussionId)
                ->orderBy('created_at', 'asc')
                ->paginate(15, ['*'], 'page', $this->request->query('page', 1));

            return $this->successResponse($replies, 'Discussion replies retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeDiscussionReply()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['discussion_id', 'user_id', 'content'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $reply = DiscussionReply::create($data);

            return $this->successResponse($reply, 'Discussion reply created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'DISCUSSION_REPLY_CREATION_ERROR', null, 400);
        }
    }

    public function indexVideoConferences()
    {
        try {
            $query = VideoConference::query();

            $virtualClassId = $this->request->query('virtual_class_id');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($virtualClassId) {
                $query->where('virtual_class_id', $virtualClassId);
            }

            $conferences = $query->orderBy('scheduled_date', 'desc')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($conferences, 'Video conferences retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeVideoConference()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['virtual_class_id', 'title', 'scheduled_date'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $conference = VideoConference::create($data);

            return $this->successResponse($conference, 'Video conference created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'VIDEO_CONFERENCE_CREATION_ERROR', null, 400);
        }
    }

    public function showVideoConference(string $id)
    {
        try {
            $conference = VideoConference::find($id);

            if (!$conference) {
                return $this->notFoundResponse('Video conference not found');
            }

            return $this->successResponse($conference, 'Video conference retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function updateVideoConference(string $id)
    {
        try {
            $conference = VideoConference::find($id);

            if (!$conference) {
                return $this->notFoundResponse('Video conference not found');
            }

            $data = $this->request->all();
            $conference->update($data);

            return $this->successResponse($conference, 'Video conference updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'VIDEO_CONFERENCE_UPDATE_ERROR', null, 400);
        }
    }

    public function destroyVideoConference(string $id)
    {
        try {
            $conference = VideoConference::find($id);

            if (!$conference) {
                return $this->notFoundResponse('Video conference not found');
            }

            $conference->delete();

            return $this->successResponse(null, 'Video conference deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'VIDEO_CONFERENCE_DELETION_ERROR', null, 400);
        }
    }
}
