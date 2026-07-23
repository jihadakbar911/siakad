# Sistem Informasi Akademik (SIAKAD)

Aplikasi Sistem Informasi Akademik Berbasis Web Menggunakan PHP Native dan MySQL.

## Deskripsi

SIAKAD adalah aplikasi web untuk mengelola proses akademik perguruan tinggi, meliputi pengelolaan data mahasiswa, dosen, mata kuliah, pengisian KRS, pengisian nilai, serta perhitungan IPS dan IPK.

## Teknologi

- **Backend**: PHP Native (PHP 8+)
- **Database**: MySQL
- **Frontend**: HTML, CSS, JavaScript, Bootstrap 5, AdminLTE 3
- **Server**: XAMPP / Laragon
- **Version Control**: Git & GitHub

## Fitur Utama

- Login multi-role (Admin, Dosen, Mahasiswa)
- Pengelolaan data master (Mahasiswa, Dosen, Mata Kuliah, Jadwal, Tahun Akademik)
- Pengisian KRS oleh Mahasiswa
- Input dan perhitungan nilai oleh Dosen
- Kartu Hasil Studi (KHS), IPS, dan IPK

## Struktur Folder

```
siakad/
├── admin/          # Halaman-halaman admin
├── dosen/          # Halaman-halaman dosen
├── mahasiswa/      # Halaman-halaman mahasiswa
├── config/         # Konfigurasi (database, auth, functions)
├── includes/       # Komponen reusable (header, sidebar, footer)
├── assets/         # CSS, JS, images, plugins
├── database/       # File SQL schema dan seed data
├── docs/           # Dokumentasi proyek
├── login.php       # Halaman login
├── logout.php      # Proses logout
├── index.php       # Halaman utama / redirect
└── README.md
```

## Akun Pengujian

| Role | Username | Password |
|------|----------|----------|
| Admin | admin | admin123 |
| Dosen | dosen001 | dosen123 |
| Dosen | dosen002 | dosen123 |
| Mahasiswa | mahasiswa001 | mahasiswa123 |
| Mahasiswa | mahasiswa002 | mahasiswa123 |
| Mahasiswa | mahasiswa003 | mahasiswa123 |
| Mahasiswa | mahasiswa004 | mahasiswa123 |
| Mahasiswa | mahasiswa005 | mahasiswa123 |

## Cara Instalasi

1. Install XAMPP atau Laragon.
2. Clone repository ini ke folder `htdocs` (XAMPP) atau `www` (Laragon).
3. Buat database baru bernama `siakad` di phpMyAdmin.
4. Import file `database/siakad.sql`.
5. Sesuaikan konfigurasi database di `config/database.php` jika perlu.
6. Akses aplikasi melalui `http://localhost/siakad`.

## Dokumentasi

- [Analisis Kebutuhan Sistem](docs/analisis-kebutuhan.md)

## Lisensi

Proyek ini dibuat untuk keperluan tugas kuliah.
