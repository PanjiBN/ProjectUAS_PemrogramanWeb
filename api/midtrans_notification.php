<?php
// Log untuk kalo ada masalah di webhook Midtrans, supaya gampang memperbaikinya
$log_file = __DIR__ . '/../uploads/midtrans_webhook.log';

// Load dependencies
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../config/midtrans_config.php';
require_once __DIR__ . '/../class/Database.php';
require_once __DIR__ . '/../class/Booking.php';
require_once __DIR__ . '/../class/Midtrans.php';

// Ambil JSON body dari Midtrans
$raw_body = file_get_contents('php://input');
$notification = json_decode($raw_body, true);

// Log incoming notification
$log_entry = date('Y-m-d H:i:s') . " | INCOMING: " . $raw_body . "\n";
file_put_contents($log_file, $log_entry, FILE_APPEND);

// Validasi payload
if (!$notification || !isset($notification['order_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid notification payload']);
    exit;
}

// Extract data dari notifikasi
$order_id         = $notification['order_id'];
$transaction_id   = $notification['transaction_id'] ?? '';
$status_code      = $notification['status_code'] ?? '';
$gross_amount     = $notification['gross_amount'] ?? '';
$signature_key    = $notification['signature_key'] ?? '';
$transaction_status = $notification['transaction_status'] ?? '';
$fraud_status     = $notification['fraud_status'] ?? 'accept';
$payment_type     = $notification['payment_type'] ?? '';

// 1. VALIDASI SIGNATURE KEY (KEAMANAN)
if (!Midtrans::verifySignature($order_id, $status_code, $gross_amount, $signature_key)) {
    $log_entry = date('Y-m-d H:i:s') . " | SIGNATURE INVALID for order: {$order_id}\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid signature key']);
    exit;
}

// 2. CARI BOOKING BERDASARKAN ORDER ID
$db = Database::getConnection();
$bookingClass = new Booking();

// Cari semua booking dengan midtrans_order_id yang sama
$stmt = $db->prepare("SELECT id_booking, status FROM booking WHERE midtrans_order_id = :order_id");
$stmt->execute(['order_id' => $order_id]);
$bookings = $stmt->fetchAll();

if (empty($bookings)) {
    $log_entry = date('Y-m-d H:i:s') . " | ORDER NOT FOUND: {$order_id}\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Order not found']);
    exit;
}

// 3. MAPPING STATUS MIDTRANS → STATUS BOOKING
$new_status = null;

if ($transaction_status === 'capture') {
    // Untuk kartu kredit: cek fraud status
    $new_status = ($fraud_status === 'accept') ? 'lunas' : null;
} elseif ($transaction_status === 'settlement') {
    // Pembayaran berhasil dikonfirmasi
    $new_status = 'lunas';
} elseif ($transaction_status === 'pending') {
    // Masih menunggu pembayaran
    $new_status = 'pending';
} elseif (in_array($transaction_status, ['cancel', 'deny', 'expire'])) {
    // Transaksi gagal/dibatalkan/expired
    $new_status = 'batal';
}

$log_entry = date('Y-m-d H:i:s') . " | ORDER: {$order_id} | MIDTRANS_STATUS: {$transaction_status} | MAPPED: {$new_status} | PAYMENT: {$payment_type}\n";
file_put_contents($log_file, $log_entry, FILE_APPEND);

// 4. UPDATE STATUS SEMUA BOOKING TERKAIT
if ($new_status !== null) {
    foreach ($bookings as $booking) {
        $id_booking = $booking['id_booking'];
        $current_status = $booking['status'];

        // Jangan downgrade status yang sudah lunas (kecuali batal)
        if ($current_status === 'lunas' && $new_status === 'pending') {
            continue;
        }

        try {
            // Update status booking + jadwal via Booking class
            if ($new_status !== $current_status) {
                $bookingClass->updateStatus($id_booking, $new_status);
            }

            // Simpan data transaksi Midtrans
            $bookingClass->updateMidtransPayment($id_booking, $transaction_id, $payment_type);

            $log_entry = date('Y-m-d H:i:s') . " | UPDATED BK-{$id_booking}: {$current_status} → {$new_status}\n";
            file_put_contents($log_file, $log_entry, FILE_APPEND);

        } catch (Exception $e) {
            $log_entry = date('Y-m-d H:i:s') . " | ERROR updating BK-{$id_booking}: " . $e->getMessage() . "\n";
            file_put_contents($log_file, $log_entry, FILE_APPEND);
        }
    }
}

http_response_code(200);
echo json_encode(['status' => 'ok']);
?>
