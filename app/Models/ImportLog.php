<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportLog extends Model
{
    protected $fillable = [
        'file_name',
        'total_rows',
        'success_rows',
        'failed_rows',
        'failed_rows_data',
        'uploaded_by',
        'status',
    ];

    protected $casts = [
        'failed_rows_data' => 'array',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
