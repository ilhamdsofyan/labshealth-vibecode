@extends('layouts.app')

@section('title', isset($role) ? 'Edit Role' : 'Tambah Role')

@section('content')
<div class="d-flex align-items-center mb-4">
    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
    <h4 class="fw-bold mb-0">{{ isset($role) ? 'Edit Role: ' . $role->display_name : 'Tambah Role' }}</h4>
</div>

<div class="card">
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger py-2">
                <ul class="mb-0 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ isset($role) ? route('admin.roles.update', $role) : route('admin.roles.store') }}">
            @csrf
            @if(isset($role)) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Nama (slug) <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $role->name ?? '') }}"
                           placeholder="contoh: petugas_uks" required {{ isset($role) && $role->name === 'superadmin' ? 'readonly' : '' }}>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Nama Tampilan <span class="text-danger">*</span></label>
                    <input type="text" name="display_name" class="form-control"
                           value="{{ old('display_name', $role->display_name ?? '') }}" placeholder="contoh: Petugas UKS" required>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold">Deskripsi</label>
                    <textarea name="description" rows="2" class="form-control"
                              placeholder="Deskripsi singkat role ini...">{{ old('description', $role->description ?? '') }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold">Permissions</label>
                    <div class="border rounded p-3" style="max-height:400px;overflow-y:auto;">
                        @foreach($groupedPermissions as $group => $permissions)
                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <strong class="small text-uppercase text-primary">{{ $group ?: 'Lainnya' }}</strong>
                                    <button type="button" class="btn btn-link btn-sm p-0 ms-2 text-decoration-none check-all-btn"
                                            data-group="{{ $group }}">
                                        <small>Pilih semua</small>
                                    </button>
                                </div>
                                <div class="row g-1">
                                    @foreach($permissions as $permission)
                                        <div class="col-md-4 col-6">
                                            <div class="form-check">
                                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                                       class="form-check-input perm-{{ $group }}"
                                                       id="perm_{{ $permission->id }}"
                                                       {{ in_array($permission->id, old('permissions', $rolePermissions ?? [])) ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="perm_{{ $permission->id }}">
                                                    {{ $permission->display_name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @if(!$loop->last) <hr class="my-2"> @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>{{ isset($role) ? 'Perbarui' : 'Simpan' }}</button>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.check-all-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const group = this.dataset.group;
        const checkboxes = document.querySelectorAll('.perm-' + CSS.escape(group));
        const allChecked = [...checkboxes].every(cb => cb.checked);
        checkboxes.forEach(cb => cb.checked = !allChecked);
    });
});
</script>
@endpush
@endsection
