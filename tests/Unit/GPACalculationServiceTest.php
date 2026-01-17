<?php

namespace Tests\Unit;

use App\Services\GPACalculationService;
use Tests\TestCase;

class GPACalculationServiceTest extends TestCase
{
    private GPACalculationService $gpaService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gpaService = new GPACalculationService();
    }

    public function test_convert_to_numeric_with_a_grade()
    {
        $result = $this->gpaService->convertToNumeric(95);
        $this->assertEquals(4.0, $result);
    }

    public function test_convert_to_numeric_with_b_grade()
    {
        $result = $this->gpaService->convertToNumeric(85);
        $this->assertEquals(3.3, $result);
    }

    public function test_convert_to_numeric_with_c_grade()
    {
        $result = $this->gpaService->convertToNumeric(75);
        $this->assertEquals(3.0, $result);
    }

    public function test_convert_to_numeric_with_d_grade()
    {
        $result = $this->gpaService->convertToNumeric(55);
        $this->assertEquals(1.3, $result);
    }

    public function test_convert_to_numeric_with_f_grade()
    {
        $result = $this->gpaService->convertToNumeric(40);
        $this->assertEquals(0.0, $result);
    }

    public function test_convert_to_numeric_grade_boundaries()
    {
        $this->assertEquals(4.0, $this->gpaService->convertToNumeric(90));
        $this->assertEquals(3.7, $this->gpaService->convertToNumeric(89));
        $this->assertEquals(3.3, $this->gpaService->convertToNumeric(80));
        $this->assertEquals(3.0, $this->gpaService->convertToNumeric(75));
        $this->assertEquals(2.0, $this->gpaService->convertToNumeric(60));
        $this->assertEquals(1.0, $this->gpaService->convertToNumeric(45));
    }

    public function test_convert_letter_to_numeric_with_valid_grades()
    {
        $this->assertEquals(4.0, $this->gpaService->convertLetterToNumeric('A'));
        $this->assertEquals(3.7, $this->gpaService->convertLetterToNumeric('A-'));
        $this->assertEquals(3.0, $this->gpaService->convertLetterToNumeric('B'));
        $this->assertEquals(2.0, $this->gpaService->convertLetterToNumeric('C'));
        $this->assertEquals(1.0, $this->gpaService->convertLetterToNumeric('D'));
        $this->assertEquals(0.0, $this->gpaService->convertLetterToNumeric('F'));
    }

    public function test_convert_letter_to_numeric_with_case_insensitivity()
    {
        $this->assertEquals(4.0, $this->gpaService->convertLetterToNumeric('a'));
        $this->assertEquals(4.0, $this->gpaService->convertLetterToNumeric('A'));
        $this->assertEquals(3.7, $this->gpaService->convertLetterToNumeric('a-'));
    }

    public function test_convert_letter_to_numeric_with_invalid_grade()
    {
        $result = $this->gpaService->convertLetterToNumeric('X');
        $this->assertEquals(0.0, $result);
    }

    public function test_convert_numeric_to_letter()
    {
        $this->assertEquals('A', $this->gpaService->convertNumericToLetter(4.0));
        $this->assertEquals('A-', $this->gpaService->convertNumericToLetter(3.7));
        $this->assertEquals('B+', $this->gpaService->convertNumericToLetter(3.5));
        $this->assertEquals('B', $this->gpaService->convertNumericToLetter(3.0));
        $this->assertEquals('C', $this->gpaService->convertNumericToLetter(2.0));
        $this->assertEquals('D', $this->gpaService->convertNumericToLetter(1.0));
        $this->assertEquals('F', $this->gpaService->convertNumericToLetter(0.5));
    }

    public function test_set_custom_grade_scale()
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

    public function test_custom_grade_scale_persists()
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