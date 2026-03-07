@extends('layouts.app')

@section('title', 'Kelola Roles')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Kelola Roles</h4>
        <p class="text-muted mb-0 small">Manajemen role dan permission</p>
    </div>
    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
        <i class="bi bi-shield-plus me-1"></i>Tambah Role
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Role</th>
                        <th>Slug</th>
                        <th>Deskripsi</th>
                        <th class="text-center">Users</th>
                        <th class="text-center">Permissions</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr>
                            <td class="fw-medium">{{ $role->display_name }}</td>
                            <td><code class="small">{{ $role->name }}</code></td>
                            <td class="small text-muted">{{ $role->description ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-info bg-opacity-10 text-info">{{ $role->users_count }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary bg-opacity-10 text-primary">{{ $role->permissions_count }}</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-outline-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($role->name !== 'superadmin')
                                        <form action="{{ route('admin.roles.destroy', $role) }}" method="POST"
                                              onsubmit="return confirm('Yakin hapus role ini?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Tidak ada data role</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
