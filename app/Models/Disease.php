<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Disease extends Model
{
    protected $fillable = ['name', 'category'];

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }
}
