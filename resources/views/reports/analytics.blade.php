@extends('layouts.app')

@section('title', 'Laporan Analitik')

@php
    $summary = $report['summary'];
    $summaryDrilldowns = $report['summary_drilldowns'];
@endphp

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Laporan Analitik</h4>
        <p class="text-muted mb-0 small">Insight operasional untuk pemakaian obat, pola rest, acc pulang, dan kunjungan berulang.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.monthly') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-table me-1"></i>Rekap Bulanan
        </a>
        <a href="{{ route('reports.acc-pulang') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-house-door me-1"></i>Acc Pulang
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.analytics') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small fw-semibold">Dari Tanggal</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small fw-semibold">Sampai Tanggal</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
                </div>
                <div class="col-md-3 col-sm-6">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-bar-chart-line me-1"></i>Tampilkan Analitik
                    </button>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="small text-muted text-md-end">
                        Periode:
                        <div class="fw-semibold">{{ $report['period']['start_date']->format('d M Y') }} - {{ $report['period']['end_date']->format('d M Y') }}</div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-1">Total Kunjungan</div>
                <button type="button" class="btn btn-link p-0 fs-4 fw-bold text-decoration-none" data-bs-toggle="modal" data-bs-target="#summaryVisitsModal">
                    {{ $summary['visits_total'] }}
                </button>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-1">Total Riwayat Rest</div>
                <button type="button" class="btn btn-link p-0 fs-4 fw-bold text-warning text-decoration-none" data-bs-toggle="modal" data-bs-target="#summaryRestModal">
                    {{ $summary['rest_total'] }}
                </button>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-1">Acc Pulang</div>
                <button type="button" class="btn btn-link p-0 fs-4 fw-bold text-danger text-decoration-none" data-bs-toggle="modal" data-bs-target="#summaryAccPulangModal">
                    {{ $summary['acc_pulang_total'] }}
                </button>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-1">Siswa Terlibat</div>
                <button type="button" class="btn btn-link p-0 fs-4 fw-bold text-primary text-decoration-none" data-bs-toggle="modal" data-bs-target="#summaryStudentsModal">
                    {{ $summary['students_total'] }}
                </button>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-1">Pegawai Terlibat</div>
                <button type="button" class="btn btn-link p-0 fs-4 fw-bold text-info text-decoration-none" data-bs-toggle="modal" data-bs-target="#summaryEmployeesModal">
                    {{ $summary['employees_total'] }}
                </button>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-1">Pemberian Obat</div>
                <button type="button" class="btn btn-link p-0 fs-4 fw-bold text-success text-decoration-none" data-bs-toggle="modal" data-bs-target="#summaryMedicationAdministrationsModal">
                    {{ $summary['medication_administrations_total'] }}
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header">
                <span class="fw-bold">Obat Paling Sering Diberikan</span>
                <div class="small text-muted">Proxy untuk melihat obat yang kemungkinan cepat bergerak / cepat habis.</div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Obat</th>
                                <th>Kategori</th>
                                <th class="text-center">Pemberian</th>
                                <th class="text-center">Kunjungan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($report['top_medications'] as $row)
                                <tr>
                                    <td class="fw-semibold">{{ $row['name'] }}</td>
                                    <td>{{ $row['category'] ?: '-' }}</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-link btn-sm p-0 fw-bold text-success text-decoration-none" data-bs-toggle="modal" data-bs-target="#medicationModal{{ $row['id'] }}">
                                            {{ $row['usage_count'] }}
                                        </button>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-link btn-sm p-0 fw-bold text-decoration-none" data-bs-toggle="modal" data-bs-target="#medicationModal{{ $row['id'] }}">
                                            {{ $row['visit_count'] }}
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Belum ada data penggunaan obat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header">
                <span class="fw-bold">Penyakit Paling Sering Muncul</span>
                <div class="small text-muted">Menunjukkan tren kasus yang paling dominan pada periode terpilih.</div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Penyakit</th>
                                <th>Kategori</th>
                                <th class="text-center">Kasus</th>
                                <th class="text-center">Kunjungan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($report['top_diseases'] as $row)
                                <tr>
                                    <td class="fw-semibold">{{ $row['name'] }}</td>
                                    <td>{{ $row['category'] ?: '-' }}</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-link btn-sm p-0 fw-bold text-danger text-decoration-none" data-bs-toggle="modal" data-bs-target="#diseaseModal{{ $row['id'] }}">
                                            {{ $row['case_count'] }}
                                        </button>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-link btn-sm p-0 fw-bold text-decoration-none" data-bs-toggle="modal" data-bs-target="#diseaseModal{{ $row['id'] }}">
                                            {{ $row['visit_count'] }}
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Belum ada data penyakit.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <span class="fw-bold">Pengunjung Paling Sering Berkunjung</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Pengunjung</th>
                                <th class="text-center">Kunjungan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($report['frequent_visitors'] as $row)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $row['name'] }}</div>
                                        <div class="small text-muted">{{ strtoupper($row['type']) }}{{ $row['meta'] ? ' | ' . $row['meta'] : '' }}</div>
                                        @if($row['history_url'])
                                            <a href="{{ $row['history_url'] }}" class="small">Lihat riwayat</a>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-link btn-sm p-0 fw-bold text-decoration-none" data-bs-toggle="modal" data-bs-target="#frequentVisitorModal{{ $row['type'] }}{{ $row['id'] }}">
                                            {{ $row['visit_count'] }}
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-4">Belum ada data pengunjung berulang.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <span class="fw-bold">Siswa / Pegawai Paling Sering Rest</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Pengunjung</th>
                                <th class="text-center">Rest</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($report['rest_visitors'] as $row)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $row['name'] }}</div>
                                        <div class="small text-muted">{{ strtoupper($row['type']) }}{{ $row['meta'] ? ' | ' . $row['meta'] : '' }}</div>
                                        @if($row['history_url'])
                                            <a href="{{ $row['history_url'] }}" class="small">Lihat riwayat</a>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-link btn-sm p-0 fw-bold text-warning text-decoration-none" data-bs-toggle="modal" data-bs-target="#restVisitorModal{{ $row['type'] }}{{ $row['id'] }}">
                                            {{ $row['rest_count'] }}
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-4">Belum ada data rest pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <span class="fw-bold">Siswa / Pegawai Paling Sering Acc Pulang</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Pengunjung</th>
                                <th class="text-center">Acc Pulang</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($report['acc_pulang_visitors'] as $row)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $row['name'] }}</div>
                                        <div class="small text-muted">{{ strtoupper($row['type']) }}{{ $row['meta'] ? ' | ' . $row['meta'] : '' }}</div>
                                        @if($row['history_url'])
                                            <a href="{{ $row['history_url'] }}" class="small">Lihat riwayat</a>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-link btn-sm p-0 fw-bold text-danger text-decoration-none" data-bs-toggle="modal" data-bs-target="#accPulangVisitorModal{{ $row['type'] }}{{ $row['id'] }}">
                                            {{ $row['acc_pulang_count'] }}
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-4">Belum ada data acc pulang pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@include('reports.partials.drilldown-modal', [
    'id' => 'summaryVisitsModal',
    'title' => 'Detail Total Kunjungan',
    'subtitle' => 'Daftar kunjungan pada periode terpilih.',
    'items' => $summaryDrilldowns['visits_total']['items'] ?? [],
])

