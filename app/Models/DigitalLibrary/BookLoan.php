<?php

declare(strict_types=1);

namespace App\Models\DigitalLibrary;

use App\Models\Model;
use App\Models\User;

class BookLoan extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'book_id',
        'borrower_id',
        'loan_date',
        'due_date',
        'return_date',
        'status',
        'renewal_count',
        'due_date_original',
        'fine_amount',
        'fine_paid',
        'fine_paid_date',
        'library_card_id',
    ];

    protected $casts = [
        'loan_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
        'renewal_count' => 'integer',
        'due_date_original' => 'date',
        'fine_amount' => 'decimal:2',
        'fine_paid' => 'boolean',
        'fine_paid_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function borrower()
    {
        return $this->belongsTo(User::class, 'borrower_id');
    }

    public function libraryCard()
    {
        return $this->belongsTo(LibraryCard::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'borrowed')
            ->whereNull('return_date');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'borrowed')
            ->where('due_date', '<', now())
            ->whereNull('return_date');
    }

    public function scopeReturned($query)
    {
        return $query->whereNotNull('return_date');
    }

    public function isOverdue(): bool
    {
        return $this->status === 'borrowed' 
            && $this->due_date < now() 
            && is_null($this->return_date);
    }

    public function canBeRenewed(int $renewalLimit): bool
    {
        return !$this->isOverdue() 
            && $this->renewal_count < $renewalLimit;
    }
}
