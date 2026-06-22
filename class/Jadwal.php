<?php
require_once __DIR__ . '/Database.php';

class Jadwal {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // Fungsi untuk mendapatkan jadwal berdasarkan id_lapangan dan tanggal
    public function getSchedulesByFieldAndDate($id_lapangan, $tanggal) {
        // First check if any schedules exist for this field and date
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM jadwal WHERE id_lapangan = :id_lapangan AND tanggal = :tanggal");
        $stmt->execute(['id_lapangan' => $id_lapangan, 'tanggal' => $tanggal]);
        $count = $stmt->fetchColumn();

        if ($count == 0) {
            // buat jadwal default untuk tanggal tersebut jika belum ada
            $this->generateDefaultSchedules($id_lapangan, $tanggal);
        }

        // ambil semua jadwal untuk field dan tanggal tersebut
        $stmt = $this->db->prepare("SELECT * FROM jadwal WHERE id_lapangan = :id_lapangan AND tanggal = :tanggal ORDER BY jam_mulai ASC");
        $stmt->execute(['id_lapangan' => $id_lapangan, 'tanggal' => $tanggal]);
        return $stmt->fetchAll();
    }


    private function generateDefaultSchedules($id_lapangan, $tanggal) {
        $start_hour = 8;
        $end_hour = 22;
        
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("INSERT INTO jadwal (id_lapangan, tanggal, jam_mulai, jam_selesai, status) VALUES (:id_lapangan, :tanggal, :jam_mulai, :jam_selesai, 'tersedia')");
            
            for ($hour = $start_hour; $hour < $end_hour; $hour++) {
                $start_time = sprintf('%02d:00:00', $hour);
                $end_time = sprintf('%02d:00:00', $hour + 1);
                
                $stmt->execute([
                    'id_lapangan' => $id_lapangan,
                    'tanggal' => $tanggal,
                    'jam_mulai' => $start_time,
                    'jam_selesai' => $end_time
                ]);
            }
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    //fungsi untuk menambah jadwal baru (Admin view)
    public function addSchedule($id_lapangan, $tanggal, $jam_mulai, $jam_selesai) {
        $stmt = $this->db->prepare("INSERT INTO jadwal (id_lapangan, tanggal, jam_mulai, jam_selesai, status) VALUES (:id_lapangan, :tanggal, :jam_mulai, :jam_selesai, 'tersedia')");
        return $stmt->execute([
            'id_lapangan' => $id_lapangan,
            'tanggal' => $tanggal,
            'jam_mulai' => $jam_mulai,
            'jam_selesai' => $jam_selesai
        ]);
    }

    //fungsi untuk menghapus jadwal (Admin view)
    public function deleteSchedule($id_jadwal) {
        $stmt = $this->db->prepare("DELETE FROM jadwal WHERE id_jadwal = :id_jadwal");
        return $stmt->execute(['id_jadwal' => $id_jadwal]);
    }
}
?>
