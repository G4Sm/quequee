<?php
    session_start();
    require "../koneksi.php";
    
    // Process login BEFORE any HTML output
    if (isset($_POST['loginbtn'])) {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $MAX_ATTEMPTS = 3;
        $LOCK_SECONDS = 15 * 60;

        $stmt = mysqli_prepare($con, "SELECT id, username, password, failed_attempts, locked_until FROM users WHERE username = ? LIMIT 1");
        if (!$stmt) {
            $error_message = "Terjadi kesalahan server. Coba lagi nanti.";
        } else {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) !== 1) {
                $error_message = "Username atau password salah!";
                mysqli_stmt_close($stmt);
            } else {
                mysqli_stmt_bind_result($stmt, $id, $db_username, $db_password, $failed_attempts, $locked_until);
                mysqli_stmt_fetch($stmt);
                mysqli_stmt_close($stmt);

                $now = time();
                $locked_until_ts = $locked_until ? strtotime($locked_until) : 0;
                if ($locked_until_ts && $locked_until_ts > $now) {
                    $sec_left = $locked_until_ts - $now;
                    $min_left = ceil($sec_left / 60);
                    $error_message = "Akun dikunci sementara. Coba lagi setelah " . $min_left . " menit.";
                } elseif (password_verify($password, $db_password)) {
                    $upd = mysqli_prepare($con, "UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = ?");
                    if ($upd) {
                        mysqli_stmt_bind_param($upd, "i", $id);
                        mysqli_stmt_execute($upd);
                        mysqli_stmt_close($upd);
                    }
                    session_regenerate_id(true);
                    $_SESSION['username'] = $db_username;
                    $_SESSION['login'] = true;
                    header("Location: ../adminpanel");
                    exit;
                } else {
                    $failed_attempts = (int)$failed_attempts + 1;
                    if ($failed_attempts >= $MAX_ATTEMPTS) {
                        $new_locked_until = date('Y-m-d H:i:s', $now + $LOCK_SECONDS);
                        $upd = mysqli_prepare($con, "UPDATE users SET failed_attempts = 0, locked_until = ? WHERE id = ?");
                        if ($upd) {
                            mysqli_stmt_bind_param($upd, "si", $new_locked_until, $id);
                            mysqli_stmt_execute($upd);
                            mysqli_stmt_close($upd);
                        }
                        $error_message = "Akun dikunci sementara. Coba lagi setelah " . ceil($LOCK_SECONDS/60) . " menit.";
                    } else {
                        $upd = mysqli_prepare($con, "UPDATE users SET failed_attempts = ? WHERE id = ?");
                        if ($upd) {
                            mysqli_stmt_bind_param($upd, "ii", $failed_attempts, $id);
                            mysqli_stmt_execute($upd);
                            mysqli_stmt_close($upd);
                        }
                        $error_message = "Username atau password salah!";
                    }
                }
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Rumah Que Que Admin</title>
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0a1f0f;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
        }

        /* Animated Background */
        .background-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
        }

        /* Light rays */
        .light-ray {
            position: absolute;
            width: 2px;
            height: 200%;
            background: linear-gradient(180deg, transparent, rgba(255, 140, 66, 0.1), transparent);
            animation: rayMove 8s linear infinite;
            opacity: 0.3;
        }

        .light-ray:nth-child(1) { left: 10%; animation-delay: 0s; }
        .light-ray:nth-child(2) { left: 25%; animation-delay: 2s; height: 150%; }
        .light-ray:nth-child(3) { left: 45%; animation-delay: 4s; }
        .light-ray:nth-child(4) { left: 65%; animation-delay: 1s; height: 180%; }
        .light-ray:nth-child(5) { left: 85%; animation-delay: 3s; }

        @keyframes rayMove {
            0% { transform: translateY(-100%) rotate(10deg); }
            100% { transform: translateY(100%) rotate(10deg); }
        }

        /* Floating shapes */
        .shape {
            position: absolute;
            opacity: 0.05;
            animation: float 20s ease-in-out infinite;
        }

        .shape:nth-child(6) {
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255, 140, 66, 0.3), transparent);
            border-radius: 50%;
            top: -100px;
            right: -100px;
            animation-delay: 0s;
        }

        .shape:nth-child(7) {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255, 140, 66, 0.2), transparent);
            border-radius: 50%;
            bottom: -150px;
            left: -150px;
            animation-delay: 3s;
        }

        .shape:nth-child(8) {
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(255, 140, 66, 0.25), transparent);
            border-radius: 50%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: 6s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -30px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
        }

        /* Gradient overlay */
        .gradient-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(10, 31, 15, 0.9) 0%, rgba(26, 58, 31, 0.8) 50%, rgba(10, 31, 15, 0.9) 100%);
            z-index: 1;
        }

        /* Main wrapper */
        .wrapper {
            position: relative;
            z-index: 10;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 3rem 2.5rem;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            animation: slideIn 0.6s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Logo */
        .logo-container {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo {
            width: 100px;
            height: 100px;
            display: inline-block;
            margin-bottom: 1rem;
            animation: pulse 2s ease-in-out infinite;
            filter: drop-shadow(0 8px 25px rgba(255, 140, 66, 0.4));
        }

        .logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        /* Form elements */
        h1 {
            text-align: center;
            color: #ffffff;
            font-size: 2rem;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #ffffff, #ff8c42);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .subtitle {
            text-align: center;
            color: #b8b8b8;
            font-size: 0.95rem;
            margin-bottom: 2rem;
        }

        .input-box {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .input-box input {
            width: 100%;
            padding: 1rem 3rem 1rem 3rem;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .input-box input:focus {
            outline: none;
            border-color: #ff8c42;
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 4px rgba(255, 140, 66, 0.1);
        }

        .input-box input::placeholder {
            color: #888;
        }

        .input-box .icon-left {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #ff8c42;
            width: 20px;
            height: 20px;
            pointer-events: none;
        }

        /* Toggle Password Visibility */
        .icon-right {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            z-index: 1;
            user-select: none;
            transition: all 0.3s;
            color: #888;
            width: 20px;
            height: 20px;
        }

        .icon-right:hover {
            color: #ff8c42;
            transform: translateY(-50%) scale(1.1);
        }

        .icon-right.active {
            color: #ff8c42;
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .remember-forgot label {
            color: #b8b8b8;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .remember-forgot a {
            color: #ff8c42;
            text-decoration: none;
            transition: color 0.3s;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .remember-forgot a:hover {
            color: #ffa662;
        }

        .btn {
            width: 100%;
            padding: 1.2rem;
            background: linear-gradient(135deg, #ff8c42, #ffa662);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 8px 25px rgba(255, 140, 66, 0.4);
            margin-bottom: 1rem;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(255, 140, 66, 0.6);
        }

        .btn:active {
            transform: translateY(0);
        }

        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .register-link p {
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .register-link a {
            color: #ff8c42;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .register-link a:hover {
            color: #ffa662;
        }

        /* Alert styling */
        .alert {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            min-width: 300px;
            padding: 1rem 1.5rem;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: slideInRight 0.4s ease-out;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .alert-warning {
            background: linear-gradient(135deg, rgba(255, 140, 66, 0.95), rgba(255, 166, 98, 0.95));
            color: white;
            border-left: 4px solid #ff8c42;
        }

        .alert::before {
            content: '⚠️';
            margin-right: 0.5rem;
            font-size: 1.2rem;
        }

        /* Loading state */
        .btn.loading {
            position: relative;
            color: transparent;
            pointer-events: none;
        }

        .btn.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .wrapper {
                padding: 2rem 1.5rem;
                margin: 1rem;
            }

            h1 {
                font-size: 1.8rem;
            }

            .alert {
                right: 10px;
                left: 10px;
                min-width: auto;
            }

            .remember-forgot {
                flex-direction: column;
                gap: 0.5rem;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="background-animation">
        <div class="light-ray"></div>
        <div class="light-ray"></div>
        <div class="light-ray"></div>
        <div class="light-ray"></div>
        <div class="light-ray"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    <div class="gradient-overlay"></div>

    <!-- Login Form -->
    <div class="wrapper">
        <div class="logo-container">
            <div class="logo">
                <img src="../image/logo.png" alt="Rumah Que Que Logo">
            </div>
        </div>
        
        <form action="" method="post" id="loginForm">
            <h1>Admin Login</h1>
            <p class="subtitle">Masuk ke panel administrasi</p>
            
            <div class="input-box">
                <i data-feather="user" class="icon-left"></i>
                <input 
                    type="text" 
                    placeholder="Username" 
                    name="username" 
                    id="username" 
                    autocomplete="off" 
                    required
                />
            </div>
            
            <div class="input-box">
                <i data-feather="lock" class="icon-left"></i>
                <input 
                    type="password" 
                    placeholder="Password" 
                    name="password" 
                    id="password"  
                    required
                />
                <i data-feather="eye" class="icon-right" id="togglePassword"></i>
            </div>
            
            <div class="remember-forgot">
                <label>
                    <input type="checkbox" name="remember" style="cursor: pointer;">
                    Ingat saya
                </label>
                <a href="lupapassword.php">
                    <i data-feather="help-circle" style="width: 16px; height: 16px;"></i>
                    Lupa Password?
                </a>
            </div>
            
            <button type="submit" class="btn" name="loginbtn" id="loginBtn">
                Login
            </button>

            <div class="register-link">
                <p>Belum punya akun? <a href="Register.php">Daftar di sini</a></p>
                <p style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(255, 255, 255, 0.1); color: #666;">RumahQueQue © 2025 All Rights Reserved</p>
            </div>
        </form>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($error_message)): ?>
    <div class="alert alert-warning auto-hide" role="alert">
        <?php echo htmlspecialchars($error_message); ?>
    </div>
    <script>
        setTimeout(() => {
            const alertBox = document.querySelector(".auto-hide");
            if (alertBox) {
                alertBox.style.transition = "opacity 0.6s ease-in";
                alertBox.style.opacity = "0";
                setTimeout(() => alertBox.remove(), 600);
            }
        }, 3000);
    </script>
    <?php endif; ?>

    <script>
        // Initialize Feather Icons - ALWAYS run on page load
        feather.replace();

        // Password toggle with delegation to handle dynamic DOM
        document.addEventListener('click', function(e) {
            // Check if click is on toggle icon or its SVG child
            const toggleIcon = e.target.closest('#togglePassword');
            if (!toggleIcon) return;
            
            const passwordInput = document.getElementById('password');
            const currentType = passwordInput.getAttribute('type');
            const newType = currentType === 'password' ? 'text' : 'password';
            
            // Change input type
            passwordInput.setAttribute('type', newType);
            
            // Change icon
            const newIcon = newType === 'text' ? 'eye-off' : 'eye';
            toggleIcon.setAttribute('data-feather', newIcon);
            
            // Toggle active class
            toggleIcon.classList.toggle('active', newType === 'text');
            
            // Re-render icons
            feather.replace();
        });

        // Loading state
        document.getElementById('loginForm').addEventListener('submit', function() {
            document.getElementById('loginBtn').classList.add('loading');
        });

        // Auto focus
        document.getElementById('username').focus();

        // Enter navigation
        document.getElementById('username').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('password').focus();
            }
        });
    </script>
</body>
</html>