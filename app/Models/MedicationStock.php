<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicationStock extends Model
{
    protected $fillable = [
        'medication_id',
        'quantity',
        'unit',
        'min_threshold',
        'last_restocked_at',
    ];

    protected $casts = [
        'last_restocked_at' => 'datetime',
    ];

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }
}
