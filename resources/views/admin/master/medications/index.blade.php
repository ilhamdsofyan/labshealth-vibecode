@extends('layouts.app')

@section('title', 'Data Obat')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Data Obat</h4>
        <p class="text-muted mb-0 small">Master data obat dan item terapi untuk kunjungan UKS</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createMedicationModal">
            <i class="bi bi-plus-lg me-1"></i>Tambah Obat
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
            <form method="GET" action="{{ route('admin.master.medications.index') }}" class="js-async-search">
                <div class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="Cari nama obat atau kategori..." value="{{ request('search') }}">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('admin.master.medications.index') }}" class="btn btn-outline-secondary btn-sm js-async-refresh">
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
                            <th width="50">No</th>
                            <th>Nama Obat / Item</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                            <th width="140">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($medications as $index => $medication)
                            <tr>
                                <td>{{ $medications->firstItem() + $index }}</td>
                                <td class="fw-bold">{{ $medication->name }}</td>
                                <td>
                                    @if($medication->category)
                                        <span class="badge bg-light text-dark border">{{ $medication->category }}</span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($medication->stock)
                                        <span class="badge {{ $medication->stock->quantity <= $medication->stock->min_threshold ? 'bg-danger' : 'bg-success' }}">
                                            {{ $medication->stock->quantity }} {{ $medication->stock->unit }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">0 pcs</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button
                                            type="button"
                                            class="btn btn-outline-success btn-restock-medication"
                                            data-id="{{ $medication->id }}"
                                            data-name="{{ $medication->name }}"
                                            data-stock="{{ $medication->stock ? $medication->stock->quantity : 0 }}"
                                            data-unit="{{ $medication->stock ? $medication->stock->unit : 'pcs' }}"
                                            data-threshold="{{ $medication->stock ? $medication->stock->min_threshold : 10 }}"
                                            title="Kelola Stok"
                                        >
                                            <i class="bi bi-box-seam"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-outline-warning btn-edit-medication"
                                            data-id="{{ $medication->id }}"
                                            data-name="{{ $medication->name }}"
                                            data-category="{{ $medication->category }}"
                                            title="Edit"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form action="{{ route('admin.master.medications.destroy', $medication) }}" method="POST" class="js-async-delete"
                                              data-loading-text="Menghapus..."
                                              data-confirm="Yakin hapus data obat ini?" data-success-message="Data obat berhasil dihapus.">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-5">
                                    Belum ada data obat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($medications->hasPages())
            <div class="card-footer bg-transparent">
                {{ $medications->links() }}
            </div>
        @endif
    </div>
</div>

<div class="modal fade" id="createMedicationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.master.medications.store') }}" method="POST" class="js-async-master"
                  data-success-message="Data obat berhasil ditambahkan.">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Obat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Obat <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('edit_id') ? '' : old('name') }}" placeholder="Contoh: Paracetamol 500mg / Betadine" required>
                        @if($errors->any() && !old('edit_id'))
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @endif
                    </div>
                    <div>
                        <label class="form-label small fw-semibold">Kategori</label>
                        <input type="text" name="category" class="form-control @error('category') is-invalid @enderror"
                               value="{{ old('edit_id') ? '' : old('category') }}" list="medicationCategorySuggestions" placeholder="Contoh: Antipiretik / Antihistamin / Topikal">
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

<div class="modal fade" id="editMedicationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editMedicationForm" method="POST" class="js-async-master"
                  data-success-message="Data obat berhasil diperbarui.">
                @csrf
                @method('PUT')
                <input type="hidden" name="edit_id" id="edit_medication_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Obat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Obat <span class="text-danger">*</span></label>
                        <input type="text" id="edit_medication_name" name="name" class="form-control @error('name') is-invalid @enderror" required>
                        @if($errors->any() && old('edit_id'))
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @endif
                    </div>
                    <div>
                        <label class="form-label small fw-semibold">Kategori</label>
                        <input type="text" id="edit_medication_category" name="category" class="form-control @error('category') is-invalid @enderror" list="medicationCategorySuggestions">
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

<div class="modal fade" id="restockMedicationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="restockMedicationForm" action="" method="POST" class="js-async-master"
                  data-success-message="Stok obat berhasil diperbarui.">
                @csrf
                <input type="hidden" id="restock_medication_id" name="id">
                <div class="modal-header">
                    <h5 class="modal-title">Kelola Stok: <span id="restock_medication_name_display" class="text-primary"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Pilih Aksi</label>
                            <select name="action_type" class="form-select" required id="restock_action_type">
                                <option value="in">Tambah (Restock)</option>
                                <option value="out">Kurangi</option>
                                <option value="adjust">Penyesuaian (Update Absolut)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Jumlah (Kuantitas) <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control" required min="0" placeholder="Cth: 10">
                        </div>
                    </div>
                    <div class="row mb-3" id="restock_settings">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Satuan</label>
                            <input type="text" name="unit" id="restock_unit" class="form-control" value="pcs">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Min Threshold</label>
                            <input type="number" name="min_threshold" id="restock_threshold" class="form-control" value="10">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Catatan Opsional</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Cth: Pembelian baru / Expired"></textarea>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <div>
                        <span class="small text-muted">Stok saat ini: <strong id="current_stock_display">0</strong></span>
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<datalist id="medicationCategorySuggestions">
    @foreach($categorySuggestions as $category)
        <option value="{{ $category }}"></option>
    @endforeach
</datalist>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editModalEl = document.getElementById('editMedicationModal');
        const editModal = new bootstrap.Modal(editModalEl);
        const editForm = document.getElementById('editMedicationForm');
        const editIdInput = document.getElementById('edit_medication_id');
        const editNameInput = document.getElementById('edit_medication_name');
        const editCategoryInput = document.getElementById('edit_medication_category');

        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-edit-medication');
            if (btn) {
                const id = btn.dataset.id;
                editForm.action = "{{ url('admin/master/medications') }}/" + id;
                editIdInput.value = id;
                editNameInput.value = btn.dataset.name || '';
                editCategoryInput.value = btn.dataset.category || '';
                editModal.show();
                return;
            }

            const btnRestock = e.target.closest('.btn-restock-medication');
            if (btnRestock) {
                const id = btnRestock.dataset.id;
                document.getElementById('restockMedicationForm').action = "{{ url('admin/master/medications') }}/" + id + "/restock";
                document.getElementById('restock_medication_id').value = id;
                document.getElementById('restock_medication_name_display').textContent = btnRestock.dataset.name;
                document.getElementById('current_stock_display').textContent = btnRestock.dataset.stock + ' ' + btnRestock.dataset.unit;
                document.getElementById('restock_unit').value = btnRestock.dataset.unit;
                document.getElementById('restock_threshold').value = btnRestock.dataset.threshold;
                
                new bootstrap.Modal(document.getElementById('restockMedicationModal')).show();
            }
        });

        @if($errors->any() && old('edit_id'))
            editForm.action = "{{ url('admin/master/medications') }}/{{ old('edit_id') }}";
            editIdInput.value = "{{ old('edit_id') }}";
            editNameInput.value = @json(old('name'));
            editCategoryInput.value = @json(old('category'));
            editModal.show();
        @elseif($errors->any())
            new bootstrap.Modal(document.getElementById('createMedicationModal')).show();
        @endif
    });
</script>
@endpush
@endsection
