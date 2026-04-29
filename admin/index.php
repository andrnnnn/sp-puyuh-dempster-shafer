<?php
include 'includes/session.php';
include '../config/database.php';
include 'includes/header.php';

// Hitung jumlah data
$count_penyakit = $pdo->query("SELECT COUNT(*) FROM penyakit")->fetchColumn();
$count_gejala = $pdo->query("SELECT COUNT(*) FROM gejala")->fetchColumn();
$count_aturan = $pdo->query("SELECT COUNT(*) FROM basis_pengetahuan")->fetchColumn();
?>

<h2 class="mb-4">Dashboard</h2>

<div class="row">
    <div class="col-md-4">
        <div class="card text-white mb-4" style="background: linear-gradient(135deg, var(--primary), var(--accent));">
            <div class="card-body">
                <h5 class="card-title fw-light">Total Penyakit</h5>
                <h2 class="mb-0 fw-bold"><?php echo $count_penyakit; ?></h2>
            </div>
            <div class="card-footer border-0" style="background-color: rgba(0,0,0,0.1);">
                <a class="small text-white text-decoration-none" href="penyakit.php">Lihat Detail →</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white mb-4" style="background: linear-gradient(135deg, var(--accent), #14B8A6);">
            <div class="card-body">
                <h5 class="card-title fw-light">Total Gejala</h5>
                <h2 class="mb-0 fw-bold"><?php echo $count_gejala; ?></h2>
            </div>
            <div class="card-footer border-0" style="background-color: rgba(0,0,0,0.1);">
                <a class="small text-white text-decoration-none" href="gejala.php">Lihat Detail →</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white mb-4" style="background: linear-gradient(135deg, var(--primary-dark), var(--primary));">
            <div class="card-body">
                <h5 class="card-title fw-light">Aturan DST</h5>
                <h2 class="mb-0 fw-bold"><?php echo $count_aturan; ?></h2>
            </div>
            <div class="card-footer border-0" style="background-color: rgba(0,0,0,0.1);">
                <a class="small text-white text-decoration-none" href="aturan.php">Lihat Detail →</a>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header bg-white fw-bold">Informasi Sistem</div>
    <div class="card-body">
        <p>Selamat datang di Panel Admin Sistem Pakar Diagnosis Penyakit Puyuh.</p>
        <p class="mb-0">Gunakan menu di sebelah kiri untuk mengelola data penyakit, gejala, dan aturan Dempster-Shafer.</p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
