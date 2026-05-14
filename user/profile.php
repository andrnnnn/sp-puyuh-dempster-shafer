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

<style>
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
        padding: 12px 12px 12px 0;
        font-family: 'Inter', sans-serif;
        font-size: 0.95rem;
        background: transparent;
        color: var(--text-dark);
        min-width: 0;
    }
    .input-wrap input::placeholder { color: #9CA3AF; }
    .input-wrap input:disabled { color: var(--text-muted); background: transparent; }
    .input-wrap.disabled { background: #F9FAFB; }
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
    .input-wrap .btn-eye:hover { color: var(--primary); }
    .btn-save {
        background: linear-gradient(135deg, var(--primary), var(--accent));
        border: none;
        color: white;
        padding: 10px 28px;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn-save:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(5, 150, 105, 0.3);
        color: white;
    }
</style>

<div class="container my-4 my-md-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="main-card animate-in">
                <div class="card-header-custom">
                    <h3>Profil Saya</h3>
                    <p>Kelola informasi akun Anda</p>
                </div>
                <div class="p-3 p-md-4">
                    <?php if ($message): ?>
                        <div class="alert alert-success d-flex align-items-center gap-2" style="border-radius: 10px;">
                            <i class="bi bi-check-circle-fill"></i>
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger d-flex align-items-center gap-2" style="border-radius: 10px;">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Update Info -->
                    <form method="POST" class="mb-4">
                        <h5 class="mb-3" style="color: var(--primary);"><i class="bi bi-person-circle me-2"></i>Informasi Dasar</h5>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Username</label>
                            <div class="input-wrap disabled">
                                <span class="input-icon"><i class="bi bi-at"></i></span>
                                <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled readonly>
                            </div>
                            <small class="text-muted mt-1 d-block">Username tidak dapat diubah.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Nama Lengkap</label>
                            <div class="input-wrap">
                                <span class="input-icon"><i class="bi bi-person"></i></span>
                                <input type="text" name="nama_lengkap" value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" required>
                            </div>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-save">
                            <i class="bi bi-check2 me-1"></i>Simpan Perubahan
                        </button>
                    </form>

                    <hr>

                    <!-- Change Password -->
                    <form method="POST" class="mt-4">
                        <h5 class="mb-3" style="color: var(--primary);"><i class="bi bi-shield-lock me-2"></i>Ganti Password</h5>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Password Saat Ini</label>
                            <div class="input-wrap">
                                <span class="input-icon"><i class="bi bi-lock"></i></span>
                                <input type="password" name="current_password" id="currentPw" placeholder="Password saat ini" required>
                                <button class="btn-eye" type="button" onclick="togglePw('currentPw', this)"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Password Baru</label>
                                <div class="input-wrap">
                                    <span class="input-icon"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" name="new_password" id="newPw" placeholder="Min. 8 karakter" required minlength="8">
                                    <button class="btn-eye" type="button" onclick="togglePw('newPw', this)"><i class="bi bi-eye"></i></button>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Ulangi Password Baru</label>
                                <div class="input-wrap">
                                    <span class="input-icon"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" name="confirm_password" id="confirmPw" placeholder="Ulangi password" required minlength="8">
                                    <button class="btn-eye" type="button" onclick="togglePw('confirmPw', this)"><i class="bi bi-eye"></i></button>
                                </div>
                            </div>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-danger" style="border-radius: 10px; padding: 10px 28px; font-weight: 600;">
                            <i class="bi bi-key me-1"></i>Ganti Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePw(id, btn) {
    const input = document.getElementById(id);
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

<?php include '../includes/footer.php'; ?>
