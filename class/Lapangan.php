<?php
require_once __DIR__ . '/Database.php';

class Lapangan {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // mendapatkan semua lapangan
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM lapangan ORDER BY id_lapangan ASC");
        return $stmt->fetchAll();
    }

    // mendapatkan lapangan berdasarkan ID
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM lapangan WHERE id_lapangan = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    // Fungsi untuk menambah lapangan baru (Admin view)
    public function create($nama, $lokasi, $harga, $gambar) {
        $stmt = $this->db->prepare("INSERT INTO lapangan (nama_lapangan, lokasi, harga, gambar) VALUES (:nama, :lokasi, :harga, :gambar)");
        return $stmt->execute([
            'nama' => $nama,
            'lokasi' => $lokasi,
            'harga' => $harga,
            'gambar' => $gambar
        ]);
    }

    //mengupdate lapangan (Admin view)
    public function update($id, $nama, $lokasi, $harga, $gambar = null) {
        if ($gambar) {
            $stmt = $this->db->prepare("UPDATE lapangan SET nama_lapangan = :nama, lokasi = :lokasi, harga = :harga, gambar = :gambar WHERE id_lapangan = :id");
            return $stmt->execute([
                'id' => $id,
                'nama' => $nama,
                'lokasi' => $lokasi,
                'harga' => $harga,
                'gambar' => $gambar
            ]);
        } else {
            $stmt = $this->db->prepare("UPDATE lapangan SET nama_lapangan = :nama, lokasi = :lokasi, harga = :harga WHERE id_lapangan = :id");
            return $stmt->execute([
                'id' => $id,
                'nama' => $nama,
                'lokasi' => $lokasi,
                'harga' => $harga
            ]);
        }
    }

    // menghapus lapangan (Admin view)
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM lapangan WHERE id_lapangan = :id");
        return $stmt->execute(['id' => $id]);
    }
}
?>
