<?php
if (isset($_SESSION['user'])) {
    header("Location: index.php?page=home");
    exit;
}

$error_msg = '';
$success_msg = '';

// Handle Registration Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : ''; 
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (!empty($nama) && !empty($email) && !empty($password)) {
        try {
            $userClass = new User();
            $success = $userClass->register($nama, $email, $password);

            if ($success) {
                // Otomatis login setelah registrasi berhasil
                $user = $userClass->login($email, $password);
                if ($user) {
                    $_SESSION['user'] = $user;
                    header("Location: index.php?page=home&msg=registered");
                    exit;
                }
            } else {
                $error_msg = "Registrasi gagal. Silakan hubungi support.";
            }
        } catch (Exception $e) {
            $error_msg = $e->getMessage();
        }
    } else {
        $error_msg = "Silakan lengkapi semua bidang wajib.";
    }
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
            <p class="text-muted small">Buat akun FutsalHub baru untuk pemesanan instan.</p>
        </div>

        <!-- Error/Success Alerts -->
        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger bg-opacity-10 border border-danger text-danger p-2.5 rounded-3 mb-4" role="alert" style="font-size: 0.85rem;">
                <i class="fa-solid fa-circle-exclamation me-1"></i> <?= htmlspecialchars($error_msg) ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <!-- Full Name -->
            <div class="mb-3">
                <label class="label-custom">Nama Lengkap</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary border-opacity-25 text-muted"><i class="fa-solid fa-user"></i></span>
                    <input type="text" name="nama" class="form-control form-control-custom" placeholder="Nama Anda" required>
                </div>
            </div>

            <!-- Email -->
            <div class="mb-3">
                <label class="label-custom">Alamat Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary border-opacity-25 text-muted"><i class="fa-solid fa-envelope"></i></span>
                    <input type="email" name="email" class="form-control form-control-custom" placeholder="nama@email.com" required>
                </div>
            </div>

            <!-- Phone Number -->
            <div class="mb-3">
                <label class="label-custom">Nomor Telepon / WA</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary border-opacity-25 text-muted"><i class="fa-solid fa-phone"></i></span>
                    <input type="tel" name="phone" class="form-control form-control-custom" placeholder="0812XXXXXXXX" required>
                </div>
            </div>

            <!-- Password -->
            <div class="mb-4">
                <label class="label-custom">Password Baru</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary border-opacity-25 text-muted"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" name="password" class="form-control form-control-custom" placeholder="Minimal 6 karakter" required minlength="6">
                </div>
            </div>

            <!-- Submit -->
            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary-custom py-2.5 fw-bold">
                    Daftar Akun Baru
                </button>
            </div>
        </form>

        <div class="text-center mt-4 border-top border-secondary border-opacity-25 pt-3">
            <p class="text-muted small mb-2">Sudah memiliki akun FutsalHub?</p>
            <a href="index.php?page=login" class="small text-decoration-none fw-bold" style="color: var(--accent-color);">Masuk ke Akun Anda</a>
        </div>
    </div>
</div>
