<?php
function isActive($pageName) {
    global $page;
    return $page === $pageName ? 'active' : '';
}
?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand text-white d-flex align-items-center" href="index.php?page=home">
            <span style="color: var(--accent-color); margin-right: 5px;"><i class="fa-solid fa-circle-nodes"></i></span>
            FUTSAL<span style="color: var(--accent-color);">HUB</span>
        </a>
        
        <!-- Toggle Button for Mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation" style="border: none;">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Navbar Collapse Content -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                <li class="nav-item">
                    <a class="nav-link <?= isActive('home') ?>" href="index.php?page=home"><i class="fa-solid fa-house me-1"></i> Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isActive('lapangan') || isActive('detail_lapangan') ?>" href="index.php?page=lapangan"><i class="fa-solid fa-futbol me-1"></i> Lapangan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isActive('about') ?>" href="index.php?page=about"><i class="fa-solid fa-circle-info me-1"></i> Tentang Kami</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isActive('contact') ?>" href="index.php?page=contact"><i class="fa-solid fa-envelope me-1"></i> Kontak</a>
                </li>
                <?php if (isset($_SESSION['user'])): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= isActive('riwayat') ?>" href="index.php?page=riwayat"><i class="fa-solid fa-clock-rotate-left me-1"></i> Riwayat Booking</a>
                    </li>
                <?php endif; ?>
            </ul>
            
            <!-- Right Aligned User & Mock Authentication Details -->
            <div class="d-flex align-items-center gap-2">
                <?php if (isset($_SESSION['user'])): ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-custom dropdown-toggle px-3 py-2 d-flex align-items-center gap-2" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-user-circle fs-5"></i>
                            <span><?= htmlspecialchars($_SESSION['user']['nama']) ?></span>
                            <span class="badge bg-success text-black ms-1" style="font-size: 0.65rem; font-weight: 700; background: var(--accent-color) !important;">
                                <?= strtoupper($_SESSION['user']['role']) ?>
                            </span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end mt-2" aria-labelledby="userDropdown" style="background-color: var(--card-dark); border: 1px solid var(--border-dark);">
                            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                                <li><a class="dropdown-item py-2" href="index.php?page=admin_dashboard"><i class="fa-solid fa-chart-line me-2"></i> Admin Dashboard</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item py-2 text-danger" href="index.php?action=logout"><i class="fa-solid fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Guest View -->
                    <a href="index.php?page=login" class="btn btn-outline-custom px-3 py-2">Masuk</a>
                    <a href="index.php?page=register" class="btn btn-primary-custom px-3 py-2">Daftar</a>
                <?php endif; ?>
                
                <!-- Quick Demo Diagnostics (Dropup/Dropdown for easy testing) -->
                <div class="dropdown ms-2">
                    <button class="btn btn-sm btn-dark text-muted border-0 p-2" type="button" id="demoDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Demo Controls">
                        <i class="fa-solid fa-flask"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end mt-2" aria-labelledby="demoDropdown" style="background-color: var(--card-dark); border: 1px solid var(--border-dark); font-size: 0.8rem;">
                        <li class="dropdown-header text-white-50">Demo Tester Quick Login</li>
                        <?php if (!isset($_SESSION['user'])): ?>
                            <li><a class="dropdown-item" href="index.php?action=mock_login">Login User (Mock)</a></li>
                            <li><a class="dropdown-item" href="index.php?action=mock_admin_login">Login Admin (Mock)</a></li>
                        <?php else: ?>
                            <li><a class="dropdown-item text-danger" href="index.php?action=logout">Reset (Logout)</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>
