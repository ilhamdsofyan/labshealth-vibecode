@extends('layouts.app')

@section('title', isset($menu) ? 'Edit Menu' : 'Tambah Menu')

@section('content')
<div class="d-flex align-items-center mb-4">
    <a href="{{ route('admin.menus.index') }}" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
    <h4 class="fw-bold mb-0">{{ isset($menu) ? 'Edit Menu: ' . $menu->name : 'Tambah Menu' }}</h4>
</div>

<div class="card">
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger py-2">
                <ul class="mb-0 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ isset($menu) ? route('admin.menus.update', $menu) : route('admin.menus.store') }}">
            @csrf
            @if(isset($menu)) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Nama Menu <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $menu->name ?? '') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Parent Menu</label>
                    <select name="parent_id" class="form-select">
                        <option value="">— Tidak Ada (Root) —</option>
                        @foreach($parentMenus as $parent)
                            <option value="{{ $parent->id }}"
                                {{ old('parent_id', $menu->parent_id ?? '') == $parent->id ? 'selected' : '' }}>
                                {{ $parent->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Route Name</label>
                    <input type="text" name="route_name" class="form-control"
                           value="{{ old('route_name', $menu->route_name ?? '') }}" placeholder="contoh: visits.index">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Permission Name</label>
                    <input type="text" name="permission_name" class="form-control"
                           value="{{ old('permission_name', $menu->permission_name ?? '') }}" placeholder="contoh: visits.index">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Icon (Bootstrap Icons)</label>
                    <input type="text" name="icon" class="form-control"
                           value="{{ old('icon', $menu->icon ?? '') }}" placeholder="contoh: bi-house">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Urutan <span class="text-danger">*</span></label>
                    <input type="number" name="order" class="form-control" min="0"
                           value="{{ old('order', $menu->order ?? 0) }}" required>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch mt-4">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active"
                               {{ old('is_active', $menu->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Aktif</label>
                    </div>
                </div>

                <div class="col-12 mt-4">
                    <label class="form-label small fw-semibold">Visibilitas Role</label>
                    <p class="text-muted small mb-2">Pilih role yang dapat melihat menu ini. Jika tidak ada yang dipilih, menu akan terlihat oleh semua user yang memiliki permission terkait.</p>
                    <div class="d-flex flex-wrap gap-3">
                        @foreach($roles as $role)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="role_ids[]" value="{{ $role->id }}" 
                                       id="role_{{ $role->id }}"
                                       {{ (isset($menu) && $menu->roles->contains($role->id)) || (is_array(old('role_ids')) && in_array($role->id, old('role_ids'))) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="role_{{ $role->id }}">
                                    {{ $role->name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>{{ isset($menu) ? 'Perbarui' : 'Simpan' }}</button>
                <a href="{{ route('admin.menus.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
