# FutsalHub - Sistem Booking Lapangan Futsal Online

Aplikasi web booking lapangan futsal online berbasis PHP Native dengan integrasi pembayaran Midtrans dan notifikasi email otomatis menggunakan PHPMailer. Proyek ini dikembangkan sebagai Tugas UAS Mata Kuliah Pemrograman Web.

---

## Deskripsi

FutsalHub adalah sistem pemesanan lapangan futsal secara online dengan tampilan dark theme. Pengguna dapat melihat ketersediaan lapangan secara real-time, memilih jadwal bermain, dan melakukan pembayaran melalui payment gateway Midtrans. Sistem juga dilengkapi panel admin untuk mengelola lapangan, booking, dan pengguna.

---

## Tech Stack

| Kategori | Teknologi |
|----------|-----------|
| Bahasa | PHP 7.4+ (Native / OOP) |
| Database | MySQL / MariaDB |
| Frontend | Bootstrap 5.3.2 |
| Icon | Font Awesome 6.4.0 |
| Payment Gateway | Midtrans Snap API |
| Email | PHPMailer (SMTP Gmail) |
| Web Server | Apache (XAMPP) |

---

## Fitur

### Sisi Pengguna

- Registrasi dan login dengan hashing password
- Melihat daftar lapangan dengan filter pencarian
- Melihat detail lapangan dan jadwal ketersediaan per tanggal
- Booking slot jam bermain secara real-time
- Pembayaran melalui Midtrans (Bank Transfer, E-Wallet, QRIS, dll)
- Riwayat booking dengan status pembayaran
- E-Tiket digital setelah pembayaran lunas
- Bayar ulang booking yang masih pending
- Batalkan booking yang belum dibayar

### Sisi Admin

- Dashboard statistik (total pengguna, lapangan, booking, pendapatan)
- CRUD data lapangan futsal
- Manajemen semua booking (konfirmasi / batalkan)
- Manajemen data pengguna

### Fitur Sistem

- Webhook Midtrans untuk update status otomatis
- Auto-approve booking saat pembayaran berhasil
- Verifikasi signature key untuk keamanan webhook
- Logging notifikasi Midtrans

---

## Struktur Proyek

```
FutsalHub/
|
|-- index.php                        # Entry point dan router utama
|-- README.md
|
|-- admin/                           # Halaman panel admin
|   |-- dashboard.php
|   |-- lapangan.php
|   |-- booking.php
|   +-- user.php
|
|-- api/                             # Backend API
|   |-- create_transaction.php       # Pembuatan transaksi Midtrans
|   +-- midtrans_notification.php    # Webhook handler Midtrans
|
|-- assets/
|   |-- css/
|   |   +-- style.css                # Custom stylesheet
|   +-- images/                      # Gambar lapangan dan background
|
|-- class/                           # Class OOP
|   |-- Database.php                 # Singleton koneksi PDO
|   |-- User.php                     # Autentikasi dan CRUD user
|   |-- Lapangan.php                 # CRUD lapangan
|   |-- Jadwal.php                   # Manajemen jadwal
|   |-- Booking.php                  # Manajemen booking
|   +-- Midtrans.php                 # Integrasi Midtrans API
|
|-- config/
|   |-- db_config.php                # Konfigurasi database
|   +-- midtrans_config.php          # API keys Midtrans
|
|-- includes/
|   |-- header.php                   # HTML head, CSS, JS
|   |-- navbar.php                   # Navigasi utama
|   +-- footer.php                   # Footer dan scripts
|
|-- lib/
|   +-- PHPMailer/                   # Library PHPMailer
|       |-- Exception.php
|       |-- PHPMailer.php
|       +-- SMTP.php
|
+-- pages/                           # Halaman publik
    |-- home.php                     # Landing page
    |-- lapangan.php                 # Daftar lapangan
    |-- detail_lapangan.php          # Detail dan pilih jadwal
    |-- booking.php                  # Checkout pembayaran
    |-- riwayat.php                  # Riwayat booking
    |-- login.php
    |-- register.php
    |-- about.php
    +-- contact.php
```

---

## Instalasi

### Prasyarat

- XAMPP (PHP 7.4+ dan MySQL/MariaDB)
- Web browser modern

### Langkah-langkah

1. Clone repository atau download ZIP:

```bash
git clone https://github.com/PanjiBN/ProjectUAS_PemrogramanWeb.git
```

2. Pindahkan folder proyek ke direktori `htdocs` XAMPP:

```
C:\xampp\htdocs\FutsalHub\
```

3. Jalankan XAMPP, pastikan Apache dan MySQL sudah berjalan.

4. Buka phpMyAdmin (`http://localhost/phpmyadmin`), buat database baru dengan nama `futsalhub`, lalu import file `database.sql`.

5. Sesuaikan konfigurasi database di `config/db_config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'futsalhub');
```

6. Akses aplikasi di browser:

```
http://localhost/FutsalHub/
```

---

