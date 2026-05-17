# Sistem Booking Lapangan Berbasis Web

## Deskripsi Project

Sistem Booking Lapangan Berbasis Web merupakan aplikasi yang digunakan untuk membantu proses pemesanan lapangan olahraga secara online agar lebih mudah, cepat, dan efisien.

Sistem ini dibuat untuk memenuhi Final Project Mata Kuliah Pemrograman Web Universitas Ary Ginanjar.

---

# Fitur Sistem

## Fitur User
- Melihat jadwal lapangan
- Melihat slot tersedia
- Booking lapangan

## Fitur Admin
- Login admin
- Dashboard admin
- Kelola slot jadwal
- Monitoring booking
- Hapus booking

---

# Teknologi Yang Digunakan

| Bagian | Teknologi |
|---|---|
| Frontend | HTML, CSS, Bootstrap |
| Backend | PHP |
| Database | MySQL |
| Tools | Visual Studio Code, XAMPP |

---

# Struktur Folder

```text
booking_lapangan/
│
├── index.php
│
├── pages/
│   ├── home.php
│   ├── booking.php
│   ├── jadwal.php
│   ├── about.php
│   └── contact.php
│
├── admin/
│   ├── login.php
│   ├── dashboard.php
│   ├── slot.php
│   └── booking.php
│
├── process/
│   ├── booking_process.php
│   ├── login_process.php
│   ├── tambah_slot_process.php
│   ├── hapus_booking_process.php
│   └── logout.php
│
├── assets/
│   ├── css/
│   │   └── style.css
│   │
│   └── img/
│
└── database/
    └── booking_lapangan.sql
```

---

# Konsep Sistem

Sistem menggunakan konsep booking berbasis slot.

Admin menentukan slot jadwal lapangan terlebih dahulu, kemudian user dapat memilih slot yang tersedia untuk melakukan booking.

Contoh slot:
- 08:00 - 09:30
- 10:00 - 11:30
- 12:00 - 13:30

Sistem otomatis mencegah bentrok jadwal booking.

---

# Dynamic Page

Website menggunakan konsep Dynamic Page dengan:
- `index.php`
- folder `pages`
- query string `?p=`

Contoh:
```url
index.php?p=about
```

---

# Cara Menjalankan Project

## 1. Pindahkan Folder Project

Pindahkan folder:
```text
booking_lapangan
```

ke:
```text
C:/xampp/htdocs/
```

---

## 2. Jalankan XAMPP

Aktifkan:
- Apache
- MySQL

---

## 3. Import Database

1. Buka phpMyAdmin
2. Buat database:
```text
booking_lapangan
```
3. Import file:
```text
database/booking_lapangan.sql
```

---

## 4. Jalankan Website

Buka browser:

```url
http://localhost/booking_lapangan
```

---

# Team Project

| Nama | Tugas |
|---|---|
| Panji Basenda Nugroho | Booking, Database |
| Nasya Kiara Putri | Home, Admin, UI UX |
| Dwi Ferdianto | Index |
| Muhammad Azka Alasyari | Index, UI UX |

---

# Kesimpulan

Sistem Booking Lapangan Berbasis Web dibuat untuk membantu proses pemesanan lapangan secara online agar lebih mudah, cepat, dan efisien.

Sistem ini diharapkan dapat membantu pelanggan dalam melakukan booking serta membantu pengelola dalam mengatur jadwal dan transaksi dengan lebih baik.