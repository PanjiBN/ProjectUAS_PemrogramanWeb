<?php
if (!isset($_SESSION['user'])) {
    header("Location: index.php?page=login");
    exit;
}

$bookingClass = new Booking();
$id_user = $_SESSION['user']['id'];

// 1. Menyimpan data pesanan dari form checkout ke database (sebagai metode cadangan versi lama)
if (isset($_GET['action']) && $_GET['action'] === 'insert_booking' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_jadwals_str = isset($_POST['id_jadwals']) ? trim($_POST['id_jadwals']) : '';
    $total_harga = isset($_POST['total_harga']) ? intval($_POST['total_harga']) : 0;
    
    if (!empty($id_jadwals_str) && $total_harga > 0) {
        try {
            $id_jadwals = explode(',', $id_jadwals_str);
            $num_slots = count($id_jadwals);
            
            // Membagi total harga secara proporsional ke masing-masing jadwal
            $price_per_slot = intval($total_harga / $num_slots);
            
            foreach ($id_jadwals as $id_jadwal) {
                $bookingClass->createBooking($id_user, intval($id_jadwal), $price_per_slot);
            }
            
            header("Location: index.php?page=riwayat&msg=success");
            exit;
        } catch (Exception $e) {
            die("<div class='container py-5 text-center'><div class='alert alert-danger py-4 card-custom text-white'><h4>Gagal Membuat Booking</h4><p class='mb-0'>Error: {$e->getMessage()}</p></div><a href='index.php?page=lapangan' class='btn btn-primary-custom mt-3'>Kembali</a></div>");
        }
    }
}

// 2.// Memproses eksekusi pembatalan pesanan langsung ke dalam database
if (isset($_GET['action']) && $_GET['action'] === 'cancel_mock') {
    $id_booking = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
    if ($id_booking > 0) {
        try {
            $bookingClass->updateStatus($id_booking, 'batal');
            header("Location: index.php?page=riwayat&msg=cancelled");
            exit;
        } catch (Exception $e) {
            die("Error cancelling booking: " . $e->getMessage());
        }
    }
}

// Ambil data booking untuk pengguna yang sedang login
$bookings = $bookingClass->getBookingsByUser($id_user);
?>

