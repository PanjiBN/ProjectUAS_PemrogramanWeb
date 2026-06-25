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

// Ekstrak semua field dari payload Midtrans 
$order_id           = $notification['order_id'];
$transaction_id     = isset($notification['transaction_id'])     ? $notification['transaction_id']     : '';
$status_code        = isset($notification['status_code'])        ? $notification['status_code']        : '';
$gross_amount       = isset($notification['gross_amount'])       ? $notification['gross_amount']       : '';
$signature_key      = isset($notification['signature_key'])      ? $notification['signature_key']      : '';
$transaction_status = isset($notification['transaction_status']) ? $notification['transaction_status'] : '';
$fraud_status       = isset($notification['fraud_status'])       ? $notification['fraud_status']       : 'accept';
$payment_type       = isset($notification['payment_type'])       ? $notification['payment_type']       : '';

// Validasi signature key untuk keamanan
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

// Mapping status Midtrans ke status internal sistem
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


// ==========================================
// AUTO-APPROVE + AUTO-SEND E-TIKET (BATCHING)
// ==========================================

if ($new_status !== null) {

    // Siapkan wadah untuk menampung semua tiket dalam 1 order
    $booked_tickets = [];
    $customer_email = '';
    $customer_name  = '';
    $send_email_flag = false;
    

    foreach ($bookings as $booking) {

        $id_booking     = $booking['id_booking'];
        $id_user        = $booking['id_user'];
        $current_status = $booking['status'];

        // Hindari downgrade: jangan ubah status 'lunas' menjadi 'pending'
        if ($current_status == 'lunas' && $new_status == 'pending') {
            $log_entry = date('Y-m-d H:i:s') . ' | SKIP DOWNGRADE BK-' . $id_booking . ': sudah lunas' . "\n";
            file_put_contents($log_file, $log_entry, FILE_APPEND);
            continue;
        }

        try {
            // Update status & payment secara atomik per booking
            if ($new_status != $current_status) {
                $bookingClass->updateStatus($id_booking, $new_status);
            }
            if (!empty($transaction_id)) {
                $bookingClass->updateMidtransPayment($id_booking, $transaction_id, $payment_type);
            }

            $log_entry = date('Y-m-d H:i:s') . ' | DB UPDATED BK-' . $id_booking . ': [' . $current_status . '] -> [' . $new_status . "]\n";
            file_put_contents($log_file, $log_entry, FILE_APPEND);

            // Kumpulkan data untuk email JIKA status baru saja lunas
            if (($transaction_status == 'settlement' || $transaction_status == 'capture') && $new_status == 'lunas' && $current_status != 'lunas') {
                
                // Fetch detail booking
                $booking_detail = $bookingClass->getBookingById($id_booking);
                
                // Fetch data user HANYA JIKA belum ditarik (optimasi database)
                if (empty($customer_email)) {
                    $stmt_user = $db->prepare("SELECT nama, email FROM users WHERE id = :id_user LIMIT 1");
                    $stmt_user->execute(array('id_user' => $id_user));
                    $user_data = $stmt_user->fetch();

                    if ($user_data && !empty($user_data['email'])) {
                        $customer_email = $user_data['email'];
                        $customer_name  = $user_data['nama'];
                        $send_email_flag = true;
                    }
                }

                // Masukkan tiket ke dalam array batch
                if ($send_email_flag) {
                    $ts = strtotime($booking_detail['tanggal']);
                    $nama_hari = array('Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu');
                    $nama_bulan = array('','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember');
                    $tanggal_main = $nama_hari[date('w', $ts)] . ', ' . date('j', $ts) . ' ' . $nama_bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);

                    $booked_tickets[] = array(
                        'kode'     => 'BK-' . str_pad($id_booking, 4, '0', STR_PAD_LEFT),
                        'lapangan' => htmlspecialchars($booking_detail['nama_lapangan']),
                        'lokasi'   => htmlspecialchars($booking_detail['lokasi']),
                        'tanggal'  => $tanggal_main,
                        'waktu'    => date('H:i', strtotime($booking_detail['jam_mulai'])) . ' - ' . date('H:i', strtotime($booking_detail['jam_selesai'])) . ' WIB',
                        'harga'    => 'Rp ' . number_format((int)$booking_detail['total_harga'], 0, ',', '.')
                    );
                }
            }

        } catch (Exception $e) {
            // Error handling agar eksekusi loop tidak terhenti total jika 1 row gagal
            $log_entry = date('Y-m-d H:i:s') . ' | ERROR UPDATE BK-' . $id_booking . ': ' . $e->getMessage() . "\n";
            file_put_contents($log_file, $log_entry, FILE_APPEND);
        }
    } // End Foreach

    // ==========================================
    // EKSEKUSI PENGIRIMAN EMAIL DI LUAR LOOP
    // ==========================================
    if ($send_email_flag && !empty($booked_tickets)) {
        
        $subject = "E-Tiket FutsalHub - Order ID: " . htmlspecialchars($order_id) . " Dikonfirmasi!";
        
        // Mulai bangun template HTML untuk isi email (Desain Modern)
        $message_html = '<div style="font-family: Arial, sans-serif; padding: 20px; color: #333; background-color:#f8fafc;">';
        $message_html .= '<div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; border-top: 5px solid #10b981; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">';
        
        // Header Email
        $message_html .= '<div style="text-align: center; margin-bottom: 25px;">';
        $message_html .= '<h1 style="color: #10b981; margin: 0; font-size: 28px;">&#9917; FutsalHub</h1>';
        $message_html .= '<p style="color: #64748b; margin: 5px 0 0 0; font-size: 14px;">E-Tiket Booking Dikonfirmasi</p>';
        $message_html .= '</div>';

        $message_html .= '<h2 style="color: #0f172a; margin-top:0;">Halo <span style="color:#10b981;">' . htmlspecialchars($customer_name) . '</span>,</h2>';
        $message_html .= '<p style="color: #64748b; line-height: 1.6;">Pembayaran Anda telah berhasil. Berikut adalah rangkuman tiket untuk Order ID: <strong style="color:#0f172a;">' . htmlspecialchars($order_id) . '</strong></p>';
        
        // Loop array tiket untuk ditambahkan ke body HTML
        foreach ($booked_tickets as $tiket) {
            // QR Code Generator API
            $qr_payload = "Tiket: " . $tiket['kode'] . "\nOrder ID: " . $order_id . "\nLapangan: " . $tiket['lapangan'] . "\nTanggal: " . $tiket['tanggal'] . "\nWaktu: " . $tiket['waktu'];
            $qr_src = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&margin=10&data=' . rawurlencode($qr_payload);

            $message_html .= '
            <div style="border: 1px solid #e2e8f0; padding: 20px; margin-bottom: 15px; border-radius: 8px; background-color: #f1f5f9;">
                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td width="70%" valign="top">
                            <h3 style="margin-top:0; color: #065f46; font-size:18px;">&#127915; ' . $tiket['kode'] . '</h3>
                            <p style="margin: 5px 0; font-size: 14px; color: #334155;"><strong>Lapangan:</strong> ' . $tiket['lapangan'] . ' (' . $tiket['lokasi'] . ')</p>
                            <p style="margin: 5px 0; font-size: 14px; color: #334155;"><strong>Tanggal:</strong> ' . $tiket['tanggal'] . '</p>
                            <p style="margin: 5px 0; font-size: 14px; color: #334155;"><strong>Waktu:</strong> ' . $tiket['waktu'] . '</p>
                            <p style="margin: 5px 0; font-size: 14px; color: #334155;"><strong>Harga:</strong> <span style="color:#10b981; font-weight:bold;">' . $tiket['harga'] . '</span></p>
                        </td>
                        <td width="30%" align="right" valign="top">
                            <img src="' . htmlspecialchars($qr_src) . '" width="100" height="100" alt="QR Code" style="display:block; border-radius:8px; border:1px solid #cbd5e1;">
                        </td>
                    </tr>
                </table>
            </div>';
        }
        
        $message_html .= '<div style="margin-top: 25px; padding: 15px; background-color: #fffbeb; border-left: 4px solid #f59e0b; border-radius: 4px;">';
        $message_html .= '<p style="color: #b45309; font-size: 13px; margin: 0; font-weight: bold;">&#9888; Catatan Penting:</p>';
        $message_html .= '<p style="color: #78350f; font-size: 13px; margin: 5px 0 0 0; line-height: 1.5;">Harap datang 10 menit sebelum jadwal main. Tunjukkan e-tiket ini beserta QR Code kepada petugas lapangan.</p>';
        $message_html .= '</div>';
        
        $message_html .= '<p style="color: #94a3b8; font-size: 11px; margin-top: 30px; text-align: center;">&copy; ' . date('Y') . ' FutsalHub. Email ini dikirim secara otomatis oleh sistem.</p>';
        $message_html .= '</div></div>';

        // Panggil fungsi pengiriman menggunakan metode Anda (Class Mail)
        try {
            $kirim = Mail::SendEticketMail($customer_email, $customer_name, $subject, $message_html);
            
            if ($kirim) {
                $log_entry = date('Y-m-d H:i:s') . ' | BATCH EMAIL SENT -> ' . $customer_email . " (Order: " . $order_id . ")\n";
            } else {
                $error_msg = Mail::GetLastError();
                $log_entry = date('Y-m-d H:i:s') . ' | BATCH EMAIL FAILED -> ' . $customer_email . " | Error: " . $error_msg . "\n";
            }
            file_put_contents($log_file, $log_entry, FILE_APPEND);

        } catch (Exception $e) {
            $log_entry = date('Y-m-d H:i:s') . ' | BATCH EMAIL FATAL ERROR -> ' . $e->getMessage() . "\n";
            file_put_contents($log_file, $log_entry, FILE_APPEND);
        }
    }
}

// Berikan respons 200 OK ke Midtrans agar webhook dianggap sukses
http_response_code(200);
echo json_encode(array('status' => 'ok'));
exit;
?>