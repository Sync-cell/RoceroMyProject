
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Task Manager</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            height: 100vh;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 420px;
        }

        .login-container {
            background-color: #ffffff;
            padding: 50px 40px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.5s ease-out;
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

        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-icon {
            font-size: 48px;
            margin-bottom: 12px;
        }

        h2 {
            color: #2c3e50;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #7f8c8d;
            font-size: 14px;
            font-weight: 400;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            color: #2c3e50;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #667eea;
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        input[type="text"]::placeholder,
        input[type="password"]::placeholder {
            color: #bdc3c7;
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 24px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 30px 0;
            color: #bdc3c7;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background-color: #ecf0f1;
        }

        .divider span {
            margin: 0 12px;
            font-size: 14px;
        }

        .register-section {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
        }

        .register-text {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 12px;
        }

        .register-link {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .register-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .error-banner {
            display: none;
            background-color: #fadbd8;
            color: #c0392b;
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #c0392b;
            font-size: 14px;
            animation: slideDown 0.3s ease-out;
        }

        .error-banner.show {
            display: block;
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

        .input-icon {
            position: relative;
        }

        .input-icon::before {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
        }

        .form-group:nth-child(1) .input-icon::before {
            content: '👤';
        }

        .form-group:nth-child(2) .input-icon::before {
            content: '🔒';
        }

        input[type="text"],
        input[type="password"] {
            padding-left: 40px;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 40px 25px;
            }

            h2 {
                font-size: 24px;
            }

            .logo-icon {
                font-size: 40px;
            }
        }
    </style>

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>

<div class="login-wrapper">
    <div class="login-container">
        <div class="logo-section">
            <div class="logo-icon">📋</div>
            <h2>Task Manager</h2>
            <p class="subtitle">Sign in to your account</p>
        </div>

        <div class="error-banner" id="errorBanner"></div>

        <form id="loginForm" action="<?= base_url('admin/authenticate') ?>" method="post">
           <?= csrf_field() ?>
           <div>
        <div class="form-group">
                <label for="username">Username</label>
                <div class="input-icon">
                    <input type="text" id="username" name="username" placeholder="Enter your username" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-icon">
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
            </div>

            <button type="submit" class="submit-btn">Login</button>
           </div></form>

        <div class="register-section">
            <p class="register-text">Don't have an account?</p>
            <a href="<?= base_url('admin/register') ?>" class="register-link">Create New Account</a>
        </div>
    </div>
</div>

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Display error if session has one
    <?php if (session()->getFlashdata('error')): ?>
        Swal.fire({
            icon: 'error',
            title: 'Login Failed',
            text: '<?= addslashes(session()->getFlashdata('error')) ?>',
            confirmButtonColor: '#667eea',
            allowOutsideClick: false
        });
    <?php endif; ?>

    // Form submission with validation
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value.trim();

        if (!username) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Missing Username',
                text: 'Please enter your username',
                confirmButtonColor: '#667eea'
            });
            return false;
        }

        if (!password) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Missing Password',
                text: 'Please enter your password',
                confirmButtonColor: '#667eea'
            });
            return false;
        }

        if (password.length < 6) {
            e.preventDefault();
            Swal.fire({
                icon: 'info',
                title: 'Invalid Password',
                text: 'Password must be at least 6 characters',
                confirmButtonColor: '#667eea'
            });
            return false;
        }

        // Show loading state
        Swal.fire({
            title: 'Logging in...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    });

    // Focus effects
    document.getElementById('username').addEventListener('focus', function() {
        this.parentElement.style.transform = 'scale(1.02)';
    });

    document.getElementById('username').addEventListener('blur', function() {
        this.parentElement.style.transform = 'scale(1)';
    });

    document.getElementById('password').addEventListener('focus', function() {
        this.parentElement.style.transform = 'scale(1.02)';
    });

    document.getElementById('password').addEventListener('blur', function() {
        this.parentElement.style.transform = 'scale(1)';
    });
</script>

</body>
</html>