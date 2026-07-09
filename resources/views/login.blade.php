@extends('layouts.guest')

@section('title', 'Login - SIPTP')

@push('styles')
    <style>
        :root {
            --login-primary: #435ebe;
            --login-primary-light: #5a73d6;
            --login-primary-dark: #3548a0;
            --login-accent: #9694ff;
            --login-surface: rgba(255, 255, 255, 0.08);
            --login-surface-solid: rgba(255, 255, 255, 0.95);
            --login-text: #25396f;
            --login-text-muted: #7c8db5;
            --login-border: rgba(67, 94, 190, 0.15);
            --login-shadow: 0 8px 32px rgba(67, 94, 190, 0.18);
            --login-radius: 1.25rem;
        }

        *,
        *::before,
        *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
        }

        body {
            font-family: 'Nunito', sans-serif;
            overflow: hidden;
        }

        /* ====== ANIMATED GRADIENT BACKGROUND ====== */
        .login-page {
            min-height: 100vh;
            display: flex;
            position: relative;
            background: linear-gradient(135deg, #667eea 0%, #435ebe 25%, #764ba2 50%, #9694ff 75%, #667eea 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Floating orbs background */
        .login-page::before,
        .login-page::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.3;
            pointer-events: none;
        }

        .login-page::before {
            width: 500px;
            height: 500px;
            background: #9694ff;
            top: -100px;
            right: -100px;
            animation: floatOrb1 8s ease-in-out infinite;
        }

        .login-page::after {
            width: 400px;
            height: 400px;
            background: #57caeb;
            bottom: -80px;
            left: -80px;
            animation: floatOrb2 10s ease-in-out infinite;
        }

        @keyframes floatOrb1 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(-60px, 60px) scale(1.1); }
        }

        @keyframes floatOrb2 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(50px, -50px) scale(1.15); }
        }

        .login-left {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3rem;
            position: relative;
            z-index: 2;
            color: #fff;
            text-align: center;
        }

        .brand-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            color: #fff;
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.25);
            animation: fadeInUp 0.8s ease forwards;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .brand-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.75rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
            animation: fadeInUp 0.8s ease 0.1s forwards;
            opacity: 0;
        }

        .brand-subtitle {
            font-size: 1.1rem;
            font-weight: 400;
            opacity: 0;
            max-width: 380px;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.85);
            animation: fadeInUp 0.8s ease 0.2s forwards;
        }

        .brand-features {
            margin-top: 3rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            opacity: 0;
            animation: fadeInUp 0.8s ease 0.3s forwards;
        }

        .brand-feature {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .brand-feature i {
            width: 36px;
            height: 36px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 0.6rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .login-right {
            width: 520px;
            min-width: 520px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            position: relative;
            z-index: 2;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: var(--login-surface-solid);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: var(--login-radius);
            padding: 2.75rem 2.5rem;
            box-shadow: var(--login-shadow);
            border: 1px solid rgba(255, 255, 255, 0.6);
            animation: slideInRight 0.7s ease forwards;
        }

        .login-card-header {
            text-align: center;
            margin-bottom: 2.25rem;
        }

        .login-card-header .greeting {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--login-primary);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 0.5rem;
        }

        .login-card-header h1 {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--login-text);
            margin-bottom: 0.5rem;
        }

        .login-card-header p {
            font-size: 0.9rem;
            color: var(--login-text-muted);
            line-height: 1.5;
        }

        .form-floating-custom {
            position: relative;
            margin-bottom: 1.25rem;
        }

        .form-floating-custom .form-icon {
            position: absolute;
            left: 1.1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--login-text-muted);
            font-size: 1.1rem;
            transition: color 0.3s ease;
            z-index: 3;
            pointer-events: none;
        }

        .form-floating-custom .form-control {
            height: 54px;
            padding: 0.75rem 1rem 0.75rem 3rem;
            border: 2px solid var(--login-border);
            border-radius: 0.85rem;
            font-size: 0.95rem;
            font-family: 'Nunito', sans-serif;
            font-weight: 600;
            color: var(--login-text);
            background: #fff;
            transition: all 0.3s ease;
        }

        .form-floating-custom .form-control::placeholder {
            color: var(--login-text-muted);
            font-weight: 500;
        }

        .form-floating-custom .form-control:focus {
            border-color: var(--login-primary);
            box-shadow: 0 0 0 4px rgba(67, 94, 190, 0.1);
            outline: none;
        }

        .form-floating-custom .form-control:focus ~ .form-icon {
            color: var(--login-primary);
        }

        /* Password toggle */
        .password-toggle {
            position: absolute;
            right: 1.1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--login-text-muted);
            cursor: pointer;
            padding: 0.25rem;
            font-size: 1.1rem;
            transition: color 0.3s ease;
            z-index: 3;
        }

        .password-toggle:hover {
            color: var(--login-primary);
        }

        /* ====== CHECKBOX ====== */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.75rem;
        }

        .form-check-custom {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-check-custom input[type="checkbox"] {
            width: 18px;
            height: 18px;
            border: 2px solid var(--login-border);
            border-radius: 0.35rem;
            cursor: pointer;
            accent-color: var(--login-primary);
            transition: all 0.2s ease;
        }

        .form-check-custom label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--login-text-muted);
            cursor: pointer;
            user-select: none;
        }

        .forgot-link {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--login-primary);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .forgot-link:hover {
            color: var(--login-primary-dark);
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            height: 54px;
            border: none;
            border-radius: 0.85rem;
            font-size: 1rem;
            font-weight: 700;
            font-family: 'Nunito', sans-serif;
            color: #fff;
            background: linear-gradient(135deg, var(--login-primary), var(--login-accent));
            background-size: 200% 200%;
            cursor: pointer;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            letter-spacing: 0.02em;
            box-shadow: 0 4px 15px rgba(67, 94, 190, 0.35);
        }

        .btn-login:hover {
            background-position: 100% 50%;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(67, 94, 190, 0.45);
        }

        .btn-login:active {
            transform: translateY(0);
            box-shadow: 0 4px 15px rgba(67, 94, 190, 0.35);
        }

        .btn-login::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-login:hover::after {
            left: 100%;
        }

        .login-divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .login-divider::before,
        .login-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--login-border);
        }

        .login-divider span {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--login-text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.85rem;
            color: var(--login-text-muted);
        }

        .login-footer a {
            color: var(--login-primary);
            text-decoration: none;
            font-weight: 700;
            transition: color 0.3s ease;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        /* ====== ANIMATIONS ====== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(25px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(40px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* ====== RESPONSIVE ====== */
        @media (max-width: 991.98px) {
            .login-left {
                display: none;
            }

            .login-right {
                width: 100%;
                min-width: 100%;
                min-height: 100vh;
                padding: 2rem 1.5rem;
            }

            .login-card {
                max-width: 440px;
                padding: 2.5rem 2rem;
                animation: fadeInUp 0.7s ease forwards;
            }
        }

        @media (max-width: 575.98px) {
            .login-right {
                padding: 1.5rem 1rem;
                align-items: flex-start;
                padding-top: 3rem;
            }

            .login-card {
                padding: 2rem 1.5rem;
                border-radius: 1rem;
            }

            .login-card-header h1 {
                font-size: 1.5rem;
            }

            .form-floating-custom .form-control {
                height: 50px;
                font-size: 0.9rem;
            }

            .btn-login {
                height: 50px;
                font-size: 0.95rem;
            }

            .form-options {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .brand-icon-mobile {
                display: flex !important;
            }
        }

        /* Mobile brand icon (hidden on desktop) */
        .brand-icon-mobile {
            display: none;
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--login-primary), var(--login-accent));
            border-radius: 1rem;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #fff;
            margin: 0 auto 1.5rem;
            box-shadow: 0 4px 15px rgba(67, 94, 190, 0.35);
        }

        @media (max-width: 991.98px) {
            .brand-icon-mobile {
                display: flex;
            }
        }

        /* ====== INPUT GROUP ANIMATION ====== */
        .input-group-animated {
            opacity: 0;
            animation: fadeInUp 0.5s ease forwards;
        }

        .input-group-animated:nth-child(1) { animation-delay: 0.1s; }
        .input-group-animated:nth-child(2) { animation-delay: 0.2s; }
        .input-group-animated:nth-child(3) { animation-delay: 0.3s; }
        .input-group-animated:nth-child(4) { animation-delay: 0.4s; }
        .input-group-animated:nth-child(5) { animation-delay: 0.5s; }
    </style>
