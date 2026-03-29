@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Settings</h4>
        <p class="text-muted mb-0 small">Kelola profil aplikasi, operasional UKS, branding, default form, dan maintenance ringan.</p>
    </div>
    <span class="badge text-bg-light border px-3 py-2">Cached config</span>
</div>

@unless($settingsReady)
    <div class="alert alert-warning">
        Tabel <code>app_settings</code> belum tersedia. Settings tetap bisa disimpan sementara ke file lokal, lalu otomatis akan memakai database setelah migrasi selesai.
    </div>
@endunless

<div class="row g-4">
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header">Profil Aplikasi & Sekolah</div>
            <div class="card-body">
                <form method="POST" action="{{ route('settings.update') }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="section" value="profile">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Aplikasi</label>
                        <input type="text" name="app_name" class="form-control" value="{{ old('app_name', $settings['app_name']) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Sekolah</label>
                        <input type="text" name="school_name" class="form-control" value="{{ old('school_name', $settings['school_name']) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Alamat</label>
                        <textarea name="school_address" rows="3" class="form-control">{{ old('school_address', $settings['school_address']) }}</textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Telepon</label>
                            <input type="text" name="school_phone" class="form-control" value="{{ old('school_phone', $settings['school_phone']) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email</label>
                            <input type="email" name="school_email" class="form-control" value="{{ old('school_email', $settings['school_email']) }}">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-4">Simpan Profil</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header">Operasional UKS</div>
            <div class="card-body">
                <form method="POST" action="{{ route('settings.update') }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="section" value="operations">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Jam Buka</label>
                            <input type="time" name="clinic_open_time" class="form-control" value="{{ old('clinic_open_time', $settings['clinic_open_time']) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Jam Tutup</label>
                            <input type="time" name="clinic_close_time" class="form-control" value="{{ old('clinic_close_time', $settings['clinic_close_time']) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Default Rest (menit)</label>
                            <input type="number" name="default_rest_duration_minutes" class="form-control" min="5" max="600" value="{{ old('default_rest_duration_minutes', $settings['default_rest_duration_minutes']) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Min Karakter Quick Search</label>
                            <input type="number" name="quick_search_min_chars" class="form-control" min="1" max="5" value="{{ old('quick_search_min_chars', $settings['quick_search_min_chars']) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Limit Drilldown Analitik</label>
                            <input type="number" name="analytics_drilldown_limit" class="form-control" min="10" max="500" value="{{ old('analytics_drilldown_limit', $settings['analytics_drilldown_limit']) }}">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="enable_bed_auto_reassign" value="1" {{ old('enable_bed_auto_reassign', $settings['enable_bed_auto_reassign']) ? 'checked' : '' }}>
                                <label class="form-check-label">Auto reassign bed saat penuh</label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-4">Simpan Operasional</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header">Branding</div>
            <div class="card-body">
                <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="section" value="branding">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Primary Color</label>
                            <input type="color" name="brand_primary_color" class="form-control form-control-color" value="{{ old('brand_primary_color', $settings['brand_primary_color']) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Accent Color</label>
                            <input type="color" name="brand_accent_color" class="form-control form-control-color" value="{{ old('brand_accent_color', $settings['brand_accent_color']) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Logo Utama</label>
                            <input type="file" name="brand_logo" class="form-control" accept=".png,.jpg,.jpeg,.webp">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Logo Square</label>
                            <input type="file" name="brand_logo_square" class="form-control" accept=".png,.jpg,.jpeg,.webp">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-4">Simpan Branding</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header">Default Form & Behavior</div>
            <div class="card-body">
                <form method="POST" action="{{ route('settings.update') }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="section" value="forms">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Prefill Nama Petugas</label>
                        <select name="visit_officer_prefill_mode" class="form-select">
                            <option value="current_user" {{ old('visit_officer_prefill_mode', $settings['visit_officer_prefill_mode']) === 'current_user' ? 'selected' : '' }}>User login</option>
                            <option value="custom" {{ old('visit_officer_prefill_mode', $settings['visit_officer_prefill_mode']) === 'custom' ? 'selected' : '' }}>Nama custom</option>
                            <option value="blank" {{ old('visit_officer_prefill_mode', $settings['visit_officer_prefill_mode']) === 'blank' ? 'selected' : '' }}>Kosong</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Petugas Default</label>
                        <input type="text" name="visit_officer_default_name" class="form-control" value="{{ old('visit_officer_default_name', $settings['visit_officer_default_name']) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Default Waktu Kunjungan</label>
                        <select name="visit_default_time_mode" class="form-select">
                            <option value="now" {{ old('visit_default_time_mode', $settings['visit_default_time_mode']) === 'now' ? 'selected' : '' }}>Jam sekarang</option>
                            <option value="blank" {{ old('visit_default_time_mode', $settings['visit_default_time_mode']) === 'blank' ? 'selected' : '' }}>Kosong</option>
                        </select>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="visit_form_show_standard_exam" value="1" {{ old('visit_form_show_standard_exam', $settings['visit_form_show_standard_exam']) ? 'checked' : '' }}>
                        <label class="form-check-label">Tampilkan blok pemeriksaan standar secara default</label>
                    </div>

                    <button type="submit" class="btn btn-primary mt-4">Simpan Behavior</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header">Maintenance Ringan</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <form method="POST" action="{{ route('settings.maintenance') }}">
                            @csrf
                            <input type="hidden" name="action" value="clear_settings_cache">
                            <button type="submit" class="btn btn-outline-secondary w-100">Clear Settings Cache</button>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <form method="POST" action="{{ route('settings.maintenance') }}">
                            @csrf
                            <input type="hidden" name="action" value="optimize_clear">
                            <button type="submit" class="btn btn-outline-secondary w-100">Clear App Cache</button>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <form method="POST" action="{{ route('settings.maintenance') }}">
                            @csrf
                            <input type="hidden" name="action" value="sync_permissions">
                            <button type="submit" class="btn btn-outline-secondary w-100">Sync Permissions</button>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <form method="POST" action="{{ route('settings.maintenance') }}">
                            @csrf
                            <input type="hidden" name="action" value="sync_menus">
                            <button type="submit" class="btn btn-outline-secondary w-100">Sync Menus</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
