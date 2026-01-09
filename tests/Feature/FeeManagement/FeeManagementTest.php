<?php

declare(strict_types = 1);

namespace Tests\Feature\FeeManagement;

use HyperfTest\HttpTestCase;
use App\Models\FeeManagement\FeeType;
use App\Models\FeeManagement\FeeStructure;
use App\Models\FeeManagement\FeeInvoice;
use App\Models\FeeManagement\FeePayment;
use App\Models\FeeManagement\FeeWaiver;
use App\Models\User;
use App\Models\SchoolManagement\Student;

class FeeManagementTest extends HttpTestCase
{
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::create([
            'name' => 'Test Admin',
            'email' => 'testadmin@example.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
        ]);

        $response = $this->post('/auth/login', [
            'email' => 'testadmin@example.com',
            'password' => 'password123',
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        $this->token = $data['data']['token'] ?? '';
    }

    protected function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->token,
        ];
    }

    public function test_create_fee_type()
    {
        $response = $this->post('/fees/fee-types', [
            'name' => 'Tuition Fee',
            'code' => 'TUITION',
            'category' => 'tuition',
            'description' => 'Annual tuition fee',
        ], $this->getHeaders());

        $this->assertSame(201, $response->getStatusCode());
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('data', $data);
        $this->assertEquals('Tuition Fee', $data['data']['name']);
    }

    public function test_create_fee_type_validation()
    {
        $response = $this->post('/fees/fee-types', [], $this->getHeaders());

        $this->assertSame(422, $response->getStatusCode());
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('name', $data['error']['details']);
        $this->assertArrayHasKey('code', $data['error']['details']);
    }

    public function test_list_fee_types()
    {
        FeeType::create([
            'name' => 'Lab Fee',
            'code' => 'LAB',
            'category' => 'other',
            'is_active' => true,
        ]);

        $response = $this->get('/fees/fee-types', $this->getHeaders());

        $this->assertSame(200, $response->getStatusCode());
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($data['success']);
        $this->assertIsArray($data['data']['data']);
    }

    public function test_create_fee_structure()
    {
        $feeType = FeeType::create([
            'name' => 'Tuition Fee',
            'code' => 'TUITION',
            'category' => 'tuition',
        ]);

        $response = $this->post('/fees/fee-structures', [
            'fee_type_id' => $feeType->id,
            'grade_level' => '10',
            'academic_year' => '2024-2025',
            'amount' => 5000.00,
            'payment_schedule' => 'annually',
            'late_fee_percentage' => 5.00,
        ], $this->getHeaders());

        $this->assertSame(201, $response->getStatusCode());
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('10', $data['data']['grade_level']);
    }

    public function test_create_invoice()
    {
        $student = Student::create([
            'user_id' => User::first()->id,
            'nisn' => '1234567890',
            'class_id' => 'class-1',
            'birth_date' => '2010-01-01',
            'birth_place' => 'Jakarta',
            'address' => 'Test Address',
            'enrollment_date' => '2024-01-01',
            'status' => 'active',
        ]);

        $feeType = FeeType::create([
            'name' => 'Tuition Fee',
            'code' => 'TUITION',
            'category' => 'tuition',
        ]);

        $feeStructure = FeeStructure::create([
            'fee_type_id' => $feeType->id,
            'grade_level' => '10',
            'academic_year' => '2024-2025',
            'amount' => 5000.00,
        ]);

        $response = $this->post('/fees/invoices', [
            'student_id' => $student->id,
            'fee_structure_id' => $feeStructure->id,
            'issue_date' => '2024-01-01',
            'apply_waivers' => false,
        ], $this->getHeaders());

        $this->assertSame(201, $response->getStatusCode());
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('data', $data);
        $this->assertEquals('pending', $data['data']['status']);
    }

    public function test_list_invoices()
    {
        $response = $this->get('/fees/invoices', $this->getHeaders());

        $this->assertSame(200, $response->getStatusCode());
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($data['success']);
        $this->assertIsArray($data['data']['data']);
    }

    public function test_create_payment()
    {
        $student = Student::create([
            'user_id' => User::first()->id,
            'nisn' => '0987654321',
            'class_id' => 'class-2',
            'birth_date' => '2010-02-02',
            'birth_place' => 'Bandung',
            'address' => 'Test Address 2',
            'enrollment_date' => '2024-01-01',
            'status' => 'active',
        ]);

        $feeType = FeeType::create([
            'name' => 'Registration Fee',
            'code' => 'REG',
            'category' => 'other',
        ]);

        $feeStructure = FeeStructure::create([
            'fee_type_id' => $feeType->id,
            'grade_level' => '10',
            'academic_year' => '2024-2025',
            'amount' => 1000.00,
        ]);

        $invoice = FeeInvoice::create([
            'student_id' => $student->id,
            'fee_structure_id' => $feeStructure->id,
            'invoice_number' => 'INV-20240101-0001',
            'issue_date' => '2024-01-01',
            'due_date' => '2024-02-01',
            'subtotal' => 1000.00,
            'tax' => 0,
            'discount' => 0,
            'late_fee' => 0,
            'total_amount' => 1000.00,
            'balance_amount' => 1000.00,
            'status' => 'pending',
        ]);

        $response = $this->post('/fees/payments', [
            'invoice_id' => $invoice->id,
            'user_id' => User::first()->id,
            'payment_method' => 'credit_card',
            'amount' => 500.00,
            'status' => 'completed',
        ], $this->getHeaders());

        $this->assertSame(201, $response->getStatusCode());
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals(500.00, $data['data']['amount']);
    }

    public function test_create_payment_invalid_amount()
    {
        $response = $this->post('/fees/payments', [
            'invoice_id' => 'test-id',
            'user_id' => User::first()->id,
            'payment_method' => 'credit_card',
            'amount' => -100.00,
        ], $this->getHeaders());

        $this->assertSame(422, $response->getStatusCode());
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('amount', $data['error']['details']);
    }

    public function test_list_payments()
    {
        $response = $this->get('/fees/payments', $this->getHeaders());

        $this->assertSame(200, $response->getStatusCode());
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($data['success']);
        $this->assertIsArray($data['data']['data']);
    }

    public function test_create_waiver()
    {
        $student = Student::create([
            'user_id' => User::first()->id,
            'nisn' => '1122334455',
            'class_id' => 'class-3',
            'birth_date' => '2010-03-03',
            'birth_place' => 'Surabaya',
            'address' => 'Test Address 3',
            'enrollment_date' => '2024-01-01',
            'status' => 'active',
        ]);

        $response = $this->post('/fees/waivers', [
            'student_id' => $student->id,
            'waiver_type' => 'scholarship',
            'waiver_code' => 'SCHOLARSHIP-001',
            'discount_percentage' => 50.00,
            'reason' => 'Academic excellence scholarship',
            'valid_from' => '2024-01-01',
            'status' => 'active',
        ], $this->getHeaders());

        $this->assertSame(201, $response->getStatusCode());
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('scholarship', $data['data']['waiver_type']);
    }

    public function test_list_waivers()
    {
        $response = $this->get('/fees/waivers', $this->getHeaders());

        $this->assertSame(200, $response->getStatusCode());
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($data['success']);
        $this->assertIsArray($data['data']['data']);
    }

    public function test_financial_report()
    {
        $response = $this->get('/fees/reports/financial', $this->getHeaders());

        $this->assertSame(200, $response->getStatusCode());
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('total_billed', $data['data']);
        $this->assertArrayHasKey('total_paid', $data['data']);
        $this->assertArrayHasKey('payment_rate', $data['data']);
    }

    public function test_student_outstanding_balance()
    {
        $student = Student::create([
            'user_id' => User::first()->id,
            'nisn' => '5566778899',
            'class_id' => 'class-4',
            'birth_date' => '2010-04-04',
            'birth_place' => 'Medan',
            'address' => 'Test Address 4',
            'enrollment_date' => '2024-01-01',
            'status' => 'active',
        ]);

        $response = $this->get('/fees/students/' . $student->id . '/outstanding', $this->getHeaders());

        $this->assertSame(200, $response->getStatusCode());
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('total_outstanding', $data['data']);
        $this->assertArrayHasKey('overdue_count', $data['data']);
    }

    public function test_fee_type_filters()
    {
        FeeType::create([
            'name' => 'Library Fee',
            'code' => 'LIB',
            'category' => 'other',
            'is_active' => true,
        ]);

        $response = $this->get('/fees/fee-types?category=tuition', $this->getHeaders());

        $this->assertSame(200, $response->getStatusCode());
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($data['success']);
    }

    public function test_fee_structure_filters()
    {
        $feeType = FeeType::create([
            'name' => 'Sports Fee',
            'code' => 'SPORT',
            'category' => 'other',
        ]);

        FeeStructure::create([
            'fee_type_id' => $feeType->id,
            'grade_level' => '11',
            'academic_year' => '2024-2025',
            'amount' => 200.00,
        ]);

        $response = $this->get('/fees/fee-structures?grade_level=11&academic_year=2024-2025', $this->getHeaders());

        $this->assertSame(200, $response->getStatusCode());
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($data['success']);
    }

    public function test_invoice_filters()
    {
        $response = $this->get('/fees/invoices?status=pending', $this->getHeaders());

        $this->assertSame(200, $response->getStatusCode());
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($data['success']);
    }

    protected function tearDown(): void
    {
        FeeWaiver::query()->forceDelete();
        FeePayment::query()->forceDelete();
        FeeInvoice::query()->forceDelete();
        FeeStructure::query()->forceDelete();
        FeeType::query()->forceDelete();
        Student::query()->forceDelete();
        User::query()->forceDelete();
        parent::tearDown();
    }
}
