<?php
ob_start();
session_start();

// otomatis redirect jika sudah login (untuk halaman login dan register)
$assets_images_dir = __DIR__ . '/assets/images';
if (!is_dir($assets_images_dir)) {
    mkdir($assets_images_dir, 0777, true);
}
$images_mapping = [
    'hero_bg.png' => 'images/hero_bg_1781071870810.png',
    'field_vinyl.png' => 'images/field_vinyl_1781071885824.png',
    'field_turf.png' => 'images/field_turf_1781071901644.png',
    'field_parquet.png' => 'images/field_parquet_1781071916574.png'
];


foreach ($images_mapping as $dest_name => $src_path) {
    $target = $assets_images_dir . '/' . $dest_name;
    if (!file_exists($target) && file_exists($src_path)) {
        copy($src_path, $target);
    }
}

// Autoload Core Classes
require_once __DIR__ . '/config/db_config.php';
require_once __DIR__ . '/class/Database.php';
require_once __DIR__ . '/class/User.php';
require_once __DIR__ . '/class/Lapangan.php';
require_once __DIR__ . '/class/Jadwal.php';
require_once __DIR__ . '/class/Booking.php';
require_once __DIR__ . '/config/midtrans_config.php';
require_once __DIR__ . '/class/Midtrans.php';

// Handle logout action
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: index.php?page=home");
    exit;
}

// Login instan tanpa password (untuk testing)
if (isset($_GET['action']) && $_GET['action'] === 'mock_login') {
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id, nama, email, role FROM users WHERE role = 'user' LIMIT 1");
        $stmt->execute();
        $mock_user = $stmt->fetch();
        if ($mock_user) {
            $_SESSION['user'] = $mock_user;
        }
    } catch (Exception $e) {}
    header("Location: index.php?page=home");
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'mock_admin_login') {
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id, nama, email, role FROM users WHERE role = 'admin' LIMIT 1");
        $stmt->execute();
        $mock_admin = $stmt->fetch();
        if ($mock_admin) {
            $_SESSION['user'] = $mock_admin;
        }
    } catch (Exception $e) {}
    header("Location: index.php?page=admin_dashboard");
    exit;
}
$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$page = $uri === '' ? 'home' : $uri;

//tentukan halaman yang bisa diakses oleh user dan admin
$user_pages = ['home', 'about', 'contact', 'lapangan', 'detail_lapangan', 'booking', 'login', 'register', 'riwayat'];
$admin_pages = ['admin_dashboard', 'admin_lapangan', 'admin_booking', 'admin_user'];

// 1.priksa jika halaman yang diminta adalah halaman admin, jika iya pastikan user sudah login dan role nya admin
if (in_array($page, $admin_pages)) {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        header("Location: index.php?page=home");
        exit;
    }
}

// 2. Priksa jika halaman yang diminta adalah halaman user yang dilindungi, jika iya pastikan user sudah login
$protected_user_pages = ['booking', 'riwayat'];
if (in_array($page, $protected_user_pages)) {
    if (!isset($_SESSION['user'])) {
        header("Location: index.php?page=login");
        exit;
    }
}
// Tentukan path file yang akan di-include berdasarkan halaman yang diminta
if (in_array($page, $admin_pages)) {
    $admin_subpage = str_replace('admin_', '', $page);
    $page_path = "admin/" . $admin_subpage . ".php";
} else {
    $page_path = "pages/" . $page . ".php";
}

// Jika halaman tidak ditemukan, default ke halaman home
if (!file_exists($page_path)) {
    $page_path = "pages/home.php";
    $page = 'home';
}

// Render halaman
include_once "includes/header.php";
include_once "includes/navbar.php";

// Render content page
include_once $page_path;

include_once "includes/footer.php";
?>
