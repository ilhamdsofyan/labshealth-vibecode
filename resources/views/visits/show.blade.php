@extends('layouts.app')

@section('title', 'Detail Kunjungan')

@section('content')
<div class="d-flex align-items-center mb-4">
    <a href="{{ route('visits.index') }}" class="btn btn-outline-secondary btn-sm me-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="fw-bold mb-0">Detail Kunjungan</h4>
        <p class="text-muted mb-0 small">{{ $visit->visit_date->format('d F Y') }} - {{ $visit->visit_time }}</p>
    </div>
    <div class="ms-auto d-flex gap-2">
        <a href="{{ route('visits.edit', $visit) }}" class="btn btn-outline-warning btn-sm">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm overflow-hidden">
    <div class="card-body p-0">
        <div class="row g-0">
            <div class="col-lg-4 border-end bg-light bg-opacity-50 p-4">
                <div class="text-center mb-4">
                    <div class="avatar-circle mx-auto mb-2 bg-primary text-white d-flex align-items-center justify-content-center" style="width:64px; height:64px; border-radius: 50%; font-size: 24px; font-weight: bold;">
                        {{ strtoupper(substr($visit->patient_name, 0, 1)) }}
                    </div>
                    <h5 class="fw-bold mb-0 text-dark">{{ $visit->patient_name }}</h5>
                    <span class="badge badge-category badge-{{ strtolower($visit->patient_category) }} mt-2">
                        {{ $visit->patient_category }}
                    </span>
                </div>

                <div class="small">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Kategori :</span>
                        <span class="fw-medium">{{ $visit->patient_category }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">ID/NIS/NIP :</span>
                        <span class="fw-medium">
                            @if($visit->student) {{ $visit->student->nis }}
                            @elseif($visit->employee) {{ $visit->employee->nip }}
                            @else - @endif
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Jenis Kelamin :</span>
                        <span class="fw-medium">{{ $visit->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Kelas/Unit :</span>
                        <span class="fw-medium">{{ $visit->class_at_visit ?? $visit->class_or_department ?? '-' }}</span>
                    </div>
                    @if($visit->additional_info)
                        <div class="mt-3 p-2 bg-white rounded border small">
                            <span class="text-muted d-block small fw-bold">INFO TAMBAHAN:</span>
                            {{ $visit->additional_info }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-lg-8 p-4">
                <div class="row g-4 mb-4">
                    <div class="col-6">
                        <label class="small text-muted d-block mb-1">DIAGNOSA PENYAKIT</label>
                        @php
                            $diseaseNames = $visit->diseases->pluck('name')->filter()->values();
                        @endphp
                        <h6 class="fw-bold text-primary">
                            <i class="bi bi-virus me-2"></i>{{ $diseaseNames->isNotEmpty() ? $diseaseNames->implode(', ') : 'Tidak ditentukan' }}
                        </h6>
                    </div>
                    <div class="col-6 text-end">
                        <label class="small text-muted d-block mb-1">STATUS</label>
                        @if($visit->is_acc_pulang)
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2">
                                <i class="bi bi-house-door-fill me-1"></i>Acc Pulang (Izin)
                            </span>
                        @elseif($visit->is_rest)
                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2">
                                <i class="bi bi-bed me-1"></i>Sedang Rest
                            </span>
                        @elseif($visit->rest_status === 'completed')
                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2">
                                <i class="bi bi-check2-circle me-1"></i>Selesai Rest
                            </span>
                        @else
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2">
                                <i class="bi bi-person-check-fill me-1"></i>Kunjungan Teratasi
                            </span>
                        @endif
                    </div>
                </div>

                @if($visit->is_acc_pulang)
                    <div class="alert alert-warning py-2 mb-4">
                        <small class="fw-bold d-block">ALASAN PULANG:</small>
                        <span class="small">{{ $visit->acc_pulang_reason }}</span>
                    </div>
                @endif

                @if($visit->is_rest || $visit->rest_status === 'completed')
                    <div class="alert alert-light border py-2 mb-4">
                        <small class="fw-bold d-block">RINGKASAN REST:</small>
                        <span class="small">
                            Mulai: {{ $visit->rest_started_at?->format('d/m/Y H:i') ?: '-' }}
                            |
                            Selesai: {{ $visit->rest_ended_at?->format('d/m/Y H:i') ?: ($visit->is_rest ? 'Masih rest' : '-') }}
                        </span>
                    </div>
                @endif

                <div class="mb-4">
                    <label class="small text-muted d-block mb-1">KELUHAN UTAMA</label>
                    <div class="p-3 bg-light rounded border-start border-primary border-4">
                        <p class="mb-0 fw-medium">{{ $visit->complaint }}</p>
                    </div>
                </div>

                @php
                    $hasStandardExam =
                        $visit->height_cm !== null ||
                        $visit->weight_kg !== null ||
                        $visit->blood_pressure !== null ||
                        $visit->heart_rate !== null ||
                        $visit->respiratory_rate !== null ||
                        $visit->temperature_c !== null;
                @endphp

                @if($hasStandardExam)
                    <div class="mb-4">
                        <label class="small text-muted d-block mb-2">PEMERIKSAAN STANDAR</label>
                        <div class="row g-3">
                            <div class="col-md-4 col-6">
                                <div class="p-3 bg-light rounded border h-100">
                                    <div class="small text-muted mb-1">Tinggi Badan</div>
                                    <div class="fw-bold">{{ $visit->height_cm !== null ? rtrim(rtrim(number_format((float) $visit->height_cm, 2, '.', ''), '0'), '.') . ' cm' : '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="p-3 bg-light rounded border h-100">
                                    <div class="small text-muted mb-1">Berat Badan</div>
                                    <div class="fw-bold">{{ $visit->weight_kg !== null ? rtrim(rtrim(number_format((float) $visit->weight_kg, 2, '.', ''), '0'), '.') . ' kg' : '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="p-3 bg-light rounded border h-100">
                                    <div class="small text-muted mb-1">Tekanan Darah</div>
                                    <div class="fw-bold">{{ $visit->blood_pressure ?: '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="p-3 bg-light rounded border h-100">
                                    <div class="small text-muted mb-1">Heart Rate</div>
                                    <div class="fw-bold">{{ $visit->heart_rate !== null ? $visit->heart_rate . ' bpm' : '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="p-3 bg-light rounded border h-100">
                                    <div class="small text-muted mb-1">RR</div>
                                    <div class="fw-bold">{{ $visit->respiratory_rate !== null ? $visit->respiratory_rate . ' x/menit' : '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="p-3 bg-light rounded border h-100">
                                    <div class="small text-muted mb-1">Temperature</div>
                                    <div class="fw-bold">{{ $visit->temperature_c !== null ? rtrim(rtrim(number_format((float) $visit->temperature_c, 2, '.', ''), '0'), '.') . ' °C' : '-' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="small text-muted mt-2">
                            Tanda vital: tekanan darah, heart rate, RR, dan temperature.
                        </div>
                    </div>
                @endif

                <div class="mb-4">
                    <label class="small text-muted d-block mb-1">TERAPI / TINDAKAN</label>
                    <p class="fw-medium">{{ $visit->therapy ?? '-' }}</p>
                </div>

                <div class="mb-4">
                    <label class="small text-muted d-block mb-1">OBAT</label>
                    @php
                        $medicationNames = $visit->medications->pluck('name')->filter()->values();
                    @endphp
                    <p class="fw-medium">{{ $medicationNames->isNotEmpty() ? $medicationNames->implode(', ') : '-' }}</p>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="small text-muted d-block mb-1">NAMA PETUGAS</label>
                        <span class="fw-bold small">{{ $visit->officer_name }}</span>
                    </div>
                    <div class="col-md-6 text-md-end mb-3">
                        <label class="small text-muted d-block mb-1">DICATAT OLEH</label>
                        <span class="small">{{ $visit->creator->name }} pada {{ $visit->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>

                @if($visit->notes)
                    <div class="mt-2 border-top pt-3">
                        <label class="small text-muted d-block mb-1">CATATAN TAMBAHAN</label>
                        <p class="small text-muted italic mb-0">"{{ $visit->notes }}"</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
