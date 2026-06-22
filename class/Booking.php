<?php
require_once __DIR__ . '/Database.php';

class Booking {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    //tambah fungsi createBooking dengan transaksi untuk memastikan atomicity
    public function createBooking($id_user, $id_jadwal, $total_harga) {
        $this->db->beginTransaction();
        try {
            // 1. Cek apakah jadwal masih tersedia (status = 'tersedia')
            $stmt = $this->db->prepare("SELECT status FROM jadwal WHERE id_jadwal = :id_jadwal FOR UPDATE");
            $stmt->execute(['id_jadwal' => $id_jadwal]);
            $status = $stmt->fetchColumn();

            if ($status !== 'tersedia') {
                throw new Exception("Slot waktu ini sudah dibooking oleh pengguna lain.");
            }

            // 2. Buat booking record di database (status: pending)
            $stmt = $this->db->prepare("INSERT INTO booking (id_user, id_jadwal, tanggal_booking, total_harga, status) VALUES (:id_user, :id_jadwal, NOW(), :total_harga, 'pending')");
            $stmt->execute([
                'id_user' => $id_user,
                'id_jadwal' => $id_jadwal,
                'total_harga' => $total_harga
            ]);

            // 3. Update status jadwal menjadi 'dibooking' untuk mengunci slot waktu tersebut
            $stmt = $this->db->prepare("UPDATE jadwal SET status = 'dibooking' WHERE id_jadwal = :id_jadwal");
            $stmt->execute(['id_jadwal' => $id_jadwal]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // Fungsi untuk mendapatkan semua booking milik user tertentu
    public function getBookingsByUser($id_user) {
        $sql = "SELECT b.id_booking, b.tanggal_booking, b.total_harga, b.status,
                       b.snap_token, b.midtrans_order_id, b.payment_type,
                       j.tanggal, j.jam_mulai, j.jam_selesai, j.id_jadwal,
                       l.nama_lapangan, l.gambar, l.lokasi
                FROM booking b
                JOIN jadwal j ON b.id_jadwal = j.id_jadwal
                JOIN lapangan l ON j.id_lapangan = l.id_lapangan
                WHERE b.id_user = :id_user
                ORDER BY b.id_booking DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_user' => $id_user]);
        return $stmt->fetchAll();
    }

    // Fungsi untuk mendapatkan semua booking (Admin view)
    public function getAllBookings() {
        $sql = "SELECT b.id_booking, b.tanggal_booking, b.total_harga, b.status, 
                       j.tanggal, j.jam_mulai, j.jam_selesai,
                       l.nama_lapangan,
                       u.nama AS nama_user, u.email AS email_user
                FROM booking b
                JOIN jadwal j ON b.id_jadwal = j.id_jadwal
                JOIN lapangan l ON j.id_lapangan = l.id_lapangan
                JOIN users u ON b.id_user = u.id
                ORDER BY b.id_booking DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    // Fungsi untuk update status booking (misal: pending → lunas, atau batal)
    public function updateStatus($id_booking, $status) {
        $this->db->beginTransaction();
        try {
            // Ambil data booking untuk mendapatkan id_jadwal terkait
            $stmt = $this->db->prepare("SELECT id_jadwal, status FROM booking WHERE id_booking = :id_booking");
            $stmt->execute(['id_booking' => $id_booking]);
            $booking = $stmt->fetch();

            if (!$booking) {
                throw new Exception("Data booking tidak ditemukan.");
            }

            $id_jadwal = $booking['id_jadwal'];

            // Update booking status
            $stmt = $this->db->prepare("UPDATE booking SET status = :status WHERE id_booking = :id_booking");
            $stmt->execute([
                'status' => $status,
                'id_booking' => $id_booking
            ]);

            // Jika status berubah menjadi batal atau expired, kembalikan jadwal ke 'tersedia'
            if ($status === 'batal' || $status === 'expired') {
                $stmt = $this->db->prepare("UPDATE jadwal SET status = 'tersedia' WHERE id_jadwal = :id_jadwal");
                $stmt->execute(['id_jadwal' => $id_jadwal]);
            } elseif ($status === 'lunas') {
                // Jika status berubah menjadi lunas, pastikan jadwal tetap 'dibooking' (tidak berubah)
                $stmt = $this->db->prepare("UPDATE jadwal SET status = 'dibooking' WHERE id_jadwal = :id_jadwal");
                $stmt->execute(['id_jadwal' => $id_jadwal]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // Fungsi untuk mendapatkan detail booking berdasarkan ID booking
    public function getBookingById($id_booking) {
        $sql = "SELECT b.*, j.tanggal, j.jam_mulai, j.jam_selesai,
                       l.nama_lapangan, l.lokasi
                FROM booking b
                JOIN jadwal j ON b.id_jadwal = j.id_jadwal
                JOIN lapangan l ON j.id_lapangan = l.id_lapangan
                WHERE b.id_booking = :id_booking
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_booking' => $id_booking]);
        return $stmt->fetch();
    }

    // Fungsi untuk menyimpan snap token dan order ID Midtrans ke database
    public function updateSnapToken($id_booking, $snap_token, $midtrans_order_id) {
        $stmt = $this->db->prepare(
            "UPDATE booking SET snap_token = :snap_token, midtrans_order_id = :order_id WHERE id_booking = :id_booking"
        );
        return $stmt->execute([
            'snap_token' => $snap_token,
            'order_id'   => $midtrans_order_id,
            'id_booking' => $id_booking
        ]);
    }

    // Fungsi untuk menyimpan data transaksi Midtrans (transaction ID dan payment type) ke database
    public function updateMidtransPayment($id_booking, $transaction_id, $payment_type) {
        $stmt = $this->db->prepare(
            "UPDATE booking SET midtrans_transaction_id = :txn_id, payment_type = :pay_type WHERE id_booking = :id_booking"
        );
        return $stmt->execute([
            'txn_id'     => $transaction_id,
            'pay_type'   => $payment_type,
            'id_booking' => $id_booking
        ]);
    }
}
?>