@include('reports.partials.drilldown-modal', [
    'id' => 'summaryRestModal',
    'title' => 'Detail Total Rest',
    'subtitle' => 'Daftar kunjungan yang pernah rest, termasuk yang sudah selesai rest.',
    'items' => $summaryDrilldowns['rest_total']['items'] ?? [],
])

@include('reports.partials.drilldown-modal', [
    'id' => 'summaryAccPulangModal',
    'title' => 'Detail Acc Pulang',
    'subtitle' => 'Daftar kunjungan yang mendapat acc pulang.',
    'items' => $summaryDrilldowns['acc_pulang_total']['items'] ?? [],
])

@include('reports.partials.drilldown-modal', [
    'id' => 'summaryStudentsModal',
    'title' => 'Daftar Siswa Terlibat',
    'subtitle' => 'Siswa unik yang tercatat pada periode terpilih.',
    'items' => $summaryDrilldowns['students_total']['items'] ?? [],
])

@include('reports.partials.drilldown-modal', [
    'id' => 'summaryEmployeesModal',
    'title' => 'Daftar Pegawai Terlibat',
    'subtitle' => 'Pegawai unik yang tercatat pada periode terpilih.',
    'items' => $summaryDrilldowns['employees_total']['items'] ?? [],
])

@include('reports.partials.drilldown-modal', [
    'id' => 'summaryMedicationAdministrationsModal',
    'title' => 'Detail Pemberian Obat',
    'subtitle' => 'Daftar pemberian obat pada periode terpilih.',
    'items' => $summaryDrilldowns['medication_administrations_total']['items'] ?? [],
])

@foreach($report['top_medications'] as $row)
    @include('reports.partials.drilldown-modal', [
        'id' => 'medicationModal' . $row['id'],
        'title' => 'Pemakaian Obat: ' . $row['name'],
        'subtitle' => 'Daftar kunjungan yang menggunakan obat ini.',
        'items' => $row['drilldown']['items'] ?? [],
    ])
@endforeach

@foreach($report['top_diseases'] as $row)
    @include('reports.partials.drilldown-modal', [
        'id' => 'diseaseModal' . $row['id'],
        'title' => 'Kasus Penyakit: ' . $row['name'],
        'subtitle' => 'Daftar kunjungan yang terkait dengan penyakit ini.',
        'items' => $row['drilldown']['items'] ?? [],
    ])
@endforeach

@foreach($report['frequent_visitors'] as $row)
    @include('reports.partials.drilldown-modal', [
        'id' => 'frequentVisitorModal' . $row['type'] . $row['id'],
        'title' => 'Riwayat Kunjungan: ' . $row['name'],
        'subtitle' => 'Detail kunjungan berulang pada periode terpilih.',
        'items' => $row['drilldown']['items'] ?? [],
    ])
@endforeach

@foreach($report['rest_visitors'] as $row)
    @include('reports.partials.drilldown-modal', [
        'id' => 'restVisitorModal' . $row['type'] . $row['id'],
        'title' => 'Riwayat Rest: ' . $row['name'],
        'subtitle' => 'Detail kunjungan yang pernah rest pada periode terpilih.',
        'items' => $row['drilldown']['items'] ?? [],
    ])
@endforeach

@foreach($report['acc_pulang_visitors'] as $row)
    @include('reports.partials.drilldown-modal', [
        'id' => 'accPulangVisitorModal' . $row['type'] . $row['id'],
        'title' => 'Riwayat Acc Pulang: ' . $row['name'],
        'subtitle' => 'Detail kunjungan acc pulang pada periode terpilih.',
        'items' => $row['drilldown']['items'] ?? [],
    ])
@endforeach
@endsection
