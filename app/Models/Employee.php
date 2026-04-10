<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    protected $fillable = ['nip', 'name', 'gender', 'role_type', 'department', 'avatar_path'];

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function medicalRecord(): HasOne
    {
        return $this->hasOne(EmployeeMedicalRecord::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
}
