@extends('layouts.app')

@section('title', 'Data Pegawai')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Data Pegawai</h4>
        <p class="text-muted mb-0 small">Kelola data guru dan karyawan</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createEmployeeModal">
            <i class="bi bi-plus-lg me-1"></i>Tambah Pegawai
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@push('styles')
<style>
    .employee-card {
        border: 1px solid var(--border);
        border-radius: 14px;
        background: var(--bg-surface);
        transition: opacity .24s ease, transform .24s ease, box-shadow .24s ease;
    }
    .employee-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-card);
    }
    .employee-avatar {
        width: 56px;
        height: 56px;
        min-width: 56px;
        min-height: 56px;
        aspect-ratio: 1 / 1;
        flex-shrink: 0;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1rem;
        line-height: 1;
        color: #fff;
        background: linear-gradient(135deg, var(--primary), color-mix(in srgb, var(--primary) 70%, #000 30%));
    }
    .employee-photo {
        width: 56px;
        height: 56px;
        min-width: 56px;
        min-height: 56px;
        border-radius: 999px;
        object-fit: cover;
        flex-shrink: 0;
        border: 2px solid color-mix(in srgb, var(--primary) 35%, transparent);
    }
    .avatar-zoom-trigger {
        border: 0;
        padding: 0;
        background: transparent;
        cursor: zoom-in;
        border-radius: 999px;
        line-height: 0;
    }
    .avatar-preview.avatar-clickable {
        cursor: zoom-in;
    }
    .avatar-zoom-image {
        width: 100%;
        max-height: 80vh;
        object-fit: contain;
        border-radius: 12px;
    }
    .avatar-preview {
        width: 84px;
        height: 84px;
        border-radius: 999px;
        object-fit: cover;
        border: 2px dashed var(--border);
        display: none;
        margin-bottom: .5rem;
    }
    .avatar-preview.show {
        display: inline-block;
    }
    #employeeCropImage {
        max-width: 100%;
        max-height: 60vh;
    }
    .employee-meta {
        font-size: .8rem;
        color: var(--text-muted);
    }
    .employee-grid-enter {
        animation: employeeFadeIn .24s ease;
    }
    @keyframes employeeFadeIn {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .employee-detail-trigger {
        width: 100%;
        padding: 0;
        border: 0;
        background: transparent;
        text-align: left;
        color: inherit;
    }
    .employee-detail-modal .modal-dialog {
        max-width: 920px;
    }
    .employee-detail-modal .modal-content {
        border: 1px solid var(--border);
        border-radius: 18px;
        background: var(--bg-surface);
    }
    .employee-detail-section {
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 1rem;
        background: var(--bg-surface);
    }
    .employee-detail-table {
        width: 100%;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }
    .employee-detail-table th,
    .employee-detail-table td {
        padding: .7rem .85rem;
        vertical-align: top;
        border-bottom: 1px solid var(--border);
    }
    .employee-detail-table tr:last-child th,
    .employee-detail-table tr:last-child td {
        border-bottom: 0;
    }
    .employee-detail-table th {
        width: 34%;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--text-muted);
        background: color-mix(in srgb, var(--primary) 4%, #fff 96%);
    }
    .employee-detail-table td {
        color: var(--text-main);
    }
</style>
@endpush

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
@endpush

<div data-master-async-container data-async-anim="cards">
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.master.employees.index') }}" class="js-async-search js-auto-search">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Cari Pegawai</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="Nama atau NIP..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Filter Tipe</label>
                        <select name="role_type" class="form-select form-select-sm">
                            <option value="">Semua Tipe</option>
                            @foreach($roleSuggestions as $role)
                                <option value="{{ $role }}" {{ request('role_type') === $role ? 'selected' : '' }}>{{ $role }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('admin.master.employees.index') }}" class="btn btn-outline-secondary btn-sm js-async-refresh">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card" data-master-async-table>
        <div class="card-body">
            <div class="row g-3">
                @forelse($employees as $employee)
                    @php
                        $initial = strtoupper(substr($employee->name, 0, 1));
                    @endphp
                    <div class="col-md-6 col-xl-4 employee-grid-enter">
                        <div class="employee-card h-100 p-3">
                            <div class="d-flex align-items-start justify-content-between gap-2">
                                <div class="d-flex align-items-center gap-3">
                                    @if($employee->avatar_path)
                                        <button type="button" class="avatar-zoom-trigger" data-avatar-zoom data-zoom-src="{{ asset('storage/' . $employee->avatar_path) }}" data-zoom-title="{{ $employee->name }}">
                                            <img src="{{ asset('storage/' . $employee->avatar_path) }}" alt="{{ $employee->name }}" class="employee-photo">
                                        </button>
                                    @else
                                        <span class="employee-avatar">{{ $initial }}</span>
                                    @endif
                                    <button
                                        type="button"
                                        class="employee-detail-trigger"
                                        data-employee-detail
                                        data-detail-url="{{ route('admin.master.employees.show', $employee) }}"
                                    >
                                        <div class="fw-bold">{{ $employee->name }}</div>
                                        <div class="employee-meta">NIP: {{ $employee->nip }}</div>
                                        <div class="employee-meta">
                                            <span class="badge {{ $employee->role_type === 'GURU' ? 'bg-primary' : 'bg-info' }}">
                                                {{ $employee->role_type }}
                                            </span>
                                        </div>
                                        <div class="employee-meta mt-1">Unit: {{ $employee->department ?? '-' }}</div>
                                    </button>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <button
                                        type="button"
                                        class="btn btn-outline-warning btn-edit-employee"
                                        data-id="{{ $employee->id }}"
                                        data-nip="{{ $employee->nip }}"
                                        data-name="{{ $employee->name }}"
                                        data-role="{{ $employee->role_type }}"
                                        data-department="{{ $employee->department }}"
                                        data-detail-url="{{ route('admin.master.employees.show', $employee) }}"
                                        data-avatar="{{ $employee->avatar_path ? asset('storage/' . $employee->avatar_path) : '' }}"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('admin.master.employees.destroy', $employee) }}" method="POST" class="js-async-delete"
                                          data-loading-text="Menghapus..."
                                          data-confirm="Yakin hapus data pegawai ini?" data-success-message="Data pegawai berhasil dihapus.">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-5">
                        Belum ada data pegawai.
                    </div>
                @endforelse
            </div>
        </div>
        @if($employees->hasPages())
            <div class="card-footer bg-transparent">
                {{ $employees->links() }}
            </div>
        @endif
    </div>
