<?php

declare(strict_types=1);

namespace App\Models\DigitalLibrary;

use App\Models\Model;

class BookAuthor extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'book_id',
        'author_name',
        'author_order',
    ];

    protected $casts = [
        'author_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
