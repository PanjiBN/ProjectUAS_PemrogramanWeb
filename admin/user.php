<?php
$userClass = new User();
$error_msg = '';

// Handle delete user action
if (isset($_GET['action']) && isset($_GET['user_id'])) {
    $action = $_GET['action'];
    $u_id = intval($_GET['user_id']);
    
    try {
        if ($action === 'delete') {
            $userClass->deleteUser($u_id);
            header("Location: index.php?page=admin_user&msg=delete_success");
            exit;
        }
    } catch (Exception $e) {
        $error_msg = $e->getMessage();
    }
}

// Ambil semua data pengguna untuk ditampilkan di tabel
$users = $userClass->getAllUsers();
?>

<div class="py-5" style="background: var(--bg-dark); min-height: 80vh;">
    <div class="container text-start">
        <!-- Breadcrumb & Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php?page=admin_dashboard" style="color: var(--accent-color); text-decoration: none;">Dashboard</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Users</li>
                    </ol>
                </nav>
                <h1 class="text-white display-5 fw-bold">KELOLA PENGGUNA</h1>
                <p class="text-white">Pantau daftar pengguna terdaftar dan kelola hak akses akun.</p>
            </div>
        </div>

        <!-- Success/Error Alerts -->
        <?php if (isset($_GET['msg'])): ?>
            <?php if ($_GET['msg'] === 'delete_success'): ?>
                <div class="alert alert-warning bg-opacity-10 border border-warning text-warning p-3 rounded-3 mb-4 animate-fade-in" role="alert">
                    <i class="fa-solid fa-circle-exclamation me-2"></i> Akun pengguna telah berhasil dihapus secara permanen.
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger bg-opacity-10 border border-danger text-danger p-3 rounded-3 mb-4" role="alert">
                <i class="fa-solid fa-circle-xmark me-2"></i> Gagal memproses aksi: <?= htmlspecialchars($error_msg) ?>
            </div>
        <?php endif; ?>

        <!-- Users Table -->
        <div class="card-custom p-4">
            <?php if (count($users) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-custom align-middle">
                        <thead>
                            <tr>
                                <th>ID User</th>
                                <th>Nama Lengkap</th>
                                <th>Alamat Email</th>
                                <th>Peran (Role)</th>
                                <th class="text-center">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td class="fw-bold">#<?= $u['id'] ?></td>
                                    <td class="fw-bold text-white"><?= htmlspecialchars($u['nama']) ?></td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td>
                                        <?php if ($u['role'] === 'admin'): ?>
                                            <span class="badge bg-success text-black fw-bold" style="background: var(--accent-color) !important;">ADMIN</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">USER</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($u['id'] != 1): ?>
                                            <a href="index.php?page=admin_user&action=delete&user_id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger px-3" onclick="return confirm('Apakah Anda yakin ingin menghapus akun <?= htmlspecialchars($u['nama']) ?> secara permanen? Semua data booking miliknya juga akan ikut terhapus.')">
                                                <i class="fa-solid fa-trash-can"></i> Hapus Akun
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">Utama (Protected)</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-users-slash fs-2 mb-3"></i>
                    <p class="mb-0">Tidak ada pengguna yang terdaftar.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
