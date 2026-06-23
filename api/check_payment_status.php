<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../config/midtrans_config.php';
require_once __DIR__ . '/../class/Database.php';
require_once __DIR__ . '/../class/Booking.php';

$order_id = isset($_GET['order_id']) ? trim($_GET['order_id']) : '';

if (empty($order_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'order_id tidak ditemukan']);
    exit;
}

try {
    $db = Database::getConnection();
    $bookingClass = new Booking();

    // Cari booking berdasarkan order_id
    $stmt = $db->prepare("SELECT id_booking, id_user, status FROM booking WHERE midtrans_order_id = :order_id");
    $stmt->execute(['order_id' => $order_id]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($bookings)) {
        echo json_encode(['success' => false, 'message' => 'Booking tidak ditemukan', 'status' => 'not_found']);
        exit;
    }

    $current_status = $bookings[0]['status'];

    // Jika sudah lunas di DB, langsung return (tidak perlu query ke Midtrans) 
    if ($current_status === 'lunas') {
        echo json_encode([
            'success'    => true,
            'status'     => 'lunas',
            'message'    => 'Pembayaran sudah dikonfirmasi.',
            'order_id'   => $order_id
        ]);
        exit;
    }

    // Query Midtrans Transaction Status API
    $midtrans_status_url = MIDTRANS_IS_PRODUCTION
        ? 'https://api.midtrans.com/v2/' . urlencode($order_id) . '/status'
        : 'https://api.sandbox.midtrans.com/v2/' . urlencode($order_id) . '/status';

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $midtrans_status_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode(MIDTRANS_SERVER_KEY . ':')
        ],
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 15
    ]);

    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_err  = curl_error($ch);
    curl_close($ch);

    if ($curl_err) {
        // Jika tidak bisa konek ke Midtrans, return status dari DB
        echo json_encode([
            'success' => true,
            'status'  => $current_status,
            'message' => 'Tidak dapat menghubungi Midtrans, status dari database.',
            'source'  => 'database'
        ]);
        exit;
    }

    $midtrans_data = json_decode($response, true);

    if ($http_code !== 200 || !isset($midtrans_data['transaction_status'])) {
        // Order belum ada di Midtrans atau ada error
        echo json_encode([
            'success' => true,
            'status'  => $current_status,
            'message' => 'Midtrans belum memiliki data transaksi ini.',
            'source'  => 'database'
        ]);
        exit;
    }

    //  Mapping status Midtrans → status internal
    $transaction_status = $midtrans_data['transaction_status'];
    $fraud_status       = $midtrans_data['fraud_status']   ?? 'accept';
    $transaction_id     = $midtrans_data['transaction_id'] ?? '';
    $payment_type       = $midtrans_data['payment_type']   ?? '';
    $status_code        = $midtrans_data['status_code']    ?? '';
    $gross_amount       = $midtrans_data['gross_amount']   ?? '';
    $signature_key      = $midtrans_data['signature_key']  ?? '';

    $new_status = null;

    if ($transaction_status === 'capture' && $fraud_status === 'accept') {
        $new_status = 'lunas';
    } elseif ($transaction_status === 'settlement') {
        $new_status = 'lunas';
    } elseif ($transaction_status === 'pending') {
        $new_status = 'pending';
    } elseif (in_array($transaction_status, ['cancel', 'deny', 'expire'])) {
        $new_status = 'batal';
    }

    // Update DB jika ada perubahan status
    if ($new_status !== null) {
        foreach ($bookings as $booking) {
            $id_booking     = $booking['id_booking'];
            $current_db_status = $booking['status'];

            // Hindari downgrade status
            if ($current_db_status === 'lunas' && $new_status === 'pending') {
                continue;
            }

            if ($new_status !== $current_db_status) {
                $bookingClass->updateStatus($id_booking, $new_status);
            }

            // Simpan data transaksi Midtrans
            if (!empty($transaction_id)) {
                $bookingClass->updateMidtransPayment($id_booking, $transaction_id, $payment_type);
            }
        }
    }

    // Return response ke frontend 
    echo json_encode([
        'success'            => true,
        'status'             => $new_status ?? $current_status,
        'transaction_status' => $transaction_status,
        'payment_type'       => $payment_type,
        'order_id'           => $order_id,
        'source'             => 'midtrans_api'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
