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
    <title>Mutesa I Royal University - Reset Password</title>
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

        .info-box p {
            font-size: 12px;
            color: #64748b;
            line-height: 1.5;
            margin: 0;
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
            <img src="<?php echo e($ent && $ent->logo ? url('storage/' . $ent->logo) : asset('assets/8tech.png'), false); ?>" 
                 alt="MRU Logo" 
                 class="logo">
            <div class="institution-name">Mutesa I Royal University</div>
            <div class="portal-text">Academic Management Portal</div>
        </div>

        <h2>Reset Password</h2>
        <p class="subtitle">
            Enter your email address, phone number, or username and we'll send you a link to reset your password.
        </p>

        <?php if(session('status')): ?>
            <div class="alert alert-success">
                <?php echo e(session('status'), false); ?>

            </div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div class="alert alert-danger">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php echo e($error, false); ?><br>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <form id="resetForm" action="<?php echo e(url('auth/forgot-password'), false); ?>" method="POST">
            <?php echo csrf_field(); ?>
            
            <div class="form-group">
                <label>Email, Phone, or Username</label>
                <input type="text" 
                       name="identifier" 
                       class="form-input" 
                       placeholder="Enter your email, phone number, or username"
                       value="<?php echo e(old('identifier'), false); ?>"
                       required
                       autocomplete="username">
            </div>

            <div class="form-group">
                <label>Security Code</label>
                <div class="captcha-row">
                    <img src="<?php echo e(url('/auth/captcha'), false); ?>" 
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

            <button type="submit" class="submit-btn">Send Reset Link</button>
        </form>

        <div class="info-box">
            <h5>How it works</h5>
            <p>
                We'll search for your account using the information you provide. If found, we'll send a password reset link to your registered email address. The link will be valid for 60 minutes.
            </p>
        </div>

        <a href="<?php echo e(url('auth/login'), false); ?>" class="back-link">
            <i class='bx bx-arrow-back'></i> Back to Sign In
        </a>
    </div>

    <script>
        function refreshCaptcha() {
            const captchaImage = document.getElementById('captcha-image');
            const captchaInput = document.querySelector('input[name="captcha"]');
            
            if (captchaImage) {
                captchaImage.src = '<?php echo e(url("auth/captcha"), false); ?>?' + new Date().getTime();
            }
            
            if (captchaInput) {
                captchaInput.value = '';
                captchaInput.focus();
            }
        }

        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);

        window.addEventListener('load', function() {
            const firstInput = document.querySelector('input[name="identifier"]');
            if (firstInput) firstInput.focus();
        });
    </script>
</body>
</html><?php /**PATH /Applications/MAMP/htdocs/mru/resources/views/auth/forgot-password.blade.php ENDPATH**/ ?>