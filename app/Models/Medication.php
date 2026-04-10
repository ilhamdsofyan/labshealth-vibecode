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

    public function stock(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MedicationStock::class);
    }

    public function stockLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MedicationStockLog::class);
    }
}
