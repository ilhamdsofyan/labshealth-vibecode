@extends('layouts.app')

@section('title', 'Data Kunjungan')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Data Kunjungan</h4>
        <p class="text-muted mb-0 small">Kelola data kunjungan UKS (Normalized)</p>
    </div>
    <a href="{{ route('visits.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Tambah Kunjungan
    </a>
</div>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('visits.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Pencarian</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Nama, keluhan, penyakit..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2 col-6">
                    <label class="form-label small fw-semibold">Dari Tanggal</label>
                    <input type="date" name="date_from" class="form-control form-control-sm"
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2 col-6">
                    <label class="form-label small fw-semibold">Sampai Tanggal</label>
                    <input type="date" name="date_to" class="form-control form-control-sm"
                           value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Kategori</label>
                    <select name="patient_category" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        @foreach(['SMA', 'GURU', 'KARYAWAN', 'UMUM'] as $cat)
                            <option value="{{ $cat }}" {{ request('patient_category') == $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="checkbox" name="is_acc_pulang" value="1" id="filterPulang" {{ request('is_acc_pulang') ? 'checked' : '' }}>
                        <label class="form-check-label small" for="filterPulang">Hanya Acc Pulang</label>
                    </div>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Data Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>No</th>
                        <th>Waktu</th>
                        <th>Pasien</th>
                        <th>Kategori</th>
                        <th class="d-none d-lg-table-cell">Diseases</th>
                        <th class="d-none d-lg-table-cell">Obat</th>
                        <th>Keluhan</th>
                        <th>Status</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($visits as $index => $visit)
                        <tr>
                            <td class="small">{{ $visits->firstItem() + $index }}</td>
                            <td class="small text-nowrap">
                                <div class="fw-bold">{{ $visit->visit_date->format('d/m/Y') }}</div>
                                <div class="text-muted">{{ $visit->visit_time }}</div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $visit->patient_name }}</div>
                                <div class="small text-muted">
                                    @if($visit->student)
                                        NIS: {{ $visit->student->nis }}
                                    @elseif($visit->employee)
                                        NIP: {{ $visit->employee->nip }}
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-category badge-{{ strtolower($visit->patient_category) }}">
                                    {{ $visit->patient_category }}
                                </span>
                                <div class="small text-muted mt-1">{{ $visit->class_at_visit ?? $visit->class_or_department ?? '-' }}</div>
                            </td>
                            <td class="d-none d-lg-table-cell fw-medium text-primary small">
                                {{ $visit->disease?->name ?? '-' }}
                            </td>
                            <td class="d-none d-lg-table-cell small">
                                {{ $visit->medication?->name ?? '-' }}
                            </td>
                            <td class="small" title="{{ $visit->complaint }}">
                                {{ Str::limit($visit->complaint, 30) }}
                            </td>
                            <td>
                                @if($visit->is_acc_pulang)
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2">
                                        <i class="bi bi-house-door-fill me-1"></i>Pulang
                                    </span>
                                @else
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2">
                                        <i class="bi bi-person-check me-1"></i>Sembuh
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('visits.show', $visit) }}" class="btn btn-outline-primary" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('visits.edit', $visit) }}" class="btn btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('visits.destroy', $visit) }}" method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Belum ada data kunjungan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($visits->hasPages())
        <div class="card-footer bg-transparent">
            {{ $visits->links() }}
        </div>
    @endif
</div>
@endsection
