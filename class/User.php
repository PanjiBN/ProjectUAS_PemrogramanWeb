<?php
require_once __DIR__ . '/Database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    //Authenticate user credentials
    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Password benar, kembalikan data user tanpa password
            unset($user['password']);
            return $user;
        }
        return false;
    }

    // Register new user
    public function register($nama, $email, $password) {
        // Cek apakah email sudah terdaftar
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            throw new Exception("Email sudah terdaftar. Silakan gunakan email lain.");
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        //insert user baru ke database
        $stmt = $this->db->prepare("INSERT INTO users (nama, email, password, role) VALUES (:nama, :email, :password, 'user')");
        return $stmt->execute([
            'nama' => $nama,
            'email' => $email,
            'password' => $hashed_password
        ]);
    }

    // Get user by ID
    public function getUserById($id) {
        $stmt = $this->db->prepare("SELECT id, nama, email, role FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    // Get all users (Admin view)
    public function getAllUsers() {
        $stmt = $this->db->query("SELECT id, nama, email, role FROM users ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    //hapus user (Admin view)
    public function deleteUser($id) {
        // Cegah penghapusan akun admin utama (misalnya dengan ID 1)
        if ($id == 1) {
            throw new Exception("Akun Administrator Utama tidak dapat dihapus.");
        }
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
?>
