<?php
session_start();
include '../config/database.php';

// Redirect if already logged in
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/index.php");
    } else {
        header("Location: ../index.php");
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Username dan password wajib diisi!";
    } else {
        // Find user by username
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // Verify password
        // Logic: Check Hash OR Plain Text (for legacy/admin123 support)
        if ($user && (password_verify($password, $user['password']) || $password === $user['password'])) {
            
            // Success
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['user_name'] = $user['nama_lengkap'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                $_SESSION['admin_logged_in'] = true; // For legacy admin page compatibility if needed
                header("Location: ../admin/index.php");
            } else {
                header("Location: ../index.php");
            }
            exit;
        } else {
            $error = "Username atau password salah!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SP Puyuh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #059669;
            --primary-dark: #047857;
            --accent: #0D9488;
            --bg-light: #F0FDF4;
            --bg-white: #FFFFFF;
            --text-dark: #1F2937;
            --text-muted: #6B7280;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            border-radius: 20px;
            animation: fadeInUp 0.5s ease forwards;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .btn-login {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border: none;
            padding: 12px;
            font-weight: 600;
            color: white;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(5, 150, 105, 0.3);
            color: white;
        }
        .form-label {
            font-weight: 500;
            font-size: 0.9rem;
            color: var(--text-dark);
        }
        .alert {
            border-radius: 10px;
            font-size: 0.9rem;
        }

        /* Input wrapper - border seragam */
        .input-wrap {
            display: flex;
            align-items: center;
            border: 1.5px solid #E5E7EB;
            border-radius: 10px;
            background: white;
            transition: all 0.3s ease;
        }
        .input-wrap:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
        }
        .input-wrap .input-icon {
            padding: 0 12px;
            color: var(--text-muted);
            font-size: 1rem;
            flex-shrink: 0;
        }
        .input-wrap input {
            flex: 1;
            border: none;
            outline: none;
            padding: 12px 0;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            background: transparent;
            color: var(--text-dark);
        }
        .input-wrap input::placeholder {
            color: #9CA3AF;
        }
        .input-wrap .btn-eye {
            background: none;
            border: none;
            padding: 0 14px;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 1rem;
            flex-shrink: 0;
            transition: color 0.2s;
        }
        .input-wrap .btn-eye:hover {
            color: var(--primary);
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 1.5rem 0;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #E5E7EB;
        }
        .divider span {
            font-size: 0.8rem;
            color: var(--text-muted);
        }
    </style>
</head>
<body>

<div class="card login-card p-4 p-md-5">
    <div class="card-body">
        <div class="text-center mb-4">
            <img src="../assets/img/logo.png" alt="SP Puyuh Logo" style="height: 70px; width: auto; object-fit: contain;">
        </div>
        <h4 class="text-center fw-bold mb-1" style="color: var(--primary);">Selamat Datang</h4>
        <p class="text-center mb-4" style="color: var(--text-muted); font-size: 0.9rem;">Masuk ke akun Anda untuk melanjutkan</p>
        
        <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-circle-fill"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill"></i>
                Registrasi berhasil! Silakan login.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['timeout'])): ?>
            <div class="alert alert-warning d-flex align-items-center gap-2">
                <i class="bi bi-clock-history"></i>
                Sesi berakhir karena tidak aktif. Silakan login kembali.
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <div class="input-wrap">
                    <span class="input-icon"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" placeholder="Username" required autofocus value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-wrap">
                    <span class="input-icon"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" id="loginPassword" placeholder="Password" required minlength="8">
                    <button class="btn-eye" type="button" onclick="togglePassword('loginPassword', this)" title="Tampilkan/Sembunyikan Password">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-login">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
                </button>
            </div>
        </form>
        
        <div class="divider">
            <span>atau</span>
        </div>

        <div class="text-center">
            <span style="color: var(--text-muted); font-size: 0.9rem;">Belum punya akun?</span>
            <a href="register.php" class="text-decoration-none fw-semibold" style="color: var(--primary);"> Daftar sekarang</a>
        </div>
        <div class="text-center mt-3">
            <a href="../index.php" class="text-decoration-none small" style="color: var(--text-muted);">
                <i class="bi bi-arrow-left me-1"></i>Kembali ke Beranda
            </a>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}
</script>

</body>
</html>
