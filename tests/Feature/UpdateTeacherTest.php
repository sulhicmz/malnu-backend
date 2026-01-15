<?php

namespace Tests\Feature;

use App\Http\Requests\SchoolManagement\UpdateTeacher;
use PHPUnit\Framework\TestCase;

class UpdateTeacherTest extends TestCase
{
    public function test_authorized()
    {
        $request = new UpdateTeacher();
        $this->assertTrue($request->authorize());
    }

    public function test_rules_contain_optional_fields()
    {
        $request = new UpdateTeacher();
        $rules = $request->rules();

        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('nip', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('status', $rules);

        $this->assertContains('sometimes', explode('|', $rules['name']));
        $this->assertContains('sometimes', explode('|', $rules['nip']));
        $this->assertContains('sometimes', explode('|', $rules['status']));
    }

    public function test_rules_contain_type_validation()
    {
        $request = new UpdateTeacher();
        $rules = $request->rules();

        $this->assertContains('string', explode('|', $rules['name']));
        $this->assertContains('string', explode('|', $rules['nip']));
        $this->assertContains('email', explode('|', $rules['email']));
    }

    public function test_rules_contain_unique_validation_with_exclusion()
    {
        $request = new UpdateTeacher();
        $rules = $request->rules();

        $this->assertStringContainsString('unique:teachers,nip', $rules['nip']);
        $this->assertStringContainsString('unique:teachers,email', $rules['email']);
    }

    public function test_rules_contain_max_length_validation()
    {
        $request = new UpdateTeacher();
        $rules = $request->rules();

        $this->assertContains('max:255', explode('|', $rules['name']));
        $this->assertContains('max:50', explode('|', $rules['nip']));
        $this->assertContains('max:255', explode('|', $rules['email']));
    }

    public function test_rules_contain_in_validation_for_status()
    {
        $request = new UpdateTeacher();
        $rules = $request->rules();

        $this->assertContains('in:active,inactive', explode('|', $rules['status']));
    }

    public function test_rules_have_nullable_email()
    {
        $request = new UpdateTeacher();
        $rules = $request->rules();

        $this->assertContains('nullable', explode('|', $rules['email']));
    }
}
