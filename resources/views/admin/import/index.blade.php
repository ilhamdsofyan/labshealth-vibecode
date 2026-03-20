@extends('layouts.app')

@section('title', 'Import Data Kunjungan')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Import Master Data & Kunjungan</h4>
        <p class="text-muted mb-0 small">Unggah data massal dari file Excel / CSV</p>
    </div>
    <div class="dropdown">
        <button class="btn btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-file-earmark-excel me-1"></i>Unduh Template
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item small" href="{{ route('admin.import.template', ['type' => 'students']) }}">Template Siswa</a></li>
            <li><a class="dropdown-item small" href="{{ route('admin.import.template', ['type' => 'student_details']) }}">Template Detail Siswa</a></li>
            <li><a class="dropdown-item small" href="{{ route('admin.import.template', ['type' => 'employees']) }}">Template Pegawai</a></li>
            <li><a class="dropdown-item small" href="{{ route('admin.import.template', ['type' => 'diseases']) }}">Template Penyakit</a></li>
            <li><a class="dropdown-item small" href="{{ route('admin.import.template', ['type' => 'medications']) }}">Template Obat</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item small" href="{{ route('admin.import.template', ['type' => 'visits']) }}">Template Kunjungan</a></li>
        </ul>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header bg-white fw-bold"><i class="bi bi-upload me-2"></i>Upload File</div>
            <div class="card-body">
                <form action="{{ route('admin.import.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tipe Import</label>
                        <select name="type" class="form-select form-select-sm" required>
                            <option value="" disabled {{ old('type') ? '' : 'selected' }}>Pilih tipe import...</option>
                            <option value="students" {{ old('type') === 'students' ? 'selected' : '' }}>Data Siswa</option>
                            <option value="student_details" {{ old('type') === 'student_details' ? 'selected' : '' }}>Detail Siswa</option>
                            <option value="employees" {{ old('type') === 'employees' ? 'selected' : '' }}>Data Pegawai</option>
                            <option value="diseases" {{ old('type') === 'diseases' ? 'selected' : '' }}>Data Penyakit</option>
                            <option value="medications" {{ old('type') === 'medications' ? 'selected' : '' }}>Data Obat</option>
                            <option value="visits" {{ old('type') === 'visits' ? 'selected' : '' }}>Data Kunjungan</option>
                        </select>
                        @error('type')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pilih File (.xlsx, .xls, .csv)</label>
                        <input type="file" name="file" class="form-control form-control-sm" required>
                    </div>
                    <div class="alert alert-warning py-2 mb-4 small border-0">
                        <i class="bi bi-info-circle me-1"></i>Gunakan template yang disediakan untuk menghindari kegagalan sistem.
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-semibold">Mulai Import</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><i class="bi bi-clock-history me-2"></i>Riwayat Import</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>File</th>
                                <th class="text-center">Berhasil</th>
                                <th class="text-center">Gagal</th>
                                <th>Oleh</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td class="small">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="small fw-medium">{{ $log->file_name }}</td>
                                    <td class="text-center text-success"><span class="badge bg-success bg-opacity-10 text-success">{{ $log->success_rows }}</span></td>
                                    <td class="text-center text-danger"><span class="badge bg-danger bg-opacity-10 text-danger">{{ $log->failed_rows }}</span></td>
                                    <td class="small">{{ $log->uploader->name }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted small">Belum ada riwayat import</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($logs->hasPages())
                <div class="card-footer bg-transparent">{{ $logs->links() }}</div>
            @endif
        </div>
    </div>
</div>

@if(session('failedRows'))
<div class="card mt-4 border-danger">
    <div class="card-header bg-danger text-white"><i class="bi bi-exclamation-triangle me-2"></i>Baris Gagal Detail</div>
    <div class="card-body p-0">
        <div class="table-responsive" style="max-height: 300px;">
            <table class="table table-sm table-striped mb-0 small">
                <thead class="bg-light sticky-top">
                    <tr>
                        <th width="80">Baris</th>
                        <th>Alasan</th>
                        <th>Identitas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(session('failedRows') as $failed)
                        <tr>
                            <td class="text-center">{{ $failed['row'] }}</td>
                            <td class="text-danger">
                                @if(is_array($failed['errors']))
                                    <ul class="mb-0 ps-3">@foreach($failed['errors'] as $err)<li>{{ $err }}</li>@endforeach</ul>
                                @else
                                    {{ $failed['reason'] ?? 'Unknown error' }}
                                @endif
                            </td>
                            <td>
                                <pre class="mb-0 x-small" style="font-size: 10px;">{{ json_encode($failed['data'], JSON_PRETTY_PRINT) }}</pre>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@endsection
