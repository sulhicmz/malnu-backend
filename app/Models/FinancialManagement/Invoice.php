<?php

declare(strict_types=1);

namespace App\Models\FinancialManagement;

use App\Models\Model;

class Invoice extends Model
{
    protected $fillable = [
        'student_id',
        'fee_structure_id',
        'invoice_number',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'status',
        'issue_date',
        'due_date',
        'paid_date',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'issue_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function updatePaidAmount()
    {
        $this->paid_amount = $this->payments()->where('status', 'completed')->sum('amount');
        
        if ($this->paid_amount >= $this->total_amount) {
            $this->status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partial';
        }
        
        $this->save();
    }
}
