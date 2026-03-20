<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentHealth extends Model
{
    protected $table = 'student_health';

    protected $fillable = [
        'student_id',
        'height_cm',
        'weight_kg',
        'head_circumference_cm',
        'blood_type',
        'rhesus',
        'eye_condition',
        'has_eye_disorder',
        'assistive_device',
        'ear_condition',
        'uses_hearing_aid',
        'face_shape',
        'hair_type',
        'skin_tone',
    ];

    protected function casts(): array
    {
        return [
            'height_cm' => 'integer',
            'weight_kg' => 'decimal:2',
            'head_circumference_cm' => 'decimal:2',
            'has_eye_disorder' => 'boolean',
            'uses_hearing_aid' => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
