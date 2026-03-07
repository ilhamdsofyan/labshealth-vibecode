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

<div data-master-async-container>
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.master.students.index') }}" class="js-async-search">
                <div class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="Cari NIS atau Nama..." value="{{ request('search') }}">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-search"></i>
                        </button>
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
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th width="80">NIS</th>
                            <th>Nama</th>
                            <th width="50">JK</th>
                            <th>Kelas Aktif</th>
                            <th>TS/Tahun Ajaran</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                            <tr>
                                <td class="small fw-bold">{{ $student->nis }}</td>
                                <td>{{ $student->name }}</td>
                                <td>{{ $student->gender }}</td>
                                <td>{{ $student->activeClass?->class_name ?? '-' }}</td>
                                <td class="small text-muted">{{ $student->activeClass?->academic_year ?? '-' }}</td>
                                <td>
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
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    Belum ada data siswa.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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
            <form action="{{ route('admin.master.students.store') }}" method="POST" class="js-async-master"
                  data-success-message="Data siswa berhasil ditambahkan.">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
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
            <form id="editStudentForm" method="POST" class="js-async-master"
                  data-success-message="Data siswa berhasil diperbarui.">
                @csrf
                @method('PUT')
                <input type="hidden" name="edit_id" id="edit_student_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
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

<datalist id="studentClassSuggestions">
    @foreach($classSuggestions as $className)
        <option value="{{ $className }}"></option>
    @endforeach
</datalist>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editModalEl = document.getElementById('editStudentModal');
        const editModal = new bootstrap.Modal(editModalEl);
        const editForm = document.getElementById('editStudentForm');

        const fields = {
            id: document.getElementById('edit_student_id'),
            nis: document.getElementById('edit_student_nis'),
            name: document.getElementById('edit_student_name'),
            gender: document.getElementById('edit_student_gender'),
            className: document.getElementById('edit_student_class'),
            year: document.getElementById('edit_student_year'),
        };

        document.querySelectorAll('.btn-edit-student').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                editForm.action = "{{ url('admin/master/students') }}/" + id;
                fields.id.value = id;
                fields.nis.value = this.dataset.nis || '';
                fields.name.value = this.dataset.name || '';
                fields.gender.value = this.dataset.gender || '';
                fields.className.value = this.dataset.class || '';
                fields.year.value = this.dataset.year || '';
                editModal.show();
            });
        });

        @if($errors->any() && old('edit_id'))
            editForm.action = "{{ url('admin/master/students') }}/{{ old('edit_id') }}";
            fields.id.value = "{{ old('edit_id') }}";
            fields.nis.value = @json(old('nis'));
            fields.name.value = @json(old('name'));
            fields.gender.value = @json(old('gender'));
            fields.className.value = @json(old('class_name'));
            fields.year.value = @json(old('academic_year'));
            editModal.show();
        @elseif($errors->any())
            new bootstrap.Modal(document.getElementById('createStudentModal')).show();
        @endif
    });
</script>
@endpush
@endsection
