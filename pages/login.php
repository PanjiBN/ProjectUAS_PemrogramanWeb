<?php
if (isset($_SESSION['user'])) {
    header("Location: index.php?page=home");
    exit;
}

$error_msg = '';

// Handle Login Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (!empty($email) && !empty($password)) {
        try {
            $userClass = new User();
            $user = $userClass->login($email, $password);

            if ($user) {
                $_SESSION['user'] = $user;

                // Redirect ke halaman yang dituju setelah login (jika ada)
                if (isset($_SESSION['redirect_after_login'])) {
                    $destination = $_SESSION['redirect_after_login'];
                    unset($_SESSION['redirect_after_login']);
                    header("Location: " . $destination);
                } else {
                    // Redirect berdasarkan role
                    if ($user['role'] === 'admin') {
                        header("Location: index.php?page=admin_dashboard");
                    } else {
                        header("Location: index.php?page=home");
                    }
                }
                exit;
            } else {
                $error_msg = "Email atau password salah.";
            }
        } catch (Exception $e) {
            $error_msg = "Terjadi kesalahan: " . $e->getMessage();
        }
    } else {
        $error_msg = "Silakan isi semua bidang.";
    }
}

// cek jika ada parameter redirect untuk menyimpan tujuan setelah login (misalnya dari detail lapangan)
$redirect_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (isset($_GET['redirect']) && $_GET['redirect'] === 'detail_lapangan' && $redirect_id > 0) {
    $_SESSION['redirect_after_login'] = "index.php?page=detail_lapangan&id=" . $redirect_id;
}
?>

<div class="auth-wrapper" style="background: linear-gradient(135deg, rgba(11, 13, 16, 0.95) 0%, rgba(18, 22, 28, 0.9) 100%), url('assets/images/hero_bg.png'); background-size: cover; background-position: center;">
    <div class="auth-card text-start animate-slide-up">
        <!-- Brand/Logo -->
        <div class="text-center mb-4">
            <a class="navbar-brand text-white d-flex align-items-center justify-content-center mb-2" href="index.php?page=home" style="font-size: 1.8rem; font-weight: 800;">
                <span style="color: var(--accent-color); margin-right: 5px;"><i class="fa-solid fa-circle-nodes"></i></span>
                FUTSAL<span style="color: var(--accent-color);">HUB</span>
            </a>
            <p class="text-muted small">Masuk menggunakan akun terdaftar Anda.</p>
        </div>

        <!-- Error Alert -->
        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger bg-opacity-10 border border-danger text-danger p-2.5 rounded-3 mb-4" role="alert" style="font-size: 0.85rem;">
                <i class="fa-solid fa-circle-exclamation me-1"></i> <?= htmlspecialchars($error_msg) ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <!-- Email -->
            <div class="mb-3">
                <label class="label-custom">Alamat Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary border-opacity-25 text-muted"><i class="fa-solid fa-envelope"></i></span>
                    <input type="email" name="email" class="form-control form-control-custom" placeholder="nama@email.com" required value="budi@example.com">
                </div>
                <div class="form-text text-muted" style="font-size: 0.75rem;">Demo User: <code>budi@example.com</code> / Admin: <code>admin@futsalhub.com</code></div>
            </div>

            <!-- Password -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="label-custom mb-0">Password</label>
                    <a href="#" class="small text-decoration-none" style="color: var(--accent-color); font-size: 0.8rem;">Lupa Password?</a>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary border-opacity-25 text-muted"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" name="password" class="form-control form-control-custom" placeholder="••••••••" required value="userpassword">
                </div>
                <div class="form-text text-muted" style="font-size: 0.75rem;">Demo User Pass: <code>userpassword</code> / Admin Pass: <code>adminpassword</code></div>
            </div>

            <!-- Submit -->
            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary-custom py-2.5 fw-bold">
                    Masuk Akun
                </button>
            </div>
        </form>

        <div class="text-center mt-4 border-top border-secondary border-opacity-25 pt-3">
            <p class="text-muted small mb-2">Belum punya akun FutsalHub?</p>
            <a href="index.php?page=register" class="small text-decoration-none fw-bold" style="color: var(--accent-color);">Daftar Akun Baru Sekarang</a>
        </div>
    </div>
</div>
