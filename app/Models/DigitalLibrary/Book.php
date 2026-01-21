<?php

declare(strict_types=1);

namespace App\Models\DigitalLibrary;

use App\Models\Model;

class Book extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'isbn',
        'title',
        'author',
        'publisher',
        'publication_year',
        'category',
        'quantity',
        'available_quantity',
        'cover_url',
        'description',
        'subtitle',
        'language',
        'pages',
        'edition',
        'genre',
        'price',
        'location',
        'call_number',
        'is_reference_only',
        'total_copies',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function ebookFormats()
    {
        return $this->hasMany(EbookFormat::class);
    }

    public function bookLoans()
    {
        return $this->hasMany(BookLoan::class);
    }

    public function bookReviews()
    {
        return $this->hasMany(BookReview::class);
    }

    public function bookAuthors()
    {
        return $this->hasMany(BookAuthor::class);
    }

    public function categories()
    {
        return $this->belongsToMany(BookCategory::class, 'book_category_mappings', 'book_id', 'category_id');
    }

    public function subjects()
    {
        return $this->belongsToMany(BookSubject::class, 'book_subject_mappings', 'book_id', 'subject_id');
    }

    public function bookHolds()
    {
        return $this->hasMany(BookHold::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('available_quantity', '>', 0);
    }

    public function scopeByCategory($query, $categoryCode)
    {
        return $query->whereHas('categories', function ($q) use ($categoryCode) {
            $q->where('code', $categoryCode);
        });
    }

    public function scopeBySubject($query, $subjectCode)
    {
        return $query->whereHas('subjects', function ($q) use ($subjectCode) {
            $q->where('code', $subjectCode);
        });
    }

    public function scopeSearch($query, $searchTerm)
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('title', 'like', "%{$searchTerm}%")
                ->orWhere('author', 'like', "%{$searchTerm}%")
                ->orWhere('isbn', 'like', "%{$searchTerm}%")
                ->orWhere('publisher', 'like', "%{$searchTerm}%");
        });
    }
}
