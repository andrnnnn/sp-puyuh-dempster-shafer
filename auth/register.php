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
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Basic validation
    if (empty($nama_lengkap) || empty($username) || empty($password)) {
        $error = "Semua kolom wajib diisi!";
    } elseif (strlen($password) < 8) {
        $error = "Password minimal harus 8 karakter!";
    } else {
        // Check username availability
        $stmt = $pdo->prepare("SELECT id_user FROM users WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->rowCount() > 0) {
            $error = "Username tersebut sudah terdaftar. Silakan gunakan username lain.";
        } else {
            // Insert new user (Role Default: user)
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (nama_lengkap, username, password, role) VALUES (?, ?, ?, 'user')");
            
            if ($stmt->execute([$nama_lengkap, $username, $hashed_password])) {
                // Auto Login
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['user_name'] = $nama_lengkap;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = 'user';

                header("Location: ../index.php");
                exit;
            } else {
                $error = "Terjadi kesalahan saat mendaftar.";
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
    <title>Daftar Akun - SP Puyuh</title>
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
            max-width: 420px;
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
        <h4 class="text-center mb-4 fw-bold" style="color: var(--primary);">Buat Akun Baru</h4>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
                <br><a href="login.php" class="fw-bold text-success text-decoration-none">Klik disini untuk Login</a>
            </div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" class="form-control" placeholder="Contoh: Budi Santoso" required value="<?php echo isset($_POST['nama_lengkap']) ? htmlspecialchars($_POST['nama_lengkap']) : ''; ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Username untuk login" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Minimal 8 karakter" required minlength="8">
                <div class="form-text">Minimal 8 karakter.</div>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Daftar Sekarang</button>
            </div>
        </form>
        <?php endif; ?>
        
        <div class="text-center mt-4">
            <span class="text-muted">Sudah punya akun?</span>
            <a href="login.php" class="text-decoration-none fw-semibold" style="color: var(--primary);">Login disini</a>
        </div>
        <div class="text-center mt-2">
            <a href="../index.php" class="text-decoration-none small text-muted">Kembali ke Halaman Utama</a>
        </div>
    </div>
</div>

</body>
</html>
