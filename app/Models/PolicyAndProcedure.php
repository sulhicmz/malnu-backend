<?php

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

class PolicyAndProcedure extends Model
{
    protected $table = 'policies_and_procedures';

    protected $fillable = [
        'title',
        'category',
        'policy_number',
        'content',
        'version',
        'effective_date',
        'review_date',
        'status',
        'author_id',
        'approver_id',
        'change_summary',
        'document_path',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'review_date' => 'date',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePendingReview($query)
    {
        return $query->where('status', 'pending_review');
    }
}
