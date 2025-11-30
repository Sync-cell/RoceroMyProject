
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Task Manager</title>
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

        .register-wrapper {
            width: 100%;
            max-width: 420px;
        }

        .register-container {
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
        input[type="password"],
        select {
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
        input[type="password"]:focus,
        select:focus {
            border-color: #667eea;
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        input[type="text"]::placeholder,
        input[type="password"]::placeholder {
            color: #bdc3c7;
        }

        select {
            cursor: pointer;
            color: #2c3e50;
        }

        select option {
            color: #2c3e50;
            background-color: #fff;
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

        .login-section {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
        }

        .login-text {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 12px;
        }

        .login-link {
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

        .login-link:hover {
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

        .form-group:nth-child(3) .input-icon::before {
            content: '👥';
        }

        input[type="text"],
        input[type="password"],
        select {
            padding-left: 40px;
        }

        .password-requirement {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 6px;
        }

        @media (max-width: 480px) {
            .register-container {
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

<div class="register-wrapper">
    <div class="register-container">
        <div class="logo-section">
            <div class="logo-icon">📝</div>
            <h2>Create Account</h2>
            <p class="subtitle">Join Task Manager today</p>
        </div>

        <div class="error-banner" id="errorBanner"></div>

        <form id="registerForm" action="<?= base_url('admin/store') ?>" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-icon">
                    <input type="text" id="username" name="username" placeholder="Choose a username" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-icon">
                    <input type="password" id="password" name="password" placeholder="Create a password" required>
                </div>
                <p class="password-requirement">Min. 6 characters required</p>
            </div>

            <div class="form-group">
                <label for="role">Account Type</label>
                <div class="input-icon">
                    <select id="role" name="role" required>
                        <option value="" disabled selected>Select account type</option>
                        <option value="user">👤 User</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="submit-btn">Create Account</button>
        </form>

        <div class="login-section">
            <p class="login-text">Already have an account?</p>
            <a href="<?= base_url('admin/login') ?>" class="login-link">Login Here</a>
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
            title: 'Registration Failed',
            text: '<?= addslashes(session()->getFlashdata('error')) ?>',
            confirmButtonColor: '#667eea',
            allowOutsideClick: false
        });
    <?php endif; ?>

    // Display success if session has one
    <?php if (session()->getFlashdata('success')): ?>
        Swal.fire({
            icon: 'success',
            title: 'Account Created!',
            text: '<?= addslashes(session()->getFlashdata('success')) ?>',
            confirmButtonColor: '#667eea',
            allowOutsideClick: false
        }).then(() => {
            window.location.href = '<?= base_url('admin/login') ?>';
        });
    <?php endif; ?>

    // Form submission with validation
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value.trim();
        const role = document.getElementById('role').value;

        if (!username) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Missing Username',
                text: 'Please enter a username',
                confirmButtonColor: '#667eea'
            });
            return false;
        }

        if (username.length < 3) {
            e.preventDefault();
            Swal.fire({
                icon: 'info',
                title: 'Username Too Short',
                text: 'Username must be at least 3 characters',
                confirmButtonColor: '#667eea'
            });
            return false;
        }

        if (!password) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Missing Password',
                text: 'Please enter a password',
                confirmButtonColor: '#667eea'
            });
            return false;
        }

        if (password.length < 6) {
            e.preventDefault();
            Swal.fire({
                icon: 'info',
                title: 'Password Too Short',
                text: 'Password must be at least 6 characters',
                confirmButtonColor: '#667eea'
            });
            return false;
        }

        if (!role) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Select Account Type',
                text: 'Please select an account type',
                confirmButtonColor: '#667eea'
            });
            return false;
        }

        // Show loading state
        Swal.fire({
            title: 'Creating account...',
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

    document.getElementById('role').addEventListener('focus', function() {
        this.parentElement.style.transform = 'scale(1.02)';
    });

    document.getElementById('role').addEventListener('blur', function() {
        this.parentElement.style.transform = 'scale(1)';
    });
</script>

</body>
</html>