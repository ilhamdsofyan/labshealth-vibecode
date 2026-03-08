@extends('layouts.app')

@section('title', 'Agenda Klinik')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Agenda Klinik</h4>
        <p class="text-muted mb-0 small">Daftar agenda kegiatan UKS/sekolah</p>
    </div>
    <a href="{{ route('agendas.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Tambah Agenda
    </a>
</div>

<div class="card">
    <div class="card-body border-bottom">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Pencarian</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Judul, lokasi, deskripsi...">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search me-1"></i>Cari
                </button>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th width="70">No</th>
                    <th>Tanggal</th>
                    <th>Jam</th>
                    <th>Judul Agenda</th>
                    <th>Lokasi</th>
                    <th>Deskripsi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($agendas as $index => $agenda)
                    <tr>
                        <td>{{ $agendas->firstItem() + $index }}</td>
                        <td>{{ $agenda->agenda_date->translatedFormat('d M Y') }}</td>
                        <td>{{ $agenda->agenda_time ? \Illuminate\Support\Carbon::parse($agenda->agenda_time)->format('H:i') : '-' }}</td>
                        <td class="fw-semibold">{{ $agenda->title }}</td>
                        <td>{{ $agenda->location ?: '-' }}</td>
                        <td class="small text-muted">{{ \Illuminate\Support\Str::limit($agenda->description, 70) ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="bi bi-calendar-event fs-1 d-block mb-2"></i>
                            Belum ada agenda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($agendas->hasPages())
        <div class="card-footer bg-transparent">
            {{ $agendas->links() }}
        </div>
    @endif
</div>
@endsection
