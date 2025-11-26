<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use Hyperf\DbConnection\Db;

/**
 * Test migration system functionality
 */
class MigrationTest extends TestCase
{
    /**
     * Test that migration imports are properly set up
     */
    public function testMigrationImportsAreAvailable(): void
    {
        // This test ensures that the DB facade is available for migrations
        $this->assertTrue(class_exists(Db::class));
    }
}