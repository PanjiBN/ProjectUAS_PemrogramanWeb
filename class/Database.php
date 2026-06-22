<?php
class Database {
    private static $connection = null;

    public static function getConnection() {
        if (self::$connection === null) {
            //memastikan file konfigurasi database ada dan dapat di-load
            $config_path = dirname(__DIR__) . '/config/db_config.php';
            if (file_exists($config_path)) {
                require_once $config_path;
            }
            
            // cek apakah konstanta DB_HOST, DB_USER, DB_PASS, DB_NAME sudah didefinisikan di db_config.php
            $host = defined('DB_HOST') ? DB_HOST : 'localhost';
            $user = defined('DB_USER') ? DB_USER : 'root';
            $pass = defined('DB_PASS') ? DB_PASS : '';
            $name = defined('DB_NAME') ? DB_NAME : 'futsalhub';

            try {
                // Coba koneksi ke database yang ditentukan
                self::$connection = new PDO("mysql:host={$host};dbname={$name};charset=utf8mb4", $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]);
            } catch (PDOException $e) {
                // Jika koneksi ke database gagal, coba koneksi tanpa menyebutkan database untuk cek apakah server MySQL berjalan
                try {
                    self::$connection = new PDO("mysql:host={$host};charset=utf8mb4", $user, $pass, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                    ]);
                    // Database ada, tapi tidak ditemukan, kemungkinan belum di-import
                    die("<div class='container py-5 text-start'><div class='alert alert-warning card-custom text-white p-4'>
                        <h4><i class='fa-solid fa-triangle-exclamation text-warning'></i> Koneksi Database Gagal</h4>
                        <p class='mb-3'>Koneksi ke host berhasil, tetapi database <strong>'{$name}'</strong> tidak ditemukan atau belum di-import.</p>
                        <p class='mb-0 small'>Silakan buka <strong>phpMyAdmin</strong> dan import file SQL yang telah disediakan: <br>
                        <code>[Proyek-Anda]/database.sql</code></p>
                    </div></div>");
                } catch (PDOException $ex) {
                    die("<div class='container py-5 text-start'><div class='alert alert-danger card-custom text-white p-4'>
                        <h4><i class='fa-solid fa-circle-xmark text-danger'></i> Kesalahan Koneksi MySQL</h4>
                        <p class='mb-2'>Tidak dapat terhubung ke server database MySQL. Pastikan XAMPP Apache & MySQL Anda sudah berjalan.</p>
                        <p class='mb-0 small'>Pesan error: <code>{$e->getMessage()}</code></p>
                    </div></div>");
                }
            }
        }
        return self::$connection;
    }
}
?>
