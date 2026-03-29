@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Profil Saya</h4>
        <p class="text-muted mb-0 small">Kelola identitas akun, avatar, dan password Anda sendiri.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">Ringkasan Akun</div>
            <div class="card-body text-center">
                @if($user->avatar)
                    <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="rounded-circle border shadow-sm mx-auto mb-3" width="112" height="112" style="object-fit: cover;">
                @else
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 112px; height: 112px; background: linear-gradient(135deg, var(--accent), var(--primary)); color: #111827; font-size: 2rem; font-weight: 800;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif

                <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                <div class="text-muted small mb-3">{{ $user->email }}</div>

                <div class="small text-muted">Status akun</div>
                <div class="fw-semibold mb-3">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</div>

                @if($user->avatar)
                    <form method="POST" action="{{ route('profile.avatar.remove') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">Hapus Avatar</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card mb-4">
            <div class="card-header">Profil Akun</div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nama</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Avatar</label>
                            <input type="file" name="avatar" class="form-control @error('avatar') is-invalid @enderror" accept=".png,.jpg,.jpeg,.webp">
                            <div class="form-text">Maksimal 2MB. Format PNG, JPG, JPEG, atau WEBP.</div>
                            @error('avatar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-4">Simpan Profil</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Ganti Password</div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.password.update') }}">
                    @csrf
                    @method('PUT')

                    @if(filled($user->password))
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Password Saat Ini</label>
                            <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror">
                            @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Password Baru</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-4">Perbarui Password</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
