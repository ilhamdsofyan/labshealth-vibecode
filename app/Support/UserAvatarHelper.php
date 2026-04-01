<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class UserAvatarHelper
{
    public static function resolveUrl(User $user): string
    {
        $userAvatar = self::resolveUserAvatar($user->avatar);
        if ($userAvatar) {
            return $userAvatar;
        }

        $employee = $user->relationLoaded('employee') ? $user->employee : $user->employee()->first();
        if ($employee && filled($employee->avatar_path) && Storage::disk('public')->exists($employee->avatar_path)) {
            return asset('storage/' . ltrim($employee->avatar_path, '/'));
        }

        return asset('assets/img/Logo.png');
    }

    private static function resolveUserAvatar(?string $rawAvatar): ?string
    {
        if (! filled($rawAvatar)) {
            return null;
        }

        if (str_starts_with($rawAvatar, 'http://') || str_starts_with($rawAvatar, 'https://')) {
            $storagePath = self::extractPublicStoragePath($rawAvatar);
            if ($storagePath !== null) {
                return Storage::disk('public')->exists($storagePath)
                    ? asset('storage/' . ltrim($storagePath, '/'))
                    : null;
            }

            return $rawAvatar;
        }

        if (str_starts_with($rawAvatar, '/')) {
            $storagePath = self::extractPublicStoragePath($rawAvatar);

            return $storagePath !== null && Storage::disk('public')->exists($storagePath)
                ? asset('storage/' . ltrim($storagePath, '/'))
                : null;
        }

        return Storage::disk('public')->exists($rawAvatar)
            ? asset('storage/' . ltrim($rawAvatar, '/'))
            : null;
    }

    private static function extractPublicStoragePath(string $value): ?string
    {
        $path = parse_url($value, PHP_URL_PATH) ?: $value;
        $path = trim((string) $path, '/');

        if ($path === '' || ! str_contains($path, 'storage/')) {
            return null;
        }

        return ltrim(substr($path, strpos($path, 'storage/') + strlen('storage/')), '/');
    }
}
