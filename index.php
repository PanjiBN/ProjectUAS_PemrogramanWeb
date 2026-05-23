<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FutsalBook - Booking Lapangan Futsal</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 (opsional) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold fs-3" href="index.php">
            <i class="fas fa-futbol text-success me-2"></i>FutsalBook
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?p=booking">Booking</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?p=jadwal">Jadwal</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?p=admin">Admin</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- MAIN CONTENT (Routing) -->
<main>
    <?php
    $pages_dir = 'pages';
    if (!empty($_GET['p'])) {
        $p = basename($_GET['p']);
        $file = $pages_dir . '/' . $p . '.php';
        if (file_exists($file)) {
            include $file;
        } else {
            include $pages_dir . '/home.php';
        }
    } else {
        include $pages_dir . '/home.php';
    }
    ?>
</main>

<!-- FOOTER -->
<footer class="bg-dark text-white-50 text-center py-4 mt-5">
    <div class="container">
        <p class="mb-0">&copy; 2026 FutsalBook - Sistem Booking Lapangan Futsal Slot Based</p>
    </div>
</footer>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>