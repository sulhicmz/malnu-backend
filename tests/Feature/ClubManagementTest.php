<?php

declare(strict_types=1);

namespace Tests\Feature;

use Hyperf\HttpServer\Request;
use Hyperf\Test\HttpTestCase;
use App\Services\ClubManagementService;
use App\Models\Extracurricular\Club;
use App\Models\Extracurricular\Activity;
use App\Models\Extracurricular\ClubMembership;
use App\Models\Extracurricular\ActivityAttendance;
use App\Models\Extracurricular\ClubAdvisor;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Teacher;

class ClubManagementTest extends HttpTestCase
{
    private ClubManagementService $clubService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clubService = $this->getContainer()->get(ClubManagementService::class);
    }

    public function testCreateClub()
    {
        $teacher = Teacher::create([
            'user_id' => 'teacher-user-1',
            'nip' => '12345',
            'status' => 'active',
        ]);

        $data = [
            'name' => 'Chess Club',
            'description' => 'A club for chess enthusiasts',
            'category' => 'sports',
            'max_members' => 20,
            'advisor_id' => $teacher->id,
        ];

        $club = $this->clubService->createClub($data);

        $this->assertEquals('Chess Club', $club->name);
        $this->assertEquals($teacher->id, $club->advisor_id);
    }

    public function testUpdateClub()
    {
        $club = Club::create([
            'name' => 'Drama Club',
            'category' => 'arts',
        ]);

        $data = [
            'name' => 'Theater Club',
            'description' => 'Updated description',
        ];

        $updatedClub = $this->clubService->updateClub($club->id, $data);

        $this->assertEquals('Theater Club', $updatedClub->name);
        $this->assertEquals('Updated description', $updatedClub->description);
    }

    public function testDeleteClub()
    {
        $club = Club::create([
            'name' => 'Debate Club',
            'category' => 'academic',
        ]);

        $result = $this->clubService->deleteClub($club->id);

        $this->assertTrue($result);
        $this->assertNull(Club::find($club->id));
    }

    public function testGetClubStatistics()
    {
        $teacher = Teacher::create([
            'user_id' => 'teacher-1',
            'nip' => '12345',
            'status' => 'active',
        ]);

        $club = Club::create([
            'name' => 'Science Club',
            'category' => 'academic',
            'max_members' => 15,
            'advisor_id' => $teacher->id,
        ]);

        $student = Student::create([
            'user_id' => 'student-1',
            'nisn' => '123456789',
            'status' => 'active',
        ]);

        for ($i = 0; $i < 5; $i++) {
            ClubMembership::create([
                'club_id' => $club->id,
                'student_id' => $student->id,
                'role' => 'member',
                'joined_date' => date('Y-m-d'),
            ]);
        }

        $stats = $this->clubService->getClubStatistics($club->id);

        $this->assertEquals(5, $stats['total_members']);
        $this->assertEquals(0, $stats['total_activities']);
        $this->assertEquals(0, $stats['upcoming_activities']);
    }

    public function testAddMember()
    {
        $club = Club::create([
            'name' => 'Math Club',
            'category' => 'academic',
        ]);

        $student = Student::create([
            'user_id' => 'student-1',
            'nisn' => '123456789',
            'status' => 'active',
        ]);

        $membership = $this->clubService->addMember($club->id, $student->id, 'president');

        $this->assertEquals($club->id, $membership->club_id);
        $this->assertEquals($student->id, $membership->student_id);
        $this->assertEquals('president', $membership->role);
    }

    public function testRemoveMember()
    {
        $club = Club::create([
            'name' => 'Music Club',
            'category' => 'arts',
        ]);

        $student = Student::create([
            'user_id' => 'student-1',
            'nisn' => '123456789',
            'status' => 'active',
        ]);

        $membership = ClubMembership::create([
            'club_id' => $club->id,
            'student_id' => $student->id,
            'role' => 'member',
            'joined_date' => date('Y-m-d'),
        ]);

        $result = $this->clubService->removeMember($club->id, $student->id);

        $this->assertTrue($result);
        $this->assertNull(ClubMembership::where('student_id', $student->id)->first());
    }

    public function testCreateActivity()
    {
        $club = Club::create([
            'name' => 'Art Club',
            'category' => 'arts',
        ]);

        $data = [
            'name' => 'Painting Workshop',
            'description' => 'Learn painting techniques',
            'start_date' => date('Y-m-d H:i:s', strtotime('+1 week')),
            'location' => 'Art Room A',
            'max_attendees' => 10,
        ];

        $activity = $this->clubService->createActivity($club->id, $data);

        $this->assertEquals('Painting Workshop', $activity->name);
        $this->assertEquals($club->id, $activity->club_id);
        $this->assertEquals('scheduled', $activity->status);
    }

    public function testMarkAttendance()
    {
        $club = Club::create([
            'name' => 'History Club',
            'category' => 'academic',
        ]);

        $activity = Activity::create([
            'club_id' => $club->id,
            'name' => 'Guest Lecture',
            'start_date' => date('Y-m-d H:i:s'),
            'status' => 'scheduled',
        ]);

        $student = Student::create([
            'user_id' => 'student-1',
            'nisn' => '123456789',
            'status' => 'active',
        ]);

        $attendance = $this->clubService->markAttendance($activity->id, $student->id, 'present');

        $this->assertEquals('present', $attendance->status);
        $this->assertEquals($activity->id, $attendance->activity_id);
        $this->assertEquals($student->id, $attendance->student_id);
    }

    public function testGetActivityAttendanceStatistics()
    {
        $club = Club::create([
            'name' => 'Book Club',
            'category' => 'academic',
        ]);

        $activity = Activity::create([
            'club_id' => $club->id,
            'name' => 'Book Discussion',
            'start_date' => date('Y-m-d H:i:s'),
            'status' => 'scheduled',
        ]);

        for ($i = 0; $i < 5; $i++) {
            Student::create([
                'user_id' => 'student-' . $i,
                'nisn' => '123456780' . $i,
                'status' => 'active',
            ]);
        }

        Student::chunk(5, function ($students) use ($activity) {
            foreach ($students as $student) {
                ActivityAttendance::create([
                    'activity_id' => $activity->id,
                    'student_id' => $student->id,
                    'status' => $i < 3 ? 'present' : 'absent',
                ]);
            }
        });

        $stats = $this->clubService->getActivityAttendanceStatistics($activity->id);

        $this->assertEquals(5, $stats['total']);
        $this->assertEquals(3, $stats['present']);
        $this->assertEquals(2, $stats['absent']);
    }

    public function testAssignAdvisor()
    {
        $club = Club::create([
            'name' => 'Robotics Club',
            'category' => 'technology',
        ]);

        $teacher = Teacher::create([
            'user_id' => 'teacher-1',
            'nip' => '12345',
            'status' => 'active',
        ]);

        $advisor = $this->clubService->assignAdvisor($club->id, $teacher->id, 'Faculty advisor');

        $this->assertEquals($club->id, $advisor->club_id);
        $this->assertEquals($teacher->id, $advisor->teacher_id);
        $this->assertEquals('Faculty advisor', $advisor->notes);
    }

    public function testGetUpcomingActivities()
    {
        $club = Club::create([
            'name' => 'Sports Club',
            'category' => 'sports',
        ]);

        Activity::create([
            'club_id' => $club->id,
            'name' => 'Football Practice',
            'start_date' => date('Y-m-d H:i:s', strtotime('+2 days')),
            'status' => 'scheduled',
        ]);

        Activity::create([
            'club_id' => $club->id,
            'name' => 'Basketball Game',
            'start_date' => date('Y-m-d H:i:s', strtotime('+5 days')),
            'status' => 'scheduled',
        ]);

        $activities = $this->clubService->getUpcomingActivities(10);

        $this->assertCount(2, $activities);
    }

    public function testGetStudentParticipation()
    {
        $club1 = Club::create([
            'name' => 'Drama Club',
            'category' => 'arts',
        ]);

        $club2 = Club::create([
            'name' => 'Debate Club',
            'category' => 'academic',
        ]);

        $student = Student::create([
            'user_id' => 'student-1',
            'nisn' => '123456789',
            'status' => 'active',
        ]);

        ClubMembership::create([
            'club_id' => $club1->id,
            'student_id' => $student->id,
            'role' => 'president',
            'joined_date' => date('Y-m-d'),
        ]);

        ClubMembership::create([
            'club_id' => $club2->id,
            'student_id' => $student->id,
            'role' => 'member',
            'joined_date' => date('Y-m-d'),
        ]);

        $participation = $this->clubService->getStudentParticipation($student->id);

        $this->assertEquals(2, $participation['total_clubs']);
        $this->assertCount(2, $participation['memberships']);
    }

    protected function tearDown(): void
    {
        Student::query()->delete();
        Teacher::query()->delete();
        Club::query()->delete();
        ClubMembership::query()->delete();
        Activity::query()->delete();
        ActivityAttendance::query()->delete();
        ClubAdvisor::query()->delete();
        parent::tearDown();
    }
}
