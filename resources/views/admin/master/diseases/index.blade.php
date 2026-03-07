@extends('layouts.app')

@section('title', 'Data Penyakit')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Data Penyakit</h4>
        <p class="text-muted mb-0 small">Master data diagnosa penyakit</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createDiseaseModal">
            <i class="bi bi-plus-lg me-1"></i>Tambah Penyakit
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.master.diseases.index') }}">
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Cari Nama atau Kategori..." value="{{ request('search') }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.master.diseases.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th width="50">No</th>
                        <th>Nama Penyakit</th>
                        <th>Kategori</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($diseases as $index => $disease)
                        <tr>
                            <td>{{ $diseases->firstItem() + $index }}</td>
                            <td class="fw-bold">{{ $disease->name }}</td>
                            <td>
                                @if($disease->category)
                                    <span class="badge bg-light text-dark border">{{ $disease->category }}</span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button
                                        type="button"
                                        class="btn btn-outline-warning btn-edit-disease"
                                        data-id="{{ $disease->id }}"
                                        data-name="{{ $disease->name }}"
                                        data-category="{{ $disease->category }}"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('admin.master.diseases.destroy', $disease) }}" method="POST"
                                          onsubmit="return confirm('Yakin hapus data penyakit ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">
                                Belum ada data penyakit.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($diseases->hasPages())
        <div class="card-footer bg-transparent">
            {{ $diseases->links() }}
        </div>
    @endif
</div>

<div class="modal fade" id="createDiseaseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.master.diseases.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Penyakit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Penyakit <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('edit_id') ? '' : old('name') }}" required>
                        @if($errors->any() && !old('edit_id'))
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @endif
                    </div>
                    <div>
                        <label class="form-label small fw-semibold">Kategori</label>
                        <input type="text" name="category" class="form-control @error('category') is-invalid @enderror"
                               value="{{ old('edit_id') ? '' : old('category') }}" list="diseaseCategorySuggestions">
                        @if($errors->any() && !old('edit_id'))
                            @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
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

<div class="modal fade" id="editDiseaseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editDiseaseForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="edit_id" id="edit_disease_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Penyakit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Penyakit <span class="text-danger">*</span></label>
                        <input type="text" id="edit_disease_name" name="name" class="form-control @error('name') is-invalid @enderror" required>
                        @if($errors->any() && old('edit_id'))
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @endif
                    </div>
                    <div>
                        <label class="form-label small fw-semibold">Kategori</label>
                        <input type="text" id="edit_disease_category" name="category" class="form-control @error('category') is-invalid @enderror" list="diseaseCategorySuggestions">
                        @if($errors->any() && old('edit_id'))
                            @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
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

<datalist id="diseaseCategorySuggestions">
    @foreach($categorySuggestions as $category)
        <option value="{{ $category }}"></option>
    @endforeach
</datalist>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editModalEl = document.getElementById('editDiseaseModal');
        const editModal = new bootstrap.Modal(editModalEl);
        const editForm = document.getElementById('editDiseaseForm');
        const editIdInput = document.getElementById('edit_disease_id');
        const editNameInput = document.getElementById('edit_disease_name');
        const editCategoryInput = document.getElementById('edit_disease_category');

        document.querySelectorAll('.btn-edit-disease').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                editForm.action = "{{ url('admin/master/diseases') }}/" + id;
                editIdInput.value = id;
                editNameInput.value = this.dataset.name || '';
                editCategoryInput.value = this.dataset.category || '';
                editModal.show();
            });
        });

        @if($errors->any() && old('edit_id'))
            editForm.action = "{{ url('admin/master/diseases') }}/{{ old('edit_id') }}";
            editIdInput.value = "{{ old('edit_id') }}";
            editNameInput.value = @json(old('name'));
            editCategoryInput.value = @json(old('category'));
            editModal.show();
        @elseif($errors->any())
            new bootstrap.Modal(document.getElementById('createDiseaseModal')).show();
        @endif
    });
</script>
@endpush
@endsection
