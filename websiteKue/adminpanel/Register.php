<?php
    session_start();
    require "../koneksi.php";
    
    // Konfigurasi Abstract API (Gratis 100 validasi/bulan)
    // Daftar di: https://app.abstractapi.com/api/email-validation/
    $ABSTRACT_API_KEY = "YOUR_API_KEY_HERE"; // Ganti dengan API key Anda
    
    // Process registration
    if (isset($_POST['registerbtn'])) {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validasi input
        if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
            $error_message = "Semua field harus diisi!";
        } elseif (strlen($username) < 3) {
            $error_message = "Username minimal 3 karakter!";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Format email tidak valid!";
        } elseif (strlen($password) < 8) {
            $error_message = "Password minimal 8 karakter!";
        } elseif ($password !== $confirm_password) {
            $error_message = "Password dan konfirmasi password tidak cocok!";
        } else {
            // Verifikasi email menggunakan Abstract API
            $email_valid = true;
            $email_quality = 1.0;
            
            if (!empty($ABSTRACT_API_KEY) && $ABSTRACT_API_KEY !== "YOUR_API_KEY_HERE") {
                $api_url = "https://emailvalidation.abstractapi.com/v1/?api_key=" . $ABSTRACT_API_KEY . "&email=" . urlencode($email);
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $api_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                $response = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($http_code == 200 && $response) {
                    $data = json_decode($response, true);
                    
                    // Check deliverability
                    if (isset($data['deliverability']) && $data['deliverability'] !== 'DELIVERABLE') {
                        $email_valid = false;
                        $error_message = "Email tidak dapat menerima pesan. Gunakan email yang valid!";
                    }
                    
                    // Check if disposable email
                    if (isset($data['is_disposable_email']) && $data['is_disposable_email']['value'] === true) {
                        $email_valid = false;
                        $error_message = "Email disposable tidak diperbolehkan! Gunakan email permanen.";
                    }
                    
                    // Get quality score
                    if (isset($data['quality_score'])) {
                        $email_quality = floatval($data['quality_score']);
                        if ($email_quality < 0.5) {
                            $email_valid = false;
                            $error_message = "Kualitas email terlalu rendah. Gunakan email yang lebih baik!";
                        }
                    }
                }
            }
            
            if ($email_valid) {
                // Cek apakah username sudah ada
                $stmt = mysqli_prepare($con, "SELECT id FROM users WHERE username = ? LIMIT 1");
                mysqli_stmt_bind_param($stmt, "s", $username);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);
                
                if (mysqli_stmt_num_rows($stmt) > 0) {
                    $error_message = "Username sudah digunakan!";
                    mysqli_stmt_close($stmt);
                } else {
                    mysqli_stmt_close($stmt);
                    
                    // Cek apakah email sudah ada
                    $stmt = mysqli_prepare($con, "SELECT id FROM users WHERE email = ? LIMIT 1");
                    mysqli_stmt_bind_param($stmt, "s", $email);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_store_result($stmt);
                    
                    if (mysqli_stmt_num_rows($stmt) > 0) {
                        $error_message = "Email sudah terdaftar!";
                        mysqli_stmt_close($stmt);
                    } else {
                        mysqli_stmt_close($stmt);
                        
                        // Hash password
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        
                        // Generate verification token
                        $verification_token = bin2hex(random_bytes(32));
                        $is_verified = 0; // Email belum diverifikasi
                        
                        // Insert user baru
                        $stmt = mysqli_prepare($con, "INSERT INTO users (username, email, password, verification_token, is_verified, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                        mysqli_stmt_bind_param($stmt, "ssssi", $username, $email, $hashed_password, $verification_token, $is_verified);
                        
                        if (mysqli_stmt_execute($stmt)) {
                            mysqli_stmt_close($stmt);
                            
                            // TODO: Kirim email verifikasi
                            // Link verifikasi: verify.php?token=$verification_token
                            
                            $success_message = "Akun berhasil dibuat! Silakan cek email Anda untuk verifikasi.";
                            
                            // Redirect ke login setelah 3 detik
                            header("refresh:3;url=index.php");
                        } else {
                            $error_message = "Terjadi kesalahan saat membuat akun. Coba lagi!";
                            mysqli_stmt_close($stmt);
                        }
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
    <title>Daftar Akun - Rumah Que Que Admin</title>
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
            overflow-x: hidden;
            position: relative;
            padding: 2rem 0;
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

        .gradient-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(10, 31, 15, 0.9) 0%, rgba(26, 58, 31, 0.8) 50%, rgba(10, 31, 15, 0.9) 100%);
            z-index: 1;
        }

        .wrapper {
            position: relative;
            z-index: 10;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 2.5rem 2.5rem;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            animation: slideIn 0.6s ease-out;
            margin: 1rem;
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

        .logo-container {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .logo {
            width: 80px;
            height: 80px;
            display: inline-block;
            margin-bottom: 0.5rem;
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

        h1 {
            text-align: center;
            color: #ffffff;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #ffffff, #ff8c42);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .subtitle {
            text-align: center;
            color: #b8b8b8;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }

        .input-box {
            position: relative;
            margin-bottom: 1.2rem;
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

        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.85rem;
            display: none;
        }

        .strength-bar {
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            margin-top: 0.3rem;
            overflow: hidden;
        }

        .strength-bar-fill {
            height: 100%;
            width: 0%;
            transition: all 0.3s;
            border-radius: 2px;
        }

        .strength-weak { background: #ff4444; }
        .strength-medium { background: #ffaa00; }
        .strength-strong { background: #44ff44; }

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
            margin-top: 1rem;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(255, 140, 66, 0.6);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .login-link p {
            color: #888;
            font-size: 0.9rem;
        }

        .login-link a {
            color: #ff8c42;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .login-link a:hover {
            color: #ffa662;
        }

        .alert {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            min-width: 300px;
            max-width: 500px;
            padding: 1rem 1.5rem;
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

        .alert-success {
            background: linear-gradient(135deg, rgba(68, 255, 68, 0.95), rgba(98, 255, 98, 0.95));
            color: white;
            border-left: 4px solid #44ff44;
        }

        .alert-warning::before {
            content: '⚠️';
            margin-right: 0.5rem;
            font-size: 1.2rem;
        }

        .alert-success::before {
            content: '✅';
            margin-right: 0.5rem;
            font-size: 1.2rem;
        }

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

        .requirements {
            font-size: 0.85rem;
            color: #888;
            margin-top: 0.5rem;
            padding-left: 1rem;
        }

        .requirements li {
            margin: 0.3rem 0;
            transition: color 0.3s;
        }

        .requirements li.met {
            color: #44ff44;
        }

        @media (max-width: 768px) {
            .wrapper {
                padding: 2rem 1.5rem;
            }

            h1 {
                font-size: 1.6rem;
            }

            .alert {
                right: 10px;
                left: 10px;
                min-width: auto;
            }
        }
    </style>
</head>
<body>
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

    <div class="wrapper">
        <div class="logo-container">
            <div class="logo">
                <img src="../image/logo.png" alt="Rumah Que Que Logo">
            </div>
        </div>
        
        <form action="" method="post" id="registerForm">
            <h1>Daftar Akun</h1>
            <p class="subtitle">Buat akun admin baru</p>
            
            <div class="input-box">
                <i data-feather="user" class="icon-left"></i>
                <input 
                    type="text" 
                    placeholder="Username (min. 3 karakter)" 
                    name="username" 
                    id="username" 
                    autocomplete="off" 
                    required
                    minlength="3"
                />
            </div>
            
            <div class="input-box">
                <i data-feather="mail" class="icon-left"></i>
                <input 
                    type="email" 
                    placeholder="Email" 
                    name="email" 
                    id="email" 
                    autocomplete="off" 
                    required
                />
            </div>
            
            <div class="input-box">
                <i data-feather="lock" class="icon-left"></i>
                <input 
                    type="password" 
                    placeholder="Password (min. 8 karakter)" 
                    name="password" 
                    id="password"  
                    required
                    minlength="8"
                />
                <i data-feather="eye" class="icon-right" id="togglePassword"></i>
            </div>
            
            <div class="password-strength" id="passwordStrength">
                <span id="strengthText">Kekuatan: <strong></strong></span>
                <div class="strength-bar">
                    <div class="strength-bar-fill" id="strengthBar"></div>
                </div>
            </div>
            
            <div class="input-box">
                <i data-feather="lock" class="icon-left"></i>
                <input 
                    type="password" 
                    placeholder="Konfirmasi Password" 
                    name="confirm_password" 
                    id="confirm_password"  
                    required
                />
                <i data-feather="eye" class="icon-right" id="toggleConfirmPassword"></i>
            </div>
            
            <button type="submit" class="btn" name="registerbtn" id="registerBtn">
                Daftar Sekarang
            </button>

            <div class="login-link">
                <p>Sudah punya akun? <a href="index.php">Login di sini</a></p>
            </div>
        </form>
    </div>

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
        }, 5000);
    </script>
    <?php endif; ?>

    <?php if (isset($success_message)): ?>
    <div class="alert alert-success auto-hide" role="alert">
        <?php echo htmlspecialchars($success_message); ?>
    </div>
    <script>
        setTimeout(() => {
            const alertBox = document.querySelector(".auto-hide");
            if (alertBox) {
                alertBox.style.transition = "opacity 0.6s ease-in";
                alertBox.style.opacity = "0";
                setTimeout(() => alertBox.remove(), 600);
            }
        }, 5000);
    </script>
    <?php endif; ?>

    <script>
        feather.replace();

        // Password toggle handlers
        function setupPasswordToggle(toggleId, inputId) {
            document.addEventListener('click', function(e) {
                const toggleIcon = e.target.closest('#' + toggleId);
                if (!toggleIcon) return;
                
                const passwordInput = document.getElementById(inputId);
                const currentType = passwordInput.getAttribute('type');
                const newType = currentType === 'password' ? 'text' : 'password';
                
                passwordInput.setAttribute('type', newType);
                const newIcon = newType === 'text' ? 'eye-off' : 'eye';
                toggleIcon.setAttribute('data-feather', newIcon);
                toggleIcon.classList.toggle('active', newType === 'text');
                
                feather.replace();
            });
        }

        setupPasswordToggle('togglePassword', 'password');
        setupPasswordToggle('toggleConfirmPassword', 'confirm_password');

        // Password strength meter
        const passwordInput = document.getElementById('password');
        const strengthDiv = document.getElementById('passwordStrength');
        const strengthText = document.querySelector('#strengthText strong');
        const strengthBar = document.getElementById('strengthBar');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            
            if (password.length === 0) {
                strengthDiv.style.display = 'none';
                return;
            }
            
            strengthDiv.style.display = 'block';
            
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z\d]/.test(password)) strength++;
            
            let strengthLevel = 'Lemah';
            let strengthClass = 'strength-weak';
            let barWidth = '33%';
            
            if (strength >= 4) {
                strengthLevel = 'Kuat';
                strengthClass = 'strength-strong';
                barWidth = '100%';
            } else if (strength >= 2) {
                strengthLevel = 'Sedang';
                strengthClass = 'strength-medium';
                barWidth = '66%';
            }
            
            strengthText.textContent = strengthLevel;
            strengthBar.className = 'strength-bar-fill ' + strengthClass;
            strengthBar.style.width = barWidth;
        });

        // Form validation
        const form = document.getElementById('registerForm');
        const confirmPassword = document.getElementById('confirm_password');

        confirmPassword.addEventListener('input', function() {
            if (this.value !== passwordInput.value) {
                this.setCustomValidity('Password tidak cocok');
            } else {
                this.setCustomValidity('');
            }
        });

        form.addEventListener('submit', function() {
            document.getElementById('registerBtn').classList.add('loading');
        });

        // Auto focus
        document.getElementById('username').focus();
    </script>
</body>
</html>