## Konfigurasi Midtrans

Edit file `config/midtrans_config.php`:

```php
define('MIDTRANS_SERVER_KEY', 'Mid-server-XXXXXXXX');
define('MIDTRANS_CLIENT_KEY', 'Mid-client-XXXXXXXX');
define('MIDTRANS_IS_PRODUCTION', false); // false = Sandbox, true = Production
```

API key didapat dari dashboard Midtrans di menu Settings > Access Keys.

Untuk webhook, atur Notification URL di dashboard Midtrans:

```
https://domain-anda.com/api/midtrans_notification.php
```

---

## Akun Demo

| Role | Email | Password |
|------|-------|----------|
| User | budi@example.com | userpassword |
| Admin | admin@futsalhub.com | adminpassword |

Tersedia juga tombol Mock Login di navbar (ikon flask) untuk login cepat tanpa password.

---

## Alur Booking

```
Login/Register --> Pilih Lapangan --> Pilih Tanggal & Jam --> Checkout --> Bayar via Midtrans --> Lihat E-Tiket
```

---

## Skema Database

Database `futsalhub` terdiri dari 4 tabel:

### users

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | INT, PK, AI | ID pengguna |
| nama | VARCHAR(100) | Nama lengkap |
| email | VARCHAR(100), UNIQUE | Alamat email |
| password | VARCHAR(255) | Password (hashed) |
| role | ENUM('user','admin') | Role pengguna |

### lapangan

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id_lapangan | INT, PK, AI | ID lapangan |
| nama_lapangan | VARCHAR(100) | Nama lapangan |
| lokasi | VARCHAR(255) | Lokasi lapangan |
| harga | INT | Harga per jam |
| gambar | VARCHAR(255) | Path file gambar |

### jadwal

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id_jadwal | INT, PK, AI | ID jadwal |
| id_lapangan | INT, FK | Referensi ke lapangan |
| tanggal | DATE | Tanggal bermain |
| jam_mulai | TIME | Jam mulai |
| jam_selesai | TIME | Jam selesai |
| status | ENUM('tersedia','dibooking') | Status slot |

### booking

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id_booking | INT, PK, AI | ID booking |
| id_user | INT, FK | Referensi ke users |
| id_jadwal | INT, FK | Referensi ke jadwal |
| tanggal_booking | DATETIME | Waktu booking dibuat |
| total_harga | INT | Total biaya |
| status | ENUM('pending','lunas','batal','expired') | Status booking |
| snap_token | VARCHAR(255) | Token Midtrans |
| midtrans_order_id | VARCHAR(100) | Order ID Midtrans |
| midtrans_transaction_id | VARCHAR(100) | Transaction ID |
| payment_type | VARCHAR(50) | Metode pembayaran |

### Relasi

- `booking.id_user` --> `users.id`
- `booking.id_jadwal` --> `jadwal.id_jadwal`
- `jadwal.id_lapangan` --> `lapangan.id_lapangan`

---

## API Endpoints

### POST /api/create_transaction.php

Membuat transaksi pembayaran baru atau mengambil snap token untuk booking yang sudah ada.

Request (booking baru):
```json
{
    "id_jadwals": "1,2,3",
    "total_harga": 455000
}
```

Request (bayar ulang):
```json
{
    "id_booking": 5
}
```

Response:
```json
{
    "success": true,
    "snap_token": "token-string",
    "order_id": "FH-0001-1719043200"
}
```

### POST /api/midtrans_notification.php

Webhook untuk menerima notifikasi dari Midtrans. Mapping status:

| Status Midtrans | Status Booking |
|-----------------|----------------|
| settlement | lunas |
| capture (accept) | lunas |
| pending | pending |
| cancel / deny / expire | batal |

---

## Keamanan

- Password di-hash menggunakan `password_hash()` (bcrypt)
- Prepared statements (PDO) untuk mencegah SQL injection
- `htmlspecialchars()` untuk mencegah XSS
- Verifikasi signature SHA-512 pada webhook Midtrans
- Role-based access control pada session
- Database transaction untuk menjaga atomicity data booking

---

## Testing Pembayaran (Sandbox)

| Tipe Kartu | Nomor | CVV | Exp |
|------------|-------|-----|-----|
| Visa (Success) | 4811 1111 1111 1114 | 123 | Masa depan |
| Mastercard (Success) | 5211 1111 1111 1117 | 123 | Masa depan |

Untuk metode lain (VA, E-Wallet, QRIS), ikuti instruksi di popup Midtrans.

---

## Catatan

- Proyek ini menggunakan Midtrans mode Sandbox untuk demo dan testing.
- Mock login hanya untuk keperluan demo, nonaktifkan sebelum production.
- Jangan upload file konfigurasi berisi API key ke repository publik.

---

## Lisensi

Proyek ini dikembangkan untuk keperluan akademik - Tugas UAS Mata Kuliah Pemrograman Web.

---

## Kontributor

- **PanjiBN** - Full-Stack Developer
