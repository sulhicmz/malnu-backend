<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\GPACalculationService;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class GPACalculationServiceTest extends TestCase
{
    private GPACalculationService $gpaService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gpaService = new GPACalculationService();
    }

    public function testConvertToNumericWithAGrade()
    {
        $result = $this->gpaService->convertToNumeric(95);
        $this->assertEquals(4.0, $result);
    }

    public function testConvertToNumericWithBGrade()
    {
        $result = $this->gpaService->convertToNumeric(85);
        $this->assertEquals(3.3, $result);
    }

    public function testConvertToNumericWithCGrade()
    {
        $result = $this->gpaService->convertToNumeric(75);
        $this->assertEquals(3.0, $result);
    }

    public function testConvertToNumericWithDGrade()
    {
        $result = $this->gpaService->convertToNumeric(55);
        $this->assertEquals(1.3, $result);
    }

    public function testConvertToNumericWithFGrade()
    {
        $result = $this->gpaService->convertToNumeric(40);
        $this->assertEquals(0.0, $result);
    }

    public function testConvertToNumericGradeBoundaries()
    {
        $this->assertEquals(4.0, $this->gpaService->convertToNumeric(90));
        $this->assertEquals(3.7, $this->gpaService->convertToNumeric(89));
        $this->assertEquals(3.3, $this->gpaService->convertToNumeric(80));
        $this->assertEquals(3.0, $this->gpaService->convertToNumeric(75));
        $this->assertEquals(2.0, $this->gpaService->convertToNumeric(60));
        $this->assertEquals(1.0, $this->gpaService->convertToNumeric(45));
    }

    public function testConvertLetterToNumericWithValidGrades()
    {
        $this->assertEquals(4.0, $this->gpaService->convertLetterToNumeric('A'));
        $this->assertEquals(3.7, $this->gpaService->convertLetterToNumeric('A-'));
        $this->assertEquals(3.0, $this->gpaService->convertLetterToNumeric('B'));
        $this->assertEquals(2.0, $this->gpaService->convertLetterToNumeric('C'));
        $this->assertEquals(1.0, $this->gpaService->convertLetterToNumeric('D'));
        $this->assertEquals(0.0, $this->gpaService->convertLetterToNumeric('F'));
    }

    public function testConvertLetterToNumericWithCaseInsensitivity()
    {
        $this->assertEquals(4.0, $this->gpaService->convertLetterToNumeric('a'));
        $this->assertEquals(4.0, $this->gpaService->convertLetterToNumeric('A'));
        $this->assertEquals(3.7, $this->gpaService->convertLetterToNumeric('a-'));
    }

    public function testConvertLetterToNumericWithInvalidGrade()
    {
        $result = $this->gpaService->convertLetterToNumeric('X');
        $this->assertEquals(0.0, $result);
    }

    public function testConvertNumericToLetter()
    {
        $this->assertEquals('A', $this->gpaService->convertNumericToLetter(4.0));
        $this->assertEquals('A-', $this->gpaService->convertNumericToLetter(3.7));
        $this->assertEquals('B+', $this->gpaService->convertNumericToLetter(3.5));
        $this->assertEquals('B', $this->gpaService->convertNumericToLetter(3.0));
        $this->assertEquals('C', $this->gpaService->convertNumericToLetter(2.0));
        $this->assertEquals('D', $this->gpaService->convertNumericToLetter(1.0));
        $this->assertEquals('F', $this->gpaService->convertNumericToLetter(0.5));
    }

    public function testSetCustomGradeScale()
    {
        $customScale = [
            'A' => 5.0,
            'B' => 4.0,
            'C' => 3.0,
        ];

        $this->gpaService->setGradeScale($customScale);

        $this->assertEquals(5.0, $this->gpaService->convertLetterToNumeric('A'));
        $this->assertEquals(4.0, $this->gpaService->convertLetterToNumeric('B'));
    }

    public function testCustomGradeScalePersists()
    {
        $originalScale = $this->gpaService->convertLetterToNumeric('A');

        $customScale = [
            'A' => 5.0,
            'B' => 4.0,
        ];

        $this->gpaService->setGradeScale($customScale);
        $customResult = $this->gpaService->convertLetterToNumeric('A');

        $this->assertNotEquals($originalScale, $customResult);
        $this->assertEquals(5.0, $customResult);
    }
}
