@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
    $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    $categoryLabels = [
        'SMA' => 'Siswa',
        'GURU' => 'Guru',
        'KARYAWAN' => 'Karyawan',
        'UMUM' => 'Umum',
    ];

    $categoryColors = [
        'SMA' => '#0ea5e9',
        'GURU' => '#14b8a6',
        'KARYAWAN' => '#f59e0b',
        'UMUM' => '#ef4444',
    ];

    $counts = [
        'SMA' => $categoryStats['SMA'] ?? 0,
        'GURU' => $categoryStats['GURU'] ?? 0,
        'KARYAWAN' => $categoryStats['KARYAWAN'] ?? 0,
        'UMUM' => $categoryStats['UMUM'] ?? 0,
    ];

    $totalCategory = max(array_sum($counts), 1);
    $segments = [];
    foreach ($counts as $code => $count) {
        $segments[] = $categoryColors[$code] . ' ' . round(($count / $totalCategory) * 100, 2) . '%';
    }
    $donutGradient = 'conic-gradient(' . implode(', ', $segments) . ')';

    $startCalendar = now()->setYear($selectedYear)->setMonth($selectedMonth)->startOfMonth();
    $endCalendar = $startCalendar->copy()->endOfMonth();

    $calendarCells = [];
    $leadingBlanks = $startCalendar->dayOfWeekIso - 1;
    for ($i = 0; $i < $leadingBlanks; $i++) {
        $calendarCells[] = null;
    }

    for ($day = 1; $day <= $endCalendar->day; $day++) {
        $date = $startCalendar->copy()->day($day);
        $key = $date->toDateString();
        $calendarCells[] = [
            'day' => $day,
            'date' => $key,
            'count' => $calendarCounts[$key] ?? 0,
            'is_today' => $key === now()->toDateString(),
        ];
    }
@endphp

