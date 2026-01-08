<?php

declare(strict_types=1);

namespace App\Models\Library;

use App\Models\Model;

class LibraryAcquisition extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'acquisition_number',
        'title',
        'author',
        'isbn',
        'publisher',
        'quantity',
        'unit_cost',
        'total_cost',
        'vendor',
        'order_date',
        'received_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'order_date' => 'date',
        'received_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopeOrdered($query)
    {
        return $query->where('status', 'ordered');
    }

    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'ordered');
    }

    public function scopeByVendor($query, $vendor)
    {
        return $query->where('vendor', $vendor);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('order_date', [$startDate, $endDate]);
    }

    public function isReceived(): bool
    {
        return $this->status === 'received';
    }

    public function isPending(): bool
    {
        return $this->status === 'ordered';
    }
}
