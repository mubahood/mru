<?php
use App\Models\Utils;
if (!isset($company)) {
    $company = Utils::company();
}
$ent = Utils::ent();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $company->name ?? 'Mutesa I Royal University' }} - Sign In</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ $company && $company->logo ? Utils::img_url($company->logo) : Utils::get_logo() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            top: -200px;
            right: -200px;
            border-radius: 50%;
        }

        body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
            bottom: -150px;
            left: -150px;
            border-radius: 50%;
        }

        .login-container {
            background: white;
            border-radius: 24px;
            box-shadow: var(--shadow-lg);
            max-width: 1100px;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            overflow: hidden;
            position: relative;
            z-index: 1;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Left Side - Branding */
        .brand-side {
            background: linear-gradient(135deg, var(--mru-blue) 0%, #1e3a8a 100%);
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .brand-side::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(245,158,11,0.2) 0%, transparent 70%);
            top: -100px;
            right: -100px;
            border-radius: 50%;
        }

        .brand-side::after {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            bottom: -50px;
            left: -50px;
            border-radius: 50%;
        }

        .brand-header {
            position: relative;
            z-index: 1;
        }

        .brand-logo-container {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 30px;
        }

        .brand-logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
            background: white;
            padding: 10px;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        .brand-text h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 26px;
            font-weight: 700;
            margin: 0;
            line-height: 1.2;
        }

        .brand-text p {
            font-size: 14px;
            opacity: 0.9;
            margin: 4px 0 0 0;
        }

        .brand-content {
            position: relative;
            z-index: 1;
        }

        .brand-title {
            font-family: 'Poppins', sans-serif;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.3;
        }

        .brand-description {
            font-size: 16px;
            line-height: 1.6;
            opacity: 0.95;
            margin-bottom: 30px;
        }

        .brand-features {
            list-style: none;
            padding: 0;
        }

        .brand-features li {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            font-size: 15px;
        }

        .brand-features li i {
            font-size: 20px;
            color: var(--mru-gold);
        }

        /* Right Side - Form */
        .form-side {
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-header {
            margin-bottom: 40px;
        }

        .form-title {
            font-family: 'Poppins', sans-serif;
            font-size: 32px;
            font-weight: 700;
            color: var(--mru-dark);
            margin-bottom: 8px;
        }

        .form-subtitle {
            color: #64748b;
            font-size: 15px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: var(--mru-dark);
            margin-bottom: 8px;
            font-size: 14px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 20px;
            z-index: 1;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 2px solid var(--border);
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--mru-accent);
            background: white;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .captcha-container {
            display: flex;
            gap: 12px;
            margin-bottom: 12px;
            align-items: center;
        }

        .captcha-image {
            border: 2px solid var(--border);
            border-radius: 8px;
            padding: 8px;
            background: white;
        }

        .captcha-refresh {
            background: var(--mru-accent);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .captcha-refresh:hover {
            background: var(--mru-blue);
            transform: translateY(-2px);
        }

        .captcha-help {
            font-size: 13px;
            color: #64748b;
            margin-top: 6px;
        }

        .btn-signin {
            width: 100%;
            background: linear-gradient(135deg, var(--mru-blue) 0%, #1e3a8a 100%);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-signin:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(30, 64, 175, 0.3);
        }

        .form-links {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .auth-link {
            color: var(--mru-accent);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: color 0.3s ease;
        }

        .auth-link:hover {
            color: var(--mru-blue);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 32px 0;
            color: #94a3b8;
            font-size: 14px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .divider::before {
            margin-right: 16px;
        }

        .divider::after {
            margin-left: 16px;
        }

        .btn-register {
            width: 100%;
            background: white;
            color: var(--mru-blue);
            border: 2px solid var(--mru-blue);
            padding: 14px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-register:hover {
            background: var(--mru-blue);
            color: white;
            transform: translateY(-2px);
        }

        .alert {
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid var(--success);
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid var(--error);
        }

        .support-footer {
            margin-top: 30px;
            padding-top: 24px;
            border-top: 1px solid var(--border);
            text-align: center;
        }

        .support-title {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 12px;
        }

        .support-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .support-link {
            color: var(--mru-accent);
            text-decoration: none;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: color 0.3s ease;
        }

        .support-link:hover {
            color: var(--mru-blue);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .login-container {
                grid-template-columns: 1fr;
                max-width: 500px;
            }

            .brand-side {
                padding: 40px 30px;
            }

            .brand-content {
                display: none;
            }

            .form-side {
                padding: 40px 30px;
            }
        }

        @media (max-width: 576px) {
            body {
                padding: 10px;
            }

            .brand-side,
            .form-side {
                padding: 30px 20px;
            }

            .brand-logo {
                width: 60px;
                height: 60px;
            }

            .brand-text h1 {
                font-size: 20px;
            }

            .form-title {
                font-size: 26px;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <!-- Left Side - Branding -->
        <div class="brand-side">
            <div class="brand-header">
                <div class="brand-logo-container">
                    <img src="{{ $company && $company->logo ? Utils::img_url($company->logo) : Utils::get_logo() }}" 
                         alt="MRU Logo" 
                         class="brand-logo">
                    <div class="brand-text">
                        <h1>{{ $company->name ?? 'Mutesa I Royal University' }}</h1>
                        <p>Academic Management Portal</p>
                    </div>
                </div>
            </div>

            <div class="brand-content">
                <h2 class="brand-title">Welcome to Your Academic Journey</h2>
                <p class="brand-description">
                    Access comprehensive tools for managing academic records, student information, and institutional operations.
                </p>
                <ul class="brand-features">
                    <li>
                        <i class='bx bx-check-circle'></i>
                        <span>Secure & Reliable Access</span>
                    </li>
                    <li>
                        <i class='bx bx-check-circle'></i>
                        <span>Real-time Academic Data</span>
                    </li>
                    <li>
                        <i class='bx bx-check-circle'></i>
                        <span>Comprehensive Management Tools</span>
                    </li>
                    <li>
                        <i class='bx bx-check-circle'></i>
                        <span>24/7 Technical Support</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Right Side - Form -->
        <div class="form-side">
            <div class="form-header">
                <h2 class="form-title">Sign In</h2>
                <p class="form-subtitle">Enter your credentials to access your account</p>
            </div>

            <!-- Alerts -->
            @if (session('status'))
                <div class="alert alert-success">
                    <i class='bx bx-check-circle' style="font-size: 20px;"></i>
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <i class='bx bx-error-circle' style="font-size: 20px;"></i>
                    <div>
                        @foreach ($errors->all() as $error)
                            {{ $error }}<br>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Login Form -->
            <form action="{{ url('auth/login') }}" method="POST" id="loginForm">
                @csrf

                <div class="form-group">
                    <label class="form-label">Email, Phone, or Username</label>
                    <div class="input-wrapper">
                        <i class='bx bx-user input-icon'></i>
                        <input type="text" 
                               name="username" 
                               class="form-input" 
                               placeholder="Enter your credentials"
                               value="{{ old('username') }}"
                               required 
                               autocomplete="username">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-wrapper">
                        <i class='bx bx-lock-alt input-icon'></i>
                        <input type="password" 
                               name="password" 
                               class="form-input" 
                               placeholder="Enter your password"
                               required 
                               autocomplete="current-password">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Security Code</label>
                    <div class="captcha-container">
                        <img src="{{ url('/auth/captcha') }}" 
                             alt="Security Code" 
                             id="captcha-image"
                             class="captcha-image">
                        <button type="button" 
                                onclick="refreshCaptcha()" 
                                class="captcha-refresh">
                            <i class='bx bx-refresh'></i> Refresh
                        </button>
                    </div>
                    <div class="input-wrapper">
                        <i class='bx bx-shield input-icon'></i>
                        <input type="text" 
                               name="captcha" 
                               class="form-input" 
                               placeholder="Enter the 4-digit code"
                               autocomplete="off"
                               required>
                    </div>
                    <p class="captcha-help">Enter the 4-digit number shown above for security verification</p>
                </div>

                <button type="submit" class="btn-signin">
                    <i class='bx bx-log-in-circle' style="font-size: 20px;"></i>
                    Sign In to Dashboard
                </button>

                <div class="form-links">
                    <a href="{{ url('auth/forgot-password') }}" class="auth-link">
                        <i class='bx bx-key'></i> Forgot Password?
                    </a>
                    <a href="{{ url('auth/support') }}" class="auth-link">
                        <i class='bx bx-help-circle'></i> Need Help?
                    </a>
                </div>
            </form>

            <!-- Registration -->
            <div class="divider">New Institution?</div>
            <a href="{{ route('onboarding.step1') }}" class="btn-register">
                <i class='bx bx-plus-circle' style="font-size: 20px;"></i>
                Register Your School
            </a>

            <!-- Support -->
            <div class="support-footer">
                <p class="support-title">Need Assistance?</p>
                <div class="support-links">
                    <a href="tel:{{ $company && $company->phone ? $company->phone : '+256123456789' }}" class="support-link">
                        <i class='bx bx-phone'></i> {{ $company && $company->phone ? $company->phone : '+256 123 456 789' }}
                    </a>
                    <a href="mailto:{{ $company && $company->email ? $company->email : 'support@mru.ac.ug' }}" class="support-link">
                        <i class='bx bx-envelope'></i> Email Support
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function refreshCaptcha() {
            document.getElementById('captcha-image').src = '{{ url("auth/captcha") }}?' + Date.now();
            document.querySelector('input[name="captcha"]').value = '';
        }

        // Auto-hide alerts
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);

        // Focus first input
        window.onload = () => {
            document.querySelector('input[name="username"]')?.focus();
        };
    </script>
</body>
</html>
