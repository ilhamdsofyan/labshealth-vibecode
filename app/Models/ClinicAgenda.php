<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicAgenda extends Model
{
    protected $fillable = [
        'agenda_date',
        'agenda_time',
        'title',
        'location',
        'description',
        'is_public',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'agenda_date' => 'date',
            'is_public' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeVisibleTo($query, ?User $user)
    {
        if (!$user) {
            return $query->where('is_public', true);
        }

        return $query->where(function ($q) use ($user) {
            $q->where('is_public', true)
                ->orWhere('created_by', $user->id);
        });
    }
}
