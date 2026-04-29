<?php
session_start();
include '../config/database.php';

// Check login
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Fetch current data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id_user = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $nama_lengkap = trim($_POST['nama_lengkap']);
        
        if (!empty($nama_lengkap)) {
            $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = ? WHERE id_user = ?");
            if ($stmt->execute([$nama_lengkap, $user_id])) {
                $_SESSION['user_name'] = $nama_lengkap; // Update session
                $user['nama_lengkap'] = $nama_lengkap; // Update local view
                $message = "Profil berhasil diperbarui.";
            } else {
                $error = "Gagal memperbarui profil.";
            }
        } else {
            $error = "Nama Lengkap tidak boleh kosong.";
        }
    } 
    elseif (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (password_verify($current_password, $user['password'])) {
            if (!empty($new_password) && $new_password === $confirm_password) {
                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id_user = ?");
                if ($stmt->execute([$new_hash, $user_id])) {
                    $message = "Password berhasil diubah.";
                    $user['password'] = $new_hash; // Update local view hash prevents reuse effectively?
                } else {
                    $error = "Gagal mengubah password.";
                }
            } else {
                $error = "Password baru kosong atau tidak cocok.";
            }
        } else {
            $error = "Password saat ini salah.";
        }
    }
}
?>
<?php include '../includes/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="main-card animate-in">
                <div class="card-header-custom">
                    <h3>Profil Saya</h3>
                    <p>Kelola informasi akun Anda</p>
                </div>
                <div class="p-4">
                    <?php if ($message): ?>
                        <div class="alert alert-success"><?php echo $message; ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <!-- Update Info -->
                    <form method="POST" class="mb-5">
                        <h5 class="mb-3 text-success">Informasi Dasar</h5>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled readonly>
                            <small class="text-muted">Username tidak dapat diubah.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="form-control" value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" required>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary">Simpan Perubahan</button>
                    </form>

                    <hr>

                    <!-- Change Password -->
                    <form method="POST" class="mt-4">
                        <h5 class="mb-3 text-success">Ganti Password</h5>
                        <div class="mb-3">
                            <label class="form-label">Password Saat Ini</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password Baru</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ulangi Password Baru</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-danger">Ganti Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

<?php include '../includes/footer.php'; ?>
