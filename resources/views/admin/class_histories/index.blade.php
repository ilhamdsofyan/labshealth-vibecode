@extends('layouts.app')

@section('title', 'Riwayat Kelas Siswa')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Riwayat Kelas Siswa</h4>
        <p class="text-muted mb-0 small">Log perpindahan kelas dan tahun akademik siswa</p>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.class-histories.index') }}">
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control form-control-sm" 
                           placeholder="Cari Siswa atau Kelas..." value="{{ request('search') }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-search"></i>
                    </button>
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
                        <th>Tanggal Log</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Tahun Akademik</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($histories as $history)
                        <tr>
                            <td class="small">{{ $history->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $history->student->nis }}</td>
                            <td class="fw-bold">{{ $history->student->name }}</td>
                            <td>{{ $history->class_name }}</td>
                            <td>{{ $history->academic_year }}</td>
                            <td>
                                @if($history->is_active)
                                    <span class="badge bg-success small">Aktif</span>
                                @else
                                    <span class="badge bg-light text-muted border small">History</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">Belum ada data riwayat kelas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($histories->hasPages())
        <div class="card-footer bg-transparent">
            {{ $histories->links() }}
        </div>
    @endif
</div>
@endsection
