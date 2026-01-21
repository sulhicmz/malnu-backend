<?php

declare(strict_types=1);

namespace App\Models\Compliance;

use App\Models\Model;
use App\Models\User;

/**
 * ComplianceTraining Model.
 *
 * Represents compliance training modules that users must complete.
 * Covers FERPA, GDPR, security, privacy, and general training.
 */
class ComplianceTraining extends Model
{
    public $primaryKey = 'id';

    public $incrementing = false;

    protected $table = 'compliance_training';

    protected $fillable = [
        'title',
        'description',
        'content',
        'training_type',
        'duration_minutes',
        'category',
        'required_for_roles',
        'required_for_all',
        'valid_from',
        'valid_until',
        'status',
        'created_by',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'required_for_all' => 'boolean',
        'required_for_roles' => 'array',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $keyType = 'string';

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function completions()
    {
        return $this->hasMany(ComplianceTrainingCompletion::class, 'training_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('valid_from', '<=', date('Y-m-d'))
            ->where(function ($q) {
                $q->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', date('Y-m-d'));
            });
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('training_type', $type);
    }

    public function scopeRequiredForAll($query)
    {
        return $query->where('required_for_all', true);
    }

    public function isRequiredForUser(?User $user): bool
    {
        if ($this->required_for_all) {
            return true;
        }

        if ($user === null) {
            return false;
        }

        $userRoles = $user->roles()->pluck('id')->toArray();
        return ! empty(array_intersect($userRoles, $this->required_for_roles ?? []));
    }

    public function completionCount(): int
    {
        return $this->completions()->count();
    }
}
