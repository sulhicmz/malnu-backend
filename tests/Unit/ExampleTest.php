<?php

declare(strict_types=1);

namespace Tests\Unit;

use Hypervel\Foundation\Testing\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ExampleTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function testBasicUnitTest(): void
    {
        $this->assertTrue(true);
        $this->assertFalse(false);
        $this->assertEquals(1, 1);
        $this->assertNotEquals(1, 2);
    }

    /**
     * Test string operations
     */
    public function testStringOperations(): void
    {
        $string = 'hypervel';
        
        $this->assertIsString($string);
        $this->assertEquals('hypervel', $string);
        $this->assertStringContainsString('hyper', $string);
        $this->assertEquals(8, strlen($string));
    }

    /**
     * Test array operations
     */
    public function testArrayOperations(): void
    {
        $array = ['a', 'b', 'c'];
        
        $this->assertIsArray($array);
        $this->assertCount(3, $array);
        $this->assertContains('a', $array);
        $this->assertContains('b', $array);
        $this->assertContains('c', $array);
    }
}
