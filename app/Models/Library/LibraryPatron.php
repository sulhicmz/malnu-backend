<?php

declare(strict_types=1);

namespace App\Models\Library;

use App\Models\Model;
use App\Models\User;

class LibraryPatron extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'library_card_number',
        'status',
        'membership_start_date',
        'membership_expiry_date',
        'max_loan_limit',
        'current_loans',
        'total_fines',
        'notes',
    ];

    protected $casts = [
        'membership_start_date' => 'date',
        'membership_expiry_date' => 'date',
        'max_loan_limit' => 'integer',
        'current_loans' => 'integer',
        'total_fines' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fines()
    {
        return $this->hasMany(LibraryFine::class, 'patron_id');
    }

    public function holds()
    {
        return $this->hasMany(LibraryHold::class, 'patron_id');
    }

    public function readingProgramParticipants()
    {
        return $this->hasMany(LibraryReadingProgramParticipant::class, 'patron_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeCanBorrow($query)
    {
        return $query->where('status', 'active')
            ->where('current_loans', '<', 'max_loan_limit');
    }

    public function canBorrowMore(): bool
    {
        return $this->status === 'active' && $this->current_loans < $this->max_loan_limit;
    }

    public function hasOutstandingFines(): bool
    {
        return $this->total_fines > 0;
    }
}
