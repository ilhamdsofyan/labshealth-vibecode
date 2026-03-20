@extends('layouts.app')

@section('title', 'Data Siswa')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Data Siswa</h4>
        <p class="text-muted mb-0 small">Kelola data siswa (SMA)</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createStudentModal">
            <i class="bi bi-plus-lg me-1"></i>Tambah Siswa
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
    .student-card {
        border: 1px solid var(--border);
        border-radius: 14px;
        background: var(--bg-surface);
        transition: opacity .24s ease, transform .24s ease, box-shadow .24s ease;
    }
    .student-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-card);
    }
    .student-detail-trigger {
        width: 100%;
        padding: 0;
        border: 0;
        background: transparent;
        text-align: left;
        color: inherit;
    }
    .student-avatar {
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
    .student-photo {
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
    #studentCropImage {
        max-width: 100%;
        max-height: 60vh;
    }
    .student-meta {
        font-size: .8rem;
        color: var(--text-muted);
    }
    .student-grid-enter {
        animation: studentFadeIn .24s ease;
    }
    @keyframes studentFadeIn {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .student-name-link {
        transition: color .2s ease;
    }
    .student-detail-trigger:hover .student-name-link {
        color: var(--primary);
    }
    .student-detail-modal .modal-dialog {
        max-width: 1080px;
    }
    .student-detail-modal .modal-content {
        border: 1px solid var(--border);
        border-radius: 18px;
        background: var(--bg-surface);
        color: var(--text-main);
    }
    .student-detail-modal .modal-header,
    .student-detail-modal .modal-body {
        background: var(--bg-surface);
        color: var(--text-main);
    }
    .student-detail-hero {
        border: 1px solid color-mix(in srgb, var(--primary) 18%, var(--border));
        border-radius: 18px;
        padding: 1rem;
        background:
            radial-gradient(circle at top right, color-mix(in srgb, var(--primary) 16%, transparent), transparent 38%),
            linear-gradient(180deg, color-mix(in srgb, var(--primary) 6%, #fff 94%), #fff);
    }
    .student-detail-avatar {
        width: 88px;
        height: 88px;
        border-radius: 999px;
        object-fit: cover;
        border: 3px solid color-mix(in srgb, var(--primary) 20%, transparent);
    }
    .student-detail-initial {
        width: 88px;
        height: 88px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 800;
        font-size: 2rem;
        background: linear-gradient(135deg, var(--primary), color-mix(in srgb, var(--primary) 70%, #000 30%));
    }
    .student-pill-nav .nav-link {
        border-radius: 999px;
        border: 1px solid var(--border);
        color: var(--text-muted);
        font-weight: 600;
    }
    .student-pill-nav .nav-link.active {
        background: var(--primary);
        border-color: var(--primary);
        color: #fff;
        box-shadow: 0 10px 24px color-mix(in srgb, var(--primary) 26%, transparent);
    }
    .student-detail-section {
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 1rem;
        background: var(--bg-surface);
    }
    .student-detail-table {
        width: 100%;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }
    .student-detail-table th,
    .student-detail-table td {
        padding: .7rem .85rem;
        vertical-align: top;
        border-bottom: 1px solid var(--border);
    }
    .student-detail-table tr:last-child th,
    .student-detail-table tr:last-child td {
        border-bottom: 0;
    }
    .student-detail-table th {
        width: 34%;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--text-muted);
        background: color-mix(in srgb, var(--primary) 4%, #fff 96%);
    }
    .student-detail-table td {
        font-size: .95rem;
        font-weight: 500;
        color: var(--text-main);
        word-break: break-word;
    }
    .student-family-row {
        border: 1px solid var(--border);
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
    }
    .student-family-badges {
        display: flex;
        flex-wrap: wrap;
        gap: .35rem;
    }
    .student-empty-state {
        border: 1px dashed var(--border);
        border-radius: 14px;
        padding: 1rem;
        color: var(--text-muted);
        text-align: center;
    }
    html[data-theme='dark'] .student-detail-modal .modal-content {
        background: var(--bg-surface);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.45);
    }
    html[data-theme='dark'] .student-detail-modal .btn-close {
        filter: invert(1) grayscale(1) brightness(1.4);
    }
    html[data-theme='dark'] .student-detail-hero {
        border-color: color-mix(in srgb, var(--primary) 24%, var(--border));
        background:
            radial-gradient(circle at top right, color-mix(in srgb, var(--primary) 22%, transparent), transparent 42%),
            linear-gradient(180deg, color-mix(in srgb, var(--bg-surface-soft) 92%, var(--primary) 8%), color-mix(in srgb, var(--bg-surface) 94%, #000 6%));
    }
    html[data-theme='dark'] .student-pill-nav .nav-link {
        background: color-mix(in srgb, var(--bg-surface-soft) 88%, #000 12%);
        border-color: color-mix(in srgb, var(--border) 80%, var(--primary) 20%);
        color: var(--text-muted);
    }
    html[data-theme='dark'] .student-pill-nav .nav-link:hover {
        color: var(--text-main);
        border-color: color-mix(in srgb, var(--primary) 45%, var(--border));
    }
    html[data-theme='dark'] .student-pill-nav .nav-link.active {
        color: #f8fbff;
        box-shadow: 0 12px 24px color-mix(in srgb, var(--primary) 30%, transparent);
    }
    html[data-theme='dark'] .student-detail-section,
    html[data-theme='dark'] .student-family-row {
        background: color-mix(in srgb, var(--bg-surface) 94%, #000 6%);
        border-color: var(--border);
    }
    html[data-theme='dark'] .student-detail-table th {
        background: color-mix(in srgb, var(--bg-surface-soft) 92%, var(--primary) 8%);
        color: #c7d4ec;
        border-bottom-color: var(--border);
    }
    html[data-theme='dark'] .student-detail-table td {
        background: color-mix(in srgb, var(--bg-surface) 96%, #000 4%);
        color: #edf2ff;
        border-bottom-color: var(--border);
    }
    html[data-theme='dark'] #studentDetailQuickFacts .badge {
        background: color-mix(in srgb, var(--bg-surface-soft) 90%, var(--primary) 10%) !important;
        color: var(--text-main) !important;
        border-color: color-mix(in srgb, var(--primary) 30%, var(--border)) !important;
    }
    html[data-theme='dark'] .student-family-row .border-bottom {
        border-bottom-color: var(--border) !important;
    }
    html[data-theme='dark'] .student-empty-state {
        background: color-mix(in srgb, var(--bg-surface-soft) 80%, transparent);
        color: var(--text-muted);
    }
</style>
@endpush

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
@endpush

<div data-master-async-container data-async-anim="cards">
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.master.students.index') }}" class="js-async-search js-auto-search">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Cari Siswa</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="Nama atau NIS..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Filter Kelas</label>
                        <select name="class_name" class="form-select form-select-sm">
                            <option value="">Semua Kelas</option>
                            @foreach($classSuggestions as $className)
                                <option value="{{ $className }}" {{ request('class_name') === $className ? 'selected' : '' }}>{{ $className }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('admin.master.students.index') }}" class="btn btn-outline-secondary btn-sm js-async-refresh">
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
                @forelse($students as $student)
                    @php
                        $initial = strtoupper(substr($student->name, 0, 1));
                    @endphp
                    <div class="col-md-6 col-xl-4 student-grid-enter">
                        <div class="student-card h-100 p-3">
                            <div class="d-flex align-items-start justify-content-between gap-2">
                                <div class="d-flex align-items-center gap-3">
                                    @if($student->avatar_path)
                                        <button type="button" class="avatar-zoom-trigger" data-avatar-zoom data-zoom-src="{{ asset('storage/' . $student->avatar_path) }}" data-zoom-title="{{ $student->name }}">
                                            <img src="{{ asset('storage/' . $student->avatar_path) }}" alt="{{ $student->name }}" class="student-photo">
                                        </button>
                                    @else
                                        <span class="student-avatar">{{ $initial }}</span>
                                    @endif
                                    <button
                                        type="button"
                                        class="student-detail-trigger"
                                        data-student-detail
                                        data-detail-url="{{ route('admin.master.students.show', $student) }}"
                                    >
                                        <div class="fw-bold student-name-link">{{ $student->name }}</div>
                                        <div class="student-meta">NIS: {{ $student->nis }}</div>
                                        <div class="student-meta">
                                            {{ $student->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}
                                            · {{ $student->activeClass?->class_name ?? '-' }}
                                        </div>
                                        <div class="student-meta">TA: {{ $student->activeClass?->academic_year ?? '-' }}</div>
                                    </button>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <button
                                        type="button"
                                        class="btn btn-outline-warning btn-edit-student"
                                        data-id="{{ $student->id }}"
                                        data-nis="{{ $student->nis }}"
                                        data-name="{{ $student->name }}"
                                        data-gender="{{ $student->gender }}"
                                        data-class="{{ $student->activeClass?->class_name }}"
                                        data-year="{{ $student->activeClass?->academic_year }}"
                                        data-avatar="{{ $student->avatar_path ? asset('storage/' . $student->avatar_path) : '' }}"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('admin.master.students.destroy', $student) }}" method="POST" class="js-async-delete"
                                          data-loading-text="Menghapus..."
                                          data-confirm="Yakin hapus data siswa ini?" data-success-message="Data siswa berhasil dihapus.">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-5">
                        Belum ada data siswa.
                    </div>
                @endforelse
            </div>
        </div>
        @if($students->hasPages())
            <div class="card-footer bg-transparent">
                {{ $students->links() }}
            </div>
        @endif
    </div>
</div>

<div class="modal fade" id="createStudentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.master.students.store') }}" method="POST" enctype="multipart/form-data" class="js-async-master"
                  data-success-message="Data siswa berhasil ditambahkan.">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 text-center">
                        <img src="" alt="Preview Avatar" class="avatar-preview" id="create_avatar_preview">
                        <input type="file" name="avatar" id="create_avatar_input" class="form-control @error('avatar') is-invalid @enderror" accept="image/*">
                        <input type="hidden" name="avatar_cropped_data" id="create_avatar_cropped_data" value="">
                        <small class="text-muted d-block mt-1">Upload foto siswa, lalu crop persegi.</small>
                        @if($errors->any() && !old('edit_id'))
                            @error('avatar') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">NIS <span class="text-danger">*</span></label>
                        <input type="text" name="nis" class="form-control @error('nis') is-invalid @enderror"
                               value="{{ old('edit_id') ? '' : old('nis') }}" required>
                        @if($errors->any() && !old('edit_id'))
                            @error('nis') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                        <label class="form-label small fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
                        <select name="gender" class="form-select @error('gender') is-invalid @enderror" required>
                            <option value="">Pilih</option>
                            <option value="L" {{ (!old('edit_id') && old('gender') === 'L') ? 'selected' : '' }}>L</option>
                            <option value="P" {{ (!old('edit_id') && old('gender') === 'P') ? 'selected' : '' }}>P</option>
                        </select>
                        @if($errors->any() && !old('edit_id'))
                            @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kelas Aktif <span class="text-danger">*</span></label>
                        <input type="text" name="class_name" class="form-control @error('class_name') is-invalid @enderror"
                               value="{{ old('edit_id') ? '' : old('class_name') }}" list="studentClassSuggestions" required>
                        @if($errors->any() && !old('edit_id'))
                            @error('class_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @endif
                    </div>
                    <div>
                        <label class="form-label small fw-semibold">Tahun Ajaran <span class="text-danger">*</span></label>
                        <input type="text" name="academic_year" class="form-control @error('academic_year') is-invalid @enderror"
                               value="{{ old('edit_id') ? '' : old('academic_year') }}" placeholder="Contoh: 2025/2026" required>
                        @if($errors->any() && !old('edit_id'))
                            @error('academic_year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @endif
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

<div class="modal fade" id="editStudentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editStudentForm" method="POST" enctype="multipart/form-data" class="js-async-master"
                  data-success-message="Data siswa berhasil diperbarui.">
                @csrf
                @method('PUT')
                <input type="hidden" name="edit_id" id="edit_student_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 text-center">
                        <img src="" alt="Preview Avatar" class="avatar-preview avatar-clickable" id="edit_avatar_preview" data-avatar-zoom>
                        <input type="file" name="avatar" id="edit_avatar_input" class="form-control @error('avatar') is-invalid @enderror" accept="image/*">
                        <input type="hidden" name="avatar_cropped_data" id="edit_avatar_cropped_data" value="">
                        <button type="button" class="btn btn-sm btn-outline-danger mt-2 d-none" id="btn_remove_student_avatar">
                            <i class="bi bi-trash me-1"></i>Hapus Foto
                        </button>
                        <small class="text-muted d-block mt-1">Upload foto baru jika ingin mengganti, lalu crop persegi.</small>
                        @if($errors->any() && old('edit_id'))
                            @error('avatar') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">NIS <span class="text-danger">*</span></label>
                        <input type="text" id="edit_student_nis" name="nis" class="form-control @error('nis') is-invalid @enderror" required>
                        @if($errors->any() && old('edit_id'))
                            @error('nis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" id="edit_student_name" name="name" class="form-control @error('name') is-invalid @enderror" required>
                        @if($errors->any() && old('edit_id'))
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
                        <select id="edit_student_gender" name="gender" class="form-select @error('gender') is-invalid @enderror" required>
                            <option value="">Pilih</option>
                            <option value="L">L</option>
                            <option value="P">P</option>
                        </select>
                        @if($errors->any() && old('edit_id'))
                            @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kelas Aktif <span class="text-danger">*</span></label>
                        <input type="text" id="edit_student_class" name="class_name" class="form-control @error('class_name') is-invalid @enderror" list="studentClassSuggestions" required>
                        @if($errors->any() && old('edit_id'))
                            @error('class_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @endif
                    </div>
                    <div>
                        <label class="form-label small fw-semibold">Tahun Ajaran <span class="text-danger">*</span></label>
                        <input type="text" id="edit_student_year" name="academic_year" class="form-control @error('academic_year') is-invalid @enderror" required>
                        @if($errors->any() && old('edit_id'))
                            @error('academic_year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @endif
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

<div class="modal fade" id="studentCropModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crop Foto Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="studentCropImage" src="" alt="Crop Avatar">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="applyStudentCrop">Gunakan Foto</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="studentAvatarZoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="studentAvatarZoomTitle">Foto Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img src="" alt="Zoom Avatar" id="studentAvatarZoomImage" class="avatar-zoom-image">
            </div>
        </div>
    </div>
</div>

<div class="modal fade student-detail-modal" id="studentDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Detail Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3">
                <div id="studentDetailLoading" class="student-empty-state">
                    Memuat data siswa...
                </div>

                <div id="studentDetailContent" class="d-none">
                    <div class="student-detail-hero mb-4">
                        <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-3">
                            <div id="studentDetailAvatarWrap"></div>
                            <div class="flex-grow-1">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                    <h4 class="mb-0 fw-bold" id="studentDetailName">-</h4>
                                    <span class="badge text-bg-primary-subtle d-none" id="studentDetailNickname"></span>
                                </div>
                                <div class="text-muted small mb-2" id="studentDetailSummary">-</div>
                                <div class="student-family-badges" id="studentDetailQuickFacts"></div>
                            </div>
                        </div>
                    </div>

                    <ul class="nav nav-pills student-pill-nav gap-2 mb-4" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#student-detail-biodata" type="button">Biodata</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#student-detail-health" type="button">Kesehatan</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#student-detail-learning" type="button">Belajar</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#student-detail-home" type="button">Rumah</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#student-detail-family" type="button">Keluarga</button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="student-detail-biodata">
                            <div class="student-detail-section">
                                <div class="student-detail-grid" id="studentDetailBiodataGrid"></div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="student-detail-health">
                            <div class="student-detail-section mb-3">
                                <div class="student-detail-grid" id="studentDetailHealthGrid"></div>
                            </div>
                            <div class="student-detail-section">
                                <div class="student-detail-grid" id="studentDetailMedicalGrid"></div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="student-detail-learning">
                            <div class="student-detail-section mb-3">
                                <div class="student-detail-grid" id="studentDetailSchoolGrid"></div>
                            </div>
                            <div class="student-detail-section">
                                <div class="student-detail-grid" id="studentDetailLearningGrid"></div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="student-detail-home">
                            <div class="student-detail-section">
                                <div class="student-detail-grid" id="studentDetailHomeGrid"></div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="student-detail-family">
                            <div id="studentDetailFamilyList" class="d-grid gap-3"></div>
                        </div>
                    </div>
                </div>

                <div id="studentDetailError" class="student-empty-state d-none">
                    Gagal memuat detail siswa.
                </div>
            </div>
        </div>
    </div>
</div>

<datalist id="studentClassSuggestions">
    @foreach($classSuggestions as $className)
        <option value="{{ $className }}"></option>
    @endforeach
</datalist>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editModalEl = document.getElementById('editStudentModal');
        const editModal = new bootstrap.Modal(editModalEl);
        const editForm = document.getElementById('editStudentForm');
        const cropModalEl = document.getElementById('studentCropModal');
        const cropModal = new bootstrap.Modal(cropModalEl);
        const cropImage = document.getElementById('studentCropImage');
        const applyCropBtn = document.getElementById('applyStudentCrop');
        const zoomModalEl = document.getElementById('studentAvatarZoomModal');
        const zoomModal = new bootstrap.Modal(zoomModalEl);
        const zoomImage = document.getElementById('studentAvatarZoomImage');
        const zoomTitle = document.getElementById('studentAvatarZoomTitle');
        const detailModalEl = document.getElementById('studentDetailModal');
        const detailModal = new bootstrap.Modal(detailModalEl);
        const detailLoading = document.getElementById('studentDetailLoading');
        const detailContent = document.getElementById('studentDetailContent');
        const detailError = document.getElementById('studentDetailError');

        const createAvatarInput = document.getElementById('create_avatar_input');
        const createAvatarPreview = document.getElementById('create_avatar_preview');
        const createAvatarCroppedData = document.getElementById('create_avatar_cropped_data');
        const editAvatarInput = document.getElementById('edit_avatar_input');
        const editAvatarPreview = document.getElementById('edit_avatar_preview');
        const editAvatarCroppedData = document.getElementById('edit_avatar_cropped_data');
        const removeAvatarBtn = document.getElementById('btn_remove_student_avatar');
        const detailEls = {
            avatarWrap: document.getElementById('studentDetailAvatarWrap'),
            name: document.getElementById('studentDetailName'),
            nickname: document.getElementById('studentDetailNickname'),
            summary: document.getElementById('studentDetailSummary'),
            quickFacts: document.getElementById('studentDetailQuickFacts'),
            biodata: document.getElementById('studentDetailBiodataGrid'),
            health: document.getElementById('studentDetailHealthGrid'),
            medical: document.getElementById('studentDetailMedicalGrid'),
            school: document.getElementById('studentDetailSchoolGrid'),
            learning: document.getElementById('studentDetailLearningGrid'),
            home: document.getElementById('studentDetailHomeGrid'),
            family: document.getElementById('studentDetailFamilyList'),
        };

        let cropper = null;
        let activeAvatarContext = null;

        const fields = {
            id: document.getElementById('edit_student_id'),
            nis: document.getElementById('edit_student_nis'),
            name: document.getElementById('edit_student_name'),
            gender: document.getElementById('edit_student_gender'),
            className: document.getElementById('edit_student_class'),
            year: document.getElementById('edit_student_year'),
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
            zoomTitle.textContent = title || 'Foto Siswa';
            zoomModal.show();
        }

        function formatValue(value, fallback = '-') {
            if (value === null || value === undefined || value === '') return fallback;
            if (typeof value === 'boolean') return value ? 'Ya' : 'Tidak';
            return String(value);
        }

        function formatDate(value) {
            if (!value) return '-';
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return value;
            return new Intl.DateTimeFormat('id-ID', { dateStyle: 'long' }).format(date);
        }

        function formatCurrency(value) {
            if (value === null || value === undefined || value === '') return '-';
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0,
            }).format(Number(value));
        }

        function relationLabel(type) {
            const labels = {
                father: 'Ayah',
                mother: 'Ibu',
                guardian: 'Wali',
                sibling: 'Saudara Kandung',
                step_sibling: 'Saudara Tiri',
                other_family: 'Keluarga Lain',
            };

            return labels[type] || formatValue(type);
        }

        function renderGrid(container, items) {
            container.innerHTML = '';
            const filtered = items.filter(item => item && item.value !== null && item.value !== undefined && item.value !== '');

            if (!filtered.length) {
                container.innerHTML = '<div class="student-empty-state w-100">Belum ada data.</div>';
                return;
            }

            const table = document.createElement('table');
            table.className = 'student-detail-table';

            const tbody = document.createElement('tbody');
            filtered.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = '<th>' + item.label + '</th><td>' + item.value + '</td>';
                tbody.appendChild(row);
            });

            table.appendChild(tbody);
            container.appendChild(table);
        }

        function renderQuickFacts(data) {
            const facts = [
                'NIS ' + formatValue(data.nis),
                formatValue(data.class_name),
                formatValue(data.academic_year, 'Tahun ajaran belum diisi'),
                data.gender === 'L' ? 'Laki-laki' : (data.gender === 'P' ? 'Perempuan' : '-'),
            ].filter(Boolean);

            detailEls.quickFacts.innerHTML = facts
                .map(fact => '<span class="badge rounded-pill text-bg-light border">' + fact + '</span>')
                .join('');
        }

        function renderAvatar(data) {
            if (data.avatar_url) {
                detailEls.avatarWrap.innerHTML = '<button type="button" class="avatar-zoom-trigger" data-avatar-zoom data-zoom-src="' + data.avatar_url + '" data-zoom-title="' + formatValue(data.name, 'Foto Siswa') + '"><img src="' + data.avatar_url + '" alt="' + formatValue(data.name, 'Siswa') + '" class="student-detail-avatar"></button>';
                return;
            }

            const initial = formatValue(data.name, '?').trim().charAt(0).toUpperCase() || '?';
            detailEls.avatarWrap.innerHTML = '<span class="student-detail-initial">' + initial + '</span>';
        }

        function renderFamily(familyMembers) {
            detailEls.family.innerHTML = '';

            if (!familyMembers || !familyMembers.length) {
                detailEls.family.innerHTML = '<div class="student-empty-state">Belum ada data keluarga.</div>';
                return;
            }

            familyMembers.forEach(member => {
                const card = document.createElement('div');
                card.className = 'student-family-row';
                const badges = [];

                if (member.is_guardian) badges.push('<span class="badge text-bg-primary">Guardian</span>');
                if (member.is_primary_contact) badges.push('<span class="badge text-bg-dark">Kontak Utama</span>');
                if (member.is_emergency_contact) badges.push('<span class="badge text-bg-danger">Darurat</span>');
                if (member.lives_with_student) badges.push('<span class="badge text-bg-success">Tinggal Serumah</span>');

                card.innerHTML = `
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 p-3 border-bottom">
                        <div>
                            <div class="fw-bold mb-1">${formatValue(member.full_name)}</div>
                            <div class="text-muted small mb-2">${relationLabel(member.relation_type)}${member.relationship_detail ? ' - ' + member.relationship_detail : ''}</div>
                            <div class="student-family-badges">${badges.join('')}</div>
                        </div>
                        <div class="text-lg-end small text-muted">
                            <div>${formatValue(member.whatsapp_number, 'WA belum diisi')}</div>
                            <div>${formatValue(member.email, 'Email belum diisi')}</div>
                        </div>
                    </div>
                `;

                const detailGrid = document.createElement('div');
                detailGrid.className = 'p-3';
                renderGrid(detailGrid, [
                    { label: 'NIK', value: formatValue(member.nik) },
                    { label: 'Tahun Lahir', value: formatValue(member.birth_year) },
                    { label: 'Agama', value: formatValue(member.religion) },
                    { label: 'Pekerjaan', value: formatValue(member.occupation) },
                    { label: 'Pangkat/Golongan', value: formatValue(member.rank_group) },
                    { label: 'Jabatan', value: formatValue(member.position_title) },
                    { label: 'Pendidikan', value: formatValue(member.education) },
                    { label: 'Penghasilan', value: member.monthly_income ? formatCurrency(member.monthly_income) : '' },
                    { label: 'Kebutuhan Khusus', value: formatValue(member.special_needs) },
                    { label: 'Status Pernikahan', value: formatValue(member.marital_status) },
                    { label: 'Alamat', value: formatValue(member.address_text) },
                    { label: 'Catatan', value: formatValue(member.notes) },
                ]);

                card.appendChild(detailGrid);
                detailEls.family.appendChild(card);
            });
        }

        function resetDetailState() {
            detailLoading.classList.remove('d-none');
            detailContent.classList.add('d-none');
            detailError.classList.add('d-none');
        }

        function showDetailError(message) {
            detailLoading.classList.add('d-none');
            detailContent.classList.add('d-none');
            detailError.classList.remove('d-none');
            detailError.textContent = message || 'Gagal memuat detail siswa.';
        }

        function renderStudentDetail(data) {
            renderAvatar(data);
            detailEls.name.textContent = formatValue(data.name);
            detailEls.nickname.textContent = data.nickname ? 'Panggilan: ' + data.nickname : '';
            detailEls.nickname.classList.toggle('d-none', !data.nickname);
            detailEls.summary.textContent = [
                'NIS ' + formatValue(data.nis),
                formatValue(data.class_name),
                formatValue(data.academic_year, 'Tahun ajaran belum diisi'),
            ].join(' · ');

            renderQuickFacts(data);
            renderGrid(detailEls.biodata, [
                { label: 'Nama Lengkap', value: formatValue(data.name) },
                { label: 'Nama Panggilan', value: formatValue(data.nickname) },
                { label: 'Jenis Kelamin', value: data.gender === 'L' ? 'Laki-laki' : (data.gender === 'P' ? 'Perempuan' : '-') },
                { label: 'Kelas', value: formatValue(data.class_name) },
                { label: 'Tahun Ajaran', value: formatValue(data.academic_year) },
                { label: 'NIS', value: formatValue(data.nis) },
                { label: 'NISN', value: formatValue(data.nisn) },
                { label: 'NIK/No. KITAS', value: formatValue(data.nik_kitas) },
                { label: 'No. KK', value: formatValue(data.family_card_number) },
                { label: 'Tempat Lahir', value: formatValue(data.birth_place) },
                { label: 'Tanggal Lahir', value: formatDate(data.birth_date) },
                { label: 'No. Akta Lahir', value: formatValue(data.birth_certificate_number) },
                { label: 'Agama', value: formatValue(data.religion) },
                { label: 'Kewarganegaraan', value: formatValue(data.citizenship) },
                { label: 'Bahasa Sehari-hari', value: formatValue(data.daily_language) },
                { label: 'WhatsApp', value: formatValue(data.whatsapp_number) },
                { label: 'Email', value: formatValue(data.email) },
                { label: 'Alamat', value: formatValue(data.address_text) },
                { label: 'Catatan', value: formatValue(data.notes) },
            ]);

            renderGrid(detailEls.health, [
                { label: 'Tinggi Badan', value: data.health?.height_cm ? data.health.height_cm + ' cm' : '' },
                { label: 'Berat Badan', value: data.health?.weight_kg ? data.health.weight_kg + ' kg' : '' },
                { label: 'Lingkar Kepala', value: data.health?.head_circumference_cm ? data.health.head_circumference_cm + ' cm' : '' },
                { label: 'Golongan Darah', value: formatValue(data.health?.blood_type) },
                { label: 'Rhesus', value: formatValue(data.health?.rhesus) },
                { label: 'Keadaan Mata', value: formatValue(data.health?.eye_condition) },
                { label: 'Kelainan Mata', value: data.health?.has_eye_disorder },
                { label: 'Alat Bantu', value: formatValue(data.health?.assistive_device) },
                { label: 'Keadaan Telinga', value: formatValue(data.health?.ear_condition) },
                { label: 'Alat Bantu Dengar', value: data.health?.uses_hearing_aid },
                { label: 'Bentuk Wajah', value: formatValue(data.health?.face_shape) },
                { label: 'Jenis Rambut', value: formatValue(data.health?.hair_type) },
                { label: 'Warna Kulit', value: formatValue(data.health?.skin_tone) },
            ]);

            renderGrid(detailEls.medical, [
                { label: 'Riwayat Penyakit', value: formatValue(data.medical_history?.past_diseases) },
                { label: 'Pernah Rawat Inap', value: data.medical_history?.ever_hospitalized },
                { label: 'Penyakit Suka Kambuh', value: data.medical_history?.has_recurring_disease },
                { label: 'Riwayat Operasi', value: formatValue(data.medical_history?.surgery_history) },
                { label: 'Penanganan Saat Kambuh', value: formatValue(data.medical_history?.relapse_treatment) },
                { label: 'Alergi Obat/Makanan', value: formatValue(data.medical_history?.drug_food_allergies) },
            ]);

            renderGrid(detailEls.school, [
                { label: 'Asal SMP', value: formatValue(data.previous_school?.smp_school_name) },
                { label: 'NPSN SMP', value: formatValue(data.previous_school?.smp_npsn) },
                { label: 'Lama Belajar', value: data.previous_school?.smp_study_duration_months ? data.previous_school.smp_study_duration_months + ' bulan' : '' },
                { label: 'Pernah Tinggal Kelas', value: data.previous_school?.ever_repeated_grade },
                { label: 'Prestasi', value: formatValue(data.previous_school?.achievements) },
                { label: 'Menerima Beasiswa', value: data.previous_school?.receives_scholarship },
                { label: 'Ekskul SMP', value: formatValue(data.previous_school?.extracurricular_smp) },
            ]);

            renderGrid(detailEls.learning, [
                { label: 'Hobi Olahraga', value: formatValue(data.learning_profile?.sports_hobby) },
                { label: 'Hobi Kesenian', value: formatValue(data.learning_profile?.arts_hobby) },
                { label: 'Hobi Lainnya', value: formatValue(data.learning_profile?.other_hobby) },
                { label: 'Bidang Bakat', value: formatValue(data.learning_profile?.talent_field) },
                { label: 'Punya Waktu Senggang Khusus', value: data.learning_profile?.has_leisure_time },
                { label: 'Mulai Membaca', value: data.learning_profile?.reading_start_age_months ? data.learning_profile.reading_start_age_months + ' bulan' : '' },
                { label: 'Mulai Menulis', value: data.learning_profile?.writing_start_age_months ? data.learning_profile.writing_start_age_months + ' bulan' : '' },
                { label: 'Mulai Berhitung', value: data.learning_profile?.counting_start_age_months ? data.learning_profile.counting_start_age_months + ' bulan' : '' },
                { label: 'Mulai Berbicara', value: data.learning_profile?.speaking_start_age_months ? data.learning_profile.speaking_start_age_months + ' bulan' : '' },
                { label: 'Masuk KB/TK', value: data.learning_profile?.start_kb_tk_age_months ? data.learning_profile.start_kb_tk_age_months + ' bulan' : '' },
                { label: 'Masuk SD', value: data.learning_profile?.start_sd_age_months ? data.learning_profile.start_sd_age_months + ' bulan' : '' },
                { label: 'Masuk SMP', value: data.learning_profile?.start_smp_age_months ? data.learning_profile.start_smp_age_months + ' bulan' : '' },
                { label: 'Senang di Sekolah', value: data.learning_profile?.likes_school },
                { label: 'Senang Bermain Dengan', value: formatValue(data.learning_profile?.likes_play_with) },
                { label: 'Suka Permainan', value: formatValue(data.learning_profile?.likes_game_type) },
                { label: 'Lebih Suka', value: formatValue(data.learning_profile?.preferred_activity) },
                { label: 'Konsentrasi', value: formatValue(data.learning_profile?.concentration_level) },
                { label: 'Penyelesaian Tugas', value: formatValue(data.learning_profile?.task_completion_style) },
                { label: 'Peran Imajinasi', value: formatValue(data.learning_profile?.imagination_role) },
                { label: 'Kelompok Belajar', value: data.learning_profile?.has_home_study_group },
                { label: 'Kelompok Belajar Bermanfaat', value: data.learning_profile?.study_group_beneficial },
                { label: 'Ikut Bimbel', value: data.learning_profile?.attends_tutoring },
                { label: 'Lembaga Bimbel', value: formatValue(data.learning_profile?.tutoring_institution) },
                { label: 'Jam Belajar Mandiri', value: data.learning_profile?.self_study_hours_per_day ? data.learning_profile.self_study_hours_per_day + ' jam/hari' : '' },
                { label: 'Punya Jadwal Belajar', value: data.learning_profile?.has_home_study_schedule },
                { label: 'Waktu Belajar', value: formatValue(data.learning_profile?.common_study_time) },
                { label: 'Sering Bertanya', value: data.learning_profile?.asks_curiosity_questions },
                { label: 'Topik Rasa Ingin Tahu', value: formatValue(data.learning_profile?.curiosity_topics) },
            ]);

            renderGrid(detailEls.home, [
                { label: 'Jarak ke Sekolah', value: data.home_assets?.home_to_school_distance_km ? data.home_assets.home_to_school_distance_km + ' km' : '' },
                { label: 'Waktu Tempuh', value: data.home_assets?.home_to_school_travel_minutes ? data.home_assets.home_to_school_travel_minutes + ' menit' : '' },
                { label: 'Moda Transportasi', value: formatValue(data.home_assets?.transport_mode) },
                { label: 'Kendaraan di Rumah', value: formatValue(data.home_assets?.household_vehicle) },
                { label: 'Lingkungan Tempat Tinggal', value: formatValue(data.home_assets?.living_environment) },
                { label: 'Kondisi Penerangan', value: formatValue(data.home_assets?.home_lighting_condition) },
                { label: 'Ruang Tidur', value: formatValue(data.home_assets?.bedroom_condition) },
                { label: 'Ruang Belajar', value: formatValue(data.home_assets?.study_room_condition) },
                { label: 'Perlengkapan Belajar', value: formatValue(data.home_assets?.learning_tools) },
                { label: 'Punya Alat Musik', value: data.home_assets?.has_musical_instruments },
                { label: 'Alat Musik 1', value: formatValue(data.home_assets?.musical_instrument_1) },
                { label: 'Alat Musik 2', value: formatValue(data.home_assets?.musical_instrument_2) },
                { label: 'Punya Alat Olahraga', value: data.home_assets?.has_sports_equipment },
                { label: 'Alat Olahraga 1', value: formatValue(data.home_assets?.sports_equipment_1) },
                { label: 'Alat Olahraga 2', value: formatValue(data.home_assets?.sports_equipment_2) },
            ]);

            renderFamily(data.family_members || []);
            detailLoading.classList.add('d-none');
            detailError.classList.add('d-none');
            detailContent.classList.remove('d-none');
        }

        async function openStudentDetail(url) {
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
                    throw new Error(body.message || 'Gagal memuat detail siswa.');
                }

                renderStudentDetail(body);
            } catch (error) {
                showDetailError(error.message || 'Gagal memuat detail siswa.');
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
                const title = zoomTarget.dataset.zoomTitle || fields.name.value || 'Foto Siswa';
                openZoom(src, title);
                return;
            }

            const detailTrigger = e.target.closest('[data-student-detail]');
            if (detailTrigger) {
                openStudentDetail(detailTrigger.dataset.detailUrl);
                return;
            }

            const btn = e.target.closest('.btn-edit-student');
            if (!btn) return;

            const id = btn.dataset.id;
            editForm.action = "{{ url('admin/master/students') }}/" + id;
            fields.id.value = id;
            fields.nis.value = btn.dataset.nis || '';
            fields.name.value = btn.dataset.name || '';
            fields.gender.value = btn.dataset.gender || '';
            fields.className.value = btn.dataset.class || '';
            fields.year.value = btn.dataset.year || '';
            editAvatarCroppedData.value = '';
            editAvatarInput.value = '';
            const currentAvatar = btn.dataset.avatar || '';
            setPreview(editAvatarPreview, currentAvatar);

            if (removeAvatarBtn) {
                removeAvatarBtn.dataset.url = "{{ url('admin/master/students') }}/" + id + "/avatar";
                removeAvatarBtn.classList.toggle('d-none', !currentAvatar);
            }

            editModal.show();
        });

        removeAvatarBtn?.addEventListener('click', async function () {
            const endpoint = removeAvatarBtn.dataset.url;
            if (!endpoint) return;
            if (!window.confirm('Yakin hapus foto siswa ini?')) return;

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
                    throw new Error(body.message || 'Gagal menghapus foto siswa.');
                }

                editAvatarInput.value = '';
                editAvatarCroppedData.value = '';
                setPreview(editAvatarPreview, '');
                removeAvatarBtn.classList.add('d-none');

                if (typeof showAsyncAlert === 'function') {
                    showAsyncAlert('success', body.message || 'Foto siswa berhasil dihapus.');
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
            editForm.action = "{{ url('admin/master/students') }}/{{ old('edit_id') }}";
            fields.id.value = "{{ old('edit_id') }}";
            fields.nis.value = @json(old('nis'));
            fields.name.value = @json(old('name'));
            fields.gender.value = @json(old('gender'));
            fields.className.value = @json(old('class_name'));
            fields.year.value = @json(old('academic_year'));
            setPreview(editAvatarPreview, @json(old('avatar_cropped_data')));
            if (removeAvatarBtn) {
                removeAvatarBtn.dataset.url = "{{ url('admin/master/students') }}/{{ old('edit_id') }}/avatar";
                removeAvatarBtn.classList.toggle('d-none', !@json((bool) old('avatar_cropped_data')));
            }
            editModal.show();
        @elseif($errors->any())
            setPreview(createAvatarPreview, @json(old('avatar_cropped_data')));
            new bootstrap.Modal(document.getElementById('createStudentModal')).show();
        @endif
    });
</script>
@endpush
@endsection
