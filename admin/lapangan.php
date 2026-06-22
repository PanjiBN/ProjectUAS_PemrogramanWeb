<?php
$lapanganClass = new Lapangan();
$error_msg = '';
$success_msg = '';

// Handle Add, Edit, Delete actions for Lapangan
// 1. TAMBAH / BUAT LAPANGAN
if (isset($_GET['action']) && $_GET['action'] === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = isset($_POST['nama_lapangan']) ? trim($_POST['nama_lapangan']) : '';
    $lokasi = isset($_POST['lokasi']) ? trim($_POST['lokasi']) : '';
    $harga = isset($_POST['harga']) ? intval($_POST['harga']) : 0;
    
    // Handle file upload
    $target_dir = "uploads/lapangan/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $image_path = "assets/images/field_vinyl.png"; // Default image jika tidak ada upload atau gagal upload
    
    if (isset($_FILES['gambar_file']) && $_FILES['gambar_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['gambar_file']['tmp_name'];
        $file_name = time() . '_' . basename($_FILES['gambar_file']['name']);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($file_tmp, $target_file)) {
            $image_path = $target_file;
        }
    }

    if (!empty($nama) && !empty($lokasi) && $harga > 0) {
        try {
            $lapanganClass->create($nama, $lokasi, $harga, $image_path);
            header("Location: index.php?page=admin_lapangan&msg=add_success");
            exit;
        } catch (Exception $e) {
            $error_msg = $e->getMessage();
        }
    } else {
        $error_msg = "Semua kolom input wajib diisi.";
    }
}

// 2. EDIT / UPDATE LAPANGAN
if (isset($_GET['action']) && $_GET['action'] === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id_lapangan']) ? intval($_POST['id_lapangan']) : 0;
    $nama = isset($_POST['nama_lapangan']) ? trim($_POST['nama_lapangan']) : '';
    $lokasi = isset($_POST['lokasi']) ? trim($_POST['lokasi']) : '';
    $harga = isset($_POST['harga']) ? intval($_POST['harga']) : 0;
    
    $image_path = null;
    
    if (isset($_FILES['gambar_file']) && $_FILES['gambar_file']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/lapangan/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_tmp = $_FILES['gambar_file']['tmp_name'];
        $file_name = time() . '_' . basename($_FILES['gambar_file']['name']);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($file_tmp, $target_file)) {
            $image_path = $target_file;
        }
    }

    if ($id > 0 && !empty($nama) && !empty($lokasi) && $harga > 0) {
        try {
            $lapanganClass->update($id, $nama, $lokasi, $harga, $image_path);
            header("Location: index.php?page=admin_lapangan&msg=edit_success");
            exit;
        } catch (Exception $e) {
            $error_msg = $e->getMessage();
        }
    } else {
        $error_msg = "Semua kolom input wajib diisi.";
    }
}

// 3. DELETE LAPANGAN
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id > 0) {
        try {
            $lapanganClass->delete($id);
            header("Location: index.php?page=admin_lapangan&msg=delete_success");
            exit;
        } catch (Exception $e) {
            $error_msg = $e->getMessage();
        }
    }
}

// Ambil semua data lapangan untuk ditampilkan di tabel
$fields = $lapanganClass->getAll();
?>

