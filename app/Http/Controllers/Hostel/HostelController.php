<?php

declare(strict_types=1);

namespace App\Http\Controllers\Hostel;

use App\Http\Controllers\AbstractController;
use App\Services\HostelManagementService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Psr\Http\Message\ResponseInterface;

/**
 * @Controller(prefix="api/hostel")
 */
class HostelController extends AbstractController
{
    private HostelManagementService $hostelService;

    public function __construct(HostelManagementService $hostelService)
    {
        $this->hostelService = $hostelService;
    }

    /**
     * Create a new hostel
     * @PostMapping(path="hostels")
     */
    public function createHostel(): ResponseInterface
    {
        $data = $this->request->all();
        
        if (empty($data['name'])) {
            return $this->response->json([
                'success' => false,
                'message' => 'Hostel name is required'
            ])->withStatus(400);
        }

        try {
            $hostel = $this->hostelService->createHostel($data);
            
            return $this->response->json([
                'success' => true,
                'data' => $hostel,
                'message' => 'Hostel created successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to create hostel: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Get hostel by ID
     * @GetMapping(path="hostels/{id}")
     */
    public function getHostel(string $id): ResponseInterface
    {
        try {
            $hostel = $this->hostelService->getHostel($id);
            
            if (!$hostel) {
                return $this->response->json([
                    'success' => false,
                    'message' => 'Hostel not found'
                ])->withStatus(404);
            }
            
            return $this->response->json([
                'success' => true,
                'data' => $hostel
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to retrieve hostel: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Update hostel
     * @PutMapping(path="hostels/{id}")
     */
    public function updateHostel(string $id): ResponseInterface
    {
        $data = $this->request->all();
        
        try {
            $result = $this->hostelService->updateHostel($id, $data);
            
            if (!$result) {
                return $this->response->json([
                    'success' => false,
                    'message' => 'Hostel not found'
                ])->withStatus(404);
            }
            
            return $this->response->json([
                'success' => true,
                'message' => 'Hostel updated successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to update hostel: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Delete hostel
     * @DeleteMapping(path="hostels/{id}")
     */
    public function deleteHostel(string $id): ResponseInterface
    {
        try {
            $result = $this->hostelService->deleteHostel($id);
            
            if (!$result) {
                return $this->response->json([
                    'success' => false,
                    'message' => 'Hostel not found'
                ])->withStatus(404);
            }
            
            return $this->response->json([
                'success' => true,
                'message' => 'Hostel deleted successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to delete hostel: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Create a new room
     * @PostMapping(path="rooms")
     */
    public function createRoom(): ResponseInterface
    {
        $data = $this->request->all();
        
        if (empty($data['hostel_id']) || empty($data['room_number'])) {
            return $this->response->json([
                'success' => false,
                'message' => 'Hostel ID and room number are required'
            ])->withStatus(400);
        }

        try {
            $room = $this->hostelService->createRoom($data);
            
            return $this->response->json([
                'success' => true,
                'data' => $room,
                'message' => 'Room created successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to create room: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Assign student to room
     * @PostMapping(path="assignments")
     */
    public function assignStudentToRoom(): ResponseInterface
    {
        $data = $this->request->all();
        
        if (empty($data['student_id']) || empty($data['room_id']) || empty($data['hostel_id'])) {
            return $this->response->json([
                'success' => false,
                'message' => 'Student ID, room ID, and hostel ID are required'
            ])->withStatus(400);
        }

        try {
            $assignment = $this->hostelService->assignStudentToRoom($data);
            
            return $this->response->json([
                'success' => true,
                'data' => $assignment,
                'message' => 'Student assigned to room successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to assign student: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Checkout student from room
     * @PostMapping(path="assignments/{assignmentId}/checkout")
     */
    public function checkoutStudent(string $assignmentId): ResponseInterface
    {
        try {
            $result = $this->hostelService->checkoutStudentFromRoom($assignmentId);
            
            if (!$result) {
                return $this->response->json([
                    'success' => false,
                    'message' => 'Assignment not found'
                ])->withStatus(404);
            }
            
            return $this->response->json([
                'success' => true,
                'message' => 'Student checked out successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to checkout student: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Create maintenance request
     * @PostMapping(path="maintenance-requests")
     */
    public function createMaintenanceRequest(): ResponseInterface
    {
        $data = $this->request->all();
        
        if (empty($data['hostel_id']) || empty($data['description'])) {
            return $this->response->json([
                'success' => false,
                'message' => 'Hostel ID and description are required'
            ])->withStatus(400);
        }

        try {
            $request = $this->hostelService->createMaintenanceRequest($data);
            
            return $this->response->json([
                'success' => true,
                'data' => $request,
                'message' => 'Maintenance request created successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to create maintenance request: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Get hostel occupancy
     * @GetMapping(path="hostels/{hostelId}/occupancy")
     */
    public function getHostelOccupancy(string $hostelId): ResponseInterface
    {
        try {
            $occupancy = $this->hostelService->getHostelOccupancy($hostelId);
            
            if (empty($occupancy)) {
                return $this->response->json([
                    'success' => false,
                    'message' => 'Hostel not found'
                ])->withStatus(404);
            }
            
            return $this->response->json([
                'success' => true,
                'data' => $occupancy
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to retrieve occupancy: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Create visitor record
     * @PostMapping(path="visitors")
     */
    public function createVisitor(): ResponseInterface
    {
        $data = $this->request->all();
        
        if (empty($data['hostel_id']) || empty($data['visitor_name'])) {
            return $this->response->json([
                'success' => false,
                'message' => 'Hostel ID and visitor name are required'
            ])->withStatus(400);
        }

        try {
            $visitor = $this->hostelService->createVisitor($data);
            
            return $this->response->json([
                'success' => true,
                'data' => $visitor,
                'message' => 'Visitor registered successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to register visitor: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Check in student
     * @PostMapping(path="attendance/checkin")
     */
    public function checkInStudent(): ResponseInterface
    {
        $data = $this->request->all();
        
        if (empty($data['student_id']) || empty($data['hostel_id'])) {
            return $this->response->json([
                'success' => false,
                'message' => 'Student ID and hostel ID are required'
            ])->withStatus(400);
        }

        try {
            $attendance = $this->hostelService->checkInStudent($data);
            
            return $this->response->json([
                'success' => true,
                'data' => $attendance,
                'message' => 'Student checked in successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to check in student: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Get student boarding info
     * @GetMapping(path="students/{studentId}/boarding-info")
     */
    public function getStudentBoardingInfo(string $studentId): ResponseInterface
    {
        try {
            $info = $this->hostelService->getStudentBoardingInfo($studentId);
            
            return $this->response->json([
                'success' => true,
                'data' => $info
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to retrieve boarding info: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Get available rooms
     * @GetMapping(path="hostels/{hostelId}/available-rooms")
     */
    public function getAvailableRooms(string $hostelId): ResponseInterface
    {
        try {
            $filters = $this->request->all();
            $rooms = $this->hostelService->getAvailableRooms($hostelId, $filters);
            
            return $this->response->json([
                'success' => true,
                'data' => $rooms
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to retrieve available rooms: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }
}
