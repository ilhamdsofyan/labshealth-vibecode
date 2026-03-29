<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class SettingsService
{
    private const CACHE_KEY = 'settings.all';
    private const FALLBACK_FILE = 'app_settings.json';

    public function defaults(): array
    {
        return [
            'app_name' => 'LabsHealth',
            'school_name' => 'SMA Labschool Bintaro',
            'school_address' => '',
            'school_phone' => '',
            'school_email' => '',
            'clinic_open_time' => '07:00',
            'clinic_close_time' => '16:00',
            'default_rest_duration_minutes' => '60',
            'enable_bed_auto_reassign' => '1',
            'quick_search_min_chars' => '2',
            'brand_primary_color' => '#006060',
            'brand_accent_color' => '#f0d000',
            'brand_logo_path' => '',
            'brand_logo_square_path' => '',
            'visit_officer_prefill_mode' => 'current_user',
            'visit_officer_default_name' => '',
            'visit_default_time_mode' => 'now',
            'visit_form_show_standard_exam' => '1',
            'analytics_drilldown_limit' => '100',
        ];
    }

    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            $stored = $this->isReady()
                ? AppSetting::query()->pluck('value', 'key')->toArray()
                : $this->readFallbackFile();

            return array_merge($this->defaults(), $stored);
        });
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $all = $this->all();
        return $all[$key] ?? $default;
    }

    public function setMany(array $values, ?int $updatedBy = null): void
    {
        if (! $this->isReady()) {
            $this->writeFallbackFile(array_merge($this->all(), $values));
            $this->forgetCache();
            return;
        }

        foreach ($values as $key => $value) {
            AppSetting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'updated_by' => $updatedBy,
                ]
            );
        }

        $this->forgetCache();
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function isReady(): bool
    {
        return Schema::hasTable('app_settings');
    }

    public function ui(): array
    {
        $settings = $this->all();

        return [
            'app_name' => $settings['app_name'],
            'school_name' => $settings['school_name'],
            'school_address' => $settings['school_address'],
            'school_phone' => $settings['school_phone'],
            'school_email' => $settings['school_email'],
            'primary_color' => $settings['brand_primary_color'],
            'accent_color' => $settings['brand_accent_color'],
            'quick_search_min_chars' => (int) $settings['quick_search_min_chars'],
            'logo_url' => $this->resolveAssetUrl($settings['brand_logo_path'], 'assets/img/Logo Labschool Bintaro.png'),
            'logo_square_url' => $this->resolveAssetUrl($settings['brand_logo_square_path'], 'assets/img/Logo.png'),
        ];
    }

    private function resolveAssetUrl(?string $path, string $fallback): string
    {
        if (filled($path) && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        return asset($fallback);
    }

    private function readFallbackFile(): array
    {
        if (! Storage::disk('local')->exists(self::FALLBACK_FILE)) {
            return [];
        }

        $decoded = json_decode((string) Storage::disk('local')->get(self::FALLBACK_FILE), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function writeFallbackFile(array $values): void
    {
        Storage::disk('local')->put(self::FALLBACK_FILE, json_encode($values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
