@extends('layouts.app')

@section('title', $reportTitle)

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">{{ $reportTitle }}</h4>
        <p class="text-muted mb-0 small">Menampilkan data tren penggunaan dan mutasi stok (masuk/keluar)</p>
    </div>
</div>

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.stock') }}" class="row g-2 align-items-end js-async-search">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Bulan</label>
                <select name="month" class="form-select form-select-sm">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Tahun</label>
                <input type="number" name="year" class="form-control form-control-sm" value="{{ $year }}" min="2020" max="2099">
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary btn-sm px-4">Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th width="50" class="text-center">No</th>
                        <th>Nama Obat/Item</th>
                        <th>Kategori</th>
                        <th class="text-center">Stok Saat Ini</th>
                        <th class="text-center text-danger">Pemakaian Keluar</th>
                        <th class="text-center text-success">Stok Masuk</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($report as $index => $row)
                        <tr>
                            <td class="text-center text-muted">{{ $index + 1 }}</td>
                            <td class="fw-semibold">{{ $row['name'] }}</td>
                            <td>{{ $row['category'] ?: '-' }}</td>
                            <td class="text-center fw-bold">{{ $row['current_stock'] }} {{ $row['unit'] }}</td>
                            <td class="text-center text-danger fw-bold">{{ $row['usage_count'] }}</td>
                            <td class="text-center text-success fw-bold">+{{ $row['restock_count'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada data obat/stok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
