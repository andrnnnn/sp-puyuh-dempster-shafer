<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Deteksi apakah sedang di dalam subfolder (misal: user/, auth/)
$isInSubFolder = (basename(dirname($_SERVER['PHP_SELF'])) == 'user' || basename(dirname($_SERVER['PHP_SELF'])) == 'auth');
$path = $isInSubFolder ? '../' : '';

// Auto-logout: sesi kadaluarsa setelah 30 menit tidak aktif
$session_timeout = 30 * 60; // 30 menit dalam detik
if (isset($_SESSION['user_logged_in']) && isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > $session_timeout) {
        session_unset();
        session_destroy();
        session_start();
        $_SESSION['timeout_message'] = 'Sesi Anda telah berakhir karena tidak aktif. Silakan login kembali.';
        header("Location: " . $path . "auth/login.php?timeout=1");
        exit;
    }
}
if (isset($_SESSION['user_logged_in'])) {
    $_SESSION['last_activity'] = time();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pakar Puyuh</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts - Inter (modern & clean) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* =============================================
           WARNA UTAMA - Mudah diganti sesuai keinginan
           ============================================= */
        :root {
            --primary: #059669;        /* Hijau emerald */
            --primary-dark: #047857;   /* Hijau lebih gelap */
            --accent: #0D9488;         /* Teal untuk aksen */
            --warning: #F59E0B;        /* Kuning untuk highlight */
            --bg-light: #F0FDF4;       /* Background hijau muda */
            --bg-white: #FFFFFF;
            --text-dark: #1F2937;
            --text-muted: #6B7280;
        }

        /* =============================================
           STYLE DASAR
           ============================================= */
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
        }
        
        /* Overrides Bootstrap */
        .btn-primary {
            background-color: var(--primary) !important;
            border-color: var(--primary) !important;
        }
        .btn-primary:hover {
            background-color: var(--primary-dark) !important;
            border-color: var(--primary-dark) !important;
        }
        .btn-outline-primary {
            color: var(--primary) !important;
            border-color: var(--primary) !important;
        }
        .btn-outline-primary:hover {
            background-color: var(--primary) !important;
            color: white !important;
        }

        /* =============================================
           NAVBAR - Simpel dan bersih
           ============================================= */
        .navbar {
            background: var(--bg-white);
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            padding: 1rem 0;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.4rem;
            color: var(--primary) !important;
        }
        
        .navbar-brand i {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .nav-link {
            color: var(--text-dark) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            background: var(--bg-light);
            color: var(--primary) !important;
        }

        /* =============================================
           HERO SECTION - Banner dengan gradient
           ============================================= */
        .hero-section {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            padding: 80px 0;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 50%;
            height: 100%;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ccircle cx='50' cy='50' r='40' fill='rgba(255,255,255,0.1)'/%3E%3C/svg%3E") no-repeat center;
            background-size: contain;
            opacity: 0.3;
        }
        
        .hero-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .hero-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }
        
        .hero-stats {
            display: flex;
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
        }
        
        .stat-label {
            font-size: 0.85rem;
            opacity: 0.8;
        }

        /* =============================================
           CARD UTAMA - Form konsultasi
           ============================================= */
        .main-card {
            background: var(--bg-white);
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            border: none;
        }
        
        .card-header-custom {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
            padding: 1.5rem 2rem;
            text-align: center;
        }
        
        .card-header-custom h3 {
            margin: 0;
            font-weight: 600;
        }
        
        .card-header-custom p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }

        /* =============================================
           SYMPTOM CARDS - Kartu gejala yang bisa diklik
           ============================================= */
        .symptom-card {
            background: var(--bg-white);
            border: 2px solid #E5E7EB;
            border-radius: 12px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            align-items: center;
        }
        
        .symptom-card:hover {
            border-color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(5, 150, 105, 0.15);
        }
        
        /* Saat checkbox dicentang */
        .btn-check:checked + .symptom-card {
            background: var(--bg-light);
            border-color: var(--primary);
            box-shadow: 0 4px 15px rgba(5, 150, 105, 0.2);
        }
        
        .symptom-badge {
            background: var(--bg-light);
            color: var(--primary);
            padding: 0.3rem 0.6rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-right: 0.75rem;
            flex-shrink: 0;
        }
        
        .btn-check:checked + .symptom-card .symptom-badge {
            background: var(--primary);
            color: white;
        }
        
        .symptom-text {
            font-size: 0.9rem;
            color: var(--text-dark);
            line-height: 1.4;
        }

        /* =============================================
           TOMBOL SUBMIT
           ============================================= */
        .btn-submit {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border: none;
            padding: 1rem 3rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(5, 150, 105, 0.3);
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(5, 150, 105, 0.4);
            color: white;
        }

        /* =============================================
           HASIL DIAGNOSIS
           ============================================= */
        .result-card {
            background: var(--bg-white);
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .result-header {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .confidence-badge {
            display: inline-block;
            background: var(--warning);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
        }
        
        .section-title {
            color: var(--primary);
            font-weight: 600;
            border-bottom: 2px solid var(--bg-light);
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .info-box {
            background: var(--bg-light);
            border-left: 4px solid var(--primary);
            padding: 1.5rem;
            border-radius: 0 12px 12px 0;
        }

        /* =============================================
           FOOTER
           ============================================= */
        .footer {
            background: var(--bg-white);
            border-top: 1px solid #E5E7EB;
            padding: 2rem 0;
            margin-top: 3rem;
        }
        
        .footer-text {
            color: var(--text-muted);
            margin: 0;
        }

        /* =============================================
           ANIMASI SEDERHANA
           ============================================= */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-in {
            animation: fadeInUp 0.5s ease forwards;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo $path; ?>index.php">
            <img src="<?php echo $path; ?>assets/img/logo.png" alt="SP Puyuh Logo" style="height: 40px; width: auto; object-fit: contain;">
            <span>SP Puyuh</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $path; ?>index.php">Beranda</a>
                </li>
                
                <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-primary fw-bold" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Halo, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="<?php echo $path; ?>user/profile.php"><i class="bi bi-person me-2"></i>Profil Saya</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo $path; ?>auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $path; ?>auth/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary text-white ms-2 px-3 rounded-pill" href="<?php echo $path; ?>auth/register.php">Daftar</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
