<?php

declare(strict_types=1);

namespace App\Models\Library;

use App\Models\Model;

class LibraryReadingProgram extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'program_name',
        'program_type',
        'description',
        'start_date',
        'end_date',
        'target_books',
        'status',
        'prizes',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'target_books' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function participants()
    {
        return $this->hasMany(LibraryReadingProgramParticipant::class, 'program_id');
    }

    public function scopeReadingChallenge($query)
    {
        return $query->where('program_type', 'reading_challenge');
    }

    public function scopeBookClub($query)
    {
        return $query->where('program_type', 'book_club');
    }

    public function scopeLiteracyInitiative($query)
    {
        return $query->where('program_type', 'literacy_initiative');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            });
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' &&
            $this->start_date->lte(now()) &&
            (!$this->end_date || $this->end_date->gte(now()));
    }

    public function hasEnded(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }

    public function getParticipantCountAttribute(): int
    {
        return $this->participants()->count();
    }
}
