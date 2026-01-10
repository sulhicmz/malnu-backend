<?php

declare(strict_types=1);

namespace Tests\Feature;

use Hyperf\DbConnection\Db;
use App\Models\ComplianceRequirement;
use App\Models\AccreditationStandard;
use App\Models\PolicyAndProcedure;
use App\Models\StaffEvaluation;
use App\Models\ProfessionalDevelopment;
use App\Models\BudgetAllocation;
use App\Models\Expense;
use App\Models\InventoryItem;
use App\Models\VendorContract;
use App\Models\InstitutionalMetric;
use App\Models\Staff;

class SchoolAdministrationTest extends \Hyperf\Testing\Client
{
    protected function setUp(): void
    {
        parent::setUp();
        Db::statement('SET FOREIGN_KEY_CHECKS=0;');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Db::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function test_can_create_compliance_requirement()
    {
        $staff = Staff::create([
            'user_id' => Db::raw('(UUID())'),
            'position' => 'Administrator',
            'department' => 'Administration',
            'join_date' => '2024-01-01',
            'status' => 'active',
        ]);

        $data = [
            'name' => 'GDPR Compliance',
            'description' => 'Ensure data protection compliance',
            'category' => 'Data Protection',
            'regulatory_body' => 'GDPR Authority',
            'status' => 'pending',
            'due_date' => '2024-12-31',
            'priority' => 'high',
            'responsible_staff_id' => $staff->id,
        ];

        $response = $this->post('/api/administration/compliance', $data);

        $this->assertSame(200, $response->getStatusCode());
        $json = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($json['success']);
        $this->assertArrayHasKey('data', $json);
    }

    public function test_can_get_compliance_requirements()
    {
        $response = $this->get('/api/administration/compliance');

        $this->assertSame(200, $response->getStatusCode());
        $json = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($json['success']);
    }

    public function test_can_create_policy()
    {
        $user = \App\Models\User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        $data = [
            'title' => 'Data Protection Policy',
            'category' => 'Data Protection',
            'policy_number' => 'POL-001',
            'content' => 'Policy content here...',
            'effective_date' => '2024-01-01',
            'status' => 'active',
            'author_id' => $user->id,
            'version' => 1,
        ];

        $response = $this->post('/api/administration/policies', $data);

        $this->assertSame(200, $response->getStatusCode());
        $json = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($json['success']);
    }

    public function test_can_create_staff_evaluation()
    {
        $staff1 = Staff::create([
            'user_id' => Db::raw('(UUID())'),
            'position' => 'Teacher',
            'department' => 'Academic',
            'join_date' => '2023-01-01',
            'status' => 'active',
        ]);

        $staff2 = Staff::create([
            'user_id' => Db::raw('(UUID())'),
            'position' => 'Principal',
            'department' => 'Administration',
            'join_date' => '2022-01-01',
            'status' => 'active',
        ]);

        $data = [
            'staff_id' => $staff1->id,
            'evaluator_id' => $staff2->id,
            'evaluation_date' => '2024-01-15',
            'evaluation_type' => 'Annual Review',
            'academic_year' => '2023-2024',
            'overall_score' => 85.5,
            'rating' => 'excellent',
            'status' => 'draft',
        ];

        $response = $this->post('/api/administration/evaluations', $data);

        $this->assertSame(200, $response->getStatusCode());
        $json = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($json['success']);
    }

    public function test_can_create_professional_development()
    {
        $staff = Staff::create([
            'user_id' => Db::raw('(UUID())'),
            'position' => 'Teacher',
            'department' => 'Academic',
            'join_date' => '2023-01-01',
            'status' => 'active',
        ]);

        $data = [
            'staff_id' => $staff->id,
            'title' => 'Advanced Pedagogy Training',
            'training_type' => 'Internal',
            'provider' => 'School District',
            'start_date' => '2024-02-01',
            'end_date' => '2024-02-05',
            'duration_hours' => 40,
            'location' => 'School Main Hall',
            'status' => 'planned',
            'internal' => true,
        ];

        $response = $this->post('/api/administration/professional-development', $data);

        $this->assertSame(200, $response->getStatusCode());
        $json = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($json['success']);
    }

    public function test_can_create_budget_allocation()
    {
        $staff = Staff::create([
            'user_id' => Db::raw('(UUID())'),
            'position' => 'Finance Manager',
            'department' => 'Finance',
            'join_date' => '2022-01-01',
            'status' => 'active',
        ]);

        $data = [
            'budget_code' => 'ACAD-2024-001',
            'name' => 'Academic Department Budget',
            'category' => 'Operations',
            'department' => 'Academic',
            'academic_year' => '2023-2024',
            'allocated_amount' => 500000.00,
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'status' => 'active',
            'manager_id' => $staff->id,
        ];

        $response = $this->post('/api/administration/budget', $data);

        $this->assertSame(200, $response->getStatusCode());
        $json = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($json['success']);
    }

    public function test_can_create_and_approve_expense()
    {
        $budget = BudgetAllocation::create([
            'budget_code' => 'BUD-001',
            'name' => 'General Budget',
            'category' => 'Operations',
            'academic_year' => '2023-2024',
            'allocated_amount' => 100000.00,
            'spent_amount' => 0,
            'remaining_amount' => 100000.00,
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'status' => 'active',
        ]);

        $staff1 = Staff::create([
            'user_id' => Db::raw('(UUID())'),
            'position' => 'Department Head',
            'department' => 'Academic',
            'join_date' => '2022-01-01',
            'status' => 'active',
        ]);

        $staff2 = Staff::create([
            'user_id' => Db::raw('(UUID())'),
            'position' => 'Finance Manager',
            'department' => 'Finance',
            'join_date' => '2022-01-01',
            'status' => 'active',
        ]);

        $data = [
            'budget_allocation_id' => $budget->id,
            'description' => 'Teaching Supplies Purchase',
            'amount' => 2500.00,
            'expense_date' => '2024-01-15',
            'category' => 'Supplies',
            'requester_id' => $staff1->id,
            'status' => 'pending',
        ];

        $createResponse = $this->post('/api/administration/expenses', $data);

        $this->assertSame(200, $createResponse->getStatusCode());
        $json = json_decode($createResponse->getBody()->getContents(), true);
        $this->assertTrue($json['success']);
        $expenseId = $json['data']['id'];

        $approveResponse = $this->post("/api/administration/expenses/{$expenseId}/approve", [
            'approver_id' => $staff2->id,
        ]);

        $this->assertSame(200, $approveResponse->getStatusCode());
        $json = json_decode($approveResponse->getBody()->getContents(), true);
        $this->assertTrue($json['success']);
    }

    public function test_can_create_inventory_item()
    {
        $staff = Staff::create([
            'user_id' => Db::raw('(UUID())'),
            'position' => 'Facilities Manager',
            'department' => 'Operations',
            'join_date' => '2022-01-01',
            'status' => 'active',
        ]);

        $data = [
            'name' => 'Projector Epson PowerLite',
            'code' => 'INV-001',
            'category' => 'Audiovisual',
            'type' => 'Equipment',
            'quantity' => 10,
            'minimum_quantity' => 3,
            'unit' => 'units',
            'unit_cost' => 450.00,
            'location' => 'Main Storage Room A',
            'condition' => 'good',
            'purchase_date' => '2023-06-15',
            'status' => 'available',
            'responsible_staff_id' => $staff->id,
        ];

        $response = $this->post('/api/administration/inventory', $data);

        $this->assertSame(200, $response->getStatusCode());
        $json = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($json['success']);
    }

    public function test_can_create_vendor_contract()
    {
        $staff = Staff::create([
            'user_id' => Db::raw('(UUID())'),
            'position' => 'Procurement Manager',
            'department' => 'Administration',
            'join_date' => '2022-01-01',
            'status' => 'active',
        ]);

        $data = [
            'vendor_name' => 'Office Supplies Co',
            'contact_person' => 'John Smith',
            'email' => 'john@officesupplies.com',
            'phone' => '+1-555-123-4567',
            'address' => '123 Main St, City, ST 12345',
            'service_type' => 'Office Supplies',
            'contract_number' => 'CTR-2024-001',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'contract_value' => 50000.00,
            'status' => 'active',
            'manager_id' => $staff->id,
        ];

        $response = $this->post('/api/administration/vendors', $data);

        $this->assertSame(200, $response->getStatusCode());
        $json = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($json['success']);
    }

    public function test_can_create_institutional_metric()
    {
        $staff = Staff::create([
            'user_id' => Db::raw('(UUID())'),
            'position' => 'Data Analyst',
            'department' => 'Academic',
            'join_date' => '2022-01-01',
            'status' => 'active',
        ]);

        $data = [
            'metric_name' => 'Student Attendance Rate',
            'metric_type' => 'Performance',
            'category' => 'Academic',
            'value' => 95.50,
            'unit' => 'percentage',
            'metric_date' => '2024-01-31',
            'academic_year' => '2023-2024',
            'target_value' => 95.00,
            'data_source_staff_id' => $staff->id,
        ];

        $response = $this->post('/api/administration/metrics', $data);

        $this->assertSame(200, $response->getStatusCode());
        $json = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($json['success']);
    }

    public function test_can_get_reports()
    {
        $response = $this->get('/api/administration/reports?type=all');

        $this->assertSame(200, $response->getStatusCode());
        $json = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($json['success']);
        $this->assertArrayHasKey('data', $json);
    }
}
