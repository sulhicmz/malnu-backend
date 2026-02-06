<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Requests\Attendance\ApproveLeaveRequest;
use App\Http\Requests\Attendance\RejectLeaveRequest;
use App\Http\Requests\Attendance\UpdateLeaveRequest;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class LeaveRequestValidationTest extends TestCase
{
    /**
     * Test that UpdateLeaveRequest validation passes with valid comments.
     */
    public function testUpdateLeaveRequestPassesWithValidComments(): void
    {
        $request = new UpdateLeaveRequest();
        $request->merge(['comments' => 'Updated reason for leave']);

        $validator = validator($request->all(), $request->rules());

        $this->assertFalse($validator->fails());
    }

    /**
     * Test that UpdateLeaveRequest validation passes when comments are optional.
     */
    public function testUpdateLeaveRequestPassesWithoutComments(): void
    {
        $request = new UpdateLeaveRequest();
        $request->merge([]);

        $validator = validator($request->all(), $request->rules());

        $this->assertFalse($validator->fails());
    }

    /**
     * Test that UpdateLeaveRequest validation fails with non-string comments.
     */
    public function testUpdateLeaveRequestFailsWithNonStringComments(): void
    {
        $request = new UpdateLeaveRequest();
        $request->merge(['comments' => 12345]);

        $validator = validator($request->all(), $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('comments', $validator->errors()->toArray());
    }

    /**
     * Test that UpdateLeaveRequest validation fails when comments exceed max length.
     */
    public function testUpdateLeaveRequestFailsWithTooLongComments(): void
    {
        $request = new UpdateLeaveRequest();
        $request->merge(['comments' => str_repeat('a', 1001)]);

        $validator = validator($request->all(), $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('comments', $validator->errors()->toArray());
    }

    /**
     * Test that ApproveLeaveRequest validation passes with valid approval comments.
     */
    public function testApproveLeaveRequestPassesWithValidComments(): void
    {
        $request = new ApproveLeaveRequest();
        $request->merge(['approval_comments' => 'Approved leave request']);

        $validator = validator($request->all(), $request->rules());

        $this->assertFalse($validator->fails());
    }

    /**
     * Test that ApproveLeaveRequest validation passes when approval comments are optional.
     */
    public function testApproveLeaveRequestPassesWithoutComments(): void
    {
        $request = new ApproveLeaveRequest();
        $request->merge([]);

        $validator = validator($request->all(), $request->rules());

        $this->assertFalse($validator->fails());
    }

    /**
     * Test that ApproveLeaveRequest validation fails with non-string approval comments.
     */
    public function testApproveLeaveRequestFailsWithNonStringComments(): void
    {
        $request = new ApproveLeaveRequest();
        $request->merge(['approval_comments' => 12345]);

        $validator = validator($request->all(), $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('approval_comments', $validator->errors()->toArray());
    }

    /**
     * Test that ApproveLeaveRequest validation fails when approval comments exceed max length.
     */
    public function testApproveLeaveRequestFailsWithTooLongComments(): void
    {
        $request = new ApproveLeaveRequest();
        $request->merge(['approval_comments' => str_repeat('a', 1001)]);

        $validator = validator($request->all(), $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('approval_comments', $validator->errors()->toArray());
    }

    /**
     * Test that RejectLeaveRequest validation passes with valid rejection comments.
     */
    public function testRejectLeaveRequestPassesWithValidComments(): void
    {
        $request = new RejectLeaveRequest();
        $request->merge(['approval_comments' => 'Rejected leave request']);

        $validator = validator($request->all(), $request->rules());

        $this->assertFalse($validator->fails());
    }

    /**
     * Test that RejectLeaveRequest validation passes when rejection comments are optional.
     */
    public function testRejectLeaveRequestPassesWithoutComments(): void
    {
        $request = new RejectLeaveRequest();
        $request->merge([]);

        $validator = validator($request->all(), $request->rules());

        $this->assertFalse($validator->fails());
    }

    /**
     * Test that RejectLeaveRequest validation fails with non-string rejection comments.
     */
    public function testRejectLeaveRequestFailsWithNonStringComments(): void
    {
        $request = new RejectLeaveRequest();
        $request->merge(['approval_comments' => 12345]);

        $validator = validator($request->all(), $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('approval_comments', $validator->errors()->toArray());
    }

    /**
     * Test that RejectLeaveRequest validation fails when rejection comments exceed max length.
     */
    public function testRejectLeaveRequestFailsWithTooLongComments(): void
    {
        $request = new RejectLeaveRequest();
        $request->merge(['approval_comments' => str_repeat('a', 1001)]);

        $validator = validator($request->all(), $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('approval_comments', $validator->errors()->toArray());
    }
}
