<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\DigitalLibrary\BookLoan;
use App\Models\DigitalLibrary\BookReview;
use App\Models\User;
use Hyperf\Foundation\Testing\TestCase;

/**
 * @internal
 * @coversNothing
 */
class DigitalLibraryDomainModelsTest extends TestCase
{
    /**
     * Test book loan model configuration.
     */
    public function testBookLoanModelConfiguration(): void
    {
        $bookLoan = new BookLoan();
        
        $this->assertEquals('id', $bookLoan->getKeyName());
        $this->assertIsArray($bookLoan->getFillable());
        $this->assertIsArray($bookLoan->getCasts());
    }

    /**
     * Test book loan relationships.
     */
    public function testBookLoanRelationships(): void
    {
        $bookLoan = new BookLoan();
        
        $borrowerRelation = $bookLoan->borrower();
        $this->assertEquals('borrower_id', $borrowerRelation->getForeignKeyName());
    }

    /**
     * Test book review model configuration.
     */
    public function testBookReviewModelConfiguration(): void
    {
        $bookReview = new BookReview();
        
        $this->assertEquals('id', $bookReview->getKeyName());
        $this->assertIsArray($bookReview->getFillable());
        $this->assertIsArray($bookReview->getCasts());
    }

    /**
     * Test book review relationships.
     */
    public function testBookReviewRelationships(): void
    {
        $bookReview = new BookReview();
        
        $reviewerRelation = $bookReview->reviewer();
        $this->assertEquals('reviewer_id', $reviewerRelation->getForeignKeyName());
    }
}
