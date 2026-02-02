<?php

declare(strict_types=1);

namespace App\Models\Compliance;

use App\Models\Model;
use App\Models\User;

/**
 * CompliancePolicy Model.
 *
 * Represents compliance policies that users must acknowledge.
 * Covers FERPA, GDPR, CCPA, CIPA, IDEA, and general school policies.
 */
class CompliancePolicy extends Model
{
    public $primaryKey = 'id';

    public $incrementing = false;

    protected $table = 'compliance_policies';

    protected $fillable = [
        'title',
        'description',
        'content',
        'category',
        'version',
        'effective_date',
        'expiry_date',
        'status',
        'created_by',
        'superseded_by',
        'superseded_at',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'expiry_date' => 'date',
        'superseded_at' => 'datetime',
        'version' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $keyType = 'string';

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function supersededBy()
    {
        return $this->belongsTo(User::class, 'superseded_by', 'id');
    }

    public function acknowledgments()
    {
        return $this->hasMany(CompliancePolicyAcknowledgment::class, 'policy_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>=', date('Y-m-d'));
            });
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function isAcknowledgedByUser(string $userId): bool
    {
        return $this->acknowledgments()->where('user_id', $userId)->exists();
    }

    public function acknowledgmentCount(): int
    {
        return $this->acknowledgments()->count();
    }
}
