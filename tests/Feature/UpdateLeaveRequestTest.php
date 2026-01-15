<?php

namespace Tests\Feature;

use App\Http\Requests\Attendance\UpdateLeaveRequest;
use PHPUnit\Framework\TestCase;

class UpdateLeaveRequestTest extends TestCase
{
    public function test_authorized()
    {
        $request = new UpdateLeaveRequest();
        $this->assertTrue($request->authorize());
    }

    public function test_rules_contain_comments_field()
    {
        $request = new UpdateLeaveRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('comments', $rules);
    }

    public function test_rules_contain_optional_comments()
    {
        $request = new UpdateLeaveRequest();
        $rules = $request->rules();

        $this->assertContains('sometimes', explode('|', $rules['comments']));
    }

    public function test_rules_contain_type_validation()
    {
        $request = new UpdateLeaveRequest();
        $rules = $request->rules();

        $this->assertContains('string', explode('|', $rules['comments']));
    }

    public function test_rules_contain_max_length_validation()
    {
        $request = new UpdateLeaveRequest();
        $rules = $request->rules();

        $this->assertContains('max:1000', explode('|', $rules['comments']));
    }
}
