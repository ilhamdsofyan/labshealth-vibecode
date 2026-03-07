@extends('layouts.app')

@section('title', 'Data Penyakit')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Data Penyakit</h4>
        <p class="text-muted mb-0 small">Master data diagnosa penyakit</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.master.diseases.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Tambah Penyakit
        </a>
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
                                    <a href="{{ route('admin.master.diseases.edit', $disease) }}" class="btn btn-outline-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
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
@endsection
