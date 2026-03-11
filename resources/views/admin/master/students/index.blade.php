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
                                    <div>
                                        <div class="fw-bold">{{ $student->name }}</div>
                                        <div class="student-meta">NIS: {{ $student->nis }}</div>
                                        <div class="student-meta">
                                            {{ $student->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}
                                            · {{ $student->activeClass?->class_name ?? '-' }}
                                        </div>
                                        <div class="student-meta">TA: {{ $student->activeClass?->academic_year ?? '-' }}</div>
                                    </div>
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

        const createAvatarInput = document.getElementById('create_avatar_input');
        const createAvatarPreview = document.getElementById('create_avatar_preview');
        const createAvatarCroppedData = document.getElementById('create_avatar_cropped_data');
        const editAvatarInput = document.getElementById('edit_avatar_input');
        const editAvatarPreview = document.getElementById('edit_avatar_preview');
        const editAvatarCroppedData = document.getElementById('edit_avatar_cropped_data');
        const removeAvatarBtn = document.getElementById('btn_remove_student_avatar');

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
