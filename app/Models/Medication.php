<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Medication extends Model
{
    protected $fillable = ['name', 'category'];

    public function visits(): BelongsToMany
    {
        return $this->belongsToMany(Visit::class)->withTimestamps();
    }
}
