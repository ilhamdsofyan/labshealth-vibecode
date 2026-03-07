@extends('layouts.app')

@section('title', (isset($medication) ? 'Edit' : 'Tambah') . ' Obat')

@section('content')
<div class="d-flex align-items-center mb-4">
    <a href="{{ route('admin.master.medications.index') }}" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
    <h4 class="fw-bold mb-0">{{ isset($medication) ? 'Edit Obat' : 'Tambah Obat' }}</h4>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ isset($medication) ? route('admin.master.medications.update', $medication) : route('admin.master.medications.store') }}" method="POST">
            @csrf
            @if(isset($medication)) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label small fw-semibold">Nama Obat <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $medication->name ?? '') }}" placeholder="Contoh: Paracetamol 500mg / Betadine" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Kategori</label>
                    <input type="text" name="category" class="form-control @error('category') is-invalid @enderror"
                           value="{{ old('category', $medication->category ?? '') }}" placeholder="Contoh: Antipiretik / Antihistamin / Topikal">
                    @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i>{{ isset($medication) ? 'Perbarui' : 'Simpan' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
