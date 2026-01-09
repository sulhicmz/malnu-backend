<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\PPDB\PpdbRegistration;
use App\Models\PPDB\PpdbTest;
use App\Models\PPDB\PpdbDocument;
use App\Models\PPDB\PpdbAnnouncement;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class PPDBController extends BaseController
{
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }

    public function indexRegistrations()
    {
        try {
            $query = PpdbRegistration::query();

            $status = $this->request->query('status');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($status) {
                $query->where('status', $status);
            }

            $registrations = $query->orderBy('registration_date', 'desc')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($registrations, 'PPDB registrations retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeRegistration()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['student_name', 'email', 'phone', 'birth_date'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $registration = PpdbRegistration::create($data);

            return $this->successResponse($registration, 'PPDB registration created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'PPDB_REGISTRATION_CREATION_ERROR', null, 400);
        }
    }

    public function showRegistration(string $id)
    {
        try {
            $registration = PpdbRegistration::with(['documents', 'tests'])->find($id);

            if (!$registration) {
                return $this->notFoundResponse('PPDB registration not found');
            }

            return $this->successResponse($registration, 'PPDB registration retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function updateRegistration(string $id)
    {
        try {
            $registration = PpdbRegistration::find($id);

            if (!$registration) {
                return $this->notFoundResponse('PPDB registration not found');
            }

            $data = $this->request->all();
            $registration->update($data);

            return $this->successResponse($registration, 'PPDB registration updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'PPDB_REGISTRATION_UPDATE_ERROR', null, 400);
        }
    }

    public function destroyRegistration(string $id)
    {
        try {
            $registration = PpdbRegistration::find($id);

            if (!$registration) {
                return $this->notFoundResponse('PPDB registration not found');
            }

            $registration->delete();

            return $this->successResponse(null, 'PPDB registration deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'PPDB_REGISTRATION_DELETION_ERROR', null, 400);
        }
    }

    public function indexTests()
    {
        try {
            $query = PpdbTest::query();

            $registrationId = $this->request->query('registration_id');
            $status = $this->request->query('status');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($registrationId) {
                $query->where('registration_id', $registrationId);
            }

            if ($status) {
                $query->where('status', $status);
            }

            $tests = $query->orderBy('test_date', 'desc')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($tests, 'PPDB tests retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeTest()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['registration_id', 'test_date', 'location'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $test = PpdbTest::create($data);

            return $this->successResponse($test, 'PPDB test created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'PPDB_TEST_CREATION_ERROR', null, 400);
        }
    }

    public function showTest(string $id)
    {
        try {
            $test = PpdbTest::with(['registration'])->find($id);

            if (!$test) {
                return $this->notFoundResponse('PPDB test not found');
            }

            return $this->successResponse($test, 'PPDB test retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function updateTest(string $id)
    {
        try {
            $test = PpdbTest::find($id);

            if (!$test) {
                return $this->notFoundResponse('PPDB test not found');
            }

            $data = $this->request->all();
            $test->update($data);

            return $this->successResponse($test, 'PPDB test updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'PPDB_TEST_UPDATE_ERROR', null, 400);
        }
    }

    public function destroyTest(string $id)
    {
        try {
            $test = PpdbTest::find($id);

            if (!$test) {
                return $this->notFoundResponse('PPDB test not found');
            }

            $test->delete();

            return $this->successResponse(null, 'PPDB test deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'PPDB_TEST_DELETION_ERROR', null, 400);
        }
    }

    public function indexDocuments()
    {
        try {
            $query = PpdbDocument::query();

            $registrationId = $this->request->query('registration_id');
            $documentType = $this->request->query('document_type');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($registrationId) {
                $query->where('registration_id', $registrationId);
            }

            if ($documentType) {
                $query->where('document_type', $documentType);
            }

            $documents = $query->orderBy('uploaded_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($documents, 'PPDB documents retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeDocument()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['registration_id', 'document_type', 'file_path'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $document = PpdbDocument::create($data);

            return $this->successResponse($document, 'PPDB document created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'PPDB_DOCUMENT_CREATION_ERROR', null, 400);
        }
    }

    public function showDocument(string $id)
    {
        try {
            $document = PpdbDocument::with(['registration'])->find($id);

            if (!$document) {
                return $this->notFoundResponse('PPDB document not found');
            }

            return $this->successResponse($document, 'PPDB document retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function updateDocument(string $id)
    {
        try {
            $document = PpdbDocument::find($id);

            if (!$document) {
                return $this->notFoundResponse('PPDB document not found');
            }

            $data = $this->request->all();
            $document->update($data);

            return $this->successResponse($document, 'PPDB document updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'PPDB_DOCUMENT_UPDATE_ERROR', null, 400);
        }
    }

    public function destroyDocument(string $id)
    {
        try {
            $document = PpdbDocument::find($id);

            if (!$document) {
                return $this->notFoundResponse('PPDB document not found');
            }

            $document->delete();

            return $this->successResponse(null, 'PPDB document deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'PPDB_DOCUMENT_DELETION_ERROR', null, 400);
        }
    }

    public function indexAnnouncements()
    {
        try {
            $announcements = PpdbAnnouncement::orderBy('announcement_date', 'desc')
                ->paginate(15, ['*'], 'page', $this->request->query('page', 1));

            return $this->successResponse($announcements, 'PPDB announcements retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeAnnouncement()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['title', 'content', 'announcement_date'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $announcement = PpdbAnnouncement::create($data);

            return $this->successResponse($announcement, 'PPDB announcement created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'PPDB_ANNOUNCEMENT_CREATION_ERROR', null, 400);
        }
    }

    public function showAnnouncement(string $id)
    {
        try {
            $announcement = PpdbAnnouncement::find($id);

            if (!$announcement) {
                return $this->notFoundResponse('PPDB announcement not found');
            }

            return $this->successResponse($announcement, 'PPDB announcement retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function updateAnnouncement(string $id)
    {
        try {
            $announcement = PpdbAnnouncement::find($id);

            if (!$announcement) {
                return $this->notFoundResponse('PPDB announcement not found');
            }

            $data = $this->request->all();
            $announcement->update($data);

            return $this->successResponse($announcement, 'PPDB announcement updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'PPDB_ANNOUNCEMENT_UPDATE_ERROR', null, 400);
        }
    }

    public function destroyAnnouncement(string $id)
    {
        try {
            $announcement = PpdbAnnouncement::find($id);

            if (!$announcement) {
                return $this->notFoundResponse('PPDB announcement not found');
            }

            $announcement->delete();

            return $this->successResponse(null, 'PPDB announcement deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'PPDB_ANNOUNCEMENT_DELETION_ERROR', null, 400);
        }
    }
}
