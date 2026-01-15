<?php

namespace Tests\Feature;

use App\Http\Requests\SchoolManagement\StoreStudent;
use PHPUnit\Framework\TestCase;

class StoreStudentTest extends TestCase
{
    public function test_authorized()
    {
        $request = new StoreStudent();
        $this->assertTrue($request->authorize());
    }

    public function test_rules_contain_required_fields()
    {
        $request = new StoreStudent();
        $rules = $request->rules();

        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('nisn', $rules);
        $this->assertArrayHasKey('class_id', $rules);
        $this->assertArrayHasKey('enrollment_year', $rules);
        $this->assertArrayHasKey('status', $rules);

        $this->assertContains('required', explode('|', $rules['name']));
        $this->assertContains('required', explode('|', $rules['nisn']));
        $this->assertContains('required', explode('|', $rules['class_id']));
        $this->assertContains('required', explode('|', $rules['enrollment_year']));
        $this->assertContains('required', explode('|', $rules['status']));
    }

    public function test_rules_contain_type_validation()
    {
        $request = new StoreStudent();
        $rules = $request->rules();

        $this->assertContains('string', explode('|', $rules['name']));
        $this->assertContains('string', explode('|', $rules['nisn']));
        $this->assertContains('email', explode('|', $rules['email']));
        $this->assertContains('integer', explode('|', $rules['class_id']));
        $this->assertContains('integer', explode('|', $rules['enrollment_year']));
    }

    public function test_rules_contain_unique_validation()
    {
        $request = new StoreStudent();
        $rules = $request->rules();

        $this->assertContains('unique:students,nisn', explode('|', $rules['nisn']));
        $this->assertContains('unique:students,email', explode('|', $rules['email']));
    }

    public function test_rules_contain_exists_validation()
    {
        $request = new StoreStudent();
        $rules = $request->rules();

        $this->assertContains('exists:classes,id', explode('|', $rules['class_id']));
    }

    public function test_rules_contain_in_validation_for_status()
    {
        $request = new StoreStudent();
        $rules = $request->rules();

        $this->assertContains('in:active,inactive,graduated', explode('|', $rules['status']));
    }

    public function test_rules_contain_range_validation_for_enrollment_year()
    {
        $request = new StoreStudent();
        $rules = $request->rules();

        $this->assertContains('min:1900', explode('|', $rules['enrollment_year']));
        $this->assertContains('max:2100', explode('|', $rules['enrollment_year']));
    }

    public function test_rules_contain_max_length_validation()
    {
        $request = new StoreStudent();
        $rules = $request->rules();

        $this->assertContains('max:255', explode('|', $rules['name']));
        $this->assertContains('max:50', explode('|', $rules['nisn']));
        $this->assertContains('max:255', explode('|', $rules['email']));
    }

    public function test_rules_have_nullable_email()
    {
        $request = new StoreStudent();
        $rules = $request->rules();

        $this->assertContains('nullable', explode('|', $rules['email']));
    }
}
