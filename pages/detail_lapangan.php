<?php
// Initialize OOP Classes
$id_lapangan = isset($_GET['id']) ? intval($_GET['id']) : 1;
$tanggal = isset($_GET['date']) ? trim($_GET['date']) : date('Y-m-d');

$lapanganClass = new Lapangan();
$field = $lapanganClass->getById($id_lapangan);

if (!$field) {
    echo "<div class='container py-5 text-center'><div class='alert alert-danger py-4 card-custom text-white'>Lapangan tidak ditemukan.</div><a href='index.php?page=lapangan' class='btn btn-primary-custom mt-3'>Kembali ke Katalog</a></div>";
    return;
}

// Fetch real schedules for this date
$jadwalClass = new Jadwal();
$schedules = $jadwalClass->getSchedulesByFieldAndDate($id_lapangan, $tanggal);

// Define type attributes for details presentation
$tipe_label = 'Premium Vinyl';
$dimensi = '16 x 26 Meter';
$fasilitas = ['Shower Air Panas', 'Ruang Ganti AC', 'Locker Pengaman', 'Free Wi-Fi', 'Kantin Olahraga', 'Parkir Luas'];

if (stripos($field['nama_lapangan'], 'turf') !== false || stripos($field['nama_lapangan'], 'hijau') !== false) {
    $tipe_label = 'Synthetic Turf (FIFA Grade)';
    $dimensi = '18 x 28 Meter';
    $fasilitas = ['Rumput Halus Anti Lecet', 'Ruang Ganti AC', 'Tribun Penonton', 'Free Wi-Fi', 'Spot Foto Instagramable'];
} elseif (stripos($field['nama_lapangan'], 'parquet') !== false || stripos($field['nama_lapangan'], 'wood') !== false) {
    $tipe_label = 'Hardwood Parquet (Solid Wood)';
    $dimensi = '15 x 25 Meter';
    $fasilitas = ['Lantai Kayu Jati Premium', 'Locker Pengaman', 'Shower Air Panas', 'Cafeteria Makanan Sehat'];
}
?>

