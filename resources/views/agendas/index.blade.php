@extends('layouts.app')

@section('title', 'Agenda Klinik')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Agenda Klinik</h4>
        <p class="text-muted mb-0 small">Daftar agenda kegiatan UKS/sekolah</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#agendaCreateModal">
        <i class="bi bi-plus-lg me-1"></i>Tambah Agenda
    </button>
</div>

<div data-master-async-container>
    <div class="card" data-master-async-table>
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('agendas.index') }}" class="row g-2 align-items-end js-async-search">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Pencarian</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Judul, lokasi, deskripsi...">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-search me-1"></i>Cari
                    </button>
                    <a href="{{ route('agendas.index') }}" class="btn btn-outline-secondary btn-sm js-async-refresh">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
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
                        <th>Visibilitas</th>
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
                            <td>
                                <span class="badge {{ $agenda->is_public ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary bg-opacity-10 text-secondary' }}">
                                    {{ $agenda->is_public ? 'Publik' : 'Pribadi' }}
                                </span>
                            </td>
                            <td>{{ $agenda->location ?: '-' }}</td>
                            <td class="small text-muted">{{ \Illuminate\Support\Str::limit($agenda->description, 70) ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
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
</div>

<div class="modal fade" id="agendaCreateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Agenda Klinik</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('agendas.store') }}" method="POST" class="row g-3 js-async-master" data-loading-text="Menyimpan...">
                    @csrf

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="agenda_date" class="form-control" value="{{ now()->toDateString() }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Jam</label>
                        <input type="time" name="agenda_time" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Lokasi</label>
                        <input type="text" name="location" class="form-control" placeholder="Contoh: Ruang UKS / Aula">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Visibilitas</label>
                        @if($canChooseVisibility)
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" value="1" id="agenda_is_public" name="is_public">
                                <label class="form-check-label" for="agenda_is_public">Agenda publik (tampil untuk user lain)</label>
                            </div>
                        @else
                            <input type="text" class="form-control" value="Pribadi (otomatis sesuai role)" readonly>
                            <input type="hidden" name="is_public" value="0">
                        @endif
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Judul Agenda <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="Contoh: Pemeriksaan Kesehatan Berkala" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Deskripsi</label>
                        <textarea name="description" rows="4" class="form-control" placeholder="Detail tambahan agenda..."></textarea>
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check2 me-1"></i>Simpan Agenda
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
