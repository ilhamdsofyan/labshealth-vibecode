<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = ['nip', 'name', 'role_type', 'department', 'avatar_path'];

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }
}
