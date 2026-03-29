@extends('layouts.guest')

@section('title', 'Reset Password')

@section('content')
<div class="login-card card mx-auto">
    <div class="login-header">
        <img src="{{ asset('assets/img/Logo Labschool Bintaro.png') }}" alt="LabsHealth UKS" class="login-logo-long">
        <img src="{{ asset('assets/img/Logo.png') }}" alt="LabsHealth UKS" class="login-logo-square">
        <h4 class="mb-1 fw-bold">Atur Password Baru</h4>
        <p class="mb-0 opacity-75" style="font-size:0.88rem;">Gunakan password baru yang kuat dan mudah Anda ingat.</p>
    </div>

    <div class="login-body">
        @if($errors->any())
            <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $email) }}" required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">Password Baru</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-login btn-primary w-100 mb-3">
                Simpan Password Baru
            </button>
        </form>

        <a href="{{ route('login') }}" class="btn btn-google w-100 text-center">
            Kembali ke Login
        </a>
    </div>
</div>
@endsection
