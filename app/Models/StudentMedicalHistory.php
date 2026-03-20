<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentMedicalHistory extends Model
{
    protected $table = 'student_medical_histories';

    protected $fillable = [
        'student_id',
        'past_diseases',
        'ever_hospitalized',
        'has_recurring_disease',
        'surgery_history',
        'relapse_treatment',
        'drug_food_allergies',
    ];

    protected function casts(): array
    {
        return [
            'ever_hospitalized' => 'boolean',
            'has_recurring_disease' => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