<div class="py-5" style="background: var(--bg-dark); min-height: 80vh;">
    <div class="container text-start animate-fade-in">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php?page=home" style="color: var(--accent-color); text-decoration: none;">Home</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Riwayat Booking</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="text-white fw-bold"><i class="fa-solid fa-clock-rotate-left text-success me-2" style="color: var(--accent-color) !important;"></i> Riwayat Booking Anda</h2>
                <p class="text-muted">Pantau status pembayaran dan tiket aktif lapangan futsal Anda.</p>
            </div>
            <a href="index.php?page=lapangan" class="btn btn-primary-custom"><i class="fa-solid fa-plus me-1"></i> Booking Baru</a>
        </div>

        <!-- Alerts -->
        <?php if (isset($_GET['msg'])): ?>
            <?php if ($_GET['msg'] === 'success'): ?>
                <div class="alert alert-success bg-opacity-10 border border-success text-success p-3 rounded-3 mb-4 animate-fade-in" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i> Booking berhasil! Silakan selesaikan pembayaran melalui Midtrans.
                </div>
            <?php elseif ($_GET['msg'] === 'paid'): ?>
                <div class="alert alert-success bg-opacity-10 border border-success text-success p-3 rounded-3 mb-4 animate-fade-in" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i> Pembayaran berhasil dikonfirmasi! Status booking telah diperbarui ke <strong>LUNAS</strong>.
                </div>
            <?php elseif ($_GET['msg'] === 'cancelled'): ?>
                <div class="alert alert-warning bg-opacity-10 border border-warning text-warning p-3 rounded-3 mb-4 animate-fade-in" role="alert">
                    <i class="fa-solid fa-circle-exclamation me-2"></i> Booking telah berhasil dibatalkan. Jadwal lapangan dibebaskan kembali.
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (count($bookings) > 0): ?>
            <div class="table-responsive">
                <table class="table table-custom align-middle">
                    <thead>
                        <tr>
                            <th>Kode Booking</th>
                            <th>Lapangan</th>
                            <th>Tanggal Main</th>
                            <th>Waktu Bermain</th>
                            <th>Total Tagihan</th>
                            <th>Pembayaran</th>
                            <th>Status</th>
                            <th class="text-center">Aksi / Tiket</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                            <?php 
                                $bk_code = 'BK-' . str_pad($b['id_booking'], 4, '0', STR_PAD_LEFT);
                                $time_slot_label = date('H:i', strtotime($b['jam_mulai'])) . ' - ' . date('H:i', strtotime($b['jam_selesai']));
                                $payment_label = !empty($b['payment_type']) ? ucfirst(str_replace('_', ' ', $b['payment_type'])) : '-';
                            ?>
                            <tr>
                                <td class="fw-bold" style="color: var(--accent-color);"><?= $bk_code ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?= htmlspecialchars($b['gambar']) ?>" style="width: 50px; height: 35px; object-fit: cover; border-radius: var(--radius-sm);" alt="">
                                        <span><?= htmlspecialchars($b['nama_lapangan']) ?></span>
                                    </div>
                                </td>
                                <td><?= date('d F Y', strtotime($b['tanggal'])) ?></td>
                                <td>
                                    <span class="text-white-50" style="font-size: 0.85rem;"><i class="fa-regular fa-clock me-1 text-success"></i> <?= $time_slot_label ?></span>
                                </td>
                                <td class="fw-bold">Rp <?= number_format($b['total_harga'], 0, ',', '.') ?></td>
                                <td>
                                    <?php if (!empty($b['payment_type'])): ?>
                                        <span class="badge bg-dark border border-secondary border-opacity-50 text-white-50 px-2 py-1" style="font-size: 0.7rem;">
                                            <i class="fa-solid fa-credit-card me-1"></i><?= $payment_label ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($b['status'] === 'pending'): ?>
                                        <span class="badge-status pending"><i class="fa-solid fa-circle-notch fa-spin me-1"></i> Pending</span>
                                    <?php elseif ($b['status'] === 'lunas'): ?>
                                        <span class="badge-status lunas"><i class="fa-solid fa-check me-1"></i> Lunas</span>
                                    <?php elseif ($b['status'] === 'expired'): ?>
                                        <span class="badge-status batal"><i class="fa-solid fa-clock me-1"></i> Expired</span>
                                    <?php else: ?>
                                        <span class="badge-status batal"><i class="fa-solid fa-times me-1"></i> Batal</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($b['status'] === 'pending'): ?>
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" class="btn btn-sm btn-success text-black fw-bold px-3" style="background: var(--accent-color); border: none;" onclick="payBooking(<?= $b['id_booking'] ?>)">
                                                <i class="fa-solid fa-wallet me-1"></i> Bayar
                                            </button>
                                            <a href="index.php?page=riwayat&action=cancel_mock&booking_id=<?= $b['id_booking'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin membatalkan booking ini?')">Batal</a>
                                        </div>
                                    <?php elseif ($b['status'] === 'lunas'): ?>
                                        <button class="btn btn-sm btn-outline-custom text-success border-success border-opacity-50 px-3" data-bs-toggle="modal" data-bs-target="#ticketModal<?= $b['id_booking'] ?>">
                                            <i class="fa-solid fa-ticket me-1"></i> Lihat Tiket
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <!-- E-Tiket Modal (tetap dipertahankan) -->
                            <?php if ($b['status'] === 'lunas'): ?>
                                <div class="modal fade" id="ticketModal<?= $b['id_booking'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content text-start" style="background-color: var(--card-dark); border: 1px solid var(--border-dark);">
                                            <div class="modal-header border-secondary border-opacity-25">
                                                <h5 class="modal-title text-white"><i class="fa-solid fa-ticket text-success me-2" style="color: var(--accent-color) !important;"></i> E-Tiket FutsalHub</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-white text-center py-4">
                                                <!-- Barcode representation -->
                                                <div class="mb-4">
                                                    <i class="fa-solid fa-qrcode fs-1 mb-2 text-white"></i>
                                                    <div class="text-white fw-bold tracking-widest mt-1" style="font-size: 1.1rem; color: var(--accent-color) !important;"><?= $bk_code ?></div>
                                                </div>
                                                
                                                <div class="border-top border-bottom border-secondary border-opacity-25 py-3 mb-4 text-start">
                                                    <div class="row g-2">
                                                        <div class="col-5 text-muted">Nama Lapangan:</div>
                                                        <div class="col-7 fw-bold text-white"><?= htmlspecialchars($b['nama_lapangan']) ?></div>
                                                        
                                                        <div class="col-5 text-muted">Tanggal:</div>
                                                        <div class="col-7 text-white"><?= date('d F Y', strtotime($b['tanggal'])) ?></div>
                                                        
                                                        <div class="col-5 text-muted">Waktu:</div>
                                                        <div class="col-7 text-white"><?= $time_slot_label ?></div>
                                                        
                                                        <div class="col-5 text-muted">Nama Pemesan:</div>
                                                        <div class="col-7 text-white"><?= htmlspecialchars($_SESSION['user']['nama']) ?></div>

                                                        <?php if (!empty($b['payment_type'])): ?>
                                                        <div class="col-5 text-muted">Metode Bayar:</div>
                                                        <div class="col-7 text-white"><?= $payment_label ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                
                                                <p class="small text-muted mb-0"><i class="fa-solid fa-circle-exclamation me-1 text-warning"></i> Tunjukkan QR Code ini kepada pengawas lapangan FutsalHub di lokasi saat jam bermain dimulai.</p>
                                            </div>
                                            <div class="modal-footer border-secondary border-opacity-25 justify-content-center">
                                                <button type="button" class="btn btn-sm btn-outline-custom text-white" data-bs-dismiss="modal"><i class="fa-solid fa-print me-1"></i> Cetak / Simpan PDF</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="card-custom text-center py-5 px-4 animate-slide-up">
                <div class="fs-1 text-muted mb-3"><i class="fa-solid fa-clock-rotate-left"></i></div>
                <h4 class="text-white">Belum Ada Riwayat Pemesanan</h4>
                <p class="text-muted mx-auto mb-4" style="max-width: 450px;">Anda belum memiliki riwayat reservasi aktif. Mulai memesan lapangan futsal Anda sekarang!</p>
                <a href="index.php?page=lapangan" class="btn btn-primary-custom">Cari Lapangan Sekarang</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Midtrans Snap Payment Script for Riwayat Page -->
