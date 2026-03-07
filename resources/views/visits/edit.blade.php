@extends('layouts.app')

@section('title', 'Edit Kunjungan')

@section('content')
<div class="d-flex align-items-center mb-4">
    <a href="{{ route('visits.index') }}" class="btn btn-outline-secondary btn-sm me-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="fw-bold mb-0">Edit Kunjungan</h4>
        <p class="text-muted mb-0 small">Perbarui data kunjungan</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('visits.update', $visit) }}">
            @csrf @method('PUT')
            @include('visits._form')

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>Perbarui
                </button>
                <a href="{{ route('visits.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
