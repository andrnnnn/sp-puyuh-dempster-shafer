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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #059669;
            --primary-dark: #047857;
            --bg-light: #F0FDF4;
            --text-dark: #1F2937;
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
            max-width: 400px;
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            border-radius: 16px;
        }
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            padding: 12px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(5, 150, 105, 0.15);
        }
    </style>
</head>
<body>

<div class="card login-card p-4">
    <div class="card-body">
        <div class="text-center mb-4">
            <img src="../assets/img/logo.png" alt="SP Puyuh Logo" style="height: 70px; width: auto; object-fit: contain;">
        </div>
        <h4 class="text-center mb-4 fw-bold" style="color: var(--primary);">Login</h4>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success">Registrasi berhasil! Silakan login.</div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Minimal 8 karakter" required minlength="8">
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Masuk</button>
            </div>
        </form>
        
        <div class="text-center mt-4">
            <span class="text-muted">Belum punya akun?</span>
            <a href="register.php" class="text-decoration-none fw-semibold" style="color: var(--primary);">Daftar disini</a>
        </div>
        <div class="text-center mt-2">
            <a href="../index.php" class="text-decoration-none small text-muted">Kembali ke Beranda</a>
        </div>
    </div>
</div>

</body>
</html>