<script>
/**
 * Sinkronkan status pembayaran ke DB kita (query ke Midtrans API)
 * sebelum redirect — memastikan status langsung 'lunas' tanpa menunggu webhook.
 */
function checkAndSyncPaymentStatus(orderId, redirectUrl, maxRetries = 3, attempt = 1) {
    fetch('api/check_payment_status.php?order_id=' + encodeURIComponent(orderId))
        .then(r => r.json())
        .then(data => {
            if (data.success && (data.status === 'lunas' || data.transaction_status === 'settlement' || data.transaction_status === 'capture')) {
                window.location.href = redirectUrl + '&payment=confirmed';
            } else if (attempt < maxRetries) {
                setTimeout(() => checkAndSyncPaymentStatus(orderId, redirectUrl, maxRetries, attempt + 1), 2000);
            } else {
                window.location.href = redirectUrl;
            }
        })
        .catch(() => {
            window.location.href = redirectUrl;
        });
}

function payBooking(bookingId) {
    // Request snap token via AJAX
    fetch('api/create_transaction.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_booking: bookingId })
    })
    .then(response => response.json())
    .then(result => {
        if (!result.success) {
            throw new Error(result.message || 'Gagal mendapatkan token pembayaran.');
        }

        const orderId = result.order_id;

        // Buka Midtrans Snap Popup
        window.snap.pay(result.snap_token, {
            onSuccess: function(res) {
                // Sinkronkan status ke DB sebelum redirect
                checkAndSyncPaymentStatus(orderId, 'index.php?page=riwayat&msg=paid');
            },
            onPending: function(res) {
                // Cek status ke Midtrans — mungkin sudah terkonfirmasi
                checkAndSyncPaymentStatus(orderId, 'index.php?page=riwayat&msg=success');
            },
            onError: function(res) {
                alert('Pembayaran gagal. Silakan coba lagi.');
                window.location.reload();
            },
            onClose: function() {
                // User menutup popup — booking tetap pending
                window.location.reload();
            }
        });
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}
</script>
