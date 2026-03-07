@extends('layouts.app')

@section('title', (isset($student) ? 'Edit' : 'Tambah') . ' Siswa')

@section('content')
<div class="d-flex align-items-center mb-4">
    <a href="{{ route('admin.master.students.index') }}" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
    <h4 class="fw-bold mb-0">{{ isset($student) ? 'Edit Siswa' : 'Tambah Siswa' }}</h4>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ isset($student) ? route('admin.master.students.update', $student) : route('admin.master.students.store') }}" method="POST">
            @csrf
            @if(isset($student)) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">NIS <span class="text-danger">*</span></label>
                    <input type="text" name="nis" class="form-control @error('nis') is-invalid @enderror" 
                           value="{{ old('nis', $student->nis ?? '') }}" required>
                    @error('nis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                           value="{{ old('name', $student->name ?? '') }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
                    <select name="gender" class="form-select @error('gender') is-invalid @enderror" required>
                        <option value="">Pilih</option>
                        <option value="L" {{ old('gender', $student->gender ?? '') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('gender', $student->gender ?? '') == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Kelas Saat Ini <span class="text-danger">*</span></label>
                    <input type="text" name="class_name" class="form-control @error('class_name') is-invalid @enderror" 
                           value="{{ old('class_name', $student->activeClass->class_name ?? '') }}" placeholder="Contoh: X IPA 1" required>
                    @error('class_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Tahun Ajaran <span class="text-danger">*</span></label>
                    <input type="text" name="academic_year" class="form-control @error('academic_year') is-invalid @enderror" 
                           value="{{ old('academic_year', $student->activeClass->academic_year ?? date('Y').'/'.(date('Y')+1)) }}" placeholder="Contoh: 2023/2024" required>
                    @error('academic_year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i>{{ isset($student) ? 'Perbarui' : 'Simpan' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
