<?php
$bookingClass = new Booking();
$error_msg = '';

// Hanya aksi pembatalan yang diperbolehkan admin
// Konfirmasi (lunas) dilakukan OTOMATIS oleh webhook Midtrans
if (isset($_GET['action']) && isset($_GET['booking_id'])) {
    $action = $_GET['action'];
    $bk_id = intval($_GET['booking_id']);
    
    try {
        if ($action === 'cancel') {
            $bookingClass->updateStatus($bk_id, 'batal');
            header("Location: index.php?page=admin_booking&msg=cancel_success");
            exit;
        }
        // Aksi 'confirm' manual sengaja gue hapus
    } catch (Exception $e) {
        $error_msg = $e->getMessage();
    }
}

// ambil semua data booking untuk ditampilkan di tabel
$bookings = $bookingClass->getAllBookings();
?>

<div class="py-5" style="background: var(--bg-dark); min-height: 80vh;">
    <div class="container text-start">
        <!-- Breadcrumb & Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php?page=admin_dashboard" style="color: var(--accent-color); text-decoration: none;">Dashboard</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Booking</li>
                    </ol>
                </nav>
                <h1 class="text-white display-5 fw-bold">KELOLA BOOKING</h1>
                <p class="text-white">Pantau transaksi pembayaran. Status <strong class="text-success">Lunas</strong> diperbarui otomatis setelah pembayaran diterima Midtrans.</p>
            </div>
        </div>

        <!-- Info Auto-Approve -->
        <div class="alert d-flex align-items-start gap-3 mb-4" style="background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.3); border-radius: 10px; color: #6ee7b7;">
            <i class="fa-solid fa-circle-bolt fs-5 mt-1" style="color: #10b981;"></i>
            <div>
                <strong style="color: #10b981;">Auto-Approve Aktif</strong>
                <p class="mb-0 small" style="color: #94a3b8;">Sistem akan otomatis mengubah status booking dari <em>Pending</em> menjadi <strong>Lunas</strong> begitu pembayaran dikonfirmasi oleh Midtrans. Admin tidak perlu menekan tombol konfirmasi secara manual.</p>
            </div>
        </div>

        <!-- Success/Error Alerts -->
        <?php if (isset($_GET['msg'])): ?>
            <?php if ($_GET['msg'] === 'cancel_success'): ?>
                <div class="alert alert-warning bg-opacity-10 border border-warning text-warning p-3 rounded-3 mb-4 animate-fade-in" role="alert">
                    <i class="fa-solid fa-circle-exclamation me-2"></i> Reservasi dibatalkan. Jam lapangan dilepas kembali ke status <strong>Tersedia</strong>.
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger bg-opacity-10 border border-danger text-danger p-3 rounded-3 mb-4" role="alert">
                <i class="fa-solid fa-circle-xmark me-2"></i> Gagal mengubah status: <?= htmlspecialchars($error_msg) ?>
            </div>
        <?php endif; ?>

        <!-- Bookings Table -->
        <div class="card-custom p-4">
            <?php if (count($bookings) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-custom align-middle">
                        <thead>
                            <tr>
                                <th>Kode Booking</th>
                                <th>Pemesan</th>
                                <th>Lapangan</th>
                                <th>Jadwal Bermain</th>
                                <th>Total Tagihan</th>
                                <th>Metode Bayar</th>
                                <th>Tanggal Transaksi</th>
                                <th>Status</th>
                                <th class="text-center">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $b): ?>
                                <?php 
                                    $bk_code = 'BK-' . str_pad($b['id_booking'], 4, '0', STR_PAD_LEFT);
                                    $time_label = date('H:i', strtotime($b['jam_mulai'])) . ' - ' . date('H:i', strtotime($b['jam_selesai']));
                                    $payment_label = !empty($b['payment_type']) ? htmlspecialchars($b['payment_type']) : '<span class="text-muted small">-</span>';
                                ?>
                                <tr>
                                    <td class="fw-bold" style="color: var(--accent-color);"><?= $bk_code ?></td>
                                    <td>
                                        <div>
                                            <span class="text-white fw-bold d-block"><?= htmlspecialchars($b['nama_user']) ?></span>
                                            <span class="text-white small"><?= htmlspecialchars($b['email_user']) ?></span>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($b['nama_lapangan']) ?></td>
                                    <td>
                                        <div class="small">
                                            <span class="d-block text-white"><?= date('d F Y', strtotime($b['tanggal'])) ?></span>
                                            <span class="text-white-50"><i class="fa-regular fa-clock me-1 text-success"></i> <?= $time_label ?></span>
                                        </div>
                                    </td>
                                    <td class="fw-bold">Rp <?= number_format($b['total_harga'], 0, ',', '.') ?></td>
                                    <td class="small" style="color: #94a3b8;"><?= $payment_label ?></td>
                                    <td class="small text-white"><?= date('d/m/Y H:i', strtotime($b['tanggal_booking'])) ?></td>
                                    <td>
                                        <?php if ($b['status'] === 'pending'): ?>
                                            <span class="badge-status pending"><i class="fa-solid fa-circle-notch fa-spin me-1"></i> Pending</span>
                                        <?php elseif ($b['status'] === 'lunas'): ?>
                                            <span class="badge-status lunas"><i class="fa-solid fa-check me-1"></i> Lunas</span>
                                        <?php else: ?>
                                            <span class="badge-status batal"><i class="fa-solid fa-times me-1"></i> Batal</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($b['status'] === 'pending'): ?>
                                            <!-- Pending: hanya bisa dibatalkan admin. Konfirmasi otomatis via Midtrans webhook. -->
                                            <div class="d-flex justify-content-center align-items-center gap-2">
                                                <span class="small" style="color: #64748b;"><i class="fa-solid fa-clock me-1"></i>Menunggu bayar</span>
                                                <a href="index.php?page=admin_booking&action=cancel&booking_id=<?= $b['id_booking'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Batalkan booking kode <?= $bk_code ?>?')">Batalkan</a>
                                            </div>
                                        <?php elseif ($b['status'] === 'lunas'): ?>
                                            <div class="d-flex justify-content-center align-items-center gap-2">
                                                <span class="small" style="color: #10b981;"><i class="fa-solid fa-bolt me-1"></i>Auto-approved</span>
                                                <a href="index.php?page=admin_booking&action=cancel&booking_id=<?= $b['id_booking'] ?>" class="btn btn-sm btn-outline-danger px-3" onclick="return confirm('Batalkan booking kode <?= $bk_code ?> yang sudah LUNAS?')">Batalkan</a>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-calendar-xmark fs-2 mb-3"></i>
                    <p class="mb-0">Belum ada transaksi pemesanan lapangan di database.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
