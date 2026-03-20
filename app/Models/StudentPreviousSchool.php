<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPreviousSchool extends Model
{
    protected $table = 'student_previous_schools';

    protected $fillable = [
        'student_id',
        'smp_school_name',
        'smp_npsn',
        'smp_study_duration_months',
        'ever_repeated_grade',
        'achievements',
        'receives_scholarship',
        'extracurricular_smp',
    ];

    protected function casts(): array
    {
        return [
            'smp_study_duration_months' => 'integer',
            'ever_repeated_grade' => 'boolean',
            'receives_scholarship' => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
