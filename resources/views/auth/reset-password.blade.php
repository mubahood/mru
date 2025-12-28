<?php
use App\Models\Utils;
if (!isset($ent)) {
    $ent = Utils::ent();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mutesa I Royal University - Set New Password</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            background-image: 
                linear-gradient(rgba(30, 64, 175, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(30, 64, 175, 0.03) 1px, transparent 1px);
            background-size: 20px 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-box {
            background: white;
            max-width: 420px;
            width: 100%;
            padding: 35px 30px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-top: 3px solid #1e40af;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 25px;
        }

        .logo {
            width: 60px;
            height: 60px;
            object-fit: contain;
            margin-bottom: 10px;
        }

        .institution-name {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 3px;
        }

        .portal-text {
            font-size: 13px;
            color: #64748b;
        }

        h2 {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
            text-align: center;
        }

        .subtitle {
            font-size: 13px;
            color: #64748b;
            text-align: center;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #475569;
            margin-bottom: 5px;
        }

        .form-input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            font-size: 14px;
            color: #1e293b;
            transition: border-color 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: #1e40af;
        }

        .captcha-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        #captcha-image {
            border: 1px solid #e2e8f0;
            padding: 5px;
            background: white;
        }

        .refresh-btn {
            background: #1e40af;
            color: white;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .refresh-btn:hover {
            background: #1e3a8a;
        }

        .captcha-hint {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
        }

        .submit-btn {
            width: 100%;
            padding: 11px;
            background: #1e40af;
            color: white;
            border: none;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            margin-top: 5px;
        }

        .submit-btn:hover {
            background: #1e3a8a;
        }

        .info-box {
            background: #f8fafc;
            padding: 12px;
            margin: 15px 0;
            border-left: 3px solid #1e40af;
        }

        .info-box h5 {
            font-size: 13px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .info-box ul {
            margin: 0;
            padding-left: 18px;
        }

        .info-box li {
            font-size: 12px;
            color: #64748b;
            line-height: 1.5;
            margin-bottom: 3px;
        }

        .back-link {
            display: block;
            text-align: center;
            padding: 10px;
            color: #475569;
            text-decoration: none;
            font-size: 13px;
            border: 1px solid #e2e8f0;
            margin-top: 10px;
        }

        .back-link:hover {
            background: #f8fafc;
        }

        .alert {
            padding: 10px 12px;
            margin-bottom: 15px;
            font-size: 13px;
            border-left: 3px solid;
        }

        .alert-success {
            background: #dcfce7;
            border-color: #22c55e;
            color: #166534;
        }

        .alert-danger {
            background: #fee2e2;
            border-color: #ef4444;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="logo-section">
            <img src="{{ $ent && $ent->logo ? url('storage/' . $ent->logo) : asset('assets/8tech.png') }}" 
                 alt="MRU Logo" 
                 class="logo">
            <div class="institution-name">Mutesa I Royal University</div>
            <div class="portal-text">Academic Management Portal</div>
        </div>

        <h2>Set New Password</h2>
        <p class="subtitle">
            Enter your new password below. Make sure it's strong and secure.
        </p>

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
            </div>
        @endif

        <form id="resetForm" action="{{ $postAction }}" method="POST">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" 
                       name="email" 
                       class="form-input" 
                       placeholder="Enter your email address"
                       value="{{ request('email') ?? old('email') }}"
                       required
                       autocomplete="email">
            </div>

            <div class="form-group">
                <label>New Password</label>
                <input type="password" 
                       name="password" 
                       id="password"
                       class="form-input" 
                       placeholder="Enter your new password"
                       required
                       autocomplete="new-password">
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" 
                       name="password_confirmation" 
                       id="passwordConfirm"
                       class="form-input" 
                       placeholder="Confirm your new password"
                       required
                       autocomplete="new-password">
            </div>

            <div class="info-box">
                <h5>Password Requirements</h5>
                <ul>
                    <li>At least 6 characters long</li>
                    <li>Contains both uppercase and lowercase letters</li>
                    <li>Contains at least one number</li>
                    <li>Contains at least one special character</li>
                </ul>
            </div>

            <div class="form-group">
                <label>Security Code</label>
                <div class="captcha-row">
                    <img src="{{ url('/auth/captcha') }}" 
                         alt="CAPTCHA" 
                         id="captcha-image">
                    <button type="button" 
                            onclick="refreshCaptcha()" 
                            class="refresh-btn">
                        <i class='bx bx-refresh'></i> Refresh
                    </button>
                </div>
                <input type="text" 
                       name="captcha" 
                       class="form-input" 
                       placeholder="Enter the numbers shown above"
                       autocomplete="off"
                       required>
                <div class="captcha-hint">Please enter the 4-digit number shown in the image above.</div>
            </div>

            <button type="submit" class="submit-btn">Reset Password</button>
        </form>

        <a href="{{ url('auth/login') }}" class="back-link">
            <i class='bx bx-arrow-back'></i> Back to Sign In
        </a>
    </div>

    <script>
        function refreshCaptcha() {
            const captchaImage = document.getElementById('captcha-image');
            const captchaInput = document.querySelector('input[name="captcha"]');
            
            if (captchaImage) {
                captchaImage.src = '{{ url("auth/captcha") }}?' + new Date().getTime();
            }
            
            if (captchaInput) {
                captchaInput.value = '';
                captchaInput.focus();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const password = document.getElementById('password');
            const passwordConfirm = document.getElementById('passwordConfirm');
            
            function validatePasswords() {
                if (password.value && passwordConfirm.value && password.value !== passwordConfirm.value) {
                    passwordConfirm.style.borderColor = '#ef4444';
                } else {
                    passwordConfirm.style.borderColor = '#e2e8f0';
                }
            }

            if (password) password.addEventListener('input', validatePasswords);
            if (passwordConfirm) passwordConfirm.addEventListener('input', validatePasswords);

            const form = document.getElementById('resetForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (password.value !== passwordConfirm.value) {
                        e.preventDefault();
                        alert('Passwords do not match!');
                        return false;
                    }
                });
            }
        });

        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);

        window.addEventListener('load', function() {
            const firstInput = document.querySelector('input[name="email"]');
            if (firstInput) firstInput.focus();
        });
    </script>
</body>
</html>