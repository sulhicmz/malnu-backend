<?php

namespace App\Http\Controllers\Cafeteria;

use App\Http\Controllers\Api\BaseController;
use App\Services\CafeteriaManagementService;
use App\Models\Cafeteria\StudentMealPreference;
use App\Models\Cafeteria\MealPayment;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class CafeteriaController extends BaseController
{
    protected CafeteriaManagementService $cafeteriaService;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container,
        CafeteriaManagementService $cafeteriaService
    ) {
        parent::__construct($request, $response, $container);
        $this->cafeteriaService = $cafeteriaService;
    }

    public function indexMealPlans()
    {
        try {
            $mealPlans = $this->cafeteriaService->getAllMealPlans();
            return $this->successResponse($mealPlans, 'Meal plans retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeMealPlan()
    {
        try {
            $data = $this->request->all();
            $requiredFields = ['name', 'start_date', 'end_date'];
            $errors = [];

            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $mealPlan = $this->cafeteriaService->createMealPlan($data);
            return $this->successResponse($mealPlan, 'Meal plan created successfully', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function showMealPlan($id)
    {
        try {
            $mealPlan = $this->cafeteriaService->getMealPlan($id);
            if (!$mealPlan) {
                return $this->notFoundResponse('Meal plan not found');
            }
            return $this->successResponse($mealPlan, 'Meal plan retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function updateMealPlan($id)
    {
        try {
            $data = $this->request->all();
            $result = $this->cafeteriaService->updateMealPlan($id, $data);
            if (!$result) {
                return $this->notFoundResponse('Meal plan not found');
            }
            return $this->successResponse(null, 'Meal plan updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function destroyMealPlan($id)
    {
        try {
            $result = $this->cafeteriaService->deleteMealPlan($id);
            if (!$result) {
                return $this->notFoundResponse('Meal plan not found');
            }
            return $this->successResponse(null, 'Meal plan deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function indexStudentPreferences()
    {
        try {
            $studentId = $this->request->query('student_id');
            if ($studentId) {
                $preferences = $this->cafeteriaService->getStudentPreferences($studentId);
            } else {
                $preferences = StudentMealPreference::with(['student', 'mealPlan'])->get();
            }
            return $this->successResponse($preferences, 'Student preferences retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeStudentPreference()
    {
        try {
            $data = $this->request->all();
            $requiredFields = ['student_id'];
            $errors = [];

            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $preference = $this->cafeteriaService->createStudentPreference($data);
            return $this->successResponse($preference, 'Student preference created successfully', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function showStudentPreference($id)
    {
        try {
            $preference = $this->cafeteriaService->getStudentPreference($id);
            if (!$preference) {
                return $this->notFoundResponse('Student preference not found');
            }
            return $this->successResponse($preference, 'Student preference retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function updateStudentPreference($id)
    {
        try {
            $data = $this->request->all();
            $result = $this->cafeteriaService->updateStudentPreference($id, $data);
            if (!$result) {
                return $this->notFoundResponse('Student preference not found');
            }
            return $this->successResponse(null, 'Student preference updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function destroyStudentPreference($id)
    {
        try {
            $result = $this->cafeteriaService->deleteStudentPreference($id);
            if (!$result) {
                return $this->notFoundResponse('Student preference not found');
            }
            return $this->successResponse(null, 'Student preference deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function indexInventory()
    {
        try {
            $inventory = $this->cafeteriaService->getAllInventory();
            return $this->successResponse($inventory, 'Inventory retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeInventory()
    {
        try {
            $data = $this->request->all();
            $requiredFields = ['item_name', 'category', 'quantity', 'unit_cost'];
            $errors = [];

            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $inventory = $this->cafeteriaService->createInventoryItem($data);
            return $this->successResponse($inventory, 'Inventory item created successfully', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function showInventory($id)
    {
        try {
            $inventory = $this->cafeteriaService->getInventoryItem($id);
            if (!$inventory) {
                return $this->notFoundResponse('Inventory item not found');
            }
            return $this->successResponse($inventory, 'Inventory item retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function updateInventory($id)
    {
        try {
            $data = $this->request->all();
            $result = $this->cafeteriaService->updateInventoryItem($id, $data);
            if (!$result) {
                return $this->notFoundResponse('Inventory item not found');
            }
            return $this->successResponse(null, 'Inventory item updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function destroyInventory($id)
    {
        try {
            $result = $this->cafeteriaService->deleteInventoryItem($id);
            if (!$result) {
                return $this->notFoundResponse('Inventory item not found');
            }
            return $this->successResponse(null, 'Inventory item deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function lowStock()
    {
        try {
            $threshold = (int) $this->request->query('threshold', 10);
            $items = $this->cafeteriaService->getLowStockItems($threshold);
            return $this->successResponse($items, 'Low stock items retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function indexPayments()
    {
        try {
            $studentId = $this->request->query('student_id');
            if ($studentId) {
                $payments = $this->cafeteriaService->getStudentPayments($studentId);
            } else {
                $payments = MealPayment::with('student')->get();
            }
            return $this->successResponse($payments, 'Meal payments retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storePayment()
    {
        try {
            $data = $this->request->all();
            $requiredFields = ['student_id', 'amount', 'payment_method', 'payment_date'];
            $errors = [];

            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $payment = $this->cafeteriaService->createMealPayment($data);
            return $this->successResponse($payment, 'Meal payment created successfully', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function showPayment($id)
    {
        try {
            $payment = $this->cafeteriaService->getMealPayment($id);
            if (!$payment) {
                return $this->notFoundResponse('Meal payment not found');
            }
            return $this->successResponse($payment, 'Meal payment retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function updatePayment($id)
    {
        try {
            $data = $this->request->all();
            $result = $this->cafeteriaService->updateMealPayment($id, $data);
            if (!$result) {
                return $this->notFoundResponse('Meal payment not found');
            }
            return $this->successResponse(null, 'Meal payment updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function destroyPayment($id)
    {
        try {
            $result = $this->cafeteriaService->deleteMealPayment($id);
            if (!$result) {
                return $this->notFoundResponse('Meal payment not found');
            }
            return $this->successResponse(null, 'Meal payment deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function indexVendors()
    {
        try {
            $vendors = $this->cafeteriaService->getAllVendors();
            return $this->successResponse($vendors, 'Vendors retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeVendor()
    {
        try {
            $data = $this->request->all();
            $requiredFields = ['name', 'contact_person', 'phone'];
            $errors = [];

            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $vendor = $this->cafeteriaService->createVendor($data);
            return $this->successResponse($vendor, 'Vendor created successfully', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function showVendor($id)
    {
        try {
            $vendor = $this->cafeteriaService->getVendor($id);
            if (!$vendor) {
                return $this->notFoundResponse('Vendor not found');
            }
            return $this->successResponse($vendor, 'Vendor retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function updateVendor($id)
    {
        try {
            $data = $this->request->all();
            $result = $this->cafeteriaService->updateVendor($id, $data);
            if (!$result) {
                return $this->notFoundResponse('Vendor not found');
            }
            return $this->successResponse(null, 'Vendor updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function destroyVendor($id)
    {
        try {
            $result = $this->cafeteriaService->deleteVendor($id);
            if (!$result) {
                return $this->notFoundResponse('Vendor not found');
            }
            return $this->successResponse(null, 'Vendor deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}
