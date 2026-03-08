<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicAgenda extends Model
{
    protected $fillable = [
        'agenda_date',
        'agenda_time',
        'title',
        'location',
        'description',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'agenda_date' => 'date',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
