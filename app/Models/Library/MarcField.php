<?php

declare(strict_types=1);

namespace App\Models\Library;

use App\Models\Model;

class MarcField extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'marc_record_id',
        'tag',
        'indicator1',
        'indicator2',
        'data',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function marcRecord()
    {
        return $this->belongsTo(MarcRecord::class, 'marc_record_id');
    }

    public function scopeByTag($query, $tag)
    {
        return $query->where('tag', $tag);
    }

    public function scopeTitleFields($query)
    {
        return $query->whereIn('tag', ['245', '246']);
    }

    public function scopeAuthorFields($query)
    {
        return $query->whereIn('tag', ['100', '700']);
    }

    public function scopeSubjectFields($query)
    {
        return $query->whereIn('tag', ['600', '610', '611', '630', '650', '651', '655']);
    }

    public function scopeISBNFields($query)
    {
        return $query->whereIn('tag', ['020', '024']);
    }
}
