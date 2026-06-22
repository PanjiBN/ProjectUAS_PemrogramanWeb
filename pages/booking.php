<?php
// Verify if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.php?page=login");
    exit;
}

$field_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$booking_date = isset($_GET['date']) ? trim($_GET['date']) : '';
$slots_str = isset($_GET['slots']) ? trim($_GET['slots']) : '';

if ($field_id === 0 || empty($booking_date) || empty($slots_str)) {
    echo "<div class='container py-5 text-center'><div class='alert alert-danger py-4 card-custom text-white'>Parameter pemesanan tidak lengkap. Silakan pilih lapangan terlebih dahulu.</div><a href='index.php?page=lapangan' class='btn btn-primary-custom mt-3'>Kembali ke Lapangan</a></div>";
    return;
}

// Fetch field info from DB
$lapanganClass = new Lapangan();
$field = $lapanganClass->getById($field_id);

if (!$field) {
    echo "<div class='container py-5 text-center'><div class='alert alert-danger py-4 card-custom text-white'>Lapangan tidak ditemukan.</div><a href='index.php?page=lapangan' class='btn btn-primary-custom mt-3'>Kembali ke Lapangan</a></div>";
    return;
}

// Load chosen schedule slots from DB
$db = Database::getConnection();
$slots_arr = explode(',', $slots_str);
$slots_placeholders = implode(',', array_fill(0, count($slots_arr), '?'));

$stmt = $db->prepare("SELECT * FROM jadwal WHERE id_jadwal IN ($slots_placeholders) ORDER BY jam_mulai ASC");
$stmt->execute($slots_arr);
$selected_schedules = $stmt->fetchAll();

$num_hours = count($selected_schedules);
if ($num_hours === 0) {
    echo "<div class='container py-5 text-center'><div class='alert alert-danger py-4 card-custom text-white'>Waktu jadwal yang dipilih tidak valid.</div><a href='index.php?page=lapangan' class='btn btn-primary-custom mt-3'>Kembali ke Lapangan</a></div>";
    return;
}

$subtotal = $num_hours * $field['harga'];
$service_fee = 5000;
$total_payment = $subtotal + $service_fee;

// Format date
$formatted_date = date('d F Y', strtotime($booking_date));

// Determine type label dynamically
$tipe_label = 'Premium Vinyl';
if (stripos($field['nama_lapangan'], 'turf') !== false) {
    $tipe_label = 'Synthetic Turf';
} elseif (stripos($field['nama_lapangan'], 'parquet') !== false) {
    $tipe_label = 'Hardwood Parquet';
}
?>

