<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FutsalHub - Booking Lapangan Futsal Online Premium</title>
    
    <!-- Meta SEO ini Jangan di hapus -->
    <meta name="description" content="Booking lapangan futsal pilihanmu dengan mudah, cepat, dan aman di FutsalHub. Nikmati lapangan premium Vinyl, Turf, dan Parquet dengan fasilitas modern.">
    <meta name="keywords" content="booking futsal, sewa lapangan futsal, futsal online, lapangan futsal terdekat, futsalhub">
    
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Premium Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Midtrans Snap JS SDK -->
    <?php
    $midtrans_config_path = __DIR__ . '/../config/midtrans_config.php';
    if (file_exists($midtrans_config_path) && !defined('MIDTRANS_CLIENT_KEY')) {
        require_once $midtrans_config_path;
    }
    if (defined('MIDTRANS_SNAP_URL') && defined('MIDTRANS_CLIENT_KEY')):
    ?>
    <script src="<?= MIDTRANS_SNAP_URL ?>" data-client-key="<?= MIDTRANS_CLIENT_KEY ?>"></script>
    <?php endif; ?>
</head>
<body>
