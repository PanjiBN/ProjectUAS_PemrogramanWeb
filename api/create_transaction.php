<?php

session_start();

// Set header untuk response JSON
header('Content-Type: application/json');

// Pastikan user sudah login
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Anda harus login terlebih dahulu.']);
    exit;
}

// Load dependencies
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../config/midtrans_config.php';
require_once __DIR__ . '/../class/Database.php';
require_once __DIR__ . '/../class/Booking.php';
require_once __DIR__ . '/../class/Midtrans.php';

// Ambil input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    // Fallback ke POST biasa
    $input = $_POST;
}

$bookingClass = new Booking();
$user = $_SESSION['user'];

try {
    // CASE 1: Bayar ulang booking yang sudah ada (dari riwayat)
    if (isset($input['id_booking']) && !empty($input['id_booking'])) {
        $id_booking = intval($input['id_booking']);
        $booking = $bookingClass->getBookingById($id_booking);

        if (!$booking) {
            throw new Exception("Booking tidak ditemukan.");
        }

        // Pastikan booking milik user yang login
        if ((int)$booking['id_user'] !== (int)$user['id']) {
            throw new Exception("Anda tidak memiliki akses ke booking ini.");
        }

        // Pastikan status masih pending
        if ($booking['status'] !== 'pending') {
            throw new Exception("Booking ini sudah tidak berstatus pending.");
        }

        // Jika sudah ada snap_token yang valid, gunakan kembali
        if (!empty($booking['snap_token'])) {
            echo json_encode([
                'success'    => true,
                'snap_token' => $booking['snap_token'],
                'order_id'   => $booking['midtrans_order_id']
            ]);
            exit;
        }

        // Generate order ID baru dan snap token
        $order_id = 'FH-' . str_pad($id_booking, 4, '0', STR_PAD_LEFT) . '-' . time();
        $gross_amount = (int) $booking['total_harga'];

        $snap = Midtrans::createSnapToken($order_id, $gross_amount, [
            'first_name' => $user['nama'],
            'email'      => $user['email'],
            'phone'      => ''
        ], [
            [
                'id'       => 'BOOKING-' . $id_booking,
                'price'    => $gross_amount,
                'quantity' => 1,
                'name'     => 'Sewa Lapangan Futsal - BK-' . str_pad($id_booking, 4, '0', STR_PAD_LEFT)

            ]
        ]);

        // Simpan snap token ke database
        $bookingClass->updateSnapToken($id_booking, $snap['snap_token'], $order_id);

        echo json_encode([
            'success'    => true,
            'snap_token' => $snap['snap_token'],
            'order_id'   => $order_id
        ]);
        exit;
    }

    // 2: Booking baru dari halaman checkout
    $id_jadwals_str = isset($input['id_jadwals']) ? trim($input['id_jadwals']) : '';
    $total_harga = isset($input['total_harga']) ? intval($input['total_harga']) : 0;

    if (empty($id_jadwals_str) || $total_harga <= 0) {
        throw new Exception("Parameter booking tidak lengkap.");
    }

    $id_jadwals = explode(',', $id_jadwals_str);
    $num_slots = count($id_jadwals);
    $price_per_slot = intval($total_harga / $num_slots);

    // Buat booking record di database (status: pending)
    $booking_ids = [];
    foreach ($id_jadwals as $id_jadwal) {
        $bookingClass->createBooking($user['id'], intval($id_jadwal), $price_per_slot);
        
        // Ambil ID booking yang baru dibuat
        $db = Database::getConnection();
        $booking_ids[] = $db->lastInsertId();
    }

    // Gunakan booking ID pertama sebagai primary reference
    $primary_booking_id = $booking_ids[0];
    $order_id = 'FH-' . str_pad($primary_booking_id, 4, '0', STR_PAD_LEFT) . '-' . time();

    // Generate Snap Token
    $items = [];
    foreach ($booking_ids as $idx => $bk_id) {
        $items[] = [
            'id'       => 'SLOT-' . $id_jadwals[$idx],
            'price'    => $price_per_slot,
            'quantity' => 1,
            'name'     => 'Sewa Lapangan Jam ke-' . ($idx + 1)
        ];
    }

    // Tambah service fee jika ada selisih (karena pembulatan)
    $item_total = $price_per_slot * $num_slots;
    if ($item_total < $total_harga) {
        $items[] = [
            'id'       => 'ADMIN-FEE',
            'price'    => $total_harga - $item_total,
            'quantity' => 1,
            'name'     => 'Biaya Administrasi'
        ];
    }

    $snap = Midtrans::createSnapToken($order_id, $total_harga, [
        'first_name' => $user['nama'],
        'email'      => $user['email'],
        'phone'      => ''
    ], $items);

    // Simpan snap token ke semua booking yang dibuat
    foreach ($booking_ids as $bk_id) {
        $bookingClass->updateSnapToken($bk_id, $snap['snap_token'], $order_id);
    }

    echo json_encode([
        'success'     => true,
        'snap_token'  => $snap['snap_token'],
        'order_id'    => $order_id,
        'booking_ids' => $booking_ids
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