<div class="py-5" style="background: var(--bg-dark);">
    <div class="container text-start animate-fade-in">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="text-white fw-bold"><i class="fa-solid fa-file-invoice-dollar text-success me-2" style="color: var(--accent-color) !important;"></i> Konfirmasi Pemesanan</h2>
                <p class="text-muted">Periksa kembali detail pemesanan Anda sebelum melakukan konfirmasi pembayaran.</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Column: Checkout Details Form -->
            <div class="col-lg-7">
                <!-- Form data (tidak lagi POST ke riwayat, dihandle AJAX) -->
                <div id="checkout-form">
                    <!-- Hidden fields untuk AJAX -->
                    <input type="hidden" id="input-id-jadwals" value="<?= htmlspecialchars($slots_str) ?>">
                    <input type="hidden" id="input-total-harga" value="<?= $total_payment ?>">
                    
                    <!-- Form Customer Details -->
                    <div class="card-custom p-4 mb-4">
                        <h5 class="text-white mb-4"><i class="fa-regular fa-address-card text-success me-2" style="color: var(--accent-color) !important;"></i> Informasi Pemesan</h5>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="label-custom">Nama Lengkap</label>
                                <input type="text" class="form-control form-control-custom" value="<?= htmlspecialchars($_SESSION['user']['nama']) ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="label-custom">Email</label>
                                <input type="email" class="form-control form-control-custom" value="<?= htmlspecialchars($_SESSION['user']['email']) ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="label-custom">Nomor Telepon / WhatsApp</label>
                                <input type="tel" id="input-telepon" class="form-control form-control-custom" placeholder="Contoh: 0812XXXXXXXX" required value="08129876543">
                            </div>
                            <div class="col-md-6">
                                <label class="label-custom">Nama Tim (Opsional)</label>
                                <input type="text" class="form-control form-control-custom" placeholder="Contoh: FC Karyawan">
                            </div>
                            <div class="col-12">
                                <label class="label-custom">Catatan Tambahan (Opsional)</label>
                                <textarea class="form-control form-control-custom" rows="2" placeholder="Tulis catatan di sini, misal: minta rompi cadangan"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method Info -->
                    <div class="card-custom p-4 mb-4">
                        <h5 class="text-white mb-4"><i class="fa-solid fa-credit-card text-success me-2" style="color: var(--accent-color) !important;"></i> Metode Pembayaran</h5>
                        
                        <div class="d-flex align-items-center gap-3 p-3 border rounded" style="border-color: var(--accent-color) !important; background: rgba(0, 200, 83, 0.05);">
                            <div class="d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; border-radius: 12px; background: rgba(0, 200, 83, 0.15);">
                                <i class="fa-solid fa-shield-halved fs-5" style="color: var(--accent-color);"></i>
                            </div>
                            <div>
                                <h6 class="text-white mb-1">Midtrans Secure Payment</h6>
                                <p class="text-muted small mb-0">Pilih metode pembayaran (Bank Transfer, E-Wallet, QRIS, dll) pada popup pembayaran yang akan muncul setelah klik tombol di bawah.</p>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <span class="badge bg-dark border border-secondary border-opacity-50 text-white-50 px-2 py-1" style="font-size: 0.75rem;">BCA VA</span>
                            <span class="badge bg-dark border border-secondary border-opacity-50 text-white-50 px-2 py-1" style="font-size: 0.75rem;">Mandiri VA</span>
                            <span class="badge bg-dark border border-secondary border-opacity-50 text-white-50 px-2 py-1" style="font-size: 0.75rem;">BNI VA</span>
                            <span class="badge bg-dark border border-secondary border-opacity-50 text-white-50 px-2 py-1" style="font-size: 0.75rem;">BRI VA</span>
                            <span class="badge bg-dark border border-secondary border-opacity-50 text-white-50 px-2 py-1" style="font-size: 0.75rem;">GoPay</span>
                            <span class="badge bg-dark border border-secondary border-opacity-50 text-white-50 px-2 py-1" style="font-size: 0.75rem;">ShopeePay</span>
                            <span class="badge bg-dark border border-secondary border-opacity-50 text-white-50 px-2 py-1" style="font-size: 0.75rem;">QRIS</span>
                            <span class="badge bg-dark border border-secondary border-opacity-50 text-white-50 px-2 py-1" style="font-size: 0.75rem;">Indomaret</span>
                            <span class="badge bg-dark border border-secondary border-opacity-50 text-white-50 px-2 py-1" style="font-size: 0.75rem;">Alfamart</span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid mb-5">
                        <button type="button" id="btn-pay-now" class="btn btn-primary-custom py-3 fw-bold fs-5" onclick="processCheckout()">
                            <i class="fa-solid fa-lock me-2"></i> Konfirmasi & Bayar Sekarang
                        </button>
                    </div>
                    

                    <!-- Loading overlay -->
                    <div id="checkout-loading" class="d-none">
                        <div class="card-custom p-4 text-center mb-4">
                            <div class="spinner-border mb-3" style="color: var(--accent-color);" role="status"></div>
                            <p class="text-white mb-0">Memproses pembayaran, mohon tunggu...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Booking Summary -->
            <div class="col-lg-5">
                <div class="card-custom p-4 sticky-top" style="top: 100px; z-index: 5;">
                    <h5 class="text-white mb-4"><i class="fa-solid fa-cart-shopping text-success me-2" style="color: var(--accent-color) !important;"></i> Ringkasan Booking</h5>
                    
                    <!-- Field Visual Info -->
                    <div class="d-flex gap-3 mb-4">
                        <img src="<?= htmlspecialchars($field['gambar']) ?>" style="width: 110px; height: 70px; object-fit: cover; border-radius: var(--radius-sm);" alt="<?= htmlspecialchars($field['nama_lapangan']) ?>">
                        <div>
                            <h6 class="text-white mb-1 fw-bold"><?= htmlspecialchars($field['nama_lapangan']) ?></h6>
                            <p class="text-muted small mb-0"><i class="fa-solid fa-location-dot me-1"></i> <?= htmlspecialchars($field['lokasi']) ?></p>
                            <span class="badge bg-secondary mt-1" style="font-size: 0.7rem;"><?= $tipe_label ?></span>
                        </div>
                    </div>

                    <hr class="border-secondary opacity-25 my-3">

                    <!-- Schedule Selection Summary -->
                    <div class="mb-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="text-success" style="color: var(--accent-color) !important;"><i class="fa-regular fa-calendar-check"></i></span>
                            <span class="text-white font-weight-500">Tanggal: <?= $formatted_date ?></span>
                        </div>
                        <div class="d-flex align-items-start gap-2">
                            <span class="text-success mt-1" style="color: var(--accent-color) !important;"><i class="fa-regular fa-clock"></i></span>
                            <div>
                                <span class="text-white d-block">Jam Main (<?= $num_hours ?> Jam):</span>
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    <?php foreach ($selected_schedules as $sched): ?>
                                        <?php $time_label = date('H:i', strtotime($sched['jam_mulai'])) . ' - ' . date('H:i', strtotime($sched['jam_selesai'])); ?>
                                        <span class="badge bg-dark border border-secondary border-opacity-50 text-white-50 px-2 py-1" style="font-size: 0.75rem;"><?= $time_label ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="border-secondary opacity-25 my-3">

                    <!-- Price breakdown -->
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Sewa Lapangan (<?= $num_hours ?> jam)</span>
                        <span class="text-white">Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Biaya Administrasi/Layanan</span>
                        <span class="text-white">Rp <?= number_format($service_fee, 0, ',', '.') ?></span>
                    </div>
                    
                    <div class="p-3 border-top border-secondary border-opacity-50 d-flex justify-content-between align-items-center" style="background: rgba(255, 255, 255, 0.01); border-radius: var(--radius-sm);">
                        <span class="text-white fw-bold">Total Tagihan:</span>
                        <span class="fs-4 text-success fw-bold" style="color: var(--accent-color) !important;">Rp <?= number_format($total_payment, 0, ',', '.') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Midtrans Snap Payment Script -->
