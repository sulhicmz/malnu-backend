<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function testBasicTest(): void
    {
        $this->assertTrue(true);
    }
    
    /**
     * Test basic string operations.
     */
    public function testStringOperations(): void
    {
        $string = 'hello world';
        $this->assertEquals('HELLO WORLD', strtoupper($string));
        $this->assertEquals(11, strlen($string));
    }
    
    /**
     * Test basic array operations.
     */
    public function testArrayOperations(): void
    {
        $array = [1, 2, 3, 4, 5];
        $this->assertCount(5, $array);
        $this->assertContains(3, $array);
        $this->assertEquals(1, $array[0]);
    }
}
