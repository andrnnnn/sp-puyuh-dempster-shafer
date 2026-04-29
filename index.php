<?php include 'includes/header.php'; ?>

<?php
// Ambil data gejala dan statistik
include 'config/database.php';
$stmt = $pdo->query("SELECT * FROM gejala WHERE status='aktif' ORDER BY id_gejala ASC");
$gejala = $stmt->fetchAll();

// Hitung statistik untuk ditampilkan
$totalGejala = count($gejala);
$stmtPenyakit = $pdo->query("SELECT COUNT(*) FROM penyakit WHERE status='aktif'");
$totalPenyakit = $stmtPenyakit->fetchColumn();

// Cek Login Status
$isLoggedIn = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="hero-title">Sistem Pakar Diagnosis Penyakit Puyuh</h1>
                <p class="hero-subtitle">
                    Diagnosa cepat untuk menjaga kesehatan ternak puyuh Anda. 
                    Pilih gejala yang dialami dan dapatkan hasil analisis segera.
                </p>
                <a href="#konsultasi" class="btn btn-light btn-lg px-4">Mulai Diagnosis</a>
                
                <!-- Statistik -->
                <div class="hero-stats">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $totalPenyakit; ?></div>
                        <div class="stat-label">Penyakit Terdeteksi</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $totalGejala; ?></div>
                        <div class="stat-label">Gejala Tersedia</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number" style="font-size: 1.1rem; line-height: 1.3;">Dempster-<br>Shafer Theory</div>
                        <div class="stat-label">Metode Analisis</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block text-center">
                <img src="assets/img/banner_burung_puyuh.jpg" alt="Burung Puyuh" class="img-fluid rounded-4 shadow" style="max-height: 350px; object-fit: cover;">
            </div>
        </div>
    </div>
</section>

<!-- Form Konsultasi -->
<div class="container my-5" id="konsultasi">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="main-card animate-in" style="position: relative;">
                
                <?php if (!$isLoggedIn): ?>
                <!-- Locking Overlay -->
                <div class="lock-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.85); backdrop-filter: blur(4px); z-index: 10; display: flex; flex-direction: column; justify-content: center; align-items: center; border-radius: 20px;">
                    <div class="text-center p-5 bg-white shadow-lg rounded-4 border">
                        <div class="mb-3">
                            <i class="bi bi-lock-fill text-warning" style="font-size: 3rem;"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-2">Fitur Terkunci</h4>
                        <p class="text-muted mb-4">Silakan login terlebih dahulu untuk melakukan diagnosis.</p>
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="auth/login.php" class="btn btn-primary px-4 py-2">Login</a>
                            <a href="auth/register.php" class="btn btn-outline-primary px-4 py-2">Daftar Akun</a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Header Card -->
                <div class="card-header-custom">
                    <h3>Formulir Diagnosis</h3>
                    <p>Centang semua gejala yang terlihat pada puyuh Anda</p>
                </div>
                
                <!-- Body Card -->
                <div class="p-4 p-md-5">
                    
                    <!-- Counter gejala yang dipilih -->
                    <div class="alert alert-light border mb-4">
                        Gejala dipilih: <strong id="selectedCount">0</strong> dari <?php echo $totalGejala; ?>
                        <span id="minWarning" class="text-danger ms-2 small"><i class="bi bi-exclamation-circle"></i> Minimal 3 gejala</span>
                        <span id="minOk" class="text-success ms-2 small d-none"><i class="bi bi-check-circle"></i> Siap diagnosis</span>
                    </div>
                    
                    <form action="user/process.php" method="POST" id="diagnosisForm">
                        
                        <!-- Grid Gejala -->
                        <div class="row g-3">
                            <?php foreach($gejala as $g): ?>
                            <div class="col-6 col-md-4 col-lg-4">
                                <input type="checkbox" 
                                       class="btn-check symptom-checkbox" 
                                       name="gejala[]" 
                                       value="<?php echo $g['id_gejala']; ?>" 
                                       id="g_<?php echo $g['id_gejala']; ?>" 
                                       autocomplete="off">
                                <label class="symptom-card" for="g_<?php echo $g['id_gejala']; ?>">
                                    <span class="symptom-badge"><?php echo $g['id_gejala']; ?></span>
                                    <span class="symptom-text"><?php echo $g['nama_gejala']; ?></span>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Tombol Submit -->
                        <div class="text-center mt-5">
                            <button type="submit" class="btn btn-submit" id="submitBtn" disabled>Analisis Gejala</button>
                            <p class="text-muted small mt-2" id="submitHint">Pilih minimal 3 gejala untuk memulai diagnosis</p>
                        </div>
                        
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script untuk counter gejala -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.symptom-checkbox');
    const counter = document.getElementById('selectedCount');
    const submitBtn = document.getElementById('submitBtn');
    const submitHint = document.getElementById('submitHint');
    const minWarning = document.getElementById('minWarning');
    const minOk = document.getElementById('minOk');
    const MIN_GEJALA = 3;

    function updateUI() {
        const checked = document.querySelectorAll('.symptom-checkbox:checked').length;
        counter.textContent = checked;

        if (checked >= MIN_GEJALA) {
            submitBtn.disabled = false;
            submitHint.classList.add('d-none');
            minWarning.classList.add('d-none');
            minOk.classList.remove('d-none');
        } else {
            submitBtn.disabled = true;
            submitHint.classList.remove('d-none');
            minWarning.classList.remove('d-none');
            minOk.classList.add('d-none');
        }
    }

    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', updateUI);
    });

    updateUI(); // inisialisasi saat halaman load
});
</script>

<?php include 'includes/footer.php'; ?>
