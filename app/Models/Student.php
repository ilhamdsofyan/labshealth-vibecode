<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    protected $fillable = ['nis', 'name', 'gender'];

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
}
