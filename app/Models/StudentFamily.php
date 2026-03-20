<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentFamily extends Model
{
    protected $table = 'student_family';

    protected $fillable = [
        'student_id',
        'relation_type',
        'full_name',
        'nik',
        'birth_year',
        'relationship_detail',
        'whatsapp_number',
        'email',
        'religion',
        'occupation',
        'rank_group',
        'position_title',
        'education',
        'monthly_income',
        'special_needs',
        'is_guardian',
        'is_emergency_contact',
        'is_primary_contact',
        'lives_with_student',
        'marital_status',
        'address_text',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'birth_year' => 'integer',
            'monthly_income' => 'integer',
            'is_guardian' => 'boolean',
            'is_emergency_contact' => 'boolean',
            'is_primary_contact' => 'boolean',
            'lives_with_student' => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
