<?php

namespace App\Http\Controllers;

use App\Services\SettingsService;
use Database\Seeders\MenuSeeder;
use Database\Seeders\SettingsFeatureSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(
        private readonly SettingsService $settingsService
    ) {}

    public function index(): View
    {
        return view('settings.index', [
            'settings' => $this->settingsService->all(),
            'settingsReady' => $this->settingsService->isReady(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $section = $request->input('section');

        if (! in_array($section, ['profile', 'operations', 'branding', 'forms'], true)) {
            return back()->with('error', 'Section settings tidak valid.');
        }

        $validated = match ($section) {
            'profile' => $request->validate([
                'app_name' => ['required', 'string', 'max:120'],
                'school_name' => ['required', 'string', 'max:160'],
                'school_address' => ['nullable', 'string', 'max:500'],
                'school_phone' => ['nullable', 'string', 'max:40'],
                'school_email' => ['nullable', 'email', 'max:120'],
            ]),
            'operations' => $request->validate([
                'clinic_open_time' => ['required', 'date_format:H:i'],
                'clinic_close_time' => ['required', 'date_format:H:i'],
                'default_rest_duration_minutes' => ['required', 'integer', 'min:5', 'max:600'],
                'enable_bed_auto_reassign' => ['nullable', 'boolean'],
                'quick_search_min_chars' => ['required', 'integer', 'min:1', 'max:5'],
                'analytics_drilldown_limit' => ['required', 'integer', 'min:10', 'max:500'],
            ]),
            'branding' => $request->validate([
                'brand_primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
                'brand_accent_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
                'brand_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
                'brand_logo_square' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            ]),
            'forms' => $request->validate([
                'visit_officer_prefill_mode' => ['required', 'in:current_user,custom,blank'],
                'visit_officer_default_name' => ['nullable', 'string', 'max:120'],
                'visit_default_time_mode' => ['required', 'in:now,blank'],
                'visit_form_show_standard_exam' => ['nullable', 'boolean'],
            ]),
        };

        $payload = $validated;

        if ($section === 'operations') {
            $payload['enable_bed_auto_reassign'] = $request->boolean('enable_bed_auto_reassign') ? '1' : '0';
        }

        if ($section === 'forms') {
            $payload['visit_form_show_standard_exam'] = $request->boolean('visit_form_show_standard_exam') ? '1' : '0';
        }

        if ($section === 'branding') {
            unset($payload['brand_logo'], $payload['brand_logo_square']);

            if ($request->hasFile('brand_logo')) {
                $payload['brand_logo_path'] = $request->file('brand_logo')->store('branding', 'public');
            }

            if ($request->hasFile('brand_logo_square')) {
                $payload['brand_logo_square_path'] = $request->file('brand_logo_square')->store('branding', 'public');
            }
        }

        $this->settingsService->setMany($payload, auth()->id());

        return back()->with('success', 'Settings berhasil diperbarui.');
    }

    public function maintenance(Request $request): RedirectResponse
    {
        $action = $request->input('action');

        match ($action) {
            'clear_settings_cache' => $this->settingsService->forgetCache(),
            'optimize_clear' => Artisan::call('optimize:clear'),
            'sync_permissions' => $this->syncPermissionsAndSettingsAccess(),
            'sync_menus' => $this->syncMenusAndSettingsAccess(),
            default => null,
        };

        if (! in_array($action, ['clear_settings_cache', 'optimize_clear', 'sync_permissions', 'sync_menus'], true)) {
            return back()->with('error', 'Aksi maintenance tidak valid.');
        }

        $messages = [
            'clear_settings_cache' => 'Cache settings berhasil dibersihkan.',
            'optimize_clear' => 'Cache aplikasi berhasil dibersihkan.',
            'sync_permissions' => 'Permission berhasil disinkronkan.',
            'sync_menus' => 'Registry menu berhasil disinkronkan.',
        ];

        return back()->with('success', $messages[$action]);
    }

    private function syncPermissionsAndSettingsAccess(): void
    {
        Artisan::call('permission:sync');
        app(SettingsFeatureSeeder::class)->run();
    }

    private function syncMenusAndSettingsAccess(): void
    {
        app(MenuSeeder::class)->run();
        app(SettingsFeatureSeeder::class)->run();
    }
}
