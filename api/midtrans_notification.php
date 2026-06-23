<?php
$log_file = __DIR__ . '/../uploads/midtrans_webhook.log';

// ── Load dependensi core ──
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../config/midtrans_config.php';
require_once __DIR__ . '/../class/Database.php';
require_once __DIR__ . '/../class/Booking.php';
require_once __DIR__ . '/../class/Midtrans.php';
require_once __DIR__ . '/../class/class.Mail.php';


$raw_body     = file_get_contents('php://input');
$notification = json_decode($raw_body, true);

$log_entry = date('Y-m-d H:i:s') . ' | INCOMING: ' . $raw_body . "\n";
file_put_contents($log_file, $log_entry, FILE_APPEND);

if (!$notification || !isset($notification['order_id'])) {
    http_response_code(400);
    echo json_encode(array('status' => 'error', 'message' => 'Invalid notification payload'));
    exit;
}

//  Ekstrak semua field dari payload Midtrans 
$order_id           = $notification['order_id'];
$transaction_id     = isset($notification['transaction_id'])     ? $notification['transaction_id']     : '';
$status_code        = isset($notification['status_code'])        ? $notification['status_code']        : '';
$gross_amount       = isset($notification['gross_amount'])       ? $notification['gross_amount']       : '';
$signature_key      = isset($notification['signature_key'])      ? $notification['signature_key']      : '';
$transaction_status = isset($notification['transaction_status']) ? $notification['transaction_status'] : '';
$fraud_status       = isset($notification['fraud_status'])       ? $notification['fraud_status']       : 'accept';
$payment_type       = isset($notification['payment_type'])       ? $notification['payment_type']       : '';


// validasi signature key untuk keamanan
if (!Midtrans::verifySignature($order_id, $status_code, $gross_amount, $signature_key)) {
    $log_entry = date('Y-m-d H:i:s') . ' | SIGNATURE INVALID for order: ' . $order_id . "\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);

    http_response_code(403);
    echo json_encode(array('status' => 'error', 'message' => 'Invalid signature key'));
    exit;
}
// Cari data booking berdasarkan order_id

$db = Database::getConnection();
$bookingClass = new Booking();

$stmt = $db->prepare("SELECT id_booking, id_user, status FROM booking WHERE midtrans_order_id = :order_id");
$stmt->execute(array('order_id' => $order_id));
$bookings = $stmt->fetchAll();

if (empty($bookings)) {
    $log_entry = date('Y-m-d H:i:s') . ' | ORDER NOT FOUND: ' . $order_id . "\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);

    http_response_code(404);
    echo json_encode(array('status' => 'error', 'message' => 'Order not found'));
    exit;
}
//Mapping status Midtrans ke status internal sistem
$new_status = null;

if ($transaction_status == 'capture') {
    if ($fraud_status == 'accept') {
        $new_status = 'lunas';
    }
} elseif ($transaction_status == 'settlement') {
    $new_status = 'lunas';
} elseif ($transaction_status == 'pending') {
    $new_status = 'pending';
} elseif ($transaction_status == 'cancel' || $transaction_status == 'deny' || $transaction_status == 'expire') {
    $new_status = 'batal';
}

$log_entry = date('Y-m-d H:i:s') . ' | ORDER: ' . $order_id . ' | STATUS_MIDTRANS: ' . $transaction_status . ' | STATUS_INTERNAL: ' . $new_status . ' | PAYMENT: ' . $payment_type . "\n";
file_put_contents($log_file, $log_entry, FILE_APPEND);

// AUTO-APPROVE + AUTO-SEND E-TIKET

