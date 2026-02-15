<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Attendance\LeaveRequest;
use App\Models\Attendance\LeaveType;
use App\Models\User;
use Hypervel\Foundation\Testing\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AttendanceDomainModelsTest extends TestCase
{
    /**
     * Test leave request model configuration.
     */
    public function testLeaveRequestModelConfiguration(): void
    {
        $leaveRequest = new LeaveRequest();
        
        $this->assertEquals('id', $leaveRequest->getKeyName());
        $this->assertIsArray($leaveRequest->getFillable());
        $this->assertIsArray($leaveRequest->getCasts());
    }

    /**
     * Test leave request relationships.
     */
    public function testLeaveRequestRelationships(): void
    {
        $leaveRequest = new LeaveRequest();
        
        $userRelation = $leaveRequest->user();
        $this->assertEquals('user_id', $userRelation->getForeignKeyName());
        
        $leaveTypeRelation = $leaveRequest->leaveType();
        $this->assertEquals('leave_type_id', $leaveTypeRelation->getForeignKeyName());
    }

    /**
     * Test leave type model configuration.
     */
    public function testLeaveTypeModelConfiguration(): void
    {
        $leaveType = new LeaveType();
        
        $this->assertEquals('id', $leaveType->getKeyName());
        $this->assertIsArray($leaveType->getFillable());
    }

    /**
     * Test leave type relationship.
     */
    public function testLeaveTypeRelationship(): void
    {
        $leaveType = new LeaveType();
        
        $relation = $leaveType->leaveRequests();
        $this->assertEquals('leave_type_id', $relation->getForeignKeyName());
    }
}
