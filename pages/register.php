<?php
if (isset($_SESSION['user'])) {
    header("Location: index.php?page=home");
    exit;
}

$error_msg   = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = isset($_POST['nama'])     ? trim($_POST['nama'])     : '';
    $email    = isset($_POST['email'])    ? trim($_POST['email'])    : '';
    $phone    = isset($_POST['phone'])    ? trim($_POST['phone'])    : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirm  = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';

    // Validasi konfirmasi password
    if (!empty($nama) && !empty($email) && !empty($password)) {
        if ($password !== $confirm) {
            $error_msg = "Password dan konfirmasi password tidak cocok.";
        } elseif (strlen($password) < 6) {
            $error_msg = "Password minimal 6 karakter.";
        } else {
            try {
                $userClass = new User();
                $success   = $userClass->register($nama, $email, $password);

                if ($success) {
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
        }
    } else {
        $error_msg = "Silakan lengkapi semua bidang wajib.";
    }
}
?>

<style>
/* Reuse variabel auth dari login.php — definisikan di sini juga
   agar halaman register bisa berdiri sendiri */
.auth-page-bg {
    position: fixed;
    inset: 0;
    background: radial-gradient(ellipse at 80% 50%, rgba(0,200,83,0.07) 0%, transparent 60%),
                radial-gradient(ellipse at 20% 20%, rgba(0,200,83,0.05) 0%, transparent 50%),
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

.auth-split-card {
    display: flex;
    width: 100%;
    max-width: 960px;
    background: rgba(14, 17, 22, 0.95);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 30px 80px rgba(0,0,0,0.6), 0 0 0 1px rgba(0,200,83,0.05);
    backdrop-filter: blur(20px);
}

/* Panel kiri — form (posisi terbalik dari login) */
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
    margin-bottom: 1.75rem;
}

/* Panel kanan — dekorasi */
.auth-deco-panel {
    width: 40%;
    padding: 3rem 2.5rem;
    background: linear-gradient(160deg, rgba(0,200,83,0.18) 0%, rgba(0,200,83,0.03) 60%, rgba(0,0,0,0) 100%),
                linear-gradient(to bottom, #0d1f14, #0a0d0f);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
    order: 2; /* tampil di kanan */
}

.auth-deco-panel::before {
    content: '';
    position: absolute;
    width: 280px;
    height: 280px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(0,200,83,0.14) 0%, transparent 70%);
    bottom: -60px;
    right: -60px;
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

/* Checklist fitur */
.deco-feature {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 1rem;
    position: relative;
    z-index: 1;
}

.deco-feature-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: rgba(0,200,83,0.15);
    border: 1px solid rgba(0,200,83,0.25);
    color: var(--accent-color);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    flex-shrink: 0;
}

.deco-feature-text strong {
    display: block;
    color: rgba(255,255,255,0.85);
    font-size: 0.85rem;
    font-weight: 600;
}

.deco-feature-text span {
    color: rgba(255,255,255,0.35);
    font-size: 0.75rem;
}

/* Input group */
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

/* Toggle mata */
.input-password-wrap { position: relative; }

.btn-toggle-pw {
    flex-shrink: 0;
    background: none;
    border: none;
    color: rgba(255,255,255,0.35);
    cursor: pointer;
    padding: 0 14px;
    transition: color 0.2s;
    font-size: 0.9rem;
    line-height: 1;
    align-self: stretch;
    display: flex;
    align-items: center;
}

.btn-toggle-pw:hover { color: var(--accent-color); }

/* Password strength bar */
.pw-strength-bar {
    height: 4px;
    border-radius: 4px;
    background: rgba(255,255,255,0.07);
    margin-top: 8px;
    overflow: hidden;
}

.pw-strength-fill {
    height: 100%;
    border-radius: 4px;
    width: 0%;
    transition: width 0.4s ease, background 0.4s ease;
}

.pw-strength-label {
    font-size: 0.72rem;
    margin-top: 4px;
    color: rgba(255,255,255,0.35);
    transition: color 0.3s;
}

/* Submit */
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

/* Alert */
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

/* Divider */
.auth-divider {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 1.25rem 0;
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

/* Syarat password tooltip */
.pw-hint {
    font-size: 0.72rem;
    color: rgba(255,255,255,0.3);
    margin-top: 4px;
    padding-left: 4px;
}

/* Match indicator */
.pw-match-ok  { color: #4ade80; }
.pw-match-err { color: #f87171; }

@media (max-width: 768px) {
    .auth-deco-panel { display: none; }
    .auth-split-card { max-width: 480px; }
    .auth-form-panel { padding: 2.5rem 2rem; }
}
</style>

<div class="auth-page-bg"></div>

<div class="auth-page-wrap">
    <div class="auth-split-card animate-fade-in">

        <!-- ── Panel Kiri: Form Register ── -->
        <div class="auth-form-panel">
            <div>
                <p class="auth-form-sub" style="margin-bottom:0.5rem;">
                    <i class="fa-solid fa-user-plus me-1"></i> Buat akun baru
                </p>
                <h1 class="auth-form-title">Daftar ke FutsalHub</h1>
                <p class="auth-form-sub">Gratis selamanya. Tidak ada biaya pendaftaran.</p>
            </div>

            <!-- Alert Error -->
            <?php if (!empty($error_msg)): ?>
                <div class="auth-alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?= htmlspecialchars($error_msg) ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" id="formRegister" novalidate>

                <!-- Nama Lengkap -->
                <div class="mb-3">
                    <label class="label-custom" for="reg_nama">Nama Lengkap</label>
                    <div class="ig-wrap">
                        <div class="ig-icon"><i class="fa-solid fa-user"></i></div>
                        <input type="text" id="reg_nama" name="nama"
                               class="form-control form-control-custom"
                               placeholder="Nama lengkap Anda" required
                               value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : '' ?>">
                    </div>
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label class="label-custom" for="reg_email">Alamat Email</label>
                    <div class="ig-wrap">
                        <div class="ig-icon"><i class="fa-solid fa-envelope"></i></div>
                        <input type="email" id="reg_email" name="email"
                               class="form-control form-control-custom"
                               placeholder="nama@email.com" required
                               value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                    </div>
                </div>

                <!-- Nomor Telepon -->
                <div class="mb-3">
                    <label class="label-custom" for="reg_phone">Nomor Telepon / WhatsApp</label>
                    <div class="ig-wrap">
                        <div class="ig-icon"><i class="fa-solid fa-phone"></i></div>
                        <input type="tel" id="reg_phone" name="phone"
                               class="form-control form-control-custom"
                               placeholder="0812XXXXXXXX" required
                               value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
                    </div>
                </div>

                <!-- Password baru + toggle + strength bar -->
                <div class="mb-3">
                    <label class="label-custom" for="reg_password">Password Baru</label>
                    <div class="ig-wrap">
                        <div class="ig-icon"><i class="fa-solid fa-lock"></i></div>
                        <input type="password" id="reg_password" name="password"
                               class="form-control form-control-custom"
                               placeholder="Minimal 6 karakter" required minlength="6"
                               oninput="checkStrength(this.value)">
                        <button type="button" class="btn-toggle-pw"
                                onclick="togglePassword('reg_password', this)"
                                title="Tampilkan / Sembunyikan Password"
                                aria-label="Toggle visibility password">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <!-- Strength Bar -->
                    <div class="pw-strength-bar">
                        <div class="pw-strength-fill" id="pw_strength_fill"></div>
                    </div>
                    <div class="pw-strength-label" id="pw_strength_label">Minimal 6 karakter</div>
                </div>

                <!-- Konfirmasi Password + toggle + match indicator -->
                <div class="mb-4">
                    <label class="label-custom" for="reg_confirm">Konfirmasi Password</label>
                    <div class="ig-wrap">
                        <div class="ig-icon"><i class="fa-solid fa-shield-halved"></i></div>
                        <input type="password" id="reg_confirm" name="confirm_password"
                               class="form-control form-control-custom"
                               placeholder="Ulangi password Anda" required
                               oninput="checkMatch()">
                        <button type="button" class="btn-toggle-pw"
                                onclick="togglePassword('reg_confirm', this)"
                                title="Tampilkan / Sembunyikan Konfirmasi"
                                aria-label="Toggle visibility konfirmasi password">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <div class="pw-hint" id="pw_match_status"></div>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn-auth-submit mb-3" id="btn_register_submit">
                    <i class="fa-solid fa-user-check me-2"></i> Buat Akun Sekarang
                </button>
            </form>

            <div class="auth-divider">atau</div>

            <div class="text-center">
                <p class="text-muted small mb-2">Sudah memiliki akun FutsalHub?</p>
                <a href="index.php?page=login"
                   class="fw-bold text-decoration-none"
                   style="color:var(--accent-color);font-size:0.9rem;">
                    <i class="fa-solid fa-right-to-bracket me-1"></i> Masuk ke Akun
                </a>
            </div>
        </div>

        <!-- ── Panel Kanan: Dekorasi Fitur ── -->
        <div class="auth-deco-panel">
            <a class="deco-logo" href="index.php?page=home">
                <i class="fa-solid fa-circle-nodes"></i>
                FUTSAL<span>HUB</span>
            </a>

            <div style="position:relative;z-index:1;">
                <h2 style="font-family:var(--font-heading);font-size:1.35rem;font-weight:700;color:#fff;line-height:1.4;margin-bottom:0.5rem;">
                    Kenapa Daftar di<br>
                    <span style="color:var(--accent-color);">FutsalHub?</span>
                </h2>
                <p style="color:rgba(255,255,255,0.35);font-size:0.8rem;margin-bottom:1.75rem;">
                    Bergabung dengan puluhan ribu pemain aktif.
                </p>

                <div class="deco-feature">
                    <div class="deco-feature-icon"><i class="fa-solid fa-bolt"></i></div>
                    <div class="deco-feature-text">
                        <strong>Booking Instan</strong>
                        <span>Konfirmasi real-time, tidak perlu tunggu lama</span>
                    </div>
                </div>

                <div class="deco-feature">
                    <div class="deco-feature-icon"><i class="fa-solid fa-shield-halved"></i></div>
                    <div class="deco-feature-text">
                        <strong>Pembayaran Aman</strong>
                        <span>Didukung Midtrans — transfer bank, QRIS, e-wallet</span>
                    </div>
                </div>

                <div class="deco-feature">
                    <div class="deco-feature-icon"><i class="fa-solid fa-ticket"></i></div>
                    <div class="deco-feature-text">
                        <strong>E-Tiket Otomatis</strong>
                        <span>Terima tiket digital langsung di email Anda</span>
                    </div>
                </div>

                <div class="deco-feature">
                    <div class="deco-feature-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                    <div class="deco-feature-text">
                        <strong>Riwayat Booking</strong>
                        <span>Pantau semua reservasi dari satu dashboard</span>
                    </div>
                </div>

                <div class="deco-feature">
                    <div class="deco-feature-icon"><i class="fa-solid fa-headset"></i></div>
                    <div class="deco-feature-text">
                        <strong>Support 24/7</strong>
                        <span>Tim kami siap membantu kapan saja</span>
                    </div>
                </div>
            </div>

            <p style="color:rgba(255,255,255,0.2);font-size:0.72rem;position:relative;z-index:1;">
                &copy; <?= date('Y') ?> FutsalHub. Gratis untuk bergabung.
            </p>
        </div>

    </div>
</div>

<!-- ── Script: Toggle Mata + Strength Bar + Match Check ── -->
<script>
/**
 * Toggle tampilan password (show/hide).
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

/**
 * Indikator kekuatan password.
 * Kriteria: panjang, huruf kapital, angka, karakter spesial.
 */
function checkStrength(val) {
    const fill  = document.getElementById('pw_strength_fill');
    const label = document.getElementById('pw_strength_label');

    let score = 0;
    if (val.length >= 6)  score++;
    if (val.length >= 10) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const levels = [
        { pct: '0%',   color: 'transparent',                   text: 'Minimal 6 karakter' },
        { pct: '25%',  color: '#ef4444',                       text: 'Lemah' },
        { pct: '50%',  color: '#f97316',                       text: 'Cukup' },
        { pct: '75%',  color: '#eab308',                       text: 'Kuat' },
        { pct: '90%',  color: '#22c55e',                       text: 'Sangat Kuat' },
        { pct: '100%', color: 'var(--accent-color)',            text: '💪 Sempurna!' },
    ];

    const level = levels[Math.min(score, 5)];
    fill.style.width      = level.pct;
    fill.style.background = level.color;
    label.textContent     = val.length === 0 ? 'Minimal 6 karakter' : level.text;
    label.style.color     = val.length === 0 ? 'rgba(255,255,255,0.3)' : level.color;

    checkMatch(); // perbarui match jika confirm sudah diisi
}

/**
 * Pengecekan kesesuaian password & konfirmasi password.
 */
function checkMatch() {
    const pw      = document.getElementById('reg_password').value;
    const confirm = document.getElementById('reg_confirm').value;
    const status  = document.getElementById('pw_match_status');

    if (confirm.length === 0) {
        status.textContent = '';
        status.className   = 'pw-hint';
        return;
    }

    if (pw === confirm) {
        status.textContent = '✓ Password cocok';
        status.className   = 'pw-hint pw-match-ok';
    } else {
        status.textContent = '✗ Password tidak cocok';
        status.className   = 'pw-hint pw-match-err';
    }
}

/* Cegah submit jika password tidak cocok */
document.getElementById('formRegister').addEventListener('submit', function(e) {
    const pw      = document.getElementById('reg_password').value;
    const confirm = document.getElementById('reg_confirm').value;

    if (pw !== confirm) {
        e.preventDefault();
        document.getElementById('pw_match_status').textContent = '✗ Password tidak cocok — harap periksa kembali';
        document.getElementById('pw_match_status').className   = 'pw-hint pw-match-err';
        document.getElementById('reg_confirm').focus();
    }
});
</script>
