@extends('layouts.app')

@section('title', '403 — Akses Ditolak')

@section('content')
<div class="d-flex align-items-center justify-content-center" style="min-height:60vh;">
    <div class="text-center">
        <div class="mb-3" style="font-size:4rem;opacity:0.5;">🔒</div>
        <h2 class="fw-bold mb-2">403 — Akses Ditolak</h2>
        <p class="text-muted mb-4">{{ $exception->getMessage() ?: 'Anda tidak memiliki izin untuk mengakses halaman ini.' }}</p>
        <a href="{{ route('dashboard') }}" class="btn btn-primary">
            <i class="bi bi-house me-1"></i>Kembali ke Dashboard
        </a>
    </div>
</div>
@endsection
