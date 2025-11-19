<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\ClassModel;
use App\Traits\Cacheable;
use Tests\TestCase;

class PerformanceOptimizationTest extends TestCase
{
    /**
     * Test that Cacheable trait is properly implemented in User model
     */
    public function test_user_model_has_cacheable_trait(): void
    {
        $traits = class_uses(User::class);
        $this->assertContains(Cacheable::class, $traits);
        
        // Test that caching methods exist
        $this->assertTrue(method_exists(User::class, 'getCached'));
        $this->assertTrue(method_exists(User::class, 'setCached'));
        $this->assertTrue(method_exists(User::class, 'forgetCached'));
        $this->assertTrue(method_exists(User::class, 'getAllCached'));
        $this->assertTrue(method_exists(User::class, 'getByEmailCached'));
        $this->assertTrue(method_exists(User::class, 'getByUsernameCached'));
        $this->assertTrue(method_exists(User::class, 'clearRelatedCache'));
    }
    
    /**
     * Test that Cacheable trait is properly implemented in Student model
     */
    public function test_student_model_has_cacheable_trait(): void
    {
        $traits = class_uses(Student::class);
        $this->assertContains(Cacheable::class, $traits);
        
        // Test that caching methods exist
        $this->assertTrue(method_exists(Student::class, 'getCached'));
        $this->assertTrue(method_exists(Student::class, 'setCached'));
        $this->assertTrue(method_exists(Student::class, 'forgetCached'));
        $this->assertTrue(method_exists(Student::class, 'getAllCached'));
        $this->assertTrue(method_exists(Student::class, 'getByUserIdCached'));
        $this->assertTrue(method_exists(Student::class, 'getByClassIdCached'));
        $this->assertTrue(method_exists(Student::class, 'clearRelatedCache'));
    }
    
    /**
     * Test that Cacheable trait is properly implemented in ClassModel
     */
    public function test_class_model_has_cacheable_trait(): void
    {
        $traits = class_uses(ClassModel::class);
        $this->assertContains(Cacheable::class, $traits);
        
        // Test that caching methods exist
        $this->assertTrue(method_exists(ClassModel::class, 'getCached'));
        $this->assertTrue(method_exists(ClassModel::class, 'setCached'));
        $this->assertTrue(method_exists(ClassModel::class, 'forgetCached'));
        $this->assertTrue(method_exists(ClassModel::class, 'getAllCached'));
        $this->assertTrue(method_exists(ClassModel::class, 'getByNameCached'));
        $this->assertTrue(method_exists(ClassModel::class, 'getByAcademicYearCached'));
        $this->assertTrue(method_exists(ClassModel::class, 'clearRelatedCache'));
    }
    
    /**
     * Test that cache constants exist
     */
    public function test_cache_constants_exist(): void
    {
        $this->assertTrue(defined('App\Models\User::CACHE_TTL_MINUTES'));
        $this->assertTrue(defined('App\Models\SchoolManagement\Student::CACHE_TTL_MINUTES'));
        $this->assertTrue(defined('App\Models\SchoolManagement\ClassModel::CACHE_TTL_MINUTES'));
    }
}