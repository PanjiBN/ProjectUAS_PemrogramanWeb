<?php
// Initialize Lapangan OOP Class
$lapanganClass = new Lapangan();
$all_fields = $lapanganClass->getAll();

// Map DB structure to page display keys
$lapangans = [];
foreach ($all_fields as $f) {
    // Generate type classification dynamically for filtering based on name
    $tipe = 'vinyl';
    $tipe_label = 'Premium Vinyl';
    $dimensi = '16 x 26 Meter';
    $deskripsi = 'Lapangan futsal dalam ruangan dengan lantai karet/vinyl premium impor yang empuk, aman dari resiko cedera gesekan.';
    
    if (stripos($f['nama_lapangan'], 'turf') !== false || stripos($f['nama_lapangan'], 'hijau') !== false) {
        $tipe = 'turf';
        $tipe_label = 'Synthetic Turf';
        $dimensi = '18 x 28 Meter';
        $deskripsi = 'Rumput sintetis tebal standar internasional. Memberikan sensasi bermain layaknya di lapangan stadion hijau terbuka.';
    } elseif (stripos($f['nama_lapangan'], 'parquet') !== false || stripos($f['nama_lapangan'], 'wood') !== false) {
        $tipe = 'parquet';
        $tipe_label = 'Hardwood Parquet';
        $dimensi = '15 x 25 Meter';
        $deskripsi = 'Lantai kayu solid pilihan yang dilapisi polesan anti-slip. Pilihan mutlak bagi permainan berkecepatan tinggi.';
    }

    $lapangans[] = [
        'id' => $f['id_lapangan'],
        'nama' => $f['nama_lapangan'],
        'lokasi' => $f['lokasi'],
        'tipe' => $tipe,
        'tipe_label' => $tipe_label,
        'harga' => $f['harga'],
        'gambar' => $f['gambar'],
        'rating' => 4.8,
        'dimensi' => $dimensi,
        'deskripsi' => $deskripsi
    ];
}

// Get filter parameters
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$tipe_filter = isset($_GET['tipe']) ? trim($_GET['tipe']) : '';

// Filter logic
$filtered_lapangans = array_filter($lapangans, function($item) use ($search_query, $tipe_filter) {
    $matches_search = true;
    $matches_tipe = true;
    
    if ($search_query !== '') {
        $matches_search = (stripos($item['nama'], $search_query) !== false) || (stripos($item['lokasi'], $search_query) !== false);
    }
    
    if ($tipe_filter !== '') {
        $matches_tipe = ($item['tipe'] === $tipe_filter);
    }
    
    return $matches_search && $matches_tipe;
});
?>

