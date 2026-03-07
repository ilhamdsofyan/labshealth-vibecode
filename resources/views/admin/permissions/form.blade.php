@extends('layouts.app')

@section('title', isset($permission) ? 'Edit Permission' : 'Tambah Permission')

@section('content')
<div class="d-flex align-items-center mb-4">
    <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
    <h4 class="fw-bold mb-0">{{ isset($permission) ? 'Edit Permission' : 'Tambah Permission' }}</h4>
</div>

<div class="card">
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger py-2">
                <ul class="mb-0 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ isset($permission) ? route('admin.permissions.update', $permission) : route('admin.permissions.store') }}">
            @csrf
            @if(isset($permission)) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Permission Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $permission->name ?? '') }}"
                           placeholder="contoh: visits.index" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Nama Tampilan <span class="text-danger">*</span></label>
                    <input type="text" name="display_name" class="form-control"
                           value="{{ old('display_name', $permission->display_name ?? '') }}" placeholder="contoh: Lihat Kunjungan" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Group</label>
                    <input type="text" name="group_name" class="form-control" list="groups"
                           value="{{ old('group_name', $permission->group_name ?? '') }}" placeholder="contoh: visits">
                    <datalist id="groups">
                        @foreach($groups as $g)
                            <option value="{{ $g }}">
                        @endforeach
                    </datalist>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>{{ isset($permission) ? 'Perbarui' : 'Simpan' }}</button>
                <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
