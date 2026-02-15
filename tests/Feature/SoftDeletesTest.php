<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Model;
use App\Models\User;
use Hypervel\Database\Model\Model as BaseModel;
use Tests\TestCase;

class SoftDeletesTest extends TestCase
{
    public function test_model_extends_base_with_soft_deletes()
    {
        $model = new class extends Model {};
        $this->assertTrue(
            method_exists($model, 'getDeletedAtColumn'),
            'Model should have SoftDeletes trait'
        );
    }

    public function test_soft_delete_sets_deleted_at_timestamp()
    {
        $this->markTestSkipped('Requires database connection');
    }

    public function test_restore_clears_deleted_at_timestamp()
    {
        $this->markTestSkipped('Requires database connection');
    }

    public function test_force_delete_permanently_removes_record()
    {
        $this->markTestSkipped('Requires database connection');
    }

    public function test_with_trashed_includes_soft_deleted_records()
    {
        $this->markTestSkipped('Requires database connection');
    }

    public function test_only_trashed_returns_only_soft_deleted_records()
    {
        $this->markTestSkipped('Requires database connection');
    }

    public function test_normal_query_excludes_soft_deleted_records()
    {
        $this->markTestSkipped('Requires database connection');
    }

    public function test_query_scope_where_null_deleted_at()
    {
        $this->markTestSkipped('Requires database connection');
    }
}
