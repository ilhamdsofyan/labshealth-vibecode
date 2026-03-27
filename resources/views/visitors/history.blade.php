@extends('layouts.app')

@push('styles')
<style>
    .visitor-profile-card {
        border: 1px solid var(--border);
        border-radius: 20px;
        background:
            radial-gradient(circle at top right, color-mix(in srgb, var(--primary) 12%, transparent), transparent 40%),
            var(--bg-surface);
    }
    .visitor-avatar {
        width: 88px;
        height: 88px;
        border-radius: 999px;
        object-fit: cover;
        border: 3px solid color-mix(in srgb, var(--primary) 25%, transparent);
    }
    .visitor-avatar-fallback {
        width: 88px;
        height: 88px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 2rem;
        color: #fff;
        background: linear-gradient(135deg, var(--primary), color-mix(in srgb, var(--primary) 70%, #000 30%));
    }
    .visitor-info-table th {
        width: 36%;
        text-transform: uppercase;
        font-size: .75rem;
        color: var(--text-muted);
        letter-spacing: .04em;
    }
    .visitor-section {
        border: 1px solid var(--border);
        border-radius: 16px;
        background: var(--bg-surface);
    }
    .visitor-info-table th,
    .visitor-info-table td {
        padding: .65rem .8rem;
        border-bottom: 1px solid var(--border);
        vertical-align: top;
    }
    .visitor-info-table tr:last-child th,
    .visitor-info-table tr:last-child td {
        border-bottom: 0;
    }
    .visitor-accordion .accordion-item {
        border: 1px solid var(--border);
        border-radius: 16px;
        overflow: hidden;
        background: var(--bg-surface);
    }
    .visitor-accordion .accordion-button {
        font-weight: 700;
        background: var(--bg-surface);
        color: var(--text-main);
        box-shadow: none;
    }
    .visitor-accordion .accordion-button:not(.collapsed) {
        background: color-mix(in srgb, var(--bg-surface) 88%, var(--primary) 12%);
        color: var(--text-main);
    }
    .visitor-accordion .accordion-button:focus {
        box-shadow: none;
    }
    .exam-metric-card {
        border: 1px solid var(--border);
        border-radius: 14px;
        background: var(--bg-surface-soft);
        padding: .9rem;
        height: 100%;
    }
    .exam-metric-label {
        font-size: .75rem;
        color: var(--text-muted);
        margin-bottom: .25rem;
    }
    .exam-metric-value {
        font-weight: 700;
        color: var(--text-main);
    }
</style>
@endpush

@section('title', 'Riwayat Kunjungan')

@section('content')
<div class="d-flex align-items-center justify-content-between gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Riwayat Kunjungan</h4>
        <p class="text-muted mb-0 small">Fokus ke historical kunjungan, detail pengunjung bisa dibuka saat diperlukan.</p>
    </div>
    <a href="{{ route('visits.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Kunjungan
    </a>
</div>

<div class="card visitor-profile-card mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-column flex-lg-row align-items-start gap-4">
            <div>
                @if(!empty($profile['avatar_url']))
                    <img src="{{ $profile['avatar_url'] }}" alt="{{ $profile['title'] }}" class="visitor-avatar">
                @else
                    <span class="visitor-avatar-fallback">{{ strtoupper(substr($profile['title'], 0, 1)) }}</span>
                @endif
            </div>
            <div class="flex-grow-1">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <h3 class="fw-bold mb-0">{{ $profile['title'] }}</h3>
                    <span class="badge text-bg-light border">{{ strtoupper($profile['type']) }}</span>
                </div>
                <p class="text-muted small mb-3">{{ $profile['subtitle'] ?: '-' }}</p>

                @if(!empty($profile['quick_facts']))
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @foreach($profile['quick_facts'] as $fact)
                            <span class="badge rounded-pill text-bg-light border">{{ $fact }}</span>
                        @endforeach
                    </div>
                @endif

                <div class="accordion visitor-accordion" id="visitorProfileAccordion">
                    @foreach($profile['sections'] as $index => $items)
                        @php
                            $sectionTitle = is_string($index) ? $index : 'Section ' . ($loop->iteration);
                            $filteredItems = collect($items)->filter(function ($value) {
                                return $value !== null && $value !== '';
                            });
                            $collapseId = 'visitorSection' . $loop->iteration;
                            $headingId = 'visitorSectionHeading' . $loop->iteration;
                        @endphp
                        <div class="accordion-item mb-3">
                            <h2 class="accordion-header" id="{{ $headingId }}">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="false" aria-controls="{{ $collapseId }}">
                                    {{ $sectionTitle }}
                                </button>
                            </h2>
                            <div id="{{ $collapseId }}" class="accordion-collapse collapse" aria-labelledby="{{ $headingId }}" data-bs-parent="#visitorProfileAccordion">
                                <div class="accordion-body p-0">
                                    <div class="table-responsive">
                                        <table class="table visitor-info-table mb-0">
                                            <tbody>
                                                @forelse($filteredItems as $label => $value)
                                                    <tr>
                                                        <th>{{ $label }}</th>
                                                        <td>{{ $value }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="2" class="text-muted small p-3">Belum ada data.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-clock-history me-2"></i>Historical Kunjungan</span>
        <span class="small text-muted">{{ $visits->total() }} kunjungan</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Keluhan</th>
                        <th>Penyakit</th>
                        <th>Status</th>
                        <th>Petugas</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($visits as $visit)
                        @php
                            $diseaseNames = $visit->diseases->pluck('name')->filter()->implode(', ');
                            $statusLabel = 'Aktif';
                            $statusClass = 'success';
                            $hasStandardExam =
                                $visit->height_cm !== null ||
                                $visit->weight_kg !== null ||
                                $visit->blood_pressure !== null ||
                                $visit->heart_rate !== null ||
                                $visit->respiratory_rate !== null ||
                                $visit->temperature_c !== null;

                            if ($visit->is_acc_pulang) {
                                $statusLabel = 'Acc Pulang';
                                $statusClass = 'danger';
                            } elseif ($visit->is_rest) {
                                $statusLabel = 'Rest';
                                $statusClass = 'warning';
                            }
                        @endphp
                        <tr>
                            <td class="small text-nowrap">
                                <div class="fw-semibold">{{ $visit->visit_date?->format('d/m/Y') }}</div>
                                <div class="text-muted">{{ $visit->visit_time }}</div>
                            </td>
                            <td class="small">{{ \Illuminate\Support\Str::limit($visit->complaint, 90) }}</td>
                            <td class="small">{{ $diseaseNames ?: '-' }}</td>
                            <td class="small">
                                <span class="badge bg-{{ $statusClass }}-subtle text-{{ $statusClass }} border">{{ $statusLabel }}</span>
                            </td>
                            <td class="small">{{ $visit->officer_name ?: ($visit->creator?->name ?? '-') }}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button
                                        type="button"
                                        class="btn btn-outline-info btn-sm js-standard-exam-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#visitStandardExamModal"
                                        data-visit-date="{{ $visit->visit_date?->format('d/m/Y') }}"
                                        data-visit-time="{{ $visit->visit_time }}"
                                        data-patient-name="{{ $visit->patient_name }}"
                                        data-height-cm="{{ $visit->height_cm !== null ? rtrim(rtrim(number_format((float) $visit->height_cm, 2, '.', ''), '0'), '.') . ' cm' : '-' }}"
                                        data-weight-kg="{{ $visit->weight_kg !== null ? rtrim(rtrim(number_format((float) $visit->weight_kg, 2, '.', ''), '0'), '.') . ' kg' : '-' }}"
                                        data-blood-pressure="{{ $visit->blood_pressure ?: '-' }}"
                                        data-heart-rate="{{ $visit->heart_rate !== null ? $visit->heart_rate . ' bpm' : '-' }}"
                                        data-respiratory-rate="{{ $visit->respiratory_rate !== null ? $visit->respiratory_rate . ' x/menit' : '-' }}"
                                        data-temperature="{{ $visit->temperature_c !== null ? rtrim(rtrim(number_format((float) $visit->temperature_c, 2, '.', ''), '0'), '.') . ' °C' : '-' }}"
                                        {{ $hasStandardExam ? '' : 'disabled' }}
                                        title="{{ $hasStandardExam ? 'Lihat pemeriksaan standar' : 'Belum ada data pemeriksaan standar' }}"
                                    >
                                        <i class="bi bi-heart-pulse"></i>
                                    </button>
                                    <a href="{{ route('visits.show', $visit) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">Belum ada histori kunjungan.</td>
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

<div class="modal fade" id="visitStandardExamModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold mb-1">Pemeriksaan Standar</h5>
                    <p class="text-muted small mb-0" id="visitStandardExamMeta">-</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="small text-muted">Pengunjung</div>
                    <div class="fw-semibold" id="visitStandardExamPatient">-</div>
                </div>
                <div class="row g-3">
                    <div class="col-md-4 col-6">
                        <div class="exam-metric-card">
                            <div class="exam-metric-label">Tinggi Badan</div>
                            <div class="exam-metric-value" id="examHeight">-</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="exam-metric-card">
                            <div class="exam-metric-label">Berat Badan</div>
                            <div class="exam-metric-value" id="examWeight">-</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="exam-metric-card">
                            <div class="exam-metric-label">Tekanan Darah</div>
                            <div class="exam-metric-value" id="examBloodPressure">-</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="exam-metric-card">
                            <div class="exam-metric-label">Heart Rate</div>
                            <div class="exam-metric-value" id="examHeartRate">-</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="exam-metric-card">
                            <div class="exam-metric-label">RR</div>
                            <div class="exam-metric-value" id="examRespiratoryRate">-</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="exam-metric-card">
                            <div class="exam-metric-label">Temperature</div>
                            <div class="exam-metric-value" id="examTemperature">-</div>
                        </div>
                    </div>
                </div>
                <div class="small text-muted mt-3">Tanda vital: tekanan darah, heart rate, RR, dan temperature.</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const examModal = document.getElementById('visitStandardExamModal');
        if (!examModal) return;

        document.querySelectorAll('.js-standard-exam-btn').forEach((button) => {
            button.addEventListener('click', function () {
                if (this.disabled) return;

                examModal.querySelector('#visitStandardExamMeta').textContent = `${this.dataset.visitDate || '-'} | ${this.dataset.visitTime || '-'}`;
                examModal.querySelector('#visitStandardExamPatient').textContent = this.dataset.patientName || '-';
                examModal.querySelector('#examHeight').textContent = this.dataset.heightCm || '-';
                examModal.querySelector('#examWeight').textContent = this.dataset.weightKg || '-';
                examModal.querySelector('#examBloodPressure').textContent = this.dataset.bloodPressure || '-';
                examModal.querySelector('#examHeartRate').textContent = this.dataset.heartRate || '-';
                examModal.querySelector('#examRespiratoryRate').textContent = this.dataset.respiratoryRate || '-';
                examModal.querySelector('#examTemperature').textContent = this.dataset.temperature || '-';
            });
        });
    });
</script>
@endpush
