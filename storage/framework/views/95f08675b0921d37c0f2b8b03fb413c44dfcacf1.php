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
    <title>Mutesa I Royal University - Sign In</title>
    <meta name="csrf-token" content="<?php echo e(csrf_token(), false); ?>">
    <link rel="icon" href="<?php echo e($ent && $ent->logo ? url('storage/' . $ent->logo) : asset('assets/8tech.png'), false); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f5f5f5;
            background-image: 
                linear-gradient(rgba(30, 64, 175, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(30, 64, 175, 0.03) 1px, transparent 1px);
            background-size: 20px 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }

        .login-box {
            background: white;
            width: 100%;
            max-width: 420px;
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
            font-size: 12px;
            color: #64748b;
        }

        .title {
            font-size: 20px;
            font-weight: 600;
            color: #1e293b;
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #334155;
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
            gap: 8px;
            margin-bottom: 8px;
        }

        .captcha-img {
            border: 1px solid #e2e8f0;
            padding: 5px;
            background: white;
        }

        .captcha-btn {
            background: #1e40af;
            color: white;
            border: none;
            padding: 0 15px;
            font-size: 13px;
            cursor: pointer;
            font-weight: 500;
        }

        .captcha-btn:hover {
            background: #1e3a8a;
        }

        .help-text {
            font-size: 11px;
            color: #64748b;
            margin-top: 4px;
        }

        .btn-login {
            width: 100%;
            background: #1e40af;
            color: white;
            border: none;
            padding: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 5px;
        }

        .btn-login:hover {
            background: #1e3a8a;
        }

        .form-footer {
            display: flex;
            justify-content: space-between;
            margin-top: 12px;
        }

        .link {
            color: #1e40af;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
        }

        .link:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 10px 12px;
            margin-bottom: 15px;
            font-size: 13px;
            border-left: 3px solid;
        }

        .alert-success {
            background: #f0fdf4;
            border-color: #22c55e;
            color: #166534;
        }

        .alert-danger {
            background: #fef2f2;
            border-color: #ef4444;
            color: #991b1b;
        }

        @media (max-width: 480px) {
            .login-box { padding: 25px 20px; }
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

        <h1 class="title">Sign In</h1>

        <?php if(session('status')): ?>
            <div class="alert alert-success"><?php echo e(session('status'), false); ?></div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div class="alert alert-danger">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php echo e($error, false); ?><br><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo e(url('auth/login'), false); ?>" method="POST">
            <?php echo csrf_field(); ?>

            <div class="form-group">
                <label class="form-label">Email, Phone, or Username</label>
                <input type="text" 
                       name="username" 
                       class="form-input" 
                       placeholder="Enter your credentials"
                       value="<?php echo e(old('username'), false); ?>"
                       required>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" 
                       name="password" 
                       class="form-input" 
                       placeholder="Enter your password"
                       required>
            </div>

            <div class="form-group">
                <label class="form-label">Security Code</label>
                <div class="captcha-row">
                    <img src="<?php echo e(url('/auth/captcha'), false); ?>" 
                         id="captcha-image"
                         class="captcha-img">
                    <button type="button" 
                            onclick="refreshCaptcha()" 
                            class="captcha-btn">Refresh</button>
                </div>
                <input type="text" 
                       name="captcha" 
                       class="form-input" 
                       placeholder="Enter 4-digit code"
                       required>
                <div class="help-text">Enter the code shown above</div>
            </div>

            <button type="submit" class="btn-login">Sign In</button>

            <div class="form-footer">
                <a href="<?php echo e(url('auth/forgot-password'), false); ?>" class="link">Forgot Password?</a>
                <a href="<?php echo e(url('auth/support'), false); ?>" class="link">Need Help?</a>
            </div>
        </form>
    </div>

    <script>
        function refreshCaptcha() {
            document.getElementById('captcha-image').src = '<?php echo e(url("auth/captcha"), false); ?>?' + Date.now();
            document.querySelector('input[name="captcha"]').value = '';
        }

        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.transition = 'opacity 0.3s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);

        document.querySelector('input[name="username"]')?.focus();
    </script>
</body>
</html>
<?php /**PATH /Applications/MAMP/htdocs/mru/resources/views/auth/login.blade.php ENDPATH**/ ?>