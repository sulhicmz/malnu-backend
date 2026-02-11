<?php

declare(strict_types=1);

namespace App\Models\FinancialManagement;

use App\Models\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'fee_type_id',
        'description',
        'quantity',
        'unit_price',
        'discount_amount',
        'amount',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function feeType()
    {
        return $this->belongsTo(FeeType::class);
    }
}