<div class="py-5" style="background: var(--bg-dark);">
    <div class="container text-start">
        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php?page=home" style="color: var(--accent-color); text-decoration: none;">Home</a></li>
                <li class="breadcrumb-item"><a href="index.php?page=lapangan" style="color: var(--accent-color); text-decoration: none;">Lapangan</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page"><?= htmlspecialchars($field['nama_lapangan']) ?></li>
            </ol>
        </nav>

        <div class="row g-4">
            <!-- Left Column: Details & Images -->
            <div class="col-lg-7">
                <!-- Large Field Card Showcase -->
                <div class="card-custom overflow-hidden mb-4">
                    <img src="<?= htmlspecialchars($field['gambar']) ?>" class="img-fluid w-100" style="aspect-ratio: 16/9; object-fit: cover;" alt="<?= htmlspecialchars($field['nama_lapangan']) ?>">
                    <div class="p-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-success text-black px-3 py-1.5" style="background: var(--accent-color) !important; font-weight: 700; font-size: 0.75rem;">
                                <i class="fa-solid fa-layer-group me-1"></i> <?= $tipe_label ?>
                            </span>
                            <span class="badge bg-dark border border-secondary border-opacity-25 px-3 py-1.5 text-white" style="font-size: 0.75rem;">
                                <i class="fa-solid fa-arrows-up-down-left-right me-1"></i> Dimensi: <?= $dimensi ?>
                            </span>
                        </div>
                        
                        <h2 class="text-white fw-bold mb-2"><?= htmlspecialchars($field['nama_lapangan']) ?></h2>
                        <p class="text-muted"><i class="fa-solid fa-location-dot me-1 text-success" style="color: var(--accent-color) !important;"></i> <?= htmlspecialchars($field['lokasi']) ?></p>
                        
                        <hr class="border-secondary opacity-25 my-4">
                        
                        <h5 class="text-white mb-3">Deskripsi Lapangan</h5>
                        <p class="text-white-50 lead" style="font-size: 0.95rem; line-height: 1.7;">
                            Dapatkan performa bermain terbaik Anda di lapangan <?= htmlspecialchars($field['nama_lapangan']) ?>. Arena olahraga indoor dengan standar kualitas premium, sirkulasi udara optimal, serta didukung fasilitas modern untuk kenyamanan maksimal Anda dan tim futsal Anda.
                        </p>
                        
                        <hr class="border-secondary opacity-25 my-4">
                        
                        <h5 class="text-white mb-3">Fasilitas Arena</h5>
                        <div class="row g-2">
                            <?php foreach ($fasilitas as $f): ?>
                                <div class="col-md-6 d-flex align-items-center gap-2 mb-2">
                                    <span class="fs-5 text-success" style="color: var(--accent-color) !important;"><i class="fa-regular fa-circle-check"></i></span>
                                    <span class="text-white-50"><?= htmlspecialchars($f) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Booking Widget & Schedule Picker -->
            <div class="col-lg-5">
                <div class="card-custom p-4 sticky-top" style="top: 100px; z-index: 5;">
                    <h4 class="text-white mb-3"><i class="fa-solid fa-calendar-check text-success me-2" style="color: var(--accent-color) !important;"></i> Pilih Jadwal Main</h4>
                    <p class="text-muted small mb-4">Silakan tentukan tanggal dan pilih satu atau lebih jam bermain yang masih tersedia di bawah ini.</p>
                    
                    <!-- Date Input -->
                    <div class="mb-4">
                        <label class="label-custom"><i class="fa-regular fa-calendar-days me-1"></i> Tanggal Pertandingan</label>
                        <input type="date" id="booking-date" class="form-control form-control-custom" value="<?= htmlspecialchars($tanggal) ?>" min="<?= date('Y-m-d') ?>">
                    </div>

                    <!-- Time Slots Grid -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="label-custom mb-0"><i class="fa-regular fa-clock me-1"></i> Slot Waktu Tersedia</label>
                            <span class="text-muted small">Waktu Operasional: 08:00 - 22:00</span>
                        </div>
                        
                        <div class="schedule-grid">
                            <?php foreach ($schedules as $sched): ?>
                                <?php 
                                    $time_label = date('H:i', strtotime($sched['jam_mulai'])) . ' - ' . date('H:i', strtotime($sched['jam_selesai']));
                                    $is_available = $sched['status'] === 'tersedia';
                                ?>
                                <?php if ($is_available): ?>
                                    <div class="time-slot available" data-id-jadwal="<?= $sched['id_jadwal'] ?>" data-time="<?= $time_label ?>"><?= $time_label ?></div>
                                <?php else: ?>
                                    <div class="time-slot booked" title="Sudah di-booking tim lain"><?= $time_label ?></div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Order Summary Box -->
                    <div class="card p-3 border-secondary border-opacity-25 mb-4" style="background: rgba(255, 255, 255, 0.02); border-radius: var(--radius-md);">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Lapangan:</span>
                            <span class="text-white fw-bold"><?= htmlspecialchars($field['nama_lapangan']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Harga per jam:</span>
                            <span class="text-white">Rp <?= number_format($field['harga'], 0, ',', '.') ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Total Jam dipilih:</span>
                            <span class="text-white fw-bold" id="total-hours">0 Jam</span>
                        </div>
                        <hr class="border-secondary opacity-25 my-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted fw-bold">Total Pembayaran:</span>
                            <span class="fs-4 text-success fw-bold" id="total-payment" style="color: var(--accent-color) !important;">Rp 0</span>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="d-grid">
                        <button type="button" id="btn-proceed" class="btn btn-primary-custom py-3 fw-bold" disabled>
                            <i class="fa-solid fa-chevron-right me-2"></i> Lanjut Ke Booking
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Selecting slots -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const timeSlots = document.querySelectorAll('.time-slot.available');
    const totalHoursEl = document.getElementById('total-hours');
    const totalPaymentEl = document.getElementById('total-payment');
    const btnProceed = document.getElementById('btn-proceed');
    const bookingDateEl = document.getElementById('booking-date');
    
    const pricePerHour = <?= $field['harga'] ?>;
    let selectedSlots = []; // Stores IDs of selected schedules

    // Handle date change
    bookingDateEl.addEventListener('change', function() {
        const selectedDate = this.value;
        const fieldId = <?= $field['id_lapangan'] ?>;
        window.location.href = `index.php?page=detail_lapangan&id=${fieldId}&date=${selectedDate}`;
    });

    timeSlots.forEach(slot => {
        slot.addEventListener('click', function() {
            const idJadwal = this.getAttribute('data-id-jadwal');
            
            if (this.classList.contains('selected')) {
                // Deselect
                this.classList.remove('selected');
                selectedSlots = selectedSlots.filter(id => id !== idJadwal);
            } else {
                // Select
                this.classList.add('selected');
                selectedSlots.push(idJadwal);
            }
            
            updateSummary();
        });
    });

    function updateSummary() {
        const count = selectedSlots.length;
        const total = count * pricePerHour;
        
        totalHoursEl.textContent = count + ' Jam';
        totalPaymentEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
        
        if (count > 0) {
            btnProceed.disabled = false;
        } else {
            btnProceed.disabled = true;
        }
    }

    btnProceed.addEventListener('click', function() {
        const date = bookingDateEl.value;
        if (selectedSlots.length === 0 || !date) return;
        
        const slotsStr = encodeURIComponent(selectedSlots.join(','));
        const fieldId = <?= $field['id_lapangan'] ?>;
        
        <?php if (!isset($_SESSION['user'])): ?>
            alert('Silakan login terlebih dahulu untuk melanjutkan pemesanan.');
            window.location.href = `index.php?page=login&redirect=detail_lapangan&id=${fieldId}`;
        <?php else: ?>
            // Send selected schedule IDs list to booking checkout page
            window.location.href = `index.php?page=booking&id=${fieldId}&date=${date}&slots=${slotsStr}`;
        <?php endif; ?>
    });
});
</script>
