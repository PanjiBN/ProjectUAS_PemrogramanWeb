<?php
// Pastikan hanya admin yang bisa mengakses halaman ini
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: index.php?page=home");
    exit;
}
$db = Database::getConnection();

// Hitung Statistik
// 1. Total Users
$stmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
$total_users = $stmt->fetchColumn();

// 2. total lapangan
$stmt = $db->query("SELECT COUNT(*) FROM lapangan");
$total_fields = $stmt->fetchColumn();

// 3. Total Booking
$stmt = $db->query("SELECT COUNT(*) FROM booking");
$total_bookings = $stmt->fetchColumn();

// 4. Total Revenue (hanya yang lunas)
$stmt = $db->query("SELECT SUM(total_harga) FROM booking WHERE status = 'lunas'");
$total_revenue = $stmt->fetchColumn();
$total_revenue = $total_revenue ? $total_revenue : 0;

//
$sql = "SELECT b.id_booking, b.tanggal_booking, b.total_harga, b.status,
               j.tanggal, j.jam_mulai, j.jam_selesai,
               l.nama_lapangan,
               u.nama AS nama_user
        FROM booking b
        JOIN jadwal j ON b.id_jadwal = j.id_jadwal
        JOIN lapangan l ON j.id_lapangan = l.id_lapangan
        JOIN users u ON b.id_user = u.id
        ORDER BY b.id_booking DESC
        LIMIT 6";

$stmt = $db->query($sql);
$recent_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle action untuk konfirmasi pembayaran atau pembatalan booking
if (isset($_GET['action']) && isset($_GET['booking_id'])) {
    $action = $_GET['action'];
    $bk_id = intval($_GET['booking_id']);
    
    $bookingClass = new Booking();
    if ($action === 'confirm_payment') {
        $bookingClass->updateStatus($bk_id, 'lunas');
        header("Location: index.php?page=admin_dashboard&msg=confirmed");
        exit;
    } elseif ($action === 'cancel_booking') {
        $bookingClass->updateStatus($bk_id, 'batal');
        header("Location: index.php?page=admin_dashboard&msg=cancelled");
        exit;
    }
}
?>

