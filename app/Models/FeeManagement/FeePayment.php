<?php

declare(strict_types = 1);

namespace App\Models\FeeManagement;

use App\Models\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeePayment extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'invoice_id',
        'user_id',
        'payment_method',
        'transaction_reference',
        'amount',
        'status',
        'payment_gateway_response',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_gateway_response' => 'array',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(FeeInvoice::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function markAsCompleted(): void
    {
        $this->status = 'completed';
        $this->paid_at = now();
        $this->save();

        $this->invoice->updateBalance();
    }

    public function markAsFailed(string $reason = null): void
    {
        $this->status = 'failed';
        if ($reason) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . "Failed: {$reason}";
        }
        $this->save();
    }
}
