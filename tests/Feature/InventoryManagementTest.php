<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\SchoolManagement\SchoolInventory;
use App\Models\SchoolManagement\AssetCategory;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class InventoryManagementTest extends TestCase
{
    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    public function test_can_list_inventory_items()
    {
        SchoolInventory::factory()->count(3)->create([
            'status' => 'available'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/school/inventory');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'data',
                         'per_page',
                         'current_page',
                         'last_page',
                         'total'
                     ],
                     'message',
                     'timestamp'
                 ]);
    }

    public function test_can_create_inventory_item()
    {
        $category = AssetCategory::factory()->create();

        $inventoryData = [
            'name' => 'Test Computer',
            'category' => 'Electronics',
            'quantity' => 5,
            'location' => 'Lab A',
            'condition' => 'good',
            'purchase_date' => '2024-01-01',
            'category_id' => $category->id,
            'status' => 'available',
            'purchase_cost' => 1500.00,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/school/inventory', $inventoryData);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Inventory item created successfully'
                 ]);
    }

    public function test_validation_fails_without_required_fields()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/school/inventory', [
            'name' => 'Test Item'
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'success',
                     'error' => [
                         'message',
                         'code',
                         'details'
                     ]
                 ]);
    }

    public function test_can_show_inventory_item()
    {
        $item = SchoolInventory::factory()->create([
            'status' => 'available'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/school/inventory/{$item->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Inventory item retrieved successfully'
                 ]);
    }

    public function test_can_update_inventory_item()
    {
        $item = SchoolInventory::factory()->create([
            'status' => 'available'
        ]);

        $updateData = [
            'name' => 'Updated Item Name',
            'condition' => 'excellent'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/school/inventory/{$item->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Inventory item updated successfully'
                 ]);

        $this->assertDatabaseHas('school_inventory', [
            'id' => $item->id,
            'name' => 'Updated Item Name',
            'condition' => 'excellent'
        ]);
    }

    public function test_can_delete_inventory_item()
    {
        $item = SchoolInventory::factory()->create([
            'status' => 'available'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/school/inventory/{$item->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Inventory item deleted successfully'
                 ]);

        $this->assertDatabaseMissing('school_inventory', [
            'id' => $item->id
        ]);
    }

    public function test_cannot_delete_assigned_item()
    {
        $item = SchoolInventory::factory()->create([
            'status' => 'assigned',
            'assigned_to' => $this->user->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/school/inventory/{$item->id}");

        $response->assertStatus(400);
    }

    public function test_can_filter_inventory_by_status()
    {
        SchoolInventory::factory()->count(2)->create(['status' => 'available']);
        SchoolInventory::factory()->count(1)->create(['status' => 'assigned']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/school/inventory?status=available');

        $response->assertStatus(200);

        $data = $response->json('data.data');
        $this->assertCount(2, $data);
    }

    public function test_can_filter_inventory_by_category()
    {
        $category = AssetCategory::factory()->create();
        SchoolInventory::factory()->count(3)->create(['category_id' => $category->id]);
        SchoolInventory::factory()->count(2)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/school/inventory?category_id={$category->id}");

        $response->assertStatus(200);

        $data = $response->json('data.data');
        $this->assertCount(3, $data);
    }
}