<div class="py-5" style="background: var(--bg-dark); min-height: 80vh;">
    <div class="container text-start">
        <!-- Dashboard Navigation Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <span class="text-success fw-bold text-uppercase" style="color: var(--accent-color) !important; font-size: 0.85rem;">Admin Control Center</span>
                <h1 class="text-white display-5 fw-bold mt-1">DASHBOARD UTAMA</h1>
                <p class="text-muted">Kelola data pemesanan, ketersediaan lapangan futsal, dan anggota dengan cepat.</p>
            </div>
            <!-- Sub Navigation Menu for Admin -->
            <div class="col-md-4 text-md-end d-flex align-items-center justify-content-md-end gap-2 mt-3 mt-md-0">
                <a href="index.php?page=admin_lapangan" class="btn btn-sm btn-outline-custom"><i class="fa-solid fa-futbol"></i> Lapangan</a>
                <a href="index.php?page=admin_booking" class="btn btn-sm btn-outline-custom"><i class="fa-solid fa-calendar-check"></i> Booking</a>
                <a href="index.php?page=admin_user" class="btn btn-sm btn-outline-custom"><i class="fa-solid fa-users"></i> Users</a>
            </div>
        </div>

        <!-- Alerts -->
        <?php if (isset($_GET['msg'])): ?>
            <?php if ($_GET['msg'] === 'confirmed'): ?>
                <div class="alert alert-success bg-opacity-10 border border-success text-success p-3 rounded-3 mb-4 animate-fade-in" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i> Pembayaran booking berhasil dikonfirmasi ke status <strong>LUNAS</strong>.
                </div>
            <?php elseif ($_GET['msg'] === 'cancelled'): ?>
                <div class="alert alert-warning bg-opacity-10 border border-warning text-warning p-3 rounded-3 mb-4 animate-fade-in" role="alert">
                    <i class="fa-solid fa-circle-exclamation me-2"></i> Booking telah dibatalkan. Slot jadwal dibebaskan kembali.
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Stats Grid Row -->
        <div class="row g-4 mb-5">
            <!-- Stat 1 -->
            <div class="col-xl-3 col-md-6">
                <div class="card-custom p-4 d-flex align-items-center gap-4">
                    <div class="benefit-icon mb-0" style="width: 55px; height: 55px; font-size: 1.3rem;"><i class="fa-solid fa-users"></i></div>
                    <div>
                        <span class="text-muted small d-block">TOTAL PENGGUNA</span>
                        <h3 class="text-white fw-bold mb-0 mt-1"><?= $total_users ?></h3>
                    </div>
                </div>
            </div>
            
            <!-- Stat 2 -->
            <div class="col-xl-3 col-md-6">
                <div class="card-custom p-4 d-flex align-items-center gap-4">
                    <div class="benefit-icon mb-0" style="width: 55px; height: 55px; font-size: 1.3rem;"><i class="fa-solid fa-futbol"></i></div>
                    <div>
                        <span class="text-muted small d-block">TOTAL LAPANGAN</span>
                        <h3 class="text-white fw-bold mb-0 mt-1"><?= $total_fields ?></h3>
                    </div>
                </div>
            </div>

            <!-- Stat 3 -->
            <div class="col-xl-3 col-md-6">
                <div class="card-custom p-4 d-flex align-items-center gap-4">
                    <div class="benefit-icon mb-0" style="width: 55px; height: 55px; font-size: 1.3rem;"><i class="fa-solid fa-calendar-check"></i></div>
                    <div>
                        <span class="text-muted small d-block">TOTAL BOOKING</span>
                        <h3 class="text-white fw-bold mb-0 mt-1"><?= $total_bookings ?></h3>
                    </div>
                </div>
            </div>

            <!-- Stat 4 -->
            <div class="col-xl-3 col-md-6">
                <div class="card-custom p-4 d-flex align-items-center gap-4">
                    <div class="benefit-icon mb-0" style="width: 55px; height: 55px; font-size: 1.3rem;"><i class="fa-solid fa-wallet"></i></div>
                    <div>
                        <span class="text-muted small d-block">PENDAPATAN</span>
                        <h3 class="text-success fw-bold mb-0 mt-1" style="color: var(--accent-color) !important;">Rp <?= number_format($total_revenue, 0, ',', '.') ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Table Section -->
        <div class="row">
            <div class="col-12">
                <div class="card-custom p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="text-white mb-0"><i class="fa-solid fa-clock text-success me-2" style="color: var(--accent-color) !important;"></i> Aktivitas Booking Terbaru</h5>
                        <a href="index.php?page=admin_booking" class="btn btn-sm btn-outline-custom text-decoration-none">Semua Transaksi <i class="fa-solid fa-arrow-right ms-1"></i></a>
                    </div>

                    <?php if (count($recent_bookings) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-custom align-middle">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Pemesan</th>
                                        <th>Lapangan</th>
                                        <th>Jadwal Main</th>
                                        <th>Tagihan</th>
                                        <th>Status</th>
                                        <th class="text-center">Aksi Cepat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_bookings as $b): ?>
                                        <?php 
                                            $bk_code = 'BK-' . str_pad($b['id_booking'], 4, '0', STR_PAD_LEFT);
                                            $time_label = date('H:i', strtotime($b['jam_mulai'])) . ' - ' . date('H:i', strtotime($b['jam_selesai']));
                                        ?>
                                        <tr>
                                            <td class="fw-bold" style="color: var(--accent-color);"><?= $bk_code ?></td>
                                            <td><?= htmlspecialchars($b['nama_user']) ?></td>
                                            <td><?= htmlspecialchars($b['nama_lapangan']) ?></td>
                                            <td>
                                                <span class="small text-white-50"><?= date('d M Y', strtotime($b['tanggal'])) ?> • <?= $time_label ?></span>
                                            </td>
                                            <td class="fw-bold">Rp <?= number_format($b['total_harga'], 0, ',', '.') ?></td>
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
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <a href="index.php?page=admin_dashboard&action=confirm_payment&booking_id=<?= $b['id_booking'] ?>" class="btn btn-sm btn-success text-black fw-bold px-2.5 py-1" style="background: var(--accent-color); border: none;" onclick="return confirm('Konfirmasi pembayaran Lunas untuk kode <?= $bk_code ?>?')">Konfirmasi</a>
                                                        <a href="index.php?page=admin_dashboard&action=cancel_booking&booking_id=<?= $b['id_booking'] ?>" class="btn btn-sm btn-outline-danger px-2.5 py-1" onclick="return confirm('Batalkan booking kode <?= $bk_code ?>?')">Batal</a>
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
                            <p class="mb-0">Belum ada aktivitas pemesanan masuk saat ini.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