@endpush

@section('content')
<div class="login-page">
    <div class="login-left">
        <div class="brand-icon">
            <i class="bi bi-wallet2"></i>
        </div>
        <h2 class="brand-title">SIPTP</h2>
        <p class="brand-subtitle">
            Sistem Informasi Pengajuan Transaksi Pengeluaran
        </p>

        <div class="brand-features">
            <div class="brand-feature">
                <i class="bi bi-lightning-charge-fill"></i>
                <span>Proses approval yang cepat & transparan</span>
            </div>
            <div class="brand-feature">
                <i class="bi bi-graph-up-arrow"></i>
                <span>Pantau status pengajuan</span>
            </div>
        </div>
    </div>

    <div class="login-right">
        <div class="login-card">
            {{-- Mobile brand icon --}}
            <div class="brand-icon-mobile">
                <i class="bi bi-wallet2"></i>
            </div>

            <div class="login-card-header">
                <div class="greeting">Selamat Datang</div>
                <h1>Masuk ke Akun Anda</h1>
                <p>Masukkan kredensial Anda untuk mengakses SIPTP.</p>
            </div>

            <form id="loginForm">
                <div class="form-floating-custom input-group-animated">
                    <i class="bi bi-person-fill form-icon"></i>
                    <input
                        type="text"
                        class="form-control"
                        id="username"
                        name="username"
                        placeholder="Email atau Username"
                        required
                        autocomplete="username"
                    >
                </div>

                <div class="form-floating-custom input-group-animated">
                    <i class="bi bi-lock-fill form-icon"></i>
                    <input
                        type="password"
                        class="form-control"
                        id="password"
                        name="password"
                        placeholder="Password"
                        required
                        autocomplete="current-password"
                    >
                    <button type="button" class="password-toggle" id="togglePassword" aria-label="Toggle password visibility">
                        <i class="bi bi-eye-fill"></i>
                    </button>
                </div>


                <div class="input-group-animated">
                    <button type="submit" class="btn-login" id="btnLogin">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
                    </button>
                </div>
            </form>

            <div class="login-footer input-group-animated">
                &copy; {{ date('Y') }} SIPTP. All rights reserved.
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Password visibility toggle
        $('#togglePassword').on('click', function () {
            let passwordInput = $('#password');
            let icon = $(this).find('i');

            if (passwordInput.attr('type') === 'password') {
                passwordInput.attr('type', 'text');
                icon.removeClass('bi-eye-fill').addClass('bi-eye-slash-fill');
            } else {
                passwordInput.attr('type', 'password');
                icon.removeClass('bi-eye-slash-fill').addClass('bi-eye-fill');
            }
        });

        // Form submit via AJAX
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();

            let formData = {
                username: $('#username').val(),
                password: $('#password').val()
            };


            ajaxRequest({
                url: '{{ url('/login') }}',
                method: 'POST',
                data: formData,
                confirmBefore : false,
                successCallback: function(response) {
                    if (response.data && response.data.redirect) {
                        window.location.href = response.data.redirect;
                    }
                }
            });
        });
    });
</script>
@endpush