</div>

<div class="modal fade" id="createEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.master.employees.store') }}" method="POST" enctype="multipart/form-data" class="js-async-master"
                  data-success-message="Data pegawai berhasil ditambahkan.">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pegawai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 text-center">
                        <img src="" alt="Preview Avatar" class="avatar-preview" id="create_employee_avatar_preview">
                        <input type="file" name="avatar" id="create_employee_avatar_input" class="form-control @error('avatar') is-invalid @enderror" accept="image/*">
                        <input type="hidden" name="avatar_cropped_data" id="create_employee_avatar_cropped_data" value="">
                        <small class="text-muted d-block mt-1">Upload foto pegawai, lalu crop persegi.</small>
                        @if($errors->any() && !old('edit_id'))
                            @error('avatar') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">NIP <span class="text-danger">*</span></label>
                        <input type="text" name="nip" class="form-control @error('nip') is-invalid @enderror"
                               value="{{ old('edit_id') ? '' : old('nip') }}" required>
                        @if($errors->any() && !old('edit_id'))
                            @error('nip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('edit_id') ? '' : old('name') }}" required>
                        @if($errors->any() && !old('edit_id'))
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tipe Pegawai <span class="text-danger">*</span></label>
                        <input type="text" name="role_type" class="form-control @error('role_type') is-invalid @enderror"
                               value="{{ old('edit_id') ? '' : old('role_type') }}" list="employeeRoleSuggestions" required>
                        @if($errors->any() && !old('edit_id'))
                            @error('role_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @endif
                    </div>
                    <div>
                        <label class="form-label small fw-semibold">Bagian / Unit</label>
                        <input type="text" name="department" class="form-control @error('department') is-invalid @enderror"
                               value="{{ old('edit_id') ? '' : old('department') }}" list="employeeUnitSuggestions">
                        @if($errors->any() && !old('edit_id'))
                            @error('department') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @endif
                    </div>
                    <hr>
                    <div class="small fw-bold text-primary mb-3">Medical Record</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Tinggi Badan (cm)</label>
                            <input type="number" name="height_cm" class="form-control" value="{{ old('edit_id') ? '' : old('height_cm') }}" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Berat Badan (kg)</label>
                            <input type="number" name="weight_kg" class="form-control" value="{{ old('edit_id') ? '' : old('weight_kg') }}" min="0" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Golongan Darah</label>
                            <input type="text" name="blood_type" class="form-control" value="{{ old('edit_id') ? '' : old('blood_type') }}" placeholder="A / B / AB / O">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Rhesus</label>
                            <input type="text" name="rhesus" class="form-control" value="{{ old('edit_id') ? '' : old('rhesus') }}" placeholder="+ / -">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Terakhir Checkup</label>
                            <input type="date" name="last_checkup_date" class="form-control" value="{{ old('edit_id') ? '' : old('last_checkup_date') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Alergi</label>
                            <textarea name="allergies" rows="2" class="form-control">{{ old('edit_id') ? '' : old('allergies') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Penyakit Kronis</label>
                            <textarea name="chronic_diseases" rows="2" class="form-control">{{ old('edit_id') ? '' : old('chronic_diseases') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Riwayat Operasi</label>
                            <textarea name="past_surgeries" rows="2" class="form-control">{{ old('edit_id') ? '' : old('past_surgeries') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Obat Rutin</label>
                            <textarea name="regular_medications" rows="2" class="form-control">{{ old('edit_id') ? '' : old('regular_medications') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Catatan Medis</label>
                            <textarea name="medical_notes" rows="2" class="form-control">{{ old('edit_id') ? '' : old('medical_notes') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editEmployeeForm" method="POST" enctype="multipart/form-data" class="js-async-master"
                  data-success-message="Data pegawai berhasil diperbarui.">
                @csrf
                @method('PUT')
                <input type="hidden" name="edit_id" id="edit_employee_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Pegawai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 text-center">
                        <img src="" alt="Preview Avatar" class="avatar-preview avatar-clickable" id="edit_employee_avatar_preview" data-avatar-zoom>
                        <input type="file" name="avatar" id="edit_employee_avatar_input" class="form-control @error('avatar') is-invalid @enderror" accept="image/*">
                        <input type="hidden" name="avatar_cropped_data" id="edit_employee_avatar_cropped_data" value="">
                        <button type="button" class="btn btn-sm btn-outline-danger mt-2 d-none" id="btn_remove_employee_avatar">
                            <i class="bi bi-trash me-1"></i>Hapus Foto
                        </button>
                        <small class="text-muted d-block mt-1">Upload foto baru jika ingin mengganti, lalu crop persegi.</small>
                        @if($errors->any() && old('edit_id'))
                            @error('avatar') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">NIP <span class="text-danger">*</span></label>
                        <input type="text" id="edit_employee_nip" name="nip" class="form-control @error('nip') is-invalid @enderror" required>
                        @if($errors->any() && old('edit_id'))
                            @error('nip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" id="edit_employee_name" name="name" class="form-control @error('name') is-invalid @enderror" required>
                        @if($errors->any() && old('edit_id'))
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tipe Pegawai <span class="text-danger">*</span></label>
                        <input type="text" id="edit_employee_role" name="role_type" class="form-control @error('role_type') is-invalid @enderror" list="employeeRoleSuggestions" required>
                        @if($errors->any() && old('edit_id'))
                            @error('role_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @endif
                    </div>
                    <div>
                        <label class="form-label small fw-semibold">Bagian / Unit</label>
                        <input type="text" id="edit_employee_department" name="department" class="form-control @error('department') is-invalid @enderror" list="employeeUnitSuggestions">
                        @if($errors->any() && old('edit_id'))
                            @error('department') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @endif
                    </div>
                    <hr>
                    <div class="small fw-bold text-primary mb-3">Medical Record</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Tinggi Badan (cm)</label>
                            <input type="number" id="edit_employee_height" name="height_cm" class="form-control" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Berat Badan (kg)</label>
                            <input type="number" id="edit_employee_weight" name="weight_kg" class="form-control" min="0" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Golongan Darah</label>
                            <input type="text" id="edit_employee_blood_type" name="blood_type" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Rhesus</label>
                            <input type="text" id="edit_employee_rhesus" name="rhesus" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Terakhir Checkup</label>
                            <input type="date" id="edit_employee_last_checkup" name="last_checkup_date" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Alergi</label>
                            <textarea id="edit_employee_allergies" name="allergies" rows="2" class="form-control"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Penyakit Kronis</label>
                            <textarea id="edit_employee_chronic" name="chronic_diseases" rows="2" class="form-control"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Riwayat Operasi</label>
                            <textarea id="edit_employee_surgeries" name="past_surgeries" rows="2" class="form-control"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Obat Rutin</label>
                            <textarea id="edit_employee_medications" name="regular_medications" rows="2" class="form-control"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Catatan Medis</label>
                            <textarea id="edit_employee_medical_notes" name="medical_notes" rows="2" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Perbarui</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="employeeCropModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crop Foto Pegawai</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="employeeCropImage" src="" alt="Crop Avatar">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="applyEmployeeCrop">Gunakan Foto</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="employeeAvatarZoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="employeeAvatarZoomTitle">Foto Pegawai</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img src="" alt="Zoom Avatar" id="employeeAvatarZoomImage" class="avatar-zoom-image">
            </div>
        </div>
    </div>
</div>

<div class="modal fade employee-detail-modal" id="employeeDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pegawai</h5>
                <a href="#" class="btn btn-outline-primary btn-sm me-2 d-none" id="employeeDetailHistoryBtn">
                    <i class="bi bi-clock-history me-1"></i>History
                </a>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="employeeDetailLoading" class="text-center text-muted py-5">Memuat detail pegawai...</div>
                <div id="employeeDetailError" class="alert alert-danger d-none mb-0">Gagal memuat detail pegawai.</div>
                <div id="employeeDetailContent" class="d-none">
                    <div class="d-flex flex-column flex-lg-row align-items-start gap-3 mb-4">
                        <div id="employeeDetailAvatarWrap"></div>
                        <div class="flex-grow-1">
                            <h4 class="fw-bold mb-1" id="employeeDetailName">-</h4>
                            <div class="text-muted small mb-2" id="employeeDetailSummary">-</div>
                            <div class="d-flex flex-wrap gap-2" id="employeeDetailFacts"></div>
                        </div>
                    </div>
                    <div class="employee-detail-section mb-3">
                        <h6 class="fw-bold mb-3">Identitas</h6>
                        <div id="employeeDetailIdentity"></div>
                    </div>
                    <div class="employee-detail-section">
                        <h6 class="fw-bold mb-3">Medical Record</h6>
                        <div id="employeeDetailMedical"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<datalist id="employeeRoleSuggestions">
    @foreach($roleSuggestions as $role)
        <option value="{{ $role }}"></option>
    @endforeach
</datalist>

<datalist id="employeeUnitSuggestions">
    @foreach($departmentSuggestions as $department)
        <option value="{{ $department }}"></option>
    @endforeach
</datalist>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editModalEl = document.getElementById('editEmployeeModal');
        const editModal = new bootstrap.Modal(editModalEl);
        const editForm = document.getElementById('editEmployeeForm');
        const cropModalEl = document.getElementById('employeeCropModal');
        const cropModal = new bootstrap.Modal(cropModalEl);
        const cropImage = document.getElementById('employeeCropImage');
        const applyCropBtn = document.getElementById('applyEmployeeCrop');
        const zoomModalEl = document.getElementById('employeeAvatarZoomModal');
        const zoomModal = new bootstrap.Modal(zoomModalEl);
        const zoomImage = document.getElementById('employeeAvatarZoomImage');
        const zoomTitle = document.getElementById('employeeAvatarZoomTitle');
        const detailModalEl = document.getElementById('employeeDetailModal');
        const detailModal = new bootstrap.Modal(detailModalEl);
        const detailLoading = document.getElementById('employeeDetailLoading');
        const detailError = document.getElementById('employeeDetailError');
        const detailContent = document.getElementById('employeeDetailContent');
        const detailHistoryBtn = document.getElementById('employeeDetailHistoryBtn');
        const detailEls = {
            avatarWrap: document.getElementById('employeeDetailAvatarWrap'),
            name: document.getElementById('employeeDetailName'),
            summary: document.getElementById('employeeDetailSummary'),
            facts: document.getElementById('employeeDetailFacts'),
            identity: document.getElementById('employeeDetailIdentity'),
            medical: document.getElementById('employeeDetailMedical'),
        };

        const createAvatarInput = document.getElementById('create_employee_avatar_input');
        const createAvatarPreview = document.getElementById('create_employee_avatar_preview');
        const createAvatarCroppedData = document.getElementById('create_employee_avatar_cropped_data');
        const editAvatarInput = document.getElementById('edit_employee_avatar_input');
        const editAvatarPreview = document.getElementById('edit_employee_avatar_preview');
        const editAvatarCroppedData = document.getElementById('edit_employee_avatar_cropped_data');
        const removeAvatarBtn = document.getElementById('btn_remove_employee_avatar');

        let cropper = null;
        let activeAvatarContext = null;

        const fields = {
            id: document.getElementById('edit_employee_id'),
            nip: document.getElementById('edit_employee_nip'),
            name: document.getElementById('edit_employee_name'),
            role: document.getElementById('edit_employee_role'),
            department: document.getElementById('edit_employee_department'),
            height: document.getElementById('edit_employee_height'),
            weight: document.getElementById('edit_employee_weight'),
            bloodType: document.getElementById('edit_employee_blood_type'),
            rhesus: document.getElementById('edit_employee_rhesus'),
            lastCheckup: document.getElementById('edit_employee_last_checkup'),
            allergies: document.getElementById('edit_employee_allergies'),
            chronic: document.getElementById('edit_employee_chronic'),
            surgeries: document.getElementById('edit_employee_surgeries'),
            medications: document.getElementById('edit_employee_medications'),
            medicalNotes: document.getElementById('edit_employee_medical_notes'),
        };

        function setPreview(previewEl, src) {
            if (!previewEl) return;
            if (src) {
                previewEl.src = src;
                previewEl.classList.add('show');
            } else {
                previewEl.src = '';
                previewEl.classList.remove('show');
            }
        }

        function openZoom(src, title) {
            if (!src) return;
            zoomImage.src = src;
            zoomTitle.textContent = title || 'Foto Pegawai';
            zoomModal.show();
        }

        function formatValue(value, fallback = '-') {
            if (value === null || value === undefined || value === '') return fallback;
            if (typeof value === 'boolean') return value ? 'Ya' : 'Tidak';
            return String(value);
        }

        function renderDetailGrid(container, items) {
            const rows = items.filter(item => item.value !== null && item.value !== undefined && item.value !== '');
            if (!rows.length) {
                container.innerHTML = '<div class="text-muted small">Belum ada data.</div>';
                return;
            }

            container.innerHTML = `
                <div class="table-responsive">
                    <table class="table employee-detail-table mb-0">
                        <tbody>
                            ${rows.map(item => `<tr><th>${item.label}</th><td>${formatValue(item.value)}</td></tr>`).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        }

        function resetDetailState() {
            detailLoading.classList.remove('d-none');
            detailError.classList.add('d-none');
            detailContent.classList.add('d-none');
            detailHistoryBtn.classList.add('d-none');
            detailHistoryBtn.href = '#';
        }

        function renderEmployeeDetail(data) {
            detailEls.name.textContent = formatValue(data.name);
            detailEls.summary.textContent = [formatValue(data.nip), formatValue(data.role_type), formatValue(data.department)].join(' | ');
            detailEls.facts.innerHTML = [
                data.role_type ? `<span class="badge rounded-pill text-bg-light border">${data.role_type}</span>` : '',
                data.department ? `<span class="badge rounded-pill text-bg-light border">${data.department}</span>` : '',
            ].filter(Boolean).join('');

            if (data.avatar_url) {
                detailEls.avatarWrap.innerHTML = `<button type="button" class="avatar-zoom-trigger" data-avatar-zoom data-zoom-src="${data.avatar_url}" data-zoom-title="${formatValue(data.name, 'Foto Pegawai')}"><img src="${data.avatar_url}" alt="${formatValue(data.name, 'Pegawai')}" class="employee-photo" style="width:88px;height:88px;"></button>`;
            } else {
                detailEls.avatarWrap.innerHTML = `<span class="employee-avatar" style="width:88px;height:88px;font-size:2rem;">${formatValue(data.name, '?').charAt(0).toUpperCase()}</span>`;
            }

            renderDetailGrid(detailEls.identity, [
                { label: 'NIP', value: data.nip },
                { label: 'Nama', value: data.name },
                { label: 'Tipe Pegawai', value: data.role_type },
                { label: 'Bagian/Unit', value: data.department },
            ]);

            renderDetailGrid(detailEls.medical, [
                { label: 'Tinggi Badan', value: data.medical_record?.height_cm ? data.medical_record.height_cm + ' cm' : '' },
                { label: 'Berat Badan', value: data.medical_record?.weight_kg ? data.medical_record.weight_kg + ' kg' : '' },
                { label: 'Golongan Darah', value: data.medical_record?.blood_type },
                { label: 'Rhesus', value: data.medical_record?.rhesus },
                { label: 'Alergi', value: data.medical_record?.allergies },
                { label: 'Penyakit Kronis', value: data.medical_record?.chronic_diseases },
                { label: 'Riwayat Operasi', value: data.medical_record?.past_surgeries },
                { label: 'Obat Rutin', value: data.medical_record?.regular_medications },
                { label: 'Terakhir Checkup', value: data.medical_record?.last_checkup_date },
                { label: 'Catatan Medis', value: data.medical_record?.medical_notes },
            ]);

            detailHistoryBtn.classList.remove('d-none');
            detailHistoryBtn.href = "{{ url('riwayat-kunjungan/pegawai') }}/" + data.id;
            detailLoading.classList.add('d-none');
            detailError.classList.add('d-none');
            detailContent.classList.remove('d-none');
        }

        async function openEmployeeDetail(url) {
            if (!url) return;
            resetDetailState();
            detailModal.show();

            try {
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                let body = {};
                try { body = await response.json(); } catch (_) {}

                if (!response.ok) {
                    throw new Error(body.message || 'Gagal memuat detail pegawai.');
                }

                renderEmployeeDetail(body);
            } catch (error) {
                detailLoading.classList.add('d-none');
                detailContent.classList.add('d-none');
                detailError.classList.remove('d-none');
                detailError.textContent = error.message || 'Gagal memuat detail pegawai.';
            }
        }

        function openCropper(file, context) {
            if (!file || !context) return;
            activeAvatarContext = context;

            const reader = new FileReader();
            reader.onload = function (evt) {
                cropImage.src = evt.target.result;
                cropModal.show();
            };
            reader.readAsDataURL(file);
        }

        cropModalEl.addEventListener('shown.bs.modal', function () {
            if (cropper) cropper.destroy();
            cropper = new Cropper(cropImage, {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 1,
                responsive: true,
                background: false,
            });
        });

        cropModalEl.addEventListener('hidden.bs.modal', function () {
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
        });

        applyCropBtn.addEventListener('click', function () {
            if (!cropper || !activeAvatarContext) return;

            const canvas = cropper.getCroppedCanvas({
                width: 512,
                height: 512,
                imageSmoothingQuality: 'high',
            });
            const dataUrl = canvas.toDataURL('image/jpeg', 0.92);

            activeAvatarContext.hiddenInput.value = dataUrl;
            activeAvatarContext.fileInput.value = '';
            setPreview(activeAvatarContext.preview, dataUrl);
            cropModal.hide();
        });

        createAvatarInput?.addEventListener('change', function (e) {
            const file = e.target.files && e.target.files[0];
            if (!file) return;
            createAvatarCroppedData.value = '';
            openCropper(file, {
                fileInput: createAvatarInput,
                preview: createAvatarPreview,
                hiddenInput: createAvatarCroppedData,
            });
        });

        editAvatarInput?.addEventListener('change', function (e) {
            const file = e.target.files && e.target.files[0];
            if (!file) return;
            editAvatarCroppedData.value = '';
            openCropper(file, {
                fileInput: editAvatarInput,
                preview: editAvatarPreview,
                hiddenInput: editAvatarCroppedData,
            });
        });

        document.addEventListener('click', function (e) {
            const zoomTarget = e.target.closest('[data-avatar-zoom]');
            if (zoomTarget) {
                const src = zoomTarget.dataset.zoomSrc || zoomTarget.getAttribute('src');
                const title = zoomTarget.dataset.zoomTitle || fields.name.value || 'Foto Pegawai';
                openZoom(src, title);
                return;
            }

            const detailTrigger = e.target.closest('[data-employee-detail]');
            if (detailTrigger) {
                openEmployeeDetail(detailTrigger.dataset.detailUrl);
                return;
            }

            const btn = e.target.closest('.btn-edit-employee');
            if (!btn) return;

            const id = btn.dataset.id;
            editForm.action = "{{ url('admin/master/employees') }}/" + id;
            fields.id.value = id;
            fields.nip.value = btn.dataset.nip || '';
            fields.name.value = btn.dataset.name || '';
            fields.role.value = btn.dataset.role || '';
            fields.department.value = btn.dataset.department || '';
            fields.height.value = '';
            fields.weight.value = '';
            fields.bloodType.value = '';
            fields.rhesus.value = '';
            fields.lastCheckup.value = '';
            fields.allergies.value = '';
            fields.chronic.value = '';
            fields.surgeries.value = '';
            fields.medications.value = '';
            fields.medicalNotes.value = '';
            editAvatarCroppedData.value = '';
            editAvatarInput.value = '';
            const currentAvatar = btn.dataset.avatar || '';
            setPreview(editAvatarPreview, currentAvatar);

            if (removeAvatarBtn) {
                removeAvatarBtn.dataset.url = "{{ url('admin/master/employees') }}/" + id + "/avatar";
                removeAvatarBtn.classList.toggle('d-none', !currentAvatar);
            }

            fetch(btn.dataset.detailUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then(response => response.ok ? response.json() : null)
                .then(data => {
                    if (data?.medical_record) {
                        fields.height.value = data.medical_record.height_cm || '';
                        fields.weight.value = data.medical_record.weight_kg || '';
                        fields.bloodType.value = data.medical_record.blood_type || '';
                        fields.rhesus.value = data.medical_record.rhesus || '';
                        fields.lastCheckup.value = data.medical_record.last_checkup_date || '';
                        fields.allergies.value = data.medical_record.allergies || '';
                        fields.chronic.value = data.medical_record.chronic_diseases || '';
                        fields.surgeries.value = data.medical_record.past_surgeries || '';
                        fields.medications.value = data.medical_record.regular_medications || '';
                        fields.medicalNotes.value = data.medical_record.medical_notes || '';
                    }
                })
                .finally(() => editModal.show());
        });

        removeAvatarBtn?.addEventListener('click', async function () {
            const endpoint = removeAvatarBtn.dataset.url;
            if (!endpoint) return;
            if (!window.confirm('Yakin hapus foto pegawai ini?')) return;

            const originalHtml = removeAvatarBtn.innerHTML;
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            removeAvatarBtn.disabled = true;
            removeAvatarBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Menghapus...';

            try {
                const response = await fetch(endpoint, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token || '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                let body = {};
                try { body = await response.json(); } catch (_) {}

                if (!response.ok) {
                    throw new Error(body.message || 'Gagal menghapus foto pegawai.');
                }

                editAvatarInput.value = '';
                editAvatarCroppedData.value = '';
                setPreview(editAvatarPreview, '');
                removeAvatarBtn.classList.add('d-none');

                if (typeof showAsyncAlert === 'function') {
                    showAsyncAlert('success', body.message || 'Foto pegawai berhasil dihapus.');
                }

                if (typeof refreshMasterAsyncContainer === 'function') {
                    await refreshMasterAsyncContainer(window.location.href, false);
                }
            } catch (err) {
                if (typeof showAsyncAlert === 'function') {
                    showAsyncAlert('danger', err.message || 'Terjadi kesalahan jaringan.');
                }
            } finally {
                removeAvatarBtn.disabled = false;
                removeAvatarBtn.innerHTML = originalHtml;
            }
        });

        @if($errors->any() && old('edit_id'))
            editForm.action = "{{ url('admin/master/employees') }}/{{ old('edit_id') }}";
            fields.id.value = "{{ old('edit_id') }}";
            fields.nip.value = @json(old('nip'));
            fields.name.value = @json(old('name'));
            fields.role.value = @json(old('role_type'));
            fields.department.value = @json(old('department'));
            fields.height.value = @json(old('height_cm'));
            fields.weight.value = @json(old('weight_kg'));
            fields.bloodType.value = @json(old('blood_type'));
            fields.rhesus.value = @json(old('rhesus'));
            fields.lastCheckup.value = @json(old('last_checkup_date'));
            fields.allergies.value = @json(old('allergies'));
            fields.chronic.value = @json(old('chronic_diseases'));
            fields.surgeries.value = @json(old('past_surgeries'));
            fields.medications.value = @json(old('regular_medications'));
            fields.medicalNotes.value = @json(old('medical_notes'));
            setPreview(editAvatarPreview, @json(old('avatar_cropped_data')));
            if (removeAvatarBtn) {
                removeAvatarBtn.dataset.url = "{{ url('admin/master/employees') }}/{{ old('edit_id') }}/avatar";
                removeAvatarBtn.classList.toggle('d-none', !@json((bool) old('avatar_cropped_data')));
            }
            editModal.show();
        @elseif($errors->any())
            setPreview(createAvatarPreview, @json(old('avatar_cropped_data')));
            new bootstrap.Modal(document.getElementById('createEmployeeModal')).show();
        @endif
    });
</script>
@endpush
@endsection
