<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeMedicalRecord extends Model
{
    protected $fillable = [
        'employee_id',
        'height_cm',
        'weight_kg',
        'blood_type',
        'rhesus',
        'allergies',
        'chronic_diseases',
        'past_surgeries',
        'regular_medications',
        'last_checkup_date',
        'medical_notes',
    ];

    protected function casts(): array
    {
        return [
            'height_cm' => 'integer',
            'weight_kg' => 'decimal:2',
            'last_checkup_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