<div class="py-5" style="background: var(--bg-dark); min-height: 80vh;">
    <div class="container text-start">
        <!-- Breadcrumb & Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php?page=admin_dashboard" style="color: var(--accent-color); text-decoration: none;">Dashboard</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Lapangan</li>
                    </ol>
                </nav>
                <h1 class="text-white display-5 fw-bold">KELOLA LAPANGAN</h1>
                <p class="text-muted">Tambah, ubah data, atau hapus lapangan futsal di FutsalHub.</p>
            </div>
            <div class="col-md-4 text-md-end d-flex align-items-center justify-content-md-end gap-2 mt-3 mt-md-0">
                <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addFieldModal">
                    <i class="fa-solid fa-plus me-1"></i> Tambah Lapangan
                </button>
            </div>
        </div>

        <!-- Success/Error Alerts -->
        <?php if (isset($_GET['msg'])): ?>
            <?php if ($_GET['msg'] === 'add_success'): ?>
                <div class="alert alert-success bg-opacity-10 border border-success text-success p-3 rounded-3 mb-4 animate-fade-in" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i> Lapangan baru berhasil ditambahkan!
                </div>
            <?php elseif ($_GET['msg'] === 'edit_success'): ?>
                <div class="alert alert-success bg-opacity-10 border border-success text-success p-3 rounded-3 mb-4 animate-fade-in" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i> Detail lapangan berhasil diubah!
                </div>
            <?php elseif ($_GET['msg'] === 'delete_success'): ?>
                <div class="alert alert-warning bg-opacity-10 border border-warning text-warning p-3 rounded-3 mb-4 animate-fade-in" role="alert">
                    <i class="fa-solid fa-circle-exclamation me-2"></i> Lapangan berhasil dihapus dari sistem.
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger bg-opacity-10 border border-danger text-danger p-3 rounded-3 mb-4" role="alert">
                <i class="fa-solid fa-circle-xmark me-2"></i> <?= htmlspecialchars($error_msg) ?>
            </div>
        <?php endif; ?>

        <!-- Fields List Card Grid -->
        <div class="row g-4">
            <?php if (count($fields) > 0): ?>
                <?php foreach ($fields as $item): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card card-custom h-100">
                            <div class="card-img-wrapper">
                                <img src="<?= htmlspecialchars($item['gambar']) ?>" class="card-img-custom" alt="<?= htmlspecialchars($item['nama_lapangan']) ?>">
                            </div>
                            <div class="card-body p-4 d-flex flex-column">
                                <h4 class="text-white mb-2"><?= htmlspecialchars($item['nama_lapangan']) ?></h4>
                                <p class="text-muted small mb-3"><i class="fa-solid fa-location-dot me-1 text-success" style="color: var(--accent-color) !important;"></i> <?= htmlspecialchars($item['lokasi']) ?></p>
                                
                                <div class="p-3 bg-black bg-opacity-20 border border-secondary border-opacity-25 rounded mb-4 mt-auto">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted small">Harga Sewa:</span>
                                        <span class="text-success fw-bold" style="color: var(--accent-color) !important;">Rp <?= number_format($item['harga'], 0, ',', '.') ?> / Jam</span>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-custom w-50 py-2" data-bs-toggle="modal" data-bs-target="#editFieldModal<?= $item['id_lapangan'] ?>">
                                        <i class="fa-solid fa-edit me-1"></i> Edit
                                    </button>
                                    <a href="index.php?page=admin_lapangan&action=delete&id=<?= $item['id_lapangan'] ?>" class="btn btn-outline-danger w-50 py-2 d-flex align-items-center justify-content-center" onclick="return confirm('Apakah Anda yakin ingin menghapus lapangan <?= htmlspecialchars($item['nama_lapangan']) ?>?')">
                                        <i class="fa-solid fa-trash-can me-1"></i> Hapus
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Edit Field -->
                    <div class="modal fade" id="editFieldModal<?= $item['id_lapangan'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content" style="background-color: var(--card-dark); border: 1px solid var(--border-dark);">
                                <form action="index.php?page=admin_lapangan&action=edit" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="id_lapangan" value="<?= $item['id_lapangan'] ?>">
                                    
                                    <div class="modal-header border-secondary border-opacity-25 text-white">
                                        <h5 class="modal-title">Edit Lapangan: <?= htmlspecialchars($item['nama_lapangan']) ?></h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-white">
                                        <div class="mb-3">
                                            <label class="label-custom">Nama Lapangan</label>
                                            <input type="text" name="nama_lapangan" class="form-control form-control-custom" value="<?= htmlspecialchars($item['nama_lapangan']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="label-custom">Lokasi / Cabang</label>
                                            <input type="text" name="lokasi" class="form-control form-control-custom" value="<?= htmlspecialchars($item['lokasi']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="label-custom">Harga per Jam (Rp)</label>
                                            <input type="number" name="harga" class="form-control form-control-custom" value="<?= $item['harga'] ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="label-custom">Ganti File Gambar (Opsional)</label>
                                            <input type="file" name="gambar_file" class="form-control form-control-custom" accept="image/*">
                                            <div class="form-text text-muted">Abaikan jika tidak ingin mengubah gambar lapangan.</div>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-secondary border-opacity-25">
                                        <button type="button" class="btn btn-sm btn-outline-custom text-white" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-sm btn-primary-custom text-black">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5 text-muted">
                    <i class="fa-solid fa-futbol fs-1 mb-3"></i>
                    <p class="mb-0">Belum ada lapangan yang terdaftar di database.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Add Field -->
<div class="modal fade" id="addFieldModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--card-dark); border: 1px solid var(--border-dark);">
            <form action="index.php?page=admin_lapangan&action=add" method="POST" enctype="multipart/form-data">
                <div class="modal-header border-secondary border-opacity-25 text-white">
                    <h5 class="modal-title">Tambah Lapangan Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-white">
                    <div class="mb-3">
                        <label class="label-custom">Nama Lapangan</label>
                        <input type="text" name="nama_lapangan" class="form-control form-control-custom" placeholder="Contoh: Arena Futsal Vinyl" required>
                    </div>
                    <div class="mb-3">
                        <label class="label-custom">Lokasi / Cabang</label>
                        <input type="text" name="lokasi" class="form-control form-control-custom" placeholder="Contoh: Jakarta Selatan" required>
                    </div>
                    <div class="mb-3">
                        <label class="label-custom">Harga per Jam (Rp)</label>
                        <input type="number" name="harga" class="form-control form-control-custom" placeholder="Contoh: 150000" required>
                    </div>
                    <div class="mb-3">
                        <label class="label-custom">File Gambar Lapangan</label>
                        <input type="file" name="gambar_file" class="form-control form-control-custom" accept="image/*" required>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-sm btn-outline-custom text-white" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary-custom text-black">Tambahkan</button>
                </div>
            </form>
        </div>
    </div>
</div>
