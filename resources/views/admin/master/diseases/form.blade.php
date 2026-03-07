@extends('layouts.app')

@section('title', (isset($disease) ? 'Edit' : 'Tambah') . ' Penyakit')

@section('content')
<div class="d-flex align-items-center mb-4">
    <a href="{{ route('admin.master.diseases.index') }}" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
    <h4 class="fw-bold mb-0">{{ isset($disease) ? 'Edit Penyakit' : 'Tambah Penyakit' }}</h4>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ isset($disease) ? route('admin.master.diseases.update', $disease) : route('admin.master.diseases.store') }}" method="POST">
            @csrf
            @if(isset($disease)) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label small fw-semibold">Nama Penyakit <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                           value="{{ old('name', $disease->name ?? '') }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Kategori</label>
                    <input type="text" name="category" class="form-control @error('category') is-invalid @enderror" 
                           value="{{ old('category', $disease->category ?? '') }}" placeholder="Contoh: Umum / Menular">
                    @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i>{{ isset($disease) ? 'Perbarui' : 'Simpan' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
