<?php

namespace Tests\Feature;

use App\Http\Requests\SchoolManagement\StoreTeacher;
use PHPUnit\Framework\TestCase;

class StoreTeacherTest extends TestCase
{
    public function test_authorized()
    {
        $request = new StoreTeacher();
        $this->assertTrue($request->authorize());
    }

    public function test_rules_contain_required_fields()
    {
        $request = new StoreTeacher();
        $rules = $request->rules();

        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('nip', $rules);
        $this->assertArrayHasKey('subject_id', $rules);
        $this->assertArrayHasKey('join_date', $rules);
        $this->assertArrayHasKey('status', $rules);

        $this->assertContains('required', explode('|', $rules['name']));
        $this->assertContains('required', explode('|', $rules['nip']));
        $this->assertContains('required', explode('|', $rules['subject_id']));
        $this->assertContains('required', explode('|', $rules['join_date']));
        $this->assertContains('required', explode('|', $rules['status']));
    }

    public function test_rules_contain_type_validation()
    {
        $request = new StoreTeacher();
        $rules = $request->rules();

        $this->assertContains('string', explode('|', $rules['name']));
        $this->assertContains('string', explode('|', $rules['nip']));
        $this->assertContains('email', explode('|', $rules['email']));
        $this->assertContains('integer', explode('|', $rules['subject_id']));
        $this->assertContains('date', explode('|', $rules['join_date']));
    }

    public function test_rules_contain_unique_validation()
    {
        $request = new StoreTeacher();
        $rules = $request->rules();

        $this->assertContains('unique:teachers,nip', explode('|', $rules['nip']));
        $this->assertContains('unique:teachers,email', explode('|', $rules['email']));
    }

    public function test_rules_contain_exists_validation()
    {
        $request = new StoreTeacher();
        $rules = $request->rules();

        $this->assertContains('exists:subjects,id', explode('|', $rules['subject_id']));
    }

    public function test_rules_contain_date_validation_for_join_date()
    {
        $request = new StoreTeacher();
        $rules = $request->rules();

        $this->assertContains('before_or_equal:today', explode('|', $rules['join_date']));
    }

    public function test_rules_contain_in_validation_for_status()
    {
        $request = new StoreTeacher();
        $rules = $request->rules();

        $this->assertContains('in:active,inactive', explode('|', $rules['status']));
    }

    public function test_rules_contain_max_length_validation()
    {
        $request = new StoreTeacher();
        $rules = $request->rules();

        $this->assertContains('max:255', explode('|', $rules['name']));
        $this->assertContains('max:50', explode('|', $rules['nip']));
        $this->assertContains('max:255', explode('|', $rules['email']));
    }

    public function test_rules_have_nullable_email()
    {
        $request = new StoreTeacher();
        $rules = $request->rules();

        $this->assertContains('nullable', explode('|', $rules['email']));
    }
}
