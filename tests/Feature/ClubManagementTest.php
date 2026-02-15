<?php

declare (strict_types = 1);

namespace Tests\Feature;

use HyperfTest\Http\TestCase;
use App\Models\ClubManagement\Club;
use App\Models\ClubManagement\ClubMembership;
use App\Models\ClubManagement\Activity;
use App\Models\ClubManagement\ActivityAttendance;
use App\Models\ClubManagement\ClubAdvisor;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Teacher;
use App\Services\ClubManagementService;
use Hypervel\Support\Facades\DB;

class ClubManagementTest extends TestCase
{
    private ClubManagementService $clubManagementService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clubManagementService = $this->getContainer()->get(ClubManagementService::class);
    }

    public function testCanCreateClub()
    {
        $teacher = \App\Models\SchoolManagement\Teacher::create([
            'user_id' => Db::raw('(UUID())'),
            'nip' => '12345',
            'expertise' => 'Mathematics',
            'status' => 'active',
        ]);

        $data = [
            'name' => 'Math Club',
            'description' => 'A club for mathematics students',
            'category' => 'academic',
            'max_members' => 30,
            'advisor_id' => $teacher->id,
        ];

        $club = $this->clubManagementService->createClub($data);

        $this->assertEquals('Math Club', $club->name);
        $this->assertEquals('academic', $club->category);
        $this->assertEquals(30, $club->max_members);
        $this->assertEquals($teacher->id, $club->advisor_id);
    }

    public function testCanUpdateClub()
    {
        $club = Club::create([
            'name' => 'Science Club',
            'category' => 'academic',
            'status' => 'active',
        ]);

        $data = [
            'name' => 'Science Club Updated',
            'description' => 'Updated description',
            'status' => 'inactive',
        ];

        $updatedClub = $this->clubManagementService->updateClub($club->id, $data);

        $this->assertEquals('Science Club Updated', $updatedClub->name);
        $this->assertEquals('Updated description', $updatedClub->description);
        $this->assertEquals('inactive', $updatedClub->status);
    }

    public function testCanDeleteClub()
    {
        $club = Club::create([
            'name' => 'Sports Club',
            'status' => 'active',
        ]);

        $deleted = $this->clubManagementService->deleteClub($club->id);

        $this->assertTrue($deleted);
        $this->assertNull(Club::find($club->id));
    }

    public function testCanAddMember()
    {
        $club = Club::create(['name' => 'Debate Club', 'status' => 'active']);
        $student = \App\Models\SchoolManagement\Student::create([
            'user_id' => Db::raw('(UUID())'),
            'nisn' => '1234567890',
            'status' => 'active',
        ]);

        $membership = $this->clubManagementService->addMember($club->id, $student->id, 'officer');

        $this->assertEquals($club->id, $membership->club_id);
        $this->assertEquals($student->id, $membership->student_id);
        $this->assertEquals('officer', $membership->role);
        $this->assertNotNull($membership->joined_date);
    }

    public function testCanUpdateMemberRole()
    {
        $club = Club::create(['name' => 'Drama Club', 'status' => 'active']);
        $student = \App\Models\SchoolManagement\Student::create([
            'user_id' => Db::raw('(UUID())'),
            'nisn' => '9876543210',
            'status' => 'active',
        ]);

        $membership = $this->clubManagementService->addMember($club->id, $student->id, 'member');

        $updatedMembership = $this->clubManagementService->updateMemberRole($membership->club_id, $membership->student_id, 'president');

        $this->assertEquals('president', $updatedMembership->role);
    }

    public function testCanRemoveMember()
    {
        $club = Club::create(['name' => 'Music Club', 'status' => 'active']);
        $student = \App\Models\SchoolManagement\Student::create([
            'user_id' => Db::raw('(UUID())'),
            'nisn' => '5555555555',
            'status' => 'active',
        ]);

        $membership = $this->clubManagementService->addMember($club->id, $student->id, 'member');
        $deleted = $this->clubManagementService->removeMember($club->id, $student->id);

        $this->assertTrue($deleted);
        $remainingMembership = ClubMembership::where('club_id', $club->id)->where('student_id', $student->id)->first();
        $this->assertNull($remainingMembership);
    }

    public function testCanCreateActivity()
    {
        $club = Club::create(['name' => 'Robotics Club', 'status' => 'active']);

        $data = [
            'name' => 'Robotics Competition',
            'description' => 'Annual robotics competition',
            'start_date' => '2026-02-01 10:00:00',
            'end_date' => '2026-02-01 16:00:00',
            'location' => 'School Hall',
            'max_attendees' => 50,
        ];

        $activity = $this->clubManagementService->createActivity($club->id, $data);

        $this->assertEquals('Robotics Competition', $activity->name);
        $this->assertEquals('Annual robotics competition', $activity->description);
        $this->assertEquals($club->id, $activity->club_id);
        $this->assertEquals('School Hall', $activity->location);
        $this->assertEquals(50, $activity->max_attendees);
    }

    public function testCanUpdateActivity()
    {
        $club = Club::create(['name' => 'Chess Club', 'status' => 'active']);
        $activity = Activity::create([
            'club_id' => $club->id,
            'name' => 'Chess Tournament',
            'status' => 'active',
        ]);

        $data = [
            'name' => 'Chess Tournament Updated',
            'location' => 'Auditorium',
        ];

        $updatedActivity = $this->clubManagementService->updateActivity($activity->id, $data);

        $this->assertEquals('Chess Tournament Updated', $updatedActivity->name);
        $this->assertEquals('Auditorium', $updatedActivity->location);
    }

    public function testCanDeleteActivity()
    {
        $club = Club::create(['name' => 'Art Club', 'status' => 'active']);
        $activity = Activity::create([
            'club_id' => $club->id,
            'name' => 'Art Exhibition',
            'status' => 'active',
        ]);

        $deleted = $this->clubManagementService->deleteActivity($activity->id);

        $this->assertTrue($deleted);
        $this->assertNull(Activity::find($activity->id));
    }

    public function testCanMarkAttendance()
    {
        $club = Club::create(['name' => 'Sports Club', 'status' => 'active']);
        $student = \App\Models\SchoolManagement\Student::create([
            'user_id' => Db::raw('(UUID())'),
            'nisn' => '1111111111',
            'status' => 'active',
        ]);

        $activity = Activity::create([
            'club_id' => $club->id,
            'name' => 'Basketball Game',
            'status' => 'active',
        ]);

        $attendance = $this->clubManagementService->markAttendance($activity->id, $student->id, 'present', 'Good game!');

        $this->assertEquals($activity->id, $attendance->activity_id);
        $this->assertEquals($student->id, $attendance->student_id);
        $this->assertEquals('present', $attendance->status);
        $this->assertEquals('Good game!', $attendance->notes);
    }

    public function testCanAssignAdvisor()
    {
        $club = Club::create(['name' => 'Debate Club', 'status' => 'active']);
        $teacher = \App\Models\SchoolManagement\Teacher::create([
            'user_id' => Db::raw('(UUID())'),
            'nip' => '22222',
            'expertise' => 'English Language',
            'status' => 'active',
        ]);

        $advisor = $this->clubManagementService->assignAdvisor($club->id, $teacher->id);

        $this->assertEquals($club->id, $advisor->club_id);
        $this->assertEquals($teacher->id, $advisor->teacher_id);
        $this->assertNotNull($advisor->assigned_date);
    }

    public function testCanRemoveAdvisor()
    {
        $club = Club::create(['name' => 'Music Club', 'status' => 'active']);
        $teacher = \App\Models\SchoolManagement\Teacher::create([
            'user_id' => Db::raw('(UUID())'),
            'nip' => '33333',
            'expertise' => 'Music Theory',
            'status' => 'active',
        ]);

        $advisor = $this->clubManagementService->assignAdvisor($club->id, $teacher->id);
        $deleted = $this->clubManagementService->removeAdvisor($club->id, $teacher->id);

        $this->assertTrue($deleted);
        $remainingAdvisor = ClubAdvisor::where('club_id', $club->id)->where('teacher_id', $teacher->id)->first();
        $this->assertNull($remainingAdvisor);
    }

    public function testCheckClubCapacity()
    {
        $club = Club::create([
            'name' => 'Capacity Test Club',
            'max_members' => 10,
            'status' => 'active',
        ]);

        $capacity = $this->clubManagementService->checkClubCapacity($club->id);

        $this->assertEquals(10, $capacity['total']);
        $this->assertEquals(10, $capacity['available']);
        $this->assertEquals(0, $capacity['used']);
    }

    public function testClubMembershipWithRoleValidation()
    {
        $club = Club::create(['name' => 'Test Club', 'status' => 'active']);
        $student = \App\Models\SchoolManagement\Student::create([
            'user_id' => Db::raw('(UUID())'),
            'nisn' => '9999999999',
            'status' => 'active',
        ]);

        $this->clubManagementService->addMember($club->id, $student->id, 'invalid_role');
    }

    public function testActivityValidation()
    {
        $club = Club::create(['name' => 'Validation Club', 'status' => 'active']);
        $student = \App\Models\SchoolManagement\Student::create([
            'user_id' => Db::raw('(UUID())'),
            'nisn' => '8888888888',
            'status' => 'active',
        ]);

        $this->clubManagementService->createActivity($club->id, ['name' => '']);
    }
}