<div class="py-5" style="background: var(--bg-dark); min-height: 80vh;">
    <div class="container">
        <!-- Breadcrumbs & Header -->
        <div class="row mb-4">
            <div class="col-12 text-start">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php?page=home" style="color: var(--accent-color); text-decoration: none;">Home</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Lapangan</li>
                    </ol>
                </nav>
                <h1 class="text-white display-5 fw-bold">KATALOG LAPANGAN (DB-ACTIVE)</h1>
                <p class="text-muted">Temukan lapangan futsal impianmu yang terhubung langsung ke database FutsalHub secara real-time.</p>
            </div>
        </div>

        <!-- Filters Sidebar & Search Bar -->
        <div class="row g-4 mb-5">
            <div class="col-lg-3">
                <div class="card-custom p-4 text-start">
                    <h5 class="text-white mb-4"><i class="fa-solid fa-filter text-success me-2" style="color: var(--accent-color) !important;"></i> Filter Lapangan</h5>
                    
                    <form action="index.php" method="GET">
                        <input type="hidden" name="page" value="lapangan">
                        
                        <!-- Search Query Input -->
                        <div class="mb-4">
                            <label class="label-custom">Kata Kunci</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary border-opacity-25 text-muted"><i class="fa-solid fa-search"></i></span>
                                <input type="text" name="search" class="form-control form-control-custom" placeholder="Nama / Lokasi" value="<?= htmlspecialchars($search_query) ?>">
                            </div>
                        </div>
                        
                        <!-- Tipe Lapangan -->
                        <div class="mb-4">
                            <label class="label-custom">Jenis Lapangan</label>
                            <div class="d-flex flex-column gap-2">
                                <label class="d-flex align-items-center text-white-50 cursor-pointer">
                                    <input type="radio" name="tipe" value="" <?= $tipe_filter === '' ? 'checked' : '' ?> class="form-check-input me-2 bg-dark border-secondary"> Semua Lapangan
                                </label>
                                <label class="d-flex align-items-center text-white-50 cursor-pointer">
                                    <input type="radio" name="tipe" value="vinyl" <?= $tipe_filter === 'vinyl' ? 'checked' : '' ?> class="form-check-input me-2 bg-dark border-secondary"> Premium Vinyl
                                </label>
                                <label class="d-flex align-items-center text-white-50 cursor-pointer">
                                    <input type="radio" name="tipe" value="turf" <?= $tipe_filter === 'turf' ? 'checked' : '' ?> class="form-check-input me-2 bg-dark border-secondary"> Synthetic Turf
                                </label>
                                <label class="d-flex align-items-center text-white-50 cursor-pointer">
                                    <input type="radio" name="tipe" value="parquet" <?= $tipe_filter === 'parquet' ? 'checked' : '' ?> class="form-check-input me-2 bg-dark border-secondary"> Hardwood Parquet
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary-custom"><i class="fa-solid fa-rotate me-1"></i> Terapkan Filter</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Grid Listing -->
            <div class="col-lg-9 text-start">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <p class="mb-0 text-muted">Menampilkan <span class="text-white fw-bold"><?= count($filtered_lapangans) ?></span> Lapangan dari Database</p>
                    <?php if ($search_query !== '' || $tipe_filter !== ''): ?>
                        <a href="index.php?page=lapangan" class="btn btn-sm btn-outline-custom text-decoration-none py-1 px-3" style="font-size: 0.8rem;">Reset Filter <i class="fa-solid fa-times ms-1"></i></a>
                    <?php endif; ?>
                </div>
                
                <?php if (count($filtered_lapangans) > 0): ?>
                    <div class="row g-4">
                        <?php foreach ($filtered_lapangans as $item): ?>
                            <div class="col-md-6 col-lg-6 animate-slide-up">
                                <div class="card card-custom h-100">
                                    <div class="card-img-wrapper">
                                        <span class="card-badge"><i class="fa-solid fa-star text-warning me-1"></i> <?= $item['rating'] ?></span>
                                        <img src="<?= htmlspecialchars($item['gambar']) ?>" class="card-img-custom" alt="<?= htmlspecialchars($item['nama']) ?>">
                                    </div>
                                    <div class="card-body p-4 d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h4 class="text-white mb-0"><?= htmlspecialchars($item['nama']) ?></h4>
                                        </div>
                                        <p class="text-muted small mb-3"><i class="fa-solid fa-location-dot me-1"></i> <?= htmlspecialchars($item['lokasi']) ?></p>
                                        
                                        <p class="text-white-50 small mb-4 line-clamp-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 38px;">
                                            <?= htmlspecialchars($item['deskripsi']) ?>
                                        </p>
                                        
                                        <div class="row g-2 mb-4 py-2 border-top border-bottom border-secondary border-opacity-25">
                                            <div class="col-6 text-muted small"><i class="fa-solid fa-layer-group me-1 text-success" style="color: var(--accent-color) !important;"></i> <?= $item['tipe_label'] ?></div>
                                            <div class="col-6 text-muted small"><i class="fa-solid fa-arrows-up-down-left-right me-1 text-success" style="color: var(--accent-color) !important;"></i> <?= $item['dimensi'] ?></div>
                                        </div>
                                        
                                        <div class="mt-auto d-flex align-items-center justify-content-between">
                                            <div>
                                                <span class="text-muted small">Harga / Jam</span>
                                                <div class="fs-5 text-white fw-bold">Rp <?= number_format($item['harga'], 0, ',', '.') ?></div>
                                            </div>
                                            <a href="index.php?page=detail_lapangan&id=<?= $item['id'] ?>" class="btn btn-primary-custom py-2 px-3">Detail & Book</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="card-custom text-center py-5 px-4">
                        <div class="fs-1 text-muted mb-3"><i class="fa-solid fa-magnifying-glass-minus"></i></div>
                        <h4 class="text-white">Lapangan Tidak Ditemukan</h4>
                        <p class="text-muted mx-auto" style="max-width: 450px;">Maaf, lapangan tidak ditemukan. Coba reset filter pencarian Anda.</p>
                        <a href="index.php?page=lapangan" class="btn btn-primary-custom mt-3">Tampilkan Semua Lapangan</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
