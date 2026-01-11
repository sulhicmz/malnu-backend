<?php

declare(strict_types=1);

namespace App\Models\Library;

use App\Models\Model;
use App\Models\DigitalLibrary\Book;

class LibraryAnalytics extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'book_id',
        'analytics_date',
        'checkouts',
        'returns',
        'renewals',
        'holds_placed',
        'page_views',
        'unique_patrons',
        'notes',
    ];

    protected $casts = [
        'checkouts' => 'integer',
        'returns' => 'integer',
        'renewals' => 'integer',
        'holds_placed' => 'integer',
        'page_views' => 'integer',
        'unique_patrons' => 'integer',
        'analytics_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function scopeByDate($query, $date)
    {
        return $query->where('analytics_date', $date);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('analytics_date', [$startDate, $endDate]);
    }

    public function scopeForBook($query, $bookId)
    {
        return $query->where('book_id', $bookId);
    }

    public function scopeOverall($query)
    {
        return $query->whereNull('book_id');
    }

    public function scopePopularBooks($query, $limit = 10)
    {
        return $query->whereNotNull('book_id')
            ->orderByDesc('checkouts')
            ->limit($limit);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('analytics_date', '>=', now()->subDays($days));
    }
}
