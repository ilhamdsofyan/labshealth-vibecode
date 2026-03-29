@extends('layouts.guest')

@section('title', 'Forgot Password')

@section('content')
<div class="login-card card mx-auto">
    <div class="login-header">
        <img src="{{ asset('assets/img/Logo Labschool Bintaro.png') }}" alt="LabsHealth UKS" class="login-logo-long">
        <img src="{{ asset('assets/img/Logo.png') }}" alt="LabsHealth UKS" class="login-logo-square">
        <h4 class="mb-1 fw-bold">Reset Password</h4>
        <p class="mb-0 opacity-75" style="font-size:0.88rem;">Masukkan email akun Anda untuk menerima link reset password.</p>
    </div>

    <div class="login-body">
        @if(session('success'))
            <div class="alert alert-success py-2 small">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="email@example.com" required autofocus>
            </div>

            <button type="submit" class="btn btn-login btn-primary w-100 mb-3">
                Kirim Link Reset
            </button>
        </form>

        <a href="{{ route('login') }}" class="btn btn-google w-100 text-center">
            Kembali ke Login
        </a>
    </div>
</div>
@endsection
