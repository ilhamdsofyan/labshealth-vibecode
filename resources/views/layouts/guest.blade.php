<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4F46E5">
    <title>@yield('title', 'Login') — LabsHealth</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Inter', sans-serif; }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            border: none;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, #4F46E5, #7C3AED);
            padding: 2rem;
            text-align: center;
            color: white;
        }

        .login-header .icon-circle {
            width: 60px;
            height: 60px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 0.75rem;
        }

        .login-body {
            padding: 2rem;
        }

        .form-control {
            border-radius: 10px;
            padding: 0.65rem 1rem;
            border: 1.5px solid #E2E8F0;
        }

        .form-control:focus {
            border-color: #4F46E5;
            box-shadow: 0 0 0 3px rgba(79,70,229,0.15);
        }

        .btn-login {
            background: linear-gradient(135deg, #4F46E5, #7C3AED);
            border: none;
            border-radius: 10px;
            padding: 0.65rem;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #4338CA, #6D28D9);
        }

        .btn-google {
            border: 1.5px solid #E2E8F0;
            border-radius: 10px;
            padding: 0.6rem;
            font-weight: 500;
            color: #334155;
            background: white;
        }

        .btn-google:hover {
            background: #F8FAFC;
            border-color: #CBD5E1;
        }

        .divider {
            display: flex;
            align-items: center;
            color: #94A3B8;
            font-size: 0.82rem;
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #E2E8F0;
        }

        .divider span {
            padding: 0 1rem;
        }
    </style>
</head>
<body>
    <div class="container px-3">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
