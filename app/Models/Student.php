<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    protected $fillable = [
        'nis',
        'name',
        'nickname',
        'gender',
        'class_name',
        'nisn',
        'nik_kitas',
        'family_card_number',
        'birth_place',
        'birth_date',
        'birth_certificate_number',
        'religion',
        'citizenship',
        'daily_language',
        'whatsapp_number',
        'email',
        'address_text',
        'notes',
        'avatar_path',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function classHistories(): HasMany
    {
        return $this->hasMany(StudentClassHistory::class);
    }

    public function activeClass(): HasOne
    {
        return $this->hasOne(StudentClassHistory::class)->where('is_active', true);
    }

    public function health(): HasOne
    {
        return $this->hasOne(StudentHealth::class);
    }

    public function medicalHistory(): HasOne
    {
        return $this->hasOne(StudentMedicalHistory::class);
    }

    public function previousSchool(): HasOne
    {
        return $this->hasOne(StudentPreviousSchool::class);
    }

    public function learningProfile(): HasOne
    {
        return $this->hasOne(StudentLearningProfile::class);
    }

    public function homeAssets(): HasOne
    {
        return $this->hasOne(StudentAssetsHome::class);
    }

    public function familyMembers(): HasMany
    {
        return $this->hasMany(StudentFamily::class);
    }
}
