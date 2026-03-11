@extends('layouts.guest')

@section('title', 'Login')

@section('content')
<div class="login-card card mx-auto">
    <div class="login-header">
        <img src="{{ asset('assets/img/Logo Labschool Bintaro.png') }}" alt="LabsHealth UKS" class="login-logo-long">
        <img src="{{ asset('assets/img/Logo.png') }}" alt="LabsHealth UKS" class="login-logo-square">
        <h4 class="mb-1 fw-bold">LabsHealth UKS</h4>
        <p class="mb-0 opacity-75" style="font-size:0.88rem;">Sistem Pencatatan Kunjungan UKS</p>
    </div>

    <div class="login-body">
        @if(session('error'))
            <div class="alert alert-danger py-2 small">
                <i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger py-2 small">
                <i class="bi bi-exclamation-triangle me-1"></i>{{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0" style="border-radius:10px 0 0 10px;">
                        <i class="bi bi-envelope text-muted"></i>
                    </span>
                    <input type="email" name="email" class="form-control border-start-0"
                           value="{{ old('email') }}" placeholder="email@example.com" required autofocus
                           style="border-radius:0 10px 10px 0;">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0" style="border-radius:10px 0 0 10px;">
                        <i class="bi bi-lock text-muted"></i>
                    </span>
                    <input type="password" name="password" class="form-control border-start-0"
                           placeholder="••••••••" required
                           style="border-radius:0 10px 10px 0;">
                </div>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" name="remember" class="form-check-input" id="remember"
                       {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label small" for="remember">Ingat saya</label>
            </div>

            <button type="submit" class="btn btn-login btn-primary w-100 mb-3">
                <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
            </button>
        </form>

        @if(config('services.google.client_id'))
            <div class="divider mb-3">
                <span>atau</span>
            </div>

            <a href="{{ route('auth.google') }}" class="btn btn-google w-100">
                <svg width="18" height="18" viewBox="0 0 48 48" class="me-2">
                    <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"/>
                    <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"/>
                    <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"/>
                    <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"/>
                </svg>
                Masuk dengan Google
            </a>
        @endif
    </div>
</div>
@endsection
