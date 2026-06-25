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
require_once __DIR__ . '/../class/class.Mail.php';

function writeEticketLog($message) {
    $dir = __DIR__ . '/../uploads';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    file_put_contents($dir . '/eticket_mail.log', date('Y-m-d H:i:s') . ' | ' . $message . "\n", FILE_APPEND);
}

function formatTanggalIndonesia($date) {
    $nama_hari  = array('Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu');
    $nama_bulan = array('','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember');
    $ts = strtotime($date);

    return $nama_hari[date('w', $ts)] . ', ' . date('j', $ts) . ' ' . $nama_bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

function sendEticketEmail($bookingClass, $db, $id_booking, $id_user) {
    $booking_detail = $bookingClass->getBookingById($id_booking);

    if (!$booking_detail) {
        writeEticketLog('SKIP BK-' . $id_booking . ': detail booking tidak ditemukan');
        return false;
    }

    $stmt_user = $db->prepare("SELECT nama, email FROM users WHERE id = :id_user LIMIT 1");
    $stmt_user->execute(array('id_user' => $id_user));
    $user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if (!$user_data) {
        writeEticketLog('SKIP BK-' . $id_booking . ': user tidak ditemukan');
        return false;
    }

    $to_email = $user_data['email'];
    $to_name = $user_data['nama'];
    $bk_code = 'BK-' . str_pad($id_booking, 4, '0', STR_PAD_LEFT);
    $order_id = !empty($booking_detail['midtrans_order_id']) ? $booking_detail['midtrans_order_id'] : '-';
    $tanggal_main = formatTanggalIndonesia($booking_detail['tanggal']);
    $waktu_main = date('H:i', strtotime($booking_detail['jam_mulai'])) . ' - ' . date('H:i', strtotime($booking_detail['jam_selesai']));

    $qr_payload = "Kode Booking / Nomor Tiket: {$bk_code}\n"
        . "Order ID Midtrans: {$order_id}\n"
        . "Nama Pelanggan: {$to_name}\n"
        . "Nama Lapangan: {$booking_detail['nama_lapangan']}\n"
        . "Lokasi Lapangan: {$booking_detail['lokasi']}\n"
        . "Tanggal Main: {$tanggal_main}\n"
        . "Waktu / Jam Main: {$waktu_main}";
    $qr_src = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&margin=10&data=' . rawurlencode($qr_payload);

    $subject = 'E-Tiket FutsalHub - ' . $bk_code . ' Dikonfirmasi';
    $message = '
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background:#f3f4f6; font-family:Segoe UI,Arial,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f3f4f6; padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="640" cellspacing="0" cellpadding="0" style="width:100%; max-width:640px; background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 10px 28px rgba(15,23,42,.12);">
                    <tr>
                        <td style="background:#111827; color:#ffffff; text-align:center; padding:28px 24px;">
                            <div style="font-size:30px; font-weight:800; letter-spacing:.5px;">FUTSAL<span style="color:#00C853;">HUB</span></div>
                            <div style="margin-top:8px; color:#d1d5db; font-size:14px;">E-TIKET BOOKING LAPANGAN</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align:center; padding:28px 24px 20px;">
                            <div style="font-size:42px; line-height:1; color:#00C853; font-weight:800;">' . htmlspecialchars($bk_code) . '</div>
                            <div style="display:inline-block; margin-top:14px; background:#E8F5E9; color:#00C853; border-radius:50px; padding:8px 18px; font-weight:700;">&#10003; LUNAS</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 28px 28px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center" style="padding:0 0 24px;">
                                        <div style="display:inline-block; border:2px dashed #00C853; border-radius:8px; padding:12px; background:#ffffff;">
                                            <img src="' . htmlspecialchars($qr_src) . '" width="180" height="180" alt="QR Code ' . htmlspecialchars($bk_code) . '" style="display:block; border:0;">
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-top:1px solid #e5e7eb;">
                                <tr><td style="padding:13px 0; color:#6b7280; font-weight:600; border-bottom:1px dashed #d1d5db;">Kode Booking / Nomor Tiket</td><td align="right" style="padding:13px 0; color:#111827; font-weight:700; border-bottom:1px dashed #d1d5db;">' . htmlspecialchars($bk_code) . '</td></tr>
                                <tr><td style="padding:13px 0; color:#6b7280; font-weight:600; border-bottom:1px dashed #d1d5db;">Order ID Midtrans</td><td align="right" style="padding:13px 0; color:#111827; font-weight:700; border-bottom:1px dashed #d1d5db;">' . htmlspecialchars($order_id) . '</td></tr>
                                <tr><td style="padding:13px 0; color:#6b7280; font-weight:600; border-bottom:1px dashed #d1d5db;">Nama Pelanggan</td><td align="right" style="padding:13px 0; color:#111827; font-weight:700; border-bottom:1px dashed #d1d5db;">' . htmlspecialchars($to_name) . '</td></tr>
                                <tr><td style="padding:13px 0; color:#6b7280; font-weight:600; border-bottom:1px dashed #d1d5db;">Nama Lapangan</td><td align="right" style="padding:13px 0; color:#111827; font-weight:700; border-bottom:1px dashed #d1d5db;">' . htmlspecialchars($booking_detail['nama_lapangan']) . '</td></tr>
                                <tr><td style="padding:13px 0; color:#6b7280; font-weight:600; border-bottom:1px dashed #d1d5db;">Lokasi Lapangan</td><td align="right" style="padding:13px 0; color:#111827; font-weight:700; border-bottom:1px dashed #d1d5db;">' . htmlspecialchars($booking_detail['lokasi']) . '</td></tr>
                                <tr><td style="padding:13px 0; color:#6b7280; font-weight:600; border-bottom:1px dashed #d1d5db;">Tanggal Main</td><td align="right" style="padding:13px 0; color:#111827; font-weight:700; border-bottom:1px dashed #d1d5db;">' . htmlspecialchars($tanggal_main) . '</td></tr>
                                <tr><td style="padding:13px 0; color:#6b7280; font-weight:600; border-bottom:1px dashed #d1d5db;">Waktu / Jam Main</td><td align="right" style="padding:13px 0; color:#111827; font-weight:700; border-bottom:1px dashed #d1d5db;">' . htmlspecialchars($waktu_main) . ' WIB</td></tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#f8fafc; color:#555; text-align:center; padding:18px 24px; border-top:1px solid #e5e7eb;">
                            Tunjukkan e-tiket ini kepada petugas FutsalHub saat check-in lapangan.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';

    $sent = Mail::SendMail($to_email, $to_name, $subject, $message);

    if ($sent) {
        writeEticketLog('SENT -> ' . $to_email . ' (' . $bk_code . ')');
    } else {
        writeEticketLog('FAILED -> ' . $to_email . ' (' . $bk_code . '): ' . Mail::GetLastError());
    }

    return $sent;
}

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

            if ($new_status === 'lunas' && $current_db_status !== 'lunas') {
                sendEticketEmail($bookingClass, $db, $id_booking, $booking['id_user']);
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