if ($new_status !== null) {

    foreach ($bookings as $booking) {

        $id_booking     = $booking['id_booking'];
        $id_user        = $booking['id_user'];
        $current_status = $booking['status'];

        // Hindari downgrade jangan ubah status 'lunas' menjadi 'pending'
        if ($current_status == 'lunas' && $new_status == 'pending') {
            $log_entry = date('Y-m-d H:i:s') . ' | SKIP DOWNGRADE BK-' . $id_booking . ': sudah lunas, tidak di-downgrade ke pending' . "\n";
            file_put_contents($log_file, $log_entry, FILE_APPEND);
            continue;
        }

        try {
            //  Update status + data transaksi secara atomik 
            if ($new_status != $current_status) {
                $bookingClass->updateStatus($id_booking, $new_status);
            }

            // Simpan data transaksi Midtrans ke database (transaction_id & payment_type)
            if (!empty($transaction_id)) {
                $bookingClass->updateMidtransPayment($id_booking, $transaction_id, $payment_type);
            }

            $log_entry = date('Y-m-d H:i:s') . ' | DB UPDATED BK-' . $id_booking . ': [' . $current_status . '] -> [' . $new_status . "]\n";
            file_put_contents($log_file, $log_entry, FILE_APPEND);


            //  Kirim email hanya saat pembayaran baru lunas
            if (($transaction_status == 'settlement' || $transaction_status == 'capture') && $new_status == 'lunas' && $current_status != 'lunas') {

                // Ambil detail booking lengkap (JOIN jadwal + lapangan)
                $booking_detail = $bookingClass->getBookingById($id_booking);

                // Ambil nama & email pelanggan dari tabel users
                $stmt_user = $db->prepare("SELECT nama, email FROM users WHERE id = :id_user LIMIT 1");
                $stmt_user->execute(array('id_user' => $id_user));
                $user_data = $stmt_user->fetch();

                if ($user_data && !empty($user_data['email'])) {

                    //sanitasi data untuk template
                    $to_email      = $user_data['email'];
                    $to_name       = $user_data['nama'];
                    $customer_name = htmlspecialchars($to_name);
                    $bk_id         = $id_booking;
                    $bk_order_id   = htmlspecialchars($booking_detail['midtrans_order_id']);
                    $bk_lapangan   = htmlspecialchars($booking_detail['nama_lapangan']);
                    $bk_lokasi     = htmlspecialchars($booking_detail['lokasi']);
                    $bk_payment    = htmlspecialchars($payment_type);

                    $nama_hari  = array('Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu');
                    $nama_bulan = array('','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember');
                    $ts         = strtotime($booking_detail['tanggal']);
                    $bk_tanggal = $nama_hari[date('w', $ts)] . ', ' . date('j', $ts) . ' ' . $nama_bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);

                    $bk_jam_mulai   = date('H:i', strtotime($booking_detail['jam_mulai']));
                    $bk_jam_selesai = date('H:i', strtotime($booking_detail['jam_selesai']));

                    // Format harga ke Rupiah
                    $bk_harga = 'Rp ' . number_format((int)$booking_detail['total_harga'], 0, ',', '.');

                    // Subjek email
                    $subject = 'E-Tiket FutsalHub - Booking #' . $bk_id . ' Dikonfirmasi!';

                    //  detail tiket
                    $detail_order    = '<tr>
                        <td style="padding:8px 0; vertical-align:top; width:30px; color:#10b981; font-size:16px;">&#128203;</td>
                        <td style="padding:8px 0; vertical-align:top;">
                            <span style="color:#64748b; font-size:12px; display:block;">Order ID</span>
                            <span style="color:#e2e8f0; font-size:14px; font-weight:600;">' . $bk_order_id . '</span>
                        </td>
                    </tr>';

                    $detail_lapangan = '<tr>
                        <td style="padding:8px 0; vertical-align:top; width:30px; color:#10b981; font-size:16px;">&#127967;</td>
                        <td style="padding:8px 0; vertical-align:top;">
                            <span style="color:#64748b; font-size:12px; display:block;">Lapangan</span>
                            <span style="color:#e2e8f0; font-size:14px; font-weight:600;">' . $bk_lapangan . '</span>
                        </td>
                    </tr>';

                    $detail_lokasi   = '<tr>
                        <td style="padding:8px 0; vertical-align:top; width:30px; color:#10b981; font-size:16px;">&#128205;</td>
                        <td style="padding:8px 0; vertical-align:top;">
                            <span style="color:#64748b; font-size:12px; display:block;">Lokasi</span>
                            <span style="color:#e2e8f0; font-size:14px; font-weight:600;">' . $bk_lokasi . '</span>
                        </td>
                    </tr>';

                    $detail_tanggal  = '<tr>
                        <td style="padding:8px 0; vertical-align:top; width:30px; color:#10b981; font-size:16px;">&#128197;</td>
                        <td style="padding:8px 0; vertical-align:top;">
                            <span style="color:#64748b; font-size:12px; display:block;">Tanggal</span>
                            <span style="color:#e2e8f0; font-size:14px; font-weight:600;">' . $bk_tanggal . '</span>
                        </td>
                    </tr>';

                    $detail_jam      = '<tr>
                        <td style="padding:8px 0; vertical-align:top; width:30px; color:#10b981; font-size:16px;">&#9200;</td>
                        <td style="padding:8px 0; vertical-align:top;">
                            <span style="color:#64748b; font-size:12px; display:block;">Jam Main</span>
                            <span style="color:#e2e8f0; font-size:14px; font-weight:600;">' . $bk_jam_mulai . ' - ' . $bk_jam_selesai . ' WIB</span>
                        </td>
                    </tr>';

                    $detail_payment  = '<tr>
                        <td style="padding:8px 0; vertical-align:top; width:30px; color:#10b981; font-size:16px;">&#128179;</td>
                        <td style="padding:8px 0; vertical-align:top;">
                            <span style="color:#64748b; font-size:12px; display:block;">Pembayaran</span>
                            <span style="color:#e2e8f0; font-size:14px; font-weight:600;">' . $bk_payment . '</span>
                        </td>
                    </tr>';

                    $message = '
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background-color:#0f172a; font-family:\'Segoe UI\',Arial,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#0f172a; padding:30px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="max-width:600px; width:100%;">

                    <!-- HEADER -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #10b981, #059669); padding:30px 40px; border-radius:16px 16px 0 0; text-align:center;">
                            <h1 style="margin:0; color:#ffffff; font-size:28px; letter-spacing:1px;">&#9917; FutsalHub</h1>
                            <p style="margin:8px 0 0; color:#d1fae5; font-size:14px;">E-Tiket Booking Dikonfirmasi</p>
                        </td>
                    </tr>

                    <!-- BODY -->
                    <tr>
                        <td style="background-color:#1e293b; padding:35px 40px;">

                            <!-- Salam Pembuka -->
                            <p style="color:#e2e8f0; font-size:16px; margin:0 0 20px;">
                                Halo <strong style="color:#10b981;">' . $customer_name . '</strong>,
                            </p>
                            <p style="color:#94a3b8; font-size:14px; margin:0 0 25px; line-height:1.6;">
                                Pembayaran kamu telah dikonfirmasi! Berikut adalah e-tiket booking kamu.
                                Tunjukkan e-tiket ini saat datang ke lapangan.
                            </p>

                            <!-- Ticket Card -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
                                   style="background-color:#0f172a; border:1px solid #334155; border-radius:12px; overflow:hidden;">

                                <!-- Ticket Header -->
                                <tr>
                                    <td style="background-color:#064e3b; padding:15px 25px; text-align:center;">
                                        <span style="color:#6ee7b7; font-size:13px; letter-spacing:2px; text-transform:uppercase;">
                                            E-Tiket Booking
                                        </span>
                                        <h2 style="margin:5px 0 0; color:#ffffff; font-size:22px;">#' . $bk_id . '</h2>
                                    </td>
                                </tr>

                                <!-- Ticket Details -->
                                <tr>
                                    <td style="padding:25px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                            ' . $detail_order . '
                                            ' . $detail_lapangan . '
                                            ' . $detail_lokasi . '
                                            ' . $detail_tanggal . '
                                            ' . $detail_jam . '
                                            ' . $detail_payment . '
                                        </table>

                                        <!-- Total Harga -->
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
                                               style="margin-top:20px; border-top:1px dashed #334155; padding-top:15px;">
                                            <tr>
                                                <td style="color:#94a3b8; font-size:14px; padding:5px 0;">Total Pembayaran</td>
                                                <td align="right" style="color:#10b981; font-size:22px; font-weight:bold; padding:5px 0;">
                                                    ' . $bk_harga . '
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <!-- Status Badge -->
                                <tr>
                                    <td style="padding:0 25px 20px; text-align:center;">
                                        <span style="display:inline-block; background-color:#065f46; color:#6ee7b7; padding:8px 24px;
                                                     border-radius:20px; font-size:13px; font-weight:600; letter-spacing:1px;">
                                            &#10003; LUNAS &mdash; DIKONFIRMASI
                                        </span>
                                    </td>
                                </tr>

                            </table>

                            <!-- Catatan Penting -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
                                   style="margin-top:25px; background-color:#1a2332; border-radius:8px; border-left:4px solid #f59e0b;">
                                <tr>
                                    <td style="padding:15px 20px;">
                                        <p style="color:#fbbf24; font-size:13px; font-weight:600; margin:0 0 5px;">&#9888; Catatan Penting</p>
                                        <p style="color:#94a3b8; font-size:12px; margin:0; line-height:1.6;">
                                            Harap datang 10 menit sebelum jadwal main.
                                            Tunjukkan e-tiket ini (screenshot / email) kepada petugas lapangan.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td style="background-color:#0f172a; padding:25px 40px; border-top:1px solid #1e293b;
                                   border-radius:0 0 16px 16px; text-align:center;">
                            <p style="color:#475569; font-size:12px; margin:0 0 5px;">
                                Email ini dikirim otomatis oleh sistem FutsalHub.
                            </p>
                            <p style="color:#334155; font-size:11px; margin:0;">
                                &copy; ' . date('Y') . ' FutsalHub. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>';

                    // dengan memanggil fungsi Mail::SendMail, kita mengirim email ke pelanggan
                    $kirim = Mail::SendMail($to_email, $to_name, $subject, $message);

                    if ($kirim) {
                        $log_entry = date('Y-m-d H:i:s') . ' | EMAIL SENT -> ' . $to_email . ' (BK-' . $id_booking . ")\n";
                    } else {
                        $log_entry = date('Y-m-d H:i:s') . ' | EMAIL FAILED -> ' . $to_email . ' (BK-' . $id_booking . ")\n";
                    }
                    file_put_contents($log_file, $log_entry, FILE_APPEND);

                } else {
                    $log_entry = date('Y-m-d H:i:s') . ' | EMAIL SKIP: data user tidak ditemukan (BK-' . $id_booking . ")\n";
                    file_put_contents($log_file, $log_entry, FILE_APPEND);
                }

            } // Kirim email hanya saat pembayaran baru lunas

        } catch (Exception $e) {
            $log_entry = date('Y-m-d H:i:s') . ' | ERROR (BK-' . $id_booking . '): ' . $e->getMessage() . "\n";
            file_put_contents($log_file, $log_entry, FILE_APPEND);
        }

    } 

} 

http_response_code(200);
echo json_encode(array('status' => 'ok'));
?>
