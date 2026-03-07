<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentClassHistory extends Model
{
    protected $fillable = ['student_id', 'class_name', 'academic_year', 'is_active'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
