<?php

namespace Tests\Feature;

use App\Http\Requests\SchoolManagement\UpdateStudent;
use PHPUnit\Framework\TestCase;

class UpdateStudentTest extends TestCase
{
    public function test_authorized()
    {
        $request = new UpdateStudent();
        $this->assertTrue($request->authorize());
    }

    public function test_rules_contain_optional_fields()
    {
        $request = new UpdateStudent();
        $rules = $request->rules();

        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('nisn', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('status', $rules);

        $this->assertContains('sometimes', explode('|', $rules['name']));
        $this->assertContains('sometimes', explode('|', $rules['nisn']));
        $this->assertContains('sometimes', explode('|', $rules['status']));
    }

    public function test_rules_contain_type_validation()
    {
        $request = new UpdateStudent();
        $rules = $request->rules();

        $this->assertContains('string', explode('|', $rules['name']));
        $this->assertContains('string', explode('|', $rules['nisn']));
        $this->assertContains('email', explode('|', $rules['email']));
    }

    public function test_rules_contain_unique_validation_with_exclusion()
    {
        $request = new UpdateStudent();
        $rules = $request->rules();

        $this->assertStringContainsString('unique:students,nisn', $rules['nisn']);
        $this->assertStringContainsString('unique:students,email', $rules['email']);
    }

    public function test_rules_contain_max_length_validation()
    {
        $request = new UpdateStudent();
        $rules = $request->rules();

        $this->assertContains('max:255', explode('|', $rules['name']));
        $this->assertContains('max:50', explode('|', $rules['nisn']));
        $this->assertContains('max:255', explode('|', $rules['email']));
    }

    public function test_rules_contain_in_validation_for_status()
    {
        $request = new UpdateStudent();
        $rules = $request->rules();

        $this->assertContains('in:active,inactive,graduated', explode('|', $rules['status']));
    }

    public function test_rules_have_nullable_email()
    {
        $request = new UpdateStudent();
        $rules = $request->rules();

        $this->assertContains('nullable', explode('|', $rules['email']));
    }
}
