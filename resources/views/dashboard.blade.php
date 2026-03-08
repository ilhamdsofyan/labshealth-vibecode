@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
    $categoryLabels = ['SMA' => 'Siswa', 'GURU' => 'Guru', 'KARYAWAN' => 'Karyawan', 'UMUM' => 'Umum'];
    $categoryColors = ['SMA' => '#facc15', 'GURU' => '#3b82f6', 'KARYAWAN' => '#22d3ee', 'UMUM' => '#14b8a6'];

    $counts = [
        'SMA' => $categoryStats['SMA'] ?? 0,
        'GURU' => $categoryStats['GURU'] ?? 0,
        'KARYAWAN' => $categoryStats['KARYAWAN'] ?? 0,
        'UMUM' => $categoryStats['UMUM'] ?? 0,
    ];

    $totalCounts = max(array_sum($counts), 1);

    $donutSegments = [];
    foreach ($counts as $key => $val) {
        $donutSegments[] = $categoryColors[$key] . ' ' . round(($val / $totalCounts) * 100, 2) . '%';
    }

    $donutGradient = 'conic-gradient(' . implode(', ', $donutSegments) . ')';

    $bedPatients = $recentVisits->take($sickBayCapacity)->pluck('patient_name')->values();
@endphp

<style>
    .dashboard-v2 { display: flex; flex-direction: column; gap: 1rem; }
    .panel-card {
        border: 1px solid var(--border);
        border-radius: 14px;
        background: linear-gradient(120deg, color-mix(in srgb, var(--bg-surface) 95%, #000 5%), var(--bg-surface));
        box-shadow: var(--shadow-card);
        overflow: hidden;
    }

    .panel-head {
        padding: 1rem 1.1rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .panel-title {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--text-main);
    }

    .panel-body { padding: 1rem 1.1rem; }

    .hero-wrap {
        display: grid;
        grid-template-columns: 2fr 0.9fr;
        gap: 1rem;
    }

    .hero-banner {
        min-height: 185px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.5rem;
        background: linear-gradient(110deg, #1d1f24, #262a31);
        border-radius: 14px;
        border: 1px solid #2f3642;
    }

    .hero-banner h1 {
        margin: 0 0 0.45rem;
        font-size: 2.85rem;
        line-height: 1.05;
        font-weight: 900;
        letter-spacing: -0.03em;
        color: #f8fbff;
    }

    .hero-banner p {
        margin: 0 0 1rem;
        color: #b9c3d8;
        font-size: 1.05rem;
    }

    .hero-banner p strong { color: #1fb6ff; }

    .hero-actions .btn {
        border-radius: 12px;
        font-weight: 700;
        padding: 0.55rem 1.05rem;
    }

    .hero-actions .btn-outline-secondary {
        color: #e2e8f5;
        border-color: #3d475a;
    }

    .hero-actions .btn-outline-secondary:hover {
        background: #323949;
    }

    .hero-icon {
        font-size: 3rem;
        color: #475469;
    }

    .quick-actions {
        height: 100%;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.7rem;
    }

    .quick-action-item {
        min-height: 82px;
        border-radius: 12px;
        border: 1px solid #2c3444;
        background: #1c212b;
        color: #e8efff;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        font-weight: 700;
        font-size: 0.9rem;
    }

    .quick-action-item:hover { border-color: #41506a; }

    .middle-wrap {
        display: grid;
        grid-template-columns: 2fr 0.95fr 0.95fr;
        gap: 1rem;
    }

    .occupancy-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.7rem;
    }

    .bed-box {
        min-height: 90px;
        border-radius: 10px;
        border: 1px dashed #2f3644;
        background: #171d27;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.2rem;
        color: #9fb1ce;
    }

    .bed-box.active {
        border: 2px solid #22b7ff;
        background: #16232f;
        color: #ecf4ff;
    }

    .bed-name { font-size: 0.86rem; font-weight: 700; }

    .health-pulse-wrap {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.8rem;
    }

    .donut {
        width: 156px;
        height: 156px;
        border-radius: 999px;
        margin: 0.2rem auto 0.5rem;
        background: var(--donut-gradient);
        display: grid;
        place-items: center;
    }

    .donut-inner {
        width: 88px;
        height: 88px;
        border-radius: 999px;
        background: #191f2a;
        color: #f8fbff;
        display: grid;
        place-items: center;
        text-align: center;
        font-weight: 800;
    }

    .donut-score { font-size: 2rem; line-height: 1; }

    .legend-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.84rem;
        color: #c3cee5;
        margin-bottom: 0.35rem;
    }

    .legend-left { display: flex; align-items: center; gap: 0.5rem; }
    .legend-dot { width: 10px; height: 10px; border-radius: 999px; }

    .clinic-list { display: flex; flex-direction: column; gap: 0.7rem; }

    .clinic-item {
        display: grid;
        grid-template-columns: 48px 1fr;
        gap: 0.7rem;
        align-items: center;
    }

    .clinic-date {
        border-radius: 10px;
        background: #242d3b;
        color: #8bb9ff;
        text-align: center;
        line-height: 1.05;
        padding: 0.4rem 0.25rem;
        border: 1px solid #344157;
    }

    .clinic-date small { display: block; font-size: 0.6rem; font-weight: 700; letter-spacing: 0.05em; }
    .clinic-date strong { font-size: 1.3rem; font-weight: 800; }

    .clinic-title { margin: 0; font-size: 0.9rem; font-weight: 700; color: #f1f6ff; }
    .clinic-sub { margin: 0; font-size: 0.78rem; color: #9db0ce; }

    .clinic-btn {
        margin-top: 0.8rem;
        width: 100%;
        border-radius: 10px;
        border: 1px solid #39445a;
        background: #232a35;
        color: #dbe5f7;
        font-size: 0.8rem;
        font-weight: 700;
        padding: 0.45rem;
    }

    .recent-head-link {
        color: #22b7ff;
        text-decoration: none;
        font-weight: 700;
        font-size: 0.86rem;
    }

    .recent-table th {
        font-size: 0.7rem;
        letter-spacing: 0.05em;
        color: #9fb0cb;
        background: #1f2631;
    }

    .recent-table td { color: #e5edff; }

    .student-chip {
        width: 30px; height: 30px; border-radius: 999px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 0.7rem; font-weight: 800; margin-right: 0.55rem;
        background: #253248; color: #9bc4ff;
    }

    .status-pill {
        border-radius: 6px;
        padding: 0.2rem 0.5rem;
        font-size: 0.65rem;
        font-weight: 800;
        letter-spacing: 0.02em;
    }

    .status-observe { background: #fff3c4; color: #915d0f; }
    .status-discharge { background: #d9fce7; color: #0d7c44; }

    @media (max-width: 1200px) {
        .hero-wrap { grid-template-columns: 1fr; }
        .middle-wrap { grid-template-columns: 1fr; }
    }

    @media (max-width: 768px) {
        .hero-banner h1 { font-size: 1.8rem; }
        .hero-banner { min-height: auto; }
        .occupancy-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
</style>

<div class="dashboard-v2">
    <div class="hero-wrap">
        <div class="hero-banner">
            <div>
                <h1>Selamat datang kembali, {{ auth()->user()->name }}!</h1>
                <p>Saat ini ada <strong>{{ $todayVisits }} kunjungan</strong> yang tercatat hari ini.</p>
                <div class="hero-actions d-flex gap-2">
                    <a href="{{ route('visits.create') }}" class="btn btn-primary">Intake Baru</a>
                    <a href="{{ route('visits.index') }}" class="btn btn-outline-secondary">Lihat Kunjungan</a>
                </div>
            </div>
            <i class="bi bi-hospital hero-icon"></i>
        </div>

        <div class="panel-card">
            <div class="panel-head">
                <h3 class="panel-title text-uppercase" style="font-size:.92rem; letter-spacing:.08em;">Aksi Cepat</h3>
            </div>
            <div class="panel-body">
                <div class="quick-actions">
                    <a class="quick-action-item" href="{{ route('visits.create') }}"><i class="bi bi-asterisk text-danger"></i>Insiden</a>
                    <a class="quick-action-item" href="{{ route('visits.index') }}"><i class="bi bi-capsule text-info"></i>Log Obat</a>
                    <a class="quick-action-item" href="{{ route('reports.monthly') }}"><i class="bi bi-printer text-primary"></i>Laporan</a>
                    <a class="quick-action-item" href="{{ route('reports.monthly') }}"><i class="bi bi-clock-history text-warning"></i>Riwayat</a>
                </div>
            </div>
        </div>
    </div>

    <div class="middle-wrap">
        <div class="panel-card">
            <div class="panel-head">
                <h3 class="panel-title">Okupansi Ruang UKS</h3>
                <span class="badge rounded-pill text-bg-success">{{ $sickBayFilled }} / {{ $sickBayCapacity }} bed terisi</span>
            </div>
            <div class="panel-body">
                <div class="occupancy-grid">
                    @for($i = 1; $i <= $sickBayCapacity; $i++)
                        @php $hasPatient = !empty($bedPatients[$i - 1]); @endphp
                        <div class="bed-box {{ $hasPatient ? 'active' : '' }}">
                            <i class="bi bi-hospital"></i>
                            <small>BED {{ $i }}</small>
                            <div class="bed-name">{{ $hasPatient ? $bedPatients[$i - 1] : 'Kosong' }}</div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        <div class="panel-card">
            <div class="panel-head">
                <h3 class="panel-title">Pulse Kesehatan</h3>
            </div>
            <div class="panel-body health-pulse-wrap" style="--donut-gradient: {{ $donutGradient }};">
                <div class="donut">
                    <div class="donut-inner">
                        <div>
                            <div class="donut-score">{{ round((($counts['SMA'] + $counts['GURU']) / $totalCounts) * 100) }}%</div>
                            <small>Rata-rata</small>
                        </div>
                    </div>
                </div>
                <div>
                    @foreach($counts as $key => $val)
                        <div class="legend-row">
                            <div class="legend-left">
                                <span class="legend-dot" style="background: {{ $categoryColors[$key] }}"></span>
                                <span>{{ $categoryLabels[$key] }}</span>
                            </div>
                            <strong>{{ round(($val / $totalCounts) * 100) }}%</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="panel-card">
            <div class="panel-head">
                <h3 class="panel-title">Kalender Klinik</h3>
            </div>
            <div class="panel-body">
                <div class="clinic-list">
                    @forelse($clinicAgenda as $agenda)
                        @php
                            $dateObj = \Illuminate\Support\Carbon::parse($agenda['date']);
                        @endphp
                        <div class="clinic-item">
                            <div class="clinic-date">
                                <small>{{ strtoupper($dateObj->translatedFormat('M')) }}</small>
                                <strong>{{ $dateObj->format('d') }}</strong>
                            </div>
                            <div>
                                <p class="clinic-title">{{ $agenda['title'] }}</p>
                                <p class="clinic-sub">{{ $agenda['time'] }} - {{ $agenda['subtitle'] }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="clinic-sub mb-0">Belum ada agenda bulan ini.</p>
                    @endforelse
                </div>
                <button class="clinic-btn" type="button">Lihat Jadwal Lengkap</button>
            </div>
        </div>
    </div>

    <div class="panel-card">
        <div class="panel-head">
            <h3 class="panel-title">Intake Terbaru</h3>
            <a class="recent-head-link" href="{{ route('visits.index') }}">Lihat semua aktivitas</a>
        </div>
        <div class="table-responsive">
            <table class="table recent-table mb-0">
                <thead>
                    <tr>
                        <th>NAMA PASIEN</th>
                        <th>ID NUMBER</th>
                        <th>GEJALA</th>
                        <th>WAKTU MASUK</th>
                        <th>STATUS</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentVisits as $visit)
                        @php
                            $initials = strtoupper(substr($visit->patient_name, 0, 1));
                            $idNumber = $visit->student?->nis ?? $visit->employee?->nip ?? '-';
                        @endphp
                        <tr>
                            <td>
                                <span class="student-chip">{{ $initials }}</span>
                                <span class="fw-semibold">{{ $visit->patient_name }}</span>
                            </td>
                            <td>{{ $idNumber }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($visit->complaint, 36) }}</td>
                            <td class="fw-semibold">{{ $visit->visit_time }}</td>
                            <td>
                                @if($visit->is_acc_pulang)
                                    <span class="status-pill status-discharge">PULANG</span>
                                @else
                                    <span class="status-pill status-observe">OBSERVASI</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('visits.show', $visit) }}" class="text-decoration-none text-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Belum ada data intake.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
