<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'google_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // ─── Relationships ───────────────────────────────────────

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function imports(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ImportLog::class, 'uploaded_by');
    }

    // ─── Permission Helpers ──────────────────────────────────

    public function isSuperAdmin(): bool
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->contains(fn ($role) => $role->name === 'superadmin');
        }

        return $this->roles()->where('name', 'superadmin')->exists();
    }

    public function hasRole(string $roleName): bool
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->contains(fn ($role) => $role->name === $roleName);
        }

        return $this->roles()->where('name', $roleName)->exists();
    }

    public function hasPermission(string $permissionName): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->relationLoaded('roles')) {
            return $this->getAllPermissions()->contains($permissionName);
        }

        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionName) {
                $query->where('name', $permissionName);
            })
            ->exists();
    }

    public function getAllPermissions(): Collection
    {
        if ($this->isSuperAdmin()) {
            return Permission::all()->pluck('name');
        }

        if ($this->relationLoaded('roles')) {
            return $this->roles
                ->loadMissing('permissions')
                ->pluck('permissions')
                ->flatten()
                ->pluck('name')
                ->unique()
                ->values();
        }

        return $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('name')
            ->unique();
    }
}
