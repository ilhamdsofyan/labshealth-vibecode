@extends('layouts.app')

@section('title', 'Kelola Permissions')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Kelola Permissions</h4>
        <p class="text-muted mb-0 small">Daftar permission berdasarkan route</p>
    </div>
    <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary">
        <i class="bi bi-shield-plus me-1"></i>Tambah Permission
    </a>
</div>

<!-- Group Filter -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="d-flex gap-2">
            <select name="group" class="form-select form-select-sm" style="max-width:200px;">
                <option value="">Semua Group</option>
                @foreach($groups as $group)
                    <option value="{{ $group }}" {{ request('group') == $group ? 'selected' : '' }}>{{ $group }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i></button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Permission</th>
                        <th>Nama Tampilan</th>
                        <th>Group</th>
                        <th class="text-center">Roles</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($permissions as $perm)
                        <tr>
                            <td><code class="small">{{ $perm->name }}</code></td>
                            <td class="small">{{ $perm->display_name }}</td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $perm->group_name ?? '-' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary bg-opacity-10 text-primary">{{ $perm->roles_count }}</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.permissions.edit', $perm) }}" class="btn btn-outline-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.permissions.destroy', $perm) }}" method="POST"
                                          onsubmit="return confirm('Yakin hapus permission ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Tidak ada data permission</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($permissions->hasPages())
        <div class="card-footer bg-transparent">{{ $permissions->links() }}</div>
    @endif
</div>
@endsection
