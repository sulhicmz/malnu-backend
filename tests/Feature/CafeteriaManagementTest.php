<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Cafeteria\MealPlan;
use App\Models\Cafeteria\StudentMealPreference;
use App\Models\Cafeteria\CafeteriaInventory;
use App\Models\Cafeteria\MealPayment;
use App\Models\Cafeteria\Vendor;
use App\Models\SchoolManagement\Student;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class CafeteriaManagementTest extends TestCase
{
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_create_meal_plan()
    {
        $token = JWTAuth::fromUser($this->user);

        $mealPlanData = [
            'name' => 'Weekly Menu - Week 1',
            'description' => 'Standard meal plan for first week',
            'start_date' => '2026-01-13',
            'end_date' => '2026-01-19',
            'status' => 'active',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/cafeteria/meal-plans', $mealPlanData);

        $response->assertStatus(201)
                 ->assertJson([
                      'success' => true,
                      'message' => 'Meal plan created successfully'
                  ]);
    }

    public function test_get_meal_plan()
    {
        $token = JWTAuth::fromUser($this->user);

        $mealPlan = MealPlan::create([
            'name' => 'Test Meal Plan',
            'start_date' => '2026-01-13',
            'end_date' => '2026-01-19',
            'status' => 'active',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/cafeteria/meal-plans/' . $mealPlan->id);

        $response->assertStatus(200)
                 ->assertJson([
                      'success' => true,
                      'message' => 'Meal plan retrieved successfully'
                  ]);
    }

    public function test_update_meal_plan()
    {
        $token = JWTAuth::fromUser($this->user);

        $mealPlan = MealPlan::create([
            'name' => 'Test Meal Plan',
            'start_date' => '2026-01-13',
            'end_date' => '2026-01-19',
            'status' => 'active',
        ]);

        $updateData = [
            'name' => 'Updated Meal Plan',
            'status' => 'inactive',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/cafeteria/meal-plans/' . $mealPlan->id, $updateData);

        $response->assertStatus(200)
                 ->assertJson([
                      'success' => true,
                      'message' => 'Meal plan updated successfully'
                  ]);
    }

    public function test_delete_meal_plan()
    {
        $token = JWTAuth::fromUser($this->user);

        $mealPlan = MealPlan::create([
            'name' => 'Test Meal Plan',
            'start_date' => '2026-01-13',
            'end_date' => '2026-01-19',
            'status' => 'active',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/cafeteria/meal-plans/' . $mealPlan->id);

        $response->assertStatus(200)
                 ->assertJson([
                      'success' => true,
                      'message' => 'Meal plan deleted successfully'
                  ]);
    }

    public function test_create_student_preference()
    {
        $token = JWTAuth::fromUser($this->user);

        $student = Student::factory()->create();

        $preferenceData = [
            'student_id' => $student->id,
            'requires_special_diet' => true,
            'dietary_restrictions' => 'Vegetarian',
            'allergies' => 'Peanuts, Tree nuts',
            'subsidy_eligible' => true,
            'subsidy_amount' => 5.00,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/cafeteria/student-preferences', $preferenceData);

        $response->assertStatus(201)
                 ->assertJson([
                      'success' => true,
                      'message' => 'Student preference created successfully'
                  ]);
    }

    public function test_get_student_preferences()
    {
        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/cafeteria/student-preferences');

        $response->assertStatus(200)
                 ->assertJson([
                      'success' => true,
                      'message' => 'Student preferences retrieved successfully'
                  ]);
    }

    public function test_create_inventory_item()
    {
        $token = JWTAuth::fromUser($this->user);

        $vendor = Vendor::create([
            'name' => 'Test Vendor',
            'contact_person' => 'John Doe',
            'phone' => '1234567890',
            'email' => 'vendor@example.com',
            'address' => '123 Test St',
            'status' => 'active',
        ]);

        $inventoryData = [
            'item_name' => 'Rice',
            'category' => 'Grains',
            'quantity' => 50,
            'unit' => 'kg',
            'unit_cost' => 2.50,
            'vendor_id' => $vendor->id,
            'minimum_stock_level' => 10,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/cafeteria/inventory', $inventoryData);

        $response->assertStatus(201)
                 ->assertJson([
                      'success' => true,
                      'message' => 'Inventory item created successfully'
                  ]);
    }

    public function test_get_inventory()
    {
        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/cafeteria/inventory');

        $response->assertStatus(200)
                 ->assertJson([
                      'success' => true,
                      'message' => 'Inventory retrieved successfully'
                  ]);
    }

    public function test_low_stock_items()
    {
        $token = JWTAuth::fromUser($this->user);

        CafeteriaInventory::create([
            'item_name' => 'Low Stock Item',
            'category' => 'Vegetables',
            'quantity' => 5,
            'unit' => 'kg',
            'unit_cost' => 1.50,
            'minimum_stock_level' => 10,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/cafeteria/inventory/low-stock?threshold=10');

        $response->assertStatus(200)
                 ->assertJson([
                      'success' => true,
                      'message' => 'Low stock items retrieved successfully'
                  ]);
    }

    public function test_create_meal_payment()
    {
        $token = JWTAuth::fromUser($this->user);

        $student = Student::factory()->create();

        $paymentData = [
            'student_id' => $student->id,
            'amount' => 25.00,
            'subsidy_amount' => 5.00,
            'amount_paid' => 20.00,
            'payment_method' => 'cash',
            'payment_date' => '2026-01-10',
            'status' => 'paid',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/cafeteria/payments', $paymentData);

        $response->assertStatus(201)
                 ->assertJson([
                      'success' => true,
                      'message' => 'Meal payment created successfully'
                  ]);
    }

    public function test_create_vendor()
    {
        $token = JWTAuth::fromUser($this->user);

        $vendorData = [
            'name' => 'Food Supplier Inc.',
            'contact_person' => 'Jane Smith',
            'phone' => '9876543210',
            'email' => 'contact@foodsupplier.com',
            'address' => '456 Food St, City',
            'status' => 'active',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/cafeteria/vendors', $vendorData);

        $response->assertStatus(201)
                 ->assertJson([
                      'success' => true,
                      'message' => 'Vendor created successfully'
                  ]);
    }

    public function test_get_vendors()
    {
        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/cafeteria/vendors');

        $response->assertStatus(200)
                 ->assertJson([
                      'success' => true,
                      'message' => 'Vendors retrieved successfully'
                  ]);
    }

    public function test_validation_meal_plan_missing_fields()
    {
        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/cafeteria/meal-plans', []);

        $response->assertStatus(422)
                 ->assertJson([
                      'success' => false,
                      'error' => [
                          'code' => 'VALIDATION_ERROR',
                      ],
                  ]);
    }

    public function test_validation_inventory_missing_fields()
    {
        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/cafeteria/inventory', []);

        $response->assertStatus(422)
                 ->assertJson([
                      'success' => false,
                      'error' => [
                          'code' => 'VALIDATION_ERROR',
                      ],
                  ]);
    }
}
