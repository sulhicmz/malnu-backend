<?php

declare (strict_types = 1);

namespace App\Models\Grading;

use App\Models\Model;
use App\Models\User;

class ReportSignature extends Model
{
    protected $primaryKey = 'id';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = [
        'report_id',
        'signer_name',
        'signer_title',
        'signature_image_url',
        'signed_at',
        'notes',
    ];

    protected $casts = [
        'signed_at'   => 'datetime',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}
