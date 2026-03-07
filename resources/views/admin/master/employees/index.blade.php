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

<div data-master-async-container>
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.master.employees.index') }}" class="js-async-search">
                <div class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="Cari NIP atau Nama..." value="{{ request('search') }}">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-search"></i>
                        </button>
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
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th width="150">NIP</th>
                            <th>Nama</th>
                            <th>Tipe</th>
                            <th>Bagian / Unit</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $employee)
                            <tr>
                                <td class="small fw-bold">{{ $employee->nip }}</td>
                                <td>{{ $employee->name }}</td>
                                <td>
                                    <span class="badge {{ $employee->role_type === 'GURU' ? 'bg-primary' : 'bg-info' }}">
                                        {{ $employee->role_type }}
                                    </span>
                                </td>
                                <td>{{ $employee->department ?? '-' }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button
                                            type="button"
                                            class="btn btn-outline-warning btn-edit-employee"
                                            data-id="{{ $employee->id }}"
                                            data-nip="{{ $employee->nip }}"
                                            data-name="{{ $employee->name }}"
                                            data-role="{{ $employee->role_type }}"
                                            data-department="{{ $employee->department }}"
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
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    Belum ada data pegawai.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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
            <form action="{{ route('admin.master.employees.store') }}" method="POST" class="js-async-master"
                  data-success-message="Data pegawai berhasil ditambahkan.">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pegawai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
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
                        <select name="role_type" class="form-select @error('role_type') is-invalid @enderror" required>
                            <option value="">Pilih</option>
                            <option value="GURU" {{ (!old('edit_id') && old('role_type') === 'GURU') ? 'selected' : '' }}>GURU</option>
                            <option value="KARYAWAN" {{ (!old('edit_id') && old('role_type') === 'KARYAWAN') ? 'selected' : '' }}>KARYAWAN</option>
                        </select>
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
            <form id="editEmployeeForm" method="POST" class="js-async-master"
                  data-success-message="Data pegawai berhasil diperbarui.">
                @csrf
                @method('PUT')
                <input type="hidden" name="edit_id" id="edit_employee_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Pegawai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
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
                        <select id="edit_employee_role" name="role_type" class="form-select @error('role_type') is-invalid @enderror" required>
                            <option value="">Pilih</option>
                            <option value="GURU">GURU</option>
                            <option value="KARYAWAN">KARYAWAN</option>
                        </select>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Perbarui</button>
                </div>
            </form>
        </div>
    </div>
</div>

<datalist id="employeeUnitSuggestions">
    @foreach($departmentSuggestions as $department)
        <option value="{{ $department }}"></option>
    @endforeach
</datalist>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editModalEl = document.getElementById('editEmployeeModal');
        const editModal = new bootstrap.Modal(editModalEl);
        const editForm = document.getElementById('editEmployeeForm');

        const fields = {
            id: document.getElementById('edit_employee_id'),
            nip: document.getElementById('edit_employee_nip'),
            name: document.getElementById('edit_employee_name'),
            role: document.getElementById('edit_employee_role'),
            department: document.getElementById('edit_employee_department'),
        };

        document.querySelectorAll('.btn-edit-employee').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                editForm.action = "{{ url('admin/master/employees') }}/" + id;
                fields.id.value = id;
                fields.nip.value = this.dataset.nip || '';
                fields.name.value = this.dataset.name || '';
                fields.role.value = this.dataset.role || '';
                fields.department.value = this.dataset.department || '';
                editModal.show();
            });
        });

        @if($errors->any() && old('edit_id'))
            editForm.action = "{{ url('admin/master/employees') }}/{{ old('edit_id') }}";
            fields.id.value = "{{ old('edit_id') }}";
            fields.nip.value = @json(old('nip'));
            fields.name.value = @json(old('name'));
            fields.role.value = @json(old('role_type'));
            fields.department.value = @json(old('department'));
            editModal.show();
        @elseif($errors->any())
            new bootstrap.Modal(document.getElementById('createEmployeeModal')).show();
        @endif
    });
</script>
@endpush
@endsection
