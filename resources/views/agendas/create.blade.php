@extends('layouts.app')

@section('title', 'Tambah Agenda Klinik')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Tambah Agenda Klinik</h4>
        <p class="text-muted mb-0 small">Input agenda kegiatan UKS/sekolah</p>
    </div>
    <a href="{{ route('agendas.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('agendas.store') }}" method="POST" class="row g-3">
            @csrf

            <div class="col-md-4">
                <label class="form-label fw-semibold">Tanggal <span class="text-danger">*</span></label>
                <input type="date" name="agenda_date" class="form-control @error('agenda_date') is-invalid @enderror" value="{{ old('agenda_date', now()->toDateString()) }}" required>
                @error('agenda_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Jam</label>
                <input type="time" name="agenda_time" class="form-control @error('agenda_time') is-invalid @enderror" value="{{ old('agenda_time') }}">
                @error('agenda_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Lokasi</label>
                <input type="text" name="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location') }}" placeholder="Contoh: Ruang UKS / Aula">
                @error('location') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Visibilitas</label>
                @if($canChooseVisibility)
                    <div class="form-check mt-2">
                        <input class="form-check-input @error('is_public') is-invalid @enderror" type="checkbox" value="1" id="is_public" name="is_public" {{ old('is_public') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_public">
                            Agenda publik (tampil untuk user lain)
                        </label>
                        @error('is_public') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                @else
                    <input type="text" class="form-control" value="Pribadi (otomatis sesuai role)" readonly>
                    <input type="hidden" name="is_public" value="0">
                @endif
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold">Judul Agenda <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Contoh: Pemeriksaan Kesehatan Berkala" required>
                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold">Deskripsi</label>
                <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror" placeholder="Detail tambahan agenda...">{{ old('description') }}</textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12 d-flex justify-content-end gap-2">
                <a href="{{ route('agendas.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check2 me-1"></i>Simpan Agenda
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