<script>
function processCheckout() {
    const btn = document.getElementById('btn-pay-now');
    const loadingEl = document.getElementById('checkout-loading');
    
    // Validasi telepon
    const telepon = document.getElementById('input-telepon').value.trim();
    if (!telepon) {
        alert('Mohon isi nomor telepon terlebih dahulu.');
        return;
    }

    // Disable button & show loading
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';
    loadingEl.classList.remove('d-none');

    // Data untuk API
    const data = {
        id_jadwals: document.getElementById('input-id-jadwals').value,
        total_harga: parseInt(document.getElementById('input-total-harga').value)
    };

    // AJAX ke create_transaction endpoint
    fetch('api/create_transaction.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        loadingEl.classList.add('d-none');

        if (!result.success) {
            throw new Error(result.message || 'Gagal membuat transaksi.');
        }

        // Buka Midtrans Snap Popup
        window.snap.pay(result.snap_token, {
            onSuccess: function(res) {
                // Pembayaran berhasil
                window.location.href = 'index.php?page=riwayat&msg=paid';
            },
            onPending: function(res) {
                // Menunggu pembayaran
                window.location.href = 'index.php?page=riwayat&msg=success';
            },
            onError: function(res) {
                // Pembayaran gagal
                alert('Pembayaran gagal. Silakan coba lagi.');
                window.location.href = 'index.php?page=riwayat';
            },
            onClose: function() {
                // User menutup popup tanpa menyelesaikan pembayaran
                // Booking tetap pending, bisa bayar nanti dari riwayat
                window.location.href = 'index.php?page=riwayat&msg=success';
            }
        });
    })
    .catch(error => {
        loadingEl.classList.add('d-none');
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-lock me-2"></i> Konfirmasi & Bayar Sekarang';
        alert('Error: ' + error.message);
    });
}
</script>
