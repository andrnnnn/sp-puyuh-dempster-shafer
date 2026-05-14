<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SP Puyuh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Warna - Sama dengan user interface */
        :root {
            --primary: #059669;
            --primary-dark: #047857;
            --accent: #0D9488;
            --bg-light: #F0FDF4;
            --bg-white: #FFFFFF;
            --text-dark: #1F2937;
            --text-muted: #6B7280;
            --sidebar-width: 260px;
        }
        
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--bg-light); 
            color: var(--text-dark);
        }
        
        /* ===== Sidebar ===== */
        .sidebar { 
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background-color: var(--bg-white); 
            border-right: 1px solid #E5E7EB;
            z-index: 1040;
            overflow-y: auto;
            transition: transform 0.3s ease;
        }
        
        .sidebar-brand {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .nav-link { 
            color: var(--text-dark); 
            padding: 11px 16px; 
            border-radius: 8px;
            margin-bottom: 3px;
            font-size: 0.92rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s ease;
        }
        
        .nav-link:hover { 
            background-color: var(--bg-light); 
            color: var(--primary); 
        }
        
        .nav-pills .nav-link.active {
            background-color: var(--primary);
            color: #fff;
        }
        
        /* ===== Main Content ===== */
        .main-content { 
            margin-left: var(--sidebar-width);
            padding: 24px 30px;
            min-height: 100vh;
        }

        /* ===== Mobile Topbar ===== */
        .mobile-topbar {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 56px;
            background: var(--bg-white);
            border-bottom: 1px solid #E5E7EB;
            z-index: 1030;
            padding: 0 16px;
            align-items: center;
            justify-content: space-between;
        }
        .mobile-topbar .btn-hamburger {
            background: none;
            border: none;
            font-size: 1.4rem;
            color: var(--text-dark);
            padding: 4px 8px;
            border-radius: 8px;
            transition: background 0.2s;
        }
        .mobile-topbar .btn-hamburger:hover {
            background: var(--bg-light);
        }

        /* Sidebar overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 1035;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .sidebar-overlay.show {
            display: block;
            opacity: 1;
        }
        
        /* ===== Buttons ===== */
        .btn-primary { 
            background-color: var(--primary); 
            border-color: var(--primary); 
        }
        
        .btn-primary:hover { 
            background-color: var(--primary-dark); 
            border-color: var(--primary-dark); 
        }
        
        /* ===== Cards ===== */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        
        /* ===== Table ===== */
        .table th {
            background-color: var(--bg-light);
            font-weight: 600;
            font-size: 0.88rem;
            white-space: nowrap;
        }
        .table td {
            font-size: 0.9rem;
            vertical-align: middle;
        }
        
        /* ===== Form ===== */
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(5, 150, 105, 0.15);
        }
        
        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        /* ===== Text colors ===== */
        .text-primary { color: var(--primary) !important; }
        .bg-primary { background-color: var(--primary) !important; }
        
        /* ===== Custom Accent Utilities ===== */
        .btn-accent {
            background-color: var(--accent) !important;
            border-color: var(--accent) !important;
            color: white !important;
        }
        .btn-accent:hover {
            background-color: #0F766E !important;
            border-color: #0F766E !important;
            color: white !important;
        }
        .bg-accent {
            background-color: var(--accent) !important;
        }

        /* ===== Responsive ===== */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .mobile-topbar {
                display: flex;
            }
            .main-content {
                margin-left: 0;
                padding: 72px 16px 24px 16px;
            }
            /* Stack action buttons on mobile */
            .btn-group-mobile {
                display: flex;
                flex-direction: column;
                gap: 6px;
            }
            .btn-group-mobile .btn {
                width: 100%;
            }
        }
        @media (max-width: 575.98px) {
            .main-content {
                padding: 64px 12px 20px 12px;
            }
            .card { border-radius: 10px; }
            h2, h3 { font-size: 1.3rem; }
        }
    </style>
</head>
<body>

<!-- Mobile Top Bar -->
<div class="mobile-topbar">
    <button class="btn-hamburger" id="sidebarToggle" aria-label="Toggle menu">
        <i class="bi bi-list"></i>
    </button>
    <span class="sidebar-brand">SP Puyuh</span>
    <a href="logout.php" class="text-decoration-none text-danger" title="Logout" style="font-size: 1.2rem;">
        <i class="bi bi-box-arrow-right"></i>
    </a>
</div>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column flex-shrink-0 p-3" id="sidebar">
        <a href="index.php" class="d-flex align-items-center gap-2 text-decoration-none mb-3">
            <img src="../assets/img/logo.png" alt="SP Puyuh Logo" style="height: 40px; width: auto; object-fit: contain;">
            <span class="sidebar-brand">SP Puyuh</span>
        </a>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    Dashboard
                </a>
            </li>
            <li>
                <a href="penyakit.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'penyakit.php' ? 'active' : ''; ?>">
                    Data Penyakit
                </a>
            </li>
            <li>
                <a href="gejala.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'gejala.php' ? 'active' : ''; ?>">
                    Data Gejala
                </a>
            </li>
            <li>
                <a href="aturan.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'aturan.php' ? 'active' : ''; ?>">
                    Aturan DST
                </a>
            </li>
        </ul>
        <hr>
        <div class="d-flex align-items-center justify-content-between px-2">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;background:var(--bg-light);color:var(--primary);font-weight:600;font-size:0.85rem;">
                    <?php echo strtoupper(substr(isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'A', 0, 1)); ?>
                </div>
                <span class="fw-medium" style="font-size:0.88rem;"><?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin'; ?></span>
            </div>
            <a href="logout.php" class="text-danger text-decoration-none" title="Logout" style="font-size:1.1rem;">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-grow-1 main-content">

<script>
// Mobile sidebar toggle
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('sidebarOverlay');
const toggle = document.getElementById('sidebarToggle');

toggle.addEventListener('click', function() {
    sidebar.classList.toggle('show');
    overlay.classList.toggle('show');
});
overlay.addEventListener('click', function() {
    sidebar.classList.remove('show');
    overlay.classList.remove('show');
});
// Close sidebar on nav link click (mobile)
sidebar.querySelectorAll('.nav-link').forEach(function(link) {
    link.addEventListener('click', function() {
        if (window.innerWidth < 992) {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        }
    });
});
</script>
