<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SP Puyuh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        }
        
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--bg-light); 
            color: var(--text-dark);
        }
        
        /* Sidebar */
        .sidebar { 
            min-height: 100vh; 
            background-color: var(--bg-white); 
            border-right: 1px solid #E5E7EB; 
        }
        
        .sidebar-brand {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .nav-link { 
            color: var(--text-dark); 
            padding: 12px 20px; 
            border-radius: 8px;
            margin-bottom: 4px;
        }
        
        .nav-link:hover { 
            background-color: var(--bg-light); 
            color: var(--primary); 
        }
        
        .nav-pills .nav-link.active {
            background-color: var(--primary);
            color: #fff;
        }
        
        /* Main Content */
        .main-content { 
            padding: 30px; 
        }
        
        /* Buttons */
        .btn-primary { 
            background-color: var(--primary); 
            border-color: var(--primary); 
        }
        
        .btn-primary:hover { 
            background-color: var(--primary-dark); 
            border-color: var(--primary-dark); 
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        
        /* Table */
        .table th {
            background-color: var(--bg-light);
            font-weight: 600;
        }
        
        /* Form */
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(5, 150, 105, 0.15);
        }
        
        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        /* Text colors */
        .text-primary { color: var(--primary) !important; }
        .bg-primary { background-color: var(--primary) !important; }
        
        /* Custom Accent Utilities */
        .btn-accent {
            background-color: var(--accent) !important;
            border-color: var(--accent) !important;
            color: white !important;
        }
        .btn-accent:hover {
            background-color: #0F766E !important; /* Slightly darker teal */
            border-color: #0F766E !important;
            color: white !important;
        }
        .bg-accent {
            background-color: var(--accent) !important;
        }
    </style>
</head>
<body>

<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column flex-shrink-0 p-3" style="width: 250px;">
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
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                <strong><?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin'; ?></strong>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-grow-1 main-content">
