<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Api\SchoolManagement\StudentController;
use App\Http\Controllers\Api\SchoolManagement\TeacherController;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Teacher;
use Hyperf\HttpServer\Request;
use Hyperf\Test\HttpTestCase;

class CrudOperationsTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        Student::query()->delete();
        Teacher::query()->delete();
    }

    public function test_student_controller_has_required_properties()
    {
        $controller = new StudentController($this->getContainer()->get(Request::class), null, null);
        
        $this->assertEquals(Student::class, $controller->getModelClass());
        $this->assertEquals('Student', $this->getPropertyValue($controller, 'resourceName'));
        $this->assertEquals(['class'], $this->getPropertyValue($controller, 'relationships'));
        $this->assertEquals(['name', 'nisn', 'class_id', 'enrollment_year', 'status'], $this->getPropertyValue($controller, 'requiredFields'));
        $this->assertEquals(['nisn', 'email'], $this->getPropertyValue($controller, 'uniqueFields'));
        $this->assertEquals(['class_id', 'status'], $this->getPropertyValue($controller, 'filters'));
        $this->assertEquals(['name', 'nisn'], $this->getPropertyValue($controller, 'searchFields'));
        $this->assertEquals('name', $this->getPropertyValue($controller, 'defaultOrderBy'));
        $this->assertEquals('asc', $this->getPropertyValue($controller, 'defaultOrderDirection'));
    }

    public function test_teacher_controller_has_required_properties()
    {
        $controller = new TeacherController($this->getContainer()->get(Request::class), null, null);
        
        $this->assertEquals(Teacher::class, $controller->getModelClass());
        $this->assertEquals('Teacher', $this->getPropertyValue($controller, 'resourceName'));
        $this->assertEquals(['subject', 'class'], $this->getPropertyValue($controller, 'relationships'));
        $this->assertEquals(['name', 'nip', 'subject_id', 'join_date'], $this->getPropertyValue($controller, 'requiredFields'));
        $this->assertEquals(['nip', 'email'], $this->getPropertyValue($controller, 'uniqueFields'));
        $this->assertEquals(['subject_id', 'class_id', 'status'], $this->getPropertyValue($controller, 'filters'));
        $this->assertEquals(['name', 'nip'], $this->getPropertyValue($controller, 'searchFields'));
    }

    public function test_get_model_class_returns_correct_class()
    {
        $studentController = new StudentController($this->getContainer()->get(Request::class), null, null);
        $teacherController = new TeacherController($this->getContainer()->get(Request::class), null, null);
        
        $this->assertEquals(Student::class, $studentController->getModelClass());
        $this->assertEquals(Teacher::class, $teacherController->getModelClass());
    }

    public function test_get_model_class_throws_exception_if_not_set()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('$model property must be set in controller');
        
        $this->callMethod(new class {
            use \App\Traits\CrudOperations;
        }, 'getModelClass');
    }

    public function test_crud_operations_trait_provides_all_crud_methods()
    {
        $controller = new StudentController($this->getContainer()->get(Request::class), null, null);
        
        $this->assertTrue(method_exists($controller, 'index'));
        $this->assertTrue(method_exists($controller, 'store'));
        $this->assertTrue(method_exists($controller, 'show'));
        $this->assertTrue(method_exists($controller, 'update'));
        $this->assertTrue(method_exists($controller, 'destroy'));
        $this->assertTrue(method_exists($controller, 'getModelClass'));
        $this->assertTrue(method_exists($controller, 'beforeIndex'));
        $this->assertTrue(method_exists($controller, 'afterIndex'));
        $this->assertTrue(method_exists($controller, 'beforeStore'));
        $this->assertTrue(method_exists($controller, 'afterStore'));
        $this->assertTrue(method_exists($controller, 'beforeShow'));
        $this->assertTrue(method_exists($controller, 'afterShow'));
        $this->assertTrue(method_exists($controller, 'beforeUpdate'));
        $this->assertTrue(method_exists($controller, 'afterUpdate'));
        $this->assertTrue(method_exists($controller, 'beforeDestroy'));
        $this->assertTrue(method_exists($controller, 'afterDestroy'));
    }

    public function test_hooks_return_defaults()
    {
        $controller = new StudentController($this->getContainer()->get(Request::class), null, null);
        
        $mockQuery = $this->createMock('Hyperf\Database\Model\Builder');
        $mockQuery->method('with')->willReturn($mockQuery);
        
        $result = $this->callMethod($controller, 'beforeIndex', [$mockQuery]);
        $this->assertSame($mockQuery, $result);
        
        $result = $this->callMethod($controller, 'afterIndex', []);
        $this->assertEquals([], $result);
        
        $result = $this->callMethod($controller, 'beforeStore', [[]]);
        $this->assertEquals([], $result);
        
        $result = $this->callMethod($controller, 'afterStore', [null]);
        $this->assertNull($result);
    }

    protected function getPropertyValue(object $object, string $property): mixed
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
        
        return $property->getValue($object);
    }

    protected function callMethod(object $object, string $method, array $parameters = []): mixed
    {
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);
        
        return $method->invokeArgs($object, $parameters);
    }
}
