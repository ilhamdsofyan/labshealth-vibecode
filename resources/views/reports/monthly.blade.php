@extends('layouts.app')

@section('title', $reportTitle)

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">{{ $reportTitle }}</h4>
        <p class="text-muted mb-0 small">Rekapitulasi {{ $reportType === 'acc_pulang' ? 'acc pulang' : 'kunjungan' }}</p>
    </div>
    <div class="d-flex gap-2 no-print">
        <a href="{{ route('reports.export-excel', ['month' => $month, 'year' => $year, 'type' => $reportType]) }}"
           class="btn btn-success btn-sm">
            <i class="bi bi-file-earmark-excel me-1"></i>Excel
        </a>
        <a href="{{ route('reports.export-pdf', ['month' => $month, 'year' => $year, 'type' => $reportType]) }}"
           class="btn btn-danger btn-sm">
            <i class="bi bi-file-earmark-pdf me-1"></i>PDF
        </a>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-printer me-1"></i>Cetak
        </button>
    </div>
</div>

<!-- Filter -->
<div class="card mb-3 no-print">
    <div class="card-body">
        <form method="GET" action="{{ $reportType === 'acc_pulang' ? route('reports.acc-pulang') : route('reports.monthly') }}">
            <div class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label small fw-semibold">Bulan</label>
                    <select name="month" class="form-select form-select-sm">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label small fw-semibold">Tahun</label>
                    <select name="year" class="form-select form-select-sm">
                        @for($y = now()->year; $y >= now()->year - 5; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Report Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead class="bg-primary text-white">
                    <tr>
                        <th width="50">No</th>
                        <th>Nama Penyakit / Keluhan</th>
                        <th width="80" class="text-center">SMA</th>
                        <th width="80" class="text-center">GURU</th>
                        <th width="100" class="text-center">KARYAWAN</th>
                        <th width="80" class="text-center">UMUM</th>
                        <th width="80" class="text-center">Total</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($report['data'] as $index => $row)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $row['disease_name'] }}</td>
                            <td class="text-center">{{ $row['SMA'] }}</td>
                            <td class="text-center">{{ $row['GURU'] }}</td>
                            <td class="text-center">{{ $row['KARYAWAN'] }}</td>
                            <td class="text-center">{{ $row['UMUM'] }}</td>
                            <td class="text-center fw-bold">{{ $row['total'] }}</td>
                            <td>{{ $row['notes'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Tidak ada data untuk periode ini
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($report['data']->isNotEmpty())
                    <tfoot class="bg-light fw-bold">
                        <tr>
                            <td colspan="2" class="text-center">TOTAL</td>
                            <td class="text-center">{{ $report['totals']['SMA'] }}</td>
                            <td class="text-center">{{ $report['totals']['GURU'] }}</td>
                            <td class="text-center">{{ $report['totals']['KARYAWAN'] }}</td>
                            <td class="text-center">{{ $report['totals']['UMUM'] }}</td>
                            <td class="text-center">{{ $report['totals']['total'] }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
