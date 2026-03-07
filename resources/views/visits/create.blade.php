@extends('layouts.app')

@section('title', 'Tambah Kunjungan')

@section('content')
<div class="d-flex align-items-center mb-4">
    <a href="{{ route('visits.index') }}" class="btn btn-outline-secondary btn-sm me-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="fw-bold mb-0">Tambah Kunjungan</h4>
        <p class="text-muted mb-0 small">Catat kunjungan pasien baru</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('visits.store') }}">
            @csrf
            @include('visits._form')

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>Simpan
                </button>
                <a href="{{ route('visits.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
