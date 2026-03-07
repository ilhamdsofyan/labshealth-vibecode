@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Dashboard</h4>
        <p class="text-muted mb-0 small">Ringkasan kunjungan UKS</p>
    </div>
    <span class="badge bg-light text-dark border">
        <i class="bi bi-calendar3 me-1"></i>{{ now()->translatedFormat('l, d F Y') }}
    </span>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-clipboard2-pulse"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $todayVisits }}</div>
                    <div class="stat-label">Kunjungan Hari Ini</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:rgba(16,185,129,0.1);color:#10B981;">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $monthVisits }}</div>
                    <div class="stat-label">Bulan Ini</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:rgba(245,158,11,0.1);color:#F59E0B;">
                    <i class="bi bi-people"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $categoryStats['SMA'] ?? 0 }}</div>
                    <div class="stat-label">Siswa SMA</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:rgba(139,92,246,0.1);color:#8B5CF6;">
                    <i class="bi bi-person-workspace"></i>
                </div>
                <div>
                    <div class="stat-value">{{ ($categoryStats['GURU'] ?? 0) + ($categoryStats['KARYAWAN'] ?? 0) }}</div>
                    <div class="stat-label">Guru & Karyawan</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Category Breakdown -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center">
                <i class="bi bi-pie-chart me-2 text-primary"></i>
                <span>Kategori Bulan Ini</span>
            </div>
            <div class="card-body">
                @php
                    $cats = ['SMA' => 'primary', 'GURU' => 'success', 'KARYAWAN' => 'warning', 'UMUM' => 'info'];
                    $totalMonth = max(array_sum($categoryStats), 1);
                @endphp
                @foreach($cats as $cat => $color)
                    @php $count = $categoryStats[$cat] ?? 0; $pct = round(($count / $totalMonth) * 100); @endphp
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small fw-medium">{{ $cat }}</span>
                        <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }}">{{ $count }}</span>
                    </div>
                    <div class="progress mb-3" style="height:6px;">
                        <div class="progress-bar bg-{{ $color }}" style="width:{{ $pct }}%"></div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Recent Visits -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-clock-history me-2 text-primary"></i>Kunjungan Terbaru</span>
                <a href="{{ route('visits.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama Pasien</th>
                                <th>Kategori</th>
                                <th>Keluhan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentVisits as $visit)
                                <tr>
                                    <td class="small">{{ $visit->visit_date->format('d/m/Y') }}<br>
                                        <span class="text-muted">{{ $visit->visit_time }}</span>
                                    </td>
                                    <td class="fw-medium">{{ $visit->patient_name }}</td>
                                    <td>
                                        <span class="badge badge-category badge-{{ strtolower($visit->patient_category) }}">
                                            {{ $visit->patient_category }}
                                        </span>
                                    </td>
                                    <td class="small">{{ Str::limit($visit->complaint, 40) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                        Belum ada data kunjungan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
