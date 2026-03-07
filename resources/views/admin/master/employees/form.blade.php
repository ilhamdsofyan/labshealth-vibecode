@extends('layouts.app')

@section('title', (isset($employee) ? 'Edit' : 'Tambah') . ' Pegawai')

@section('content')
<div class="d-flex align-items-center mb-4">
    <a href="{{ route('admin.master.employees.index') }}" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
    <h4 class="fw-bold mb-0">{{ isset($employee) ? 'Edit Pegawai' : 'Tambah Pegawai' }}</h4>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ isset($employee) ? route('admin.master.employees.update', $employee) : route('admin.master.employees.store') }}" method="POST">
            @csrf
            @if(isset($employee)) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">NIP <span class="text-danger">*</span></label>
                    <input type="text" name="nip" class="form-control @error('nip') is-invalid @enderror" 
                           value="{{ old('nip', $employee->nip ?? '') }}" required>
                    @error('nip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                           value="{{ old('name', $employee->name ?? '') }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Tipe Pegawai <span class="text-danger">*</span></label>
                    <select name="role_type" class="form-select @error('role_type') is-invalid @enderror" required>
                        <option value="">Pilih</option>
                        <option value="GURU" {{ old('role_type', $employee->role_type ?? '') == 'GURU' ? 'selected' : '' }}>GURU</option>
                        <option value="KARYAWAN" {{ old('role_type', $employee->role_type ?? '') == 'KARYAWAN' ? 'selected' : '' }}>KARYAWAN</option>
                    </select>
                    @error('role_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Bagian / Unit</label>
                    <input type="text" name="department" class="form-control @error('department') is-invalid @enderror" 
                           value="{{ old('department', $employee->department ?? '') }}" placeholder="Contoh: Tata Usaha / Kurikulum">
                    @error('department') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i>{{ isset($employee) ? 'Perbarui' : 'Simpan' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
