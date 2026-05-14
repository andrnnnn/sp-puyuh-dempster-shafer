<?php
session_start();

// Auto-logout: sesi kadaluarsa setelah 30 menit tidak aktif
$session_timeout = 30 * 60;
if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > $session_timeout) {
        session_unset();
        session_destroy();
        session_start();
        $_SESSION['timeout_message'] = 'Sesi Anda telah berakhir karena tidak aktif. Silakan login kembali.';
        header("Location: ../auth/login.php?timeout=1");
        exit;
    }
}
$_SESSION['last_activity'] = time();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../auth/login.php");
    exit;
}
?>
