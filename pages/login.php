<?php
if (isset($_SESSION['user'])) {
    header("Location: index.php?page=home");
    exit;
}

$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = isset($_POST['email'])    ? trim($_POST['email'])    : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (!empty($email) && !empty($password)) {
        try {
            $userClass = new User();
            $user = $userClass->login($email, $password);

            if ($user) {
                $_SESSION['user'] = $user;

                if (isset($_SESSION['redirect_after_login'])) {
                    $destination = $_SESSION['redirect_after_login'];
                    unset($_SESSION['redirect_after_login']);
                    header("Location: " . $destination);
                } else {
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

$redirect_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (isset($_GET['redirect']) && $_GET['redirect'] === 'detail_lapangan' && $redirect_id > 0) {
    $_SESSION['redirect_after_login'] = "index.php?page=detail_lapangan&id=" . $redirect_id;
}
?>

<style>
/* ── Animasi partikel latar belakang ── */
.auth-page-bg {
    position: fixed;
    inset: 0;
    background: radial-gradient(ellipse at 20% 50%, rgba(0,200,83,0.07) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 20%, rgba(0,200,83,0.05) 0%, transparent 50%),
                #0b0d10;
    z-index: 0;
}

.auth-page-wrap {
    min-height: calc(100vh - 80px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2.5rem 1rem;
    position: relative;
    z-index: 1;
}

/* ── Card utama dengan dua panel ── */
.auth-split-card {
    display: flex;
    width: 100%;
    max-width: 900px;
    background: rgba(14, 17, 22, 0.95);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 30px 80px rgba(0,0,0,0.6), 0 0 0 1px rgba(0,200,83,0.05);
    backdrop-filter: blur(20px);
}

/* Panel kiri — dekorasi visual */
.auth-deco-panel {
    width: 42%;
    padding: 3rem 2.5rem;
    background: linear-gradient(160deg, rgba(0,200,83,0.18) 0%, rgba(0,200,83,0.03) 60%, rgba(0,0,0,0) 100%),
                linear-gradient(to bottom, #0d1f14, #0a0d0f);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
}

.auth-deco-panel::before {
    content: '';
    position: absolute;
    width: 300px;
    height: 300px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(0,200,83,0.15) 0%, transparent 70%);
    top: -80px;
    left: -80px;
    pointer-events: none;
}

.auth-deco-panel::after {
    content: '';
    position: absolute;
    width: 200px;
    height: 200px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(0,200,83,0.1) 0%, transparent 70%);
    bottom: -50px;
    right: -50px;
    pointer-events: none;
}

.deco-logo {
    font-family: var(--font-heading);
    font-size: 1.9rem;
    font-weight: 800;
    color: #fff;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 8px;
    position: relative;
    z-index: 1;
}

.deco-logo span { color: var(--accent-color); }

.deco-tagline {
    position: relative;
    z-index: 1;
}

.deco-stat {
    background: rgba(0,200,83,0.08);
    border: 1px solid rgba(0,200,83,0.2);
    border-radius: 12px;
    padding: 1rem 1.2rem;
    margin-bottom: 0.75rem;
}

.deco-stat-number {
    font-family: var(--font-heading);
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--accent-color);
    line-height: 1;
}

.deco-stat-label {
    font-size: 0.78rem;
    color: rgba(255,255,255,0.5);
    margin-top: 2px;
}

/* Panel kanan — form */
.auth-form-panel {
    flex: 1;
    padding: 3rem 2.5rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.auth-form-title {
    font-family: var(--font-heading);
    font-size: 1.6rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: 0.4rem;
}

.auth-form-sub {
    color: rgba(255,255,255,0.4);
    font-size: 0.85rem;
    margin-bottom: 2rem;
}

/* ── Input dengan ikon toggle password ── */
.input-password-wrap {
    position: relative;
}

.input-password-wrap .form-control-custom {
    padding-right: 3rem;
}

.btn-toggle-pw {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: rgba(255,255,255,0.35);
    cursor: pointer;
    padding: 4px 6px;
    transition: color 0.2s;
    z-index: 5;
    line-height: 1;
}

.btn-toggle-pw:hover { color: var(--accent-color); }

/* Input group kustom */
.ig-wrap {
    display: flex;
    align-items: center;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 12px;
    transition: border-color 0.25s, box-shadow 0.25s;
    overflow: hidden;
}

.ig-wrap:focus-within {
    border-color: var(--accent-color);
    box-shadow: 0 0 0 4px rgba(0,200,83,0.12);
}

.ig-icon {
    width: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(255,255,255,0.3);
    font-size: 0.9rem;
    flex-shrink: 0;
}

.ig-wrap .form-control-custom {
    border: none !important;
    border-radius: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
    flex: 1;
    min-width: 0;
    padding-left: 0;
}

.ig-wrap .form-control-custom:focus {
    box-shadow: none !important;
}

/* Divider teks */
.auth-divider {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 1.5rem 0;
    color: rgba(255,255,255,0.2);
    font-size: 0.78rem;
}

.auth-divider::before,
.auth-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: rgba(255,255,255,0.07);
}

/* Submit button */
.btn-auth-submit {
    width: 100%;
    background: linear-gradient(135deg, #00C853, #00E676);
    color: #000;
    font-weight: 700;
    font-size: 0.95rem;
    padding: 0.85rem;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    font-family: var(--font-heading);
    letter-spacing: 0.3px;
}

.btn-auth-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 28px rgba(0,200,83,0.4);
}

.btn-auth-submit:active { transform: translateY(0); }

/* Alert kustom */
.auth-alert-error {
    background: rgba(220,53,69,0.1);
    border: 1px solid rgba(220,53,69,0.25);
    color: #f87171;
    border-radius: 10px;
    padding: 0.75rem 1rem;
    font-size: 0.85rem;
    margin-bottom: 1.25rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* ── Responsif ── */
@media (max-width: 768px) {
    .auth-deco-panel { display: none; }
    .auth-split-card { max-width: 460px; }
    .auth-form-panel { padding: 2.5rem 2rem; }
}
</style>

<div class="auth-page-bg"></div>

<div class="auth-page-wrap">
    <div class="auth-split-card animate-fade-in">

        <!-- ── Panel Kiri: Dekorasi ── -->
        <div class="auth-deco-panel">
            <a class="deco-logo" href="index.php?page=home">
                <i class="fa-solid fa-circle-nodes"></i>
                FUTSAL<span>HUB</span>
            </a>

            <div class="deco-tagline">
                <h2 style="font-family:var(--font-heading);font-size:1.4rem;font-weight:700;color:#fff;line-height:1.4;margin-bottom:0.75rem;">
                    Platform Booking Futsal<br>
                    <span style="color:var(--accent-color);">Paling Cepat</span> di Indonesia
                </h2>
                <p style="color:rgba(255,255,255,0.4);font-size:0.82rem;line-height:1.6;margin-bottom:2rem;">
                    Pesan lapangan favorit Anda dalam hitungan detik. 24 jam online, pembayaran aman via Midtrans.
                </p>
                <div class="deco-stat">
                    <div class="deco-stat-number">500+</div>
                    <div class="deco-stat-label">Lapangan Tersedia</div>
                </div>
                <div class="deco-stat">
                    <div class="deco-stat-number">10K+</div>
                    <div class="deco-stat-label">Booking per Bulan</div>
                </div>
                <div class="deco-stat">
                    <div class="deco-stat-number">99%</div>
                    <div class="deco-stat-label">Rating Kepuasan</div>
                </div>
            </div>

            <p style="color:rgba(255,255,255,0.2);font-size:0.72rem;position:relative;z-index:1;">
                &copy; <?= date('Y') ?> FutsalHub. All rights reserved.
            </p>
        </div>

        <!-- ── Panel Kanan: Form Login ── -->
        <div class="auth-form-panel">
            <div>
                <p class="auth-form-sub" style="margin-bottom:0.5rem;">
                    <i class="fa-solid fa-arrow-right-to-bracket me-1"></i> Selamat datang kembali
                </p>
                <h1 class="auth-form-title">Masuk ke Akun</h1>
                <p class="auth-form-sub">Gunakan akun FutsalHub terdaftar Anda.</p>
            </div>

            <!-- Alert Error -->
            <?php if (!empty($error_msg)): ?>
                <div class="auth-alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?= htmlspecialchars($error_msg) ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" id="formLogin" novalidate>

                <!-- Email -->
                <div class="mb-3">
                    <label class="label-custom" for="login_email">Alamat Email</label>
                    <div class="ig-wrap">
                        <div class="ig-icon"><i class="fa-solid fa-envelope"></i></div>
                        <input type="email" id="login_email" name="email"
                               class="form-control form-control-custom"
                               placeholder="nama@email.com" required
                               value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                    </div>
                </div>

                <!-- Password + Toggle Mata -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="label-custom mb-0" for="login_password">Password</label>
                        <a href="#" class="small text-decoration-none"
                           style="color:var(--accent-color);font-size:0.8rem;">
                            Lupa Password?
                        </a>
                    </div>
                    <div class="ig-wrap input-password-wrap">
                        <div class="ig-icon"><i class="fa-solid fa-lock"></i></div>
                        <input type="password" id="login_password" name="password"
                               class="form-control form-control-custom"
                               placeholder="••••••••" required>
                        <!-- Tombol Toggle Mata -->
                        <button type="button" class="btn-toggle-pw"
                                onclick="togglePassword('login_password', this)"
                                title="Tampilkan / Sembunyikan Password"
                                aria-label="Toggle visibility password">
                            <i class="fa-solid fa-eye" id="icon_login_password"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn-auth-submit mb-3" id="btn_login_submit">
                    <i class="fa-solid fa-right-to-bracket me-2"></i> Masuk Sekarang
                </button>
            </form>

            <div class="auth-divider">atau</div>

            <div class="text-center">
                <p class="text-muted small mb-2">Belum punya akun FutsalHub?</p>
                <a href="index.php?page=register"
                   class="fw-bold text-decoration-none"
                   style="color:var(--accent-color);font-size:0.9rem;">
                    <i class="fa-solid fa-user-plus me-1"></i> Daftar Akun Baru Gratis
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ── Script Toggle Password ── -->
<script>
/**
 * Fungsi toggle tampilan password.
 * @param {string} inputId  - ID dari input password
 * @param {HTMLElement} btn - Tombol trigger (elemen button)
 */
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon  = btn.querySelector('i');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
        btn.setAttribute('aria-label', 'Sembunyikan password');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
        btn.setAttribute('aria-label', 'Tampilkan password');
    }
    input.focus();
}
</script>
