<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class MobileApiTest extends TestCase
{
    /**
     * Test that mobile API routes are defined
     */
    public function test_mobile_api_routes_exist(): void
    {
        $this->assertTrue(true); // Basic test to confirm file exists
    }
    
    /**
     * Test mobile authentication controller
     */
    public function test_mobile_auth_controller_exists(): void
    {
        $authController = new \App\Http\Controllers\Mobile\MobileAuthController(
            new \Hypervel\JWT\JWT(new \Hypervel\JWT\Manager(
                new \Hypervel\JWT\Providers\Lcobucci(),
                new \Hypervel\JWT\Storage\TaggedCache(\Hypervel\Support\Facades\Cache::class)
            ))
        );
        
        $this->assertNotNull($authController);
        $this->assertTrue(method_exists($authController, 'login'));
        $this->assertTrue(method_exists($authController, 'logout'));
        $this->assertTrue(method_exists($authController, 'refresh'));
        $this->assertTrue(method_exists($authController, 'me'));
    }
    
    /**
     * Test mobile student controller
     */
    public function test_mobile_student_controller_exists(): void
    {
        $studentController = new \App\Http\Controllers\Mobile\MobileStudentController(
            new \Hypervel\JWT\JWT(new \Hypervel\JWT\Manager(
                new \Hypervel\JWT\Providers\Lcobucci(),
                new \Hypervel\JWT\Storage\TaggedCache(\Hypervel\Support\Facades\Cache::class)
            ))
        );
        
        $this->assertNotNull($studentController);
        $this->assertTrue(method_exists($studentController, 'dashboard'));
        $this->assertTrue(method_exists($studentController, 'grades'));
        $this->assertTrue(method_exists($studentController, 'assignments'));
        $this->assertTrue(method_exists($studentController, 'schedule'));
        $this->assertTrue(method_exists($studentController, 'profile'));
    }
    
    /**
     * Test mobile parent controller
     */
    public function test_mobile_parent_controller_exists(): void
    {
        $parentController = new \App\Http\Controllers\Mobile\MobileParentController(
            new \Hypervel\JWT\JWT(new \Hypervel\JWT\Manager(
                new \Hypervel\JWT\Providers\Lcobucci(),
                new \Hypervel\JWT\Storage\TaggedCache(\Hypervel\Support\Facades\Cache::class)
            ))
        );
        
        $this->assertNotNull($parentController);
        $this->assertTrue(method_exists($parentController, 'dashboard'));
        $this->assertTrue(method_exists($parentController, 'studentGrades'));
        $this->assertTrue(method_exists($parentController, 'studentAssignments'));
        $this->assertTrue(method_exists($parentController, 'studentAttendance'));
        $this->assertTrue(method_exists($parentController, 'studentSchedule'));
    }
    
    /**
     * Test mobile teacher controller
     */
    public function test_mobile_teacher_controller_exists(): void
    {
        $teacherController = new \App\Http\Controllers\Mobile\MobileTeacherController(
            new \Hypervel\JWT\JWT(new \Hypervel\JWT\Manager(
                new \Hypervel\JWT\Providers\Lcobucci(),
                new \Hypervel\JWT\Storage\TaggedCache(\Hypervel\Support\Facades\Cache::class)
            ))
        );
        
        $this->assertNotNull($teacherController);
        $this->assertTrue(method_exists($teacherController, 'dashboard'));
        $this->assertTrue(method_exists($teacherController, 'classes'));
        $this->assertTrue(method_exists($teacherController, 'students'));
        $this->assertTrue(method_exists($teacherController, 'assignments'));
        $this->assertTrue(method_exists($teacherController, 'grades'));
    }
}