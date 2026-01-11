<?php

declare(strict_types=1);

namespace App\Models\Library;

use App\Models\Model;
use App\Models\DigitalLibrary\Book;

class LibraryInventory extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'book_id',
        'action_type',
        'expected_quantity',
        'actual_quantity',
        'difference',
        'notes',
        'performed_by',
        'inventory_date',
    ];

    protected $casts = [
        'expected_quantity' => 'integer',
        'actual_quantity' => 'integer',
        'difference' => 'integer',
        'inventory_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function scopeStockTake($query)
    {
        return $query->where('action_type', 'stock_take');
    }

    public function scopeWeeding($query)
    {
        return $query->where('action_type', 'weeding');
    }

    public function scopeAddition($query)
    {
        return $query->where('action_type', 'addition');
    }

    public function scopeCorrection($query)
    {
        return $query->where('action_type', 'correction');
    }

    public function scopeDiscrepancy($query)
    {
        return $query->where('difference', '!=', 0);
    }

    public function scopeReconciled($query)
    {
        return $query->where('difference', 0);
    }

    public function hasDiscrepancy(): bool
    {
        return $this->difference !== 0;
    }

    public function isShortage(): bool
    {
        return $this->difference < 0;
    }

    public function isSurplus(): bool
    {
        return $this->difference > 0;
    }
}
