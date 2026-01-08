<?php

declare(strict_types=1);

namespace App\Models\Library;

use App\Models\Model;

class LibraryReadingProgramParticipant extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'program_id',
        'patron_id',
        'enrollment_date',
        'completion_date',
        'books_read',
        'status',
        'notes',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'completion_date' => 'date',
        'books_read' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function program()
    {
        return $this->belongsTo(LibraryReadingProgram::class, 'program_id');
    }

    public function patron()
    {
        return $this->belongsTo(LibraryPatron::class, 'patron_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeWithdrawn($query)
    {
        return $query->where('status', 'withdrawn');
    }

    public function scopeByProgram($query, $programId)
    {
        return $query->where('program_id', $programId);
    }

    public function scopeByPatron($query, $patronId)
    {
        return $query->where('patron_id', $patronId);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function incrementBooksRead(): void
    {
        $this->increment('books_read');
    }

    public function complete(): void
    {
        $this->update([
            'status' => 'completed',
            'completion_date' => now(),
        ]);
    }

    public function withdraw(): void
    {
        $this->update([
            'status' => 'withdrawn',
        ]);
    }
}
