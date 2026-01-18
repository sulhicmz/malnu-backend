<?php

declare(strict_types=1);

namespace App\Models\DigitalLibrary;

use App\Models\Model;
use App\Models\User;

class ReadingHistory extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'book_id',
        'user_id',
        'loan_date',
        'return_date',
    ];

    protected $casts = [
        'loan_date' => 'date',
        'return_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByMonth($query, $year, $month)
    {
        return $query->whereYear('loan_date', $year)
            ->whereMonth('loan_date', $month);
    }
}