<style>
    .dash-shell { display: flex; flex-direction: column; gap: 1.5rem; }
    .hero-panel {
        display: flex; align-items: center; justify-content: space-between;
        background: linear-gradient(135deg, color-mix(in srgb, var(--accent) 18%, transparent), color-mix(in srgb, var(--primary) 12%, transparent));
        border: 1px solid color-mix(in srgb, var(--accent) 30%, var(--border));
        border-radius: 16px; padding: 1.8rem;
    }
    html[data-theme='dark'] .hero-panel {
        background: linear-gradient(135deg, #1e1e1e, #2a2a2a);
        border-color: #333;
    }
    .hero-title { font-size: 1.85rem; font-weight: 800; letter-spacing: -0.03em; margin-bottom: 0.35rem; }
    .hero-subtitle { color: var(--text-muted); margin-bottom: 1rem; }
    .hero-actions .btn { border-radius: 12px; font-weight: 700; }
    .quick-card { border-radius: 16px; }
    .quick-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.75rem; }
    .quick-item {
        border: 1px solid var(--border); border-radius: 14px; padding: 0.95rem;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        background: var(--bg-surface); color: var(--text-main); transition: all .2s ease;
    }
    .quick-item:hover { border-color: var(--accent); background: color-mix(in srgb, var(--accent) 10%, var(--bg-surface)); }
    .section-card { border-radius: 16px; }
    .section-head { padding: 1rem 1.2rem; border-bottom: 1px solid var(--border); display:flex; justify-content:space-between; align-items:center; }
    .section-body { padding: 1.2rem; }

    html[data-theme='dark'] .section-head h6,
    html[data-theme='dark'] .section-body,
    html[data-theme='dark'] .section-body .fw-semibold,
    html[data-theme='dark'] .section-body .small {
        color: #e8eeff;
    }

    .sickbay-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0.75rem; }
    .bed-item {
        border: 1px dashed var(--border); border-radius: 12px; min-height: 94px;
        display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.3rem;
        font-size: 0.8rem;
    }

    html[data-theme='dark'] .bed-item {
        color: #dce6fb;
    }
    .bed-item.filled {
        border: 2px solid var(--primary);
        background: color-mix(in srgb, var(--primary) 10%, var(--bg-surface));
    }

    .health-layout { display: grid; grid-template-columns: 180px 1fr; gap: 1rem; align-items: center; }
    .donut-wrap {
        width: 160px; height: 160px; border-radius: 999px; margin: 0 auto;
        background: var(--donut-gradient);
        display: grid; place-items: center;
    }
    .donut-inner {
        width: 92px; height: 92px; border-radius: 999px;
        background: var(--bg-surface); display: grid; place-items: center;
        font-weight: 800;
    }
    .health-legend { display: flex; flex-direction: column; gap: 0.5rem; }
    .legend-item { display:flex; align-items:center; justify-content:space-between; gap: 0.5rem; }
    .legend-label { display:flex; align-items:center; gap: 0.5rem; font-size: 0.86rem; }
    .legend-dot { width: 10px; height: 10px; border-radius: 999px; }

    .calendar-grid { display:grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 0.35rem; }
    .cal-day-head { font-size: 0.72rem; color: var(--text-muted); text-align: center; font-weight: 700; }
    .cal-cell {
        min-height: 54px; border: 1px solid var(--border); border-radius: 10px;
        padding: 0.3rem; font-size: 0.76rem; background: var(--bg-surface);
    }
    .cal-cell.blank { border-style: dashed; opacity: 0.5; }
    .cal-cell.today { border-color: var(--primary); }
    .cal-count { font-size: 0.65rem; color: var(--primary); font-weight: 700; }

    html[data-theme='dark'] .hero-title { color: #f8fbff; }
    html[data-theme='dark'] .hero-subtitle { color: #c6d2ea; }
    html[data-theme='dark'] .legend-label,
    html[data-theme='dark'] .cal-day-head,
    html[data-theme='dark'] .cal-cell,
    html[data-theme='dark'] .section-head .small {
        color: #cfd9ee;
    }

    @media (max-width: 1199.98px) {
        .health-layout { grid-template-columns: 1fr; }
    }

    @media (max-width: 991.98px) {
        .hero-panel { flex-direction: column; align-items: flex-start; gap: 1rem; }
        .sickbay-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
</style>

<div class="dash-shell">
    <div class="hero-panel">
        <div>
            <h1 class="hero-title">Selamat datang kembali, {{ auth()->user()->name }}!</h1>
            <p class="hero-subtitle">Saat ini ada <strong class="text-primary">{{ $todayVisits }} kunjungan</strong> yang tercatat hari ini.</p>
            <div class="hero-actions d-flex gap-2">
                <a href="{{ route('visits.create') }}" class="btn btn-primary">Intake Baru</a>
                <a href="{{ route('visits.index') }}" class="btn btn-outline-secondary">Lihat Kunjungan</a>
            </div>
        </div>
        <i class="bi bi-hospital fs-1 text-primary opacity-25"></i>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card section-card h-100">
                <div class="section-head">
                    <h6 class="mb-0 fw-bold">Okupansi Ruang UKS</h6>
                    <span class="badge bg-success bg-opacity-10 text-success">{{ $sickBayFilled }} / {{ $sickBayCapacity }} bed terisi</span>
                </div>
                <div class="section-body">
                    <div class="sickbay-grid">
                        @for($i = 1; $i <= $sickBayCapacity; $i++)
                            <div class="bed-item {{ $i <= $sickBayFilled ? 'filled' : '' }}">
                                <i class="bi bi-h-square"></i>
                                <div class="fw-semibold">Bed {{ $i }}</div>
                                <div class="text-muted">{{ $i <= $sickBayFilled ? 'Terisi' : 'Kosong' }}</div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card quick-card h-100">
                <div class="section-head">
                    <h6 class="mb-0 fw-bold">Aksi Cepat</h6>
                </div>
                <div class="section-body">
                    <div class="quick-grid">
                        <a href="{{ route('visits.create') }}" class="quick-item text-decoration-none">
                            <i class="bi bi-exclamation-triangle text-danger"></i>
                            <span>Insiden</span>
                        </a>
                        <a href="{{ route('visits.index') }}" class="quick-item text-decoration-none">
                            <i class="bi bi-capsule text-success"></i>
                            <span>Log Obat</span>
                        </a>
                        <a href="{{ route('reports.monthly') }}" class="quick-item text-decoration-none">
                            <i class="bi bi-printer text-primary"></i>
                            <span>Laporan</span>
                        </a>
                        <a href="{{ route('reports.monthly') }}" class="quick-item text-decoration-none">
                            <i class="bi bi-clock-history text-warning"></i>
                            <span>Riwayat</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-4">
            <div class="card section-card h-100">
                <div class="section-head">
                    <h6 class="mb-0 fw-bold">Pulse Kesehatan</h6>
                    <form method="GET" class="d-flex gap-2">
                        <select name="month" class="form-select form-select-sm" onchange="this.form.submit()">
                            @foreach($monthNames as $monthNumber => $monthLabel)
                                <option value="{{ $monthNumber }}" {{ $selectedMonth === $monthNumber ? 'selected' : '' }}>{{ $monthLabel }}</option>
                            @endforeach
                        </select>
                        <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                            @foreach($availableYears as $year)
                                <option value="{{ $year }}" {{ $selectedYear === (int) $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="section-body">
                    <div class="health-layout" style="--donut-gradient: {{ $donutGradient }};">
                        <div class="donut-wrap">
                            <div class="donut-inner">
                                <div class="text-center">
                                    <div class="small text-muted">Total</div>
                                    <div class="fs-5">{{ array_sum($counts) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="health-legend">
                            @foreach($counts as $code => $count)
                                @php
                                    $pct = array_sum($counts) > 0 ? round(($count / array_sum($counts)) * 100) : 0;
                                @endphp
                                <div class="legend-item">
                                    <div class="legend-label">
                                        <span class="legend-dot" style="background: {{ $categoryColors[$code] }};"></span>
                                        <span>{{ $categoryLabels[$code] }}</span>
                                    </div>
                                    <div class="small fw-semibold">{{ $count }} ({{ $pct }}%)</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card section-card h-100">
                <div class="section-head">
                    <h6 class="mb-0 fw-bold">Kalender Klinik</h6>
                    <span class="small text-muted">{{ $monthNames[$selectedMonth] }} {{ $selectedYear }}</span>
                </div>
                <div class="section-body">
                    <div class="calendar-grid mb-2">
                        @foreach(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $day)
                            <div class="cal-day-head">{{ $day }}</div>
                        @endforeach
                    </div>
                    <div class="calendar-grid">
                        @foreach($calendarCells as $cell)
                            @if(!$cell)
                                <div class="cal-cell blank"></div>
                            @else
                                <div class="cal-cell {{ $cell['is_today'] ? 'today' : '' }}">
                                    <div class="fw-semibold">{{ $cell['day'] }}</div>
                                    @if($cell['count'] > 0)
                                        <div class="cal-count">{{ $cell['count'] }} kunj.</div>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card section-card h-100">
                <div class="section-head">
                    <h6 class="mb-0 fw-bold">Intake Terbaru</h6>
                    <a href="{{ route('visits.index') }}" class="btn btn-sm btn-outline-primary">Lihat semua</a>
                </div>
                <div class="section-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Pasien</th>
                                    <th>Kategori</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentVisits as $visit)
                                    <tr>
                                        <td class="small">
                                            <div class="fw-semibold">{{ $visit->visit_date->format('d/m') }}</div>
                                            <div class="text-muted">{{ $visit->visit_time }}</div>
                                        </td>
                                        <td class="small fw-semibold">{{ $visit->patient_name }}</td>
                                        <td>
                                            <span class="badge badge-category badge-{{ strtolower($visit->patient_category) }}">{{ $categoryLabels[$visit->patient_category] ?? $visit->patient_category }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">Belum ada data intake terbaru.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
