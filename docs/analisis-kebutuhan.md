# Tahap 1: Analisis Kebutuhan Sistem

## Tujuan Tahap

Mendefinisikan kebutuhan sistem secara lengkap sebelum memulai pengembangan, meliputi identifikasi aktor, hak akses, use case, alur proses, serta kebutuhan fungsional dan nonfungsional.

---

## 1. Analisis Kebutuhan Sistem

Sistem Informasi Akademik (SIAKAD) adalah aplikasi berbasis web yang digunakan untuk mengelola proses akademik pada sebuah perguruan tinggi. Sistem ini mencakup pengelolaan data mahasiswa, dosen, mata kuliah, jadwal kuliah, pengisian Kartu Rencana Studi (KRS), pengisian nilai oleh dosen, serta perhitungan Indeks Prestasi Semester (IPS) dan Indeks Prestasi Kumulatif (IPK).

### Permasalahan yang Diselesaikan

1. Pengelolaan data akademik yang masih dilakukan secara manual atau menggunakan spreadsheet.
2. Proses pengisian KRS yang tidak terstruktur dan sulit divalidasi.
3. Pengisian dan perhitungan nilai yang rawan kesalahan jika dilakukan secara manual.
4. Sulitnya memantau progres akademik mahasiswa secara real-time.
5. Tidak adanya pembatasan akses berdasarkan peran pengguna.

### Solusi yang Ditawarkan

Membangun aplikasi web SIAKAD menggunakan PHP native dan MySQL yang memungkinkan:

- Admin mengelola seluruh data master (mahasiswa, dosen, mata kuliah, jadwal, tahun akademik).
- Dosen mengisi dan mengubah nilai mahasiswa pada mata kuliah yang diampu.
- Mahasiswa mengisi KRS, melihat nilai, KHS, IPS, dan IPK secara mandiri.
- Sistem membatasi akses berdasarkan role (Admin, Dosen, Mahasiswa).

---

## 2. Daftar Aktor dan Hak Akses

### 2.1 Admin

| No | Hak Akses | Keterangan |
|----|-----------|------------|
| 1 | Mengelola data mahasiswa | CRUD (Create, Read, Update, Delete) |
| 2 | Mengelola data dosen | CRUD |
| 3 | Mengelola data mata kuliah | CRUD |
| 4 | Mengelola tahun akademik | CRUD |
| 5 | Mengelola jadwal kuliah | CRUD |
| 6 | Menentukan dosen pengampu | Menugaskan dosen ke mata kuliah |
| 7 | Melihat data KRS seluruh mahasiswa | Read only |
| 8 | Melihat data nilai seluruh mahasiswa | Read only |
| 9 | Membuat akun dosen dan mahasiswa | Create akun pengguna |
| 10 | Mengaktifkan/menonaktifkan pengguna | Update status pengguna |
| 11 | Melihat rekap statistik pada dashboard | Read only |

### 2.2 Dosen

| No | Hak Akses | Keterangan |
|----|-----------|------------|
| 1 | Melihat mata kuliah yang diampu | Read only, hanya mata kuliah sendiri |
| 2 | Melihat daftar mahasiswa per mata kuliah | Read only |
| 3 | Mengisi nilai mahasiswa | Create nilai (tugas, UTS, UAS) |
| 4 | Mengubah nilai mahasiswa | Update nilai |
| 5 | Melihat hasil perhitungan nilai akhir | Read only, dihitung otomatis oleh sistem |

### 2.3 Mahasiswa

| No | Hak Akses | Keterangan |
|----|-----------|------------|
| 1 | Melihat profil sendiri | Read only |
| 2 | Melihat daftar mata kuliah tersedia | Read only |
| 3 | Mengisi KRS | Create, pilih mata kuliah |
| 4 | Menghapus mata kuliah dari KRS | Delete, sebelum KRS dikunci |
| 5 | Melihat KRS yang telah diambil | Read only |
| 6 | Melihat nilai setiap mata kuliah | Read only |
| 7 | Melihat KHS | Read only |
| 8 | Melihat IPS per semester | Read only |
| 9 | Melihat IPK kumulatif | Read only |
| 10 | Melihat total SKS dan mutu | Read only |

---

## 3. Use Case Sistem

### 3.1 Use Case Autentikasi

| No | Use Case | Aktor | Deskripsi |
|----|----------|-------|-----------|
| UC-01 | Login | Admin, Dosen, Mahasiswa | Pengguna memasukkan username dan password untuk masuk ke sistem |
| UC-02 | Logout | Admin, Dosen, Mahasiswa | Pengguna keluar dari sistem dan session dihapus |
| UC-03 | Pembatasan akses | Sistem | Sistem mengarahkan pengguna ke dashboard sesuai role |

### 3.2 Use Case Admin

| No | Use Case | Deskripsi |
|----|----------|-----------|
| UC-04 | Lihat dashboard admin | Menampilkan statistik jumlah mahasiswa, dosen, mata kuliah, KRS |
| UC-05 | Tambah mahasiswa | Membuat data mahasiswa baru beserta akun login |
| UC-06 | Lihat daftar mahasiswa | Menampilkan tabel data mahasiswa dengan fitur pencarian |
| UC-07 | Edit mahasiswa | Mengubah data mahasiswa yang sudah ada |
| UC-08 | Hapus mahasiswa | Menghapus data mahasiswa (dengan konfirmasi) |
| UC-09 | Tambah dosen | Membuat data dosen baru beserta akun login |
| UC-10 | Lihat daftar dosen | Menampilkan tabel data dosen |
| UC-11 | Edit dosen | Mengubah data dosen yang sudah ada |
| UC-12 | Hapus dosen | Menghapus data dosen (dengan konfirmasi) |
| UC-13 | Tambah mata kuliah | Membuat data mata kuliah baru |
| UC-14 | Lihat daftar mata kuliah | Menampilkan tabel data mata kuliah |
| UC-15 | Edit mata kuliah | Mengubah data mata kuliah |
| UC-16 | Hapus mata kuliah | Menghapus data mata kuliah (dengan konfirmasi) |
| UC-17 | Kelola tahun akademik | CRUD tahun akademik dan set status aktif |
| UC-18 | Kelola jadwal kuliah | CRUD jadwal kuliah (mata kuliah, dosen, kelas, waktu, ruangan) |
| UC-19 | Lihat KRS mahasiswa | Melihat data KRS seluruh mahasiswa |
| UC-20 | Lihat nilai mahasiswa | Melihat data nilai seluruh mahasiswa |
| UC-21 | Kelola akun pengguna | Mengaktifkan/menonaktifkan akun pengguna |

### 3.3 Use Case Dosen

| No | Use Case | Deskripsi |
|----|----------|-----------|
| UC-22 | Lihat dashboard dosen | Menampilkan informasi mata kuliah yang diampu |
| UC-23 | Lihat mata kuliah diampu | Menampilkan daftar mata kuliah yang ditugaskan |
| UC-24 | Lihat daftar mahasiswa | Menampilkan mahasiswa yang mengambil mata kuliah tertentu |
| UC-25 | Input nilai mahasiswa | Memasukkan nilai tugas, UTS, dan UAS |
| UC-26 | Ubah nilai mahasiswa | Mengubah nilai yang sudah diinput |
| UC-27 | Lihat hasil nilai akhir | Melihat nilai akhir, huruf, dan bobot yang dihitung sistem |

### 3.4 Use Case Mahasiswa

| No | Use Case | Deskripsi |
|----|----------|-----------|
| UC-28 | Lihat dashboard mahasiswa | Menampilkan ringkasan informasi akademik |
| UC-29 | Lihat profil | Menampilkan data diri mahasiswa |
| UC-30 | Lihat mata kuliah tersedia | Menampilkan mata kuliah yang bisa diambil |
| UC-31 | Isi KRS | Memilih mata kuliah untuk semester berjalan |
| UC-32 | Hapus mata kuliah dari KRS | Menghapus mata kuliah yang sudah dipilih (sebelum dikunci) |
| UC-33 | Lihat KRS | Menampilkan KRS yang telah diambil |
| UC-34 | Lihat nilai | Menampilkan nilai setiap mata kuliah |
| UC-35 | Lihat KHS | Menampilkan Kartu Hasil Studi per semester |
| UC-36 | Lihat IPS | Menampilkan Indeks Prestasi Semester |
| UC-37 | Lihat IPK | Menampilkan Indeks Prestasi Kumulatif |

---

## 4. Alur Proses

### 4.1 Alur Proses Admin

```
Login → Dashboard Admin
  ├── Kelola Mahasiswa
  │   ├── Lihat daftar → Cari mahasiswa
  │   ├── Tambah mahasiswa → Isi form → Simpan → Akun otomatis dibuat
  │   ├── Edit mahasiswa → Ubah data → Simpan
  │   └── Hapus mahasiswa → Konfirmasi → Hapus beserta akun
  │
  ├── Kelola Dosen
  │   ├── Lihat daftar
  │   ├── Tambah dosen → Isi form → Simpan → Akun otomatis dibuat
  │   ├── Edit dosen → Ubah data → Simpan
  │   └── Hapus dosen → Konfirmasi → Hapus beserta akun
  │
  ├── Kelola Mata Kuliah
  │   ├── Lihat daftar
  │   ├── Tambah mata kuliah → Isi form (kode, nama, SKS, semester) → Simpan
  │   ├── Edit mata kuliah → Ubah data → Simpan
  │   └── Hapus mata kuliah → Konfirmasi → Hapus
  │
  ├── Kelola Tahun Akademik
  │   ├── Lihat daftar
  │   ├── Tambah tahun akademik → Isi form → Simpan
  │   ├── Set status aktif → Nonaktifkan yang lain
  │   └── Hapus tahun akademik → Konfirmasi
  │
  ├── Kelola Jadwal Kuliah
  │   ├── Lihat daftar
  │   ├── Tambah jadwal → Pilih mata kuliah, dosen, kelas, waktu → Simpan
  │   ├── Edit jadwal → Ubah data → Simpan
  │   └── Hapus jadwal → Konfirmasi
  │
  ├── Lihat KRS Mahasiswa → Pilih mahasiswa/semester → Lihat detail
  │
  ├── Lihat Nilai Mahasiswa → Pilih mahasiswa/semester → Lihat detail
  │
  └── Kelola Akun → Aktifkan/Nonaktifkan pengguna
```

### 4.2 Alur Proses Dosen

```
Login → Dashboard Dosen
  ├── Lihat Mata Kuliah Diampu
  │   └── Pilih mata kuliah → Lihat detail jadwal
  │
  ├── Lihat Daftar Mahasiswa
  │   └── Pilih mata kuliah → Lihat mahasiswa yang terdaftar
  │
  └── Kelola Nilai
      ├── Pilih mata kuliah → Pilih kelas
      ├── Lihat daftar mahasiswa
      ├── Input nilai → Isi nilai tugas, UTS, UAS → Simpan
      │   └── Sistem otomatis menghitung:
      │       ├── Nilai Akhir = (30% × Tugas) + (30% × UTS) + (40% × UAS)
      │       ├── Nilai Huruf (A, A-, B+, B, B-, C+, C, D, E)
      │       └── Bobot Nilai (4.00 - 0.00)
      └── Ubah nilai → Edit nilai yang sudah ada → Simpan ulang
```

### 4.3 Alur Proses Mahasiswa

```
Login → Dashboard Mahasiswa
  ├── Lihat Profil → Tampilkan data diri
  │
  ├── Isi KRS
  │   ├── Pilih tahun akademik/semester
  │   ├── Lihat mata kuliah tersedia
  │   ├── Pilih mata kuliah → Validasi:
  │   │   ├── Cek duplikasi (tidak boleh ambil mata kuliah sama)
  │   │   ├── Cek total SKS (maksimal 24 SKS)
  │   │   └── Cek kuota kelas
  │   ├── Simpan KRS
  │   └── Hapus mata kuliah dari KRS (sebelum dikunci)
  │
  ├── Lihat KRS → Tampilkan KRS per semester
  │
  ├── Lihat Nilai → Tampilkan nilai setiap mata kuliah
  │
  ├── Lihat KHS
  │   ├── Pilih semester
  │   ├── Tampilkan daftar mata kuliah + nilai
  │   ├── Tampilkan IPS semester tersebut
  │   └── Tampilkan total SKS dan mutu semester
  │
  └── Lihat IPK
      ├── Tampilkan IPK kumulatif
      ├── Tampilkan total seluruh SKS yang ditempuh
      └── Tampilkan total seluruh mutu
```

---

## 5. Daftar Kebutuhan Fungsional

| No | Kode | Kebutuhan Fungsional | Prioritas |
|----|------|---------------------|-----------|
| 1 | FR-01 | Sistem menyediakan halaman login untuk Admin, Dosen, dan Mahasiswa | Tinggi |
| 2 | FR-02 | Sistem membedakan hak akses berdasarkan role pengguna | Tinggi |
| 3 | FR-03 | Sistem menyediakan fitur logout yang menghapus session | Tinggi |
| 4 | FR-04 | Password disimpan menggunakan hashing (password_hash) | Tinggi |
| 5 | FR-05 | Admin dapat melakukan CRUD data mahasiswa | Tinggi |
| 6 | FR-06 | Admin dapat melakukan CRUD data dosen | Tinggi |
| 7 | FR-07 | Admin dapat melakukan CRUD data mata kuliah | Tinggi |
| 8 | FR-08 | Admin dapat melakukan CRUD tahun akademik | Tinggi |
| 9 | FR-09 | Admin dapat melakukan CRUD jadwal kuliah | Tinggi |
| 10 | FR-10 | Admin dapat menentukan dosen pengampu mata kuliah | Tinggi |
| 11 | FR-11 | Admin dapat melihat KRS seluruh mahasiswa | Sedang |
| 12 | FR-12 | Admin dapat melihat nilai seluruh mahasiswa | Sedang |
| 13 | FR-13 | Admin dapat membuat akun dosen dan mahasiswa | Tinggi |
| 14 | FR-14 | Admin dapat mengaktifkan/menonaktifkan pengguna | Sedang |
| 15 | FR-15 | Dashboard Admin menampilkan rekap statistik | Sedang |
| 16 | FR-16 | Dosen dapat melihat mata kuliah yang diampu | Tinggi |
| 17 | FR-17 | Dosen dapat melihat daftar mahasiswa per mata kuliah | Tinggi |
| 18 | FR-18 | Dosen dapat mengisi nilai (tugas, UTS, UAS) | Tinggi |
| 19 | FR-19 | Dosen dapat mengubah nilai yang sudah diinput | Tinggi |
| 20 | FR-20 | Sistem menghitung nilai akhir secara otomatis | Tinggi |
| 21 | FR-21 | Sistem mengkonversi nilai akhir ke nilai huruf | Tinggi |
| 22 | FR-22 | Sistem mengkonversi nilai akhir ke bobot | Tinggi |
| 23 | FR-23 | Mahasiswa dapat melihat profil | Sedang |
| 24 | FR-24 | Mahasiswa dapat melihat mata kuliah tersedia | Tinggi |
| 25 | FR-25 | Mahasiswa dapat mengisi KRS | Tinggi |
| 26 | FR-26 | Mahasiswa dapat menghapus mata kuliah dari KRS sebelum dikunci | Tinggi |
| 27 | FR-27 | Sistem membatasi maksimal SKS (24 SKS) | Tinggi |
| 28 | FR-28 | Sistem mencegah pengambilan mata kuliah ganda | Tinggi |
| 29 | FR-29 | Mahasiswa dapat melihat KRS yang telah diambil | Sedang |
| 30 | FR-30 | Mahasiswa dapat melihat nilai setiap mata kuliah | Sedang |
| 31 | FR-31 | Mahasiswa dapat melihat KHS per semester | Sedang |
| 32 | FR-32 | Sistem menghitung IPS per semester | Tinggi |
| 33 | FR-33 | Sistem menghitung IPK kumulatif | Tinggi |
| 34 | FR-34 | Sistem menampilkan total SKS dan total mutu | Sedang |
| 35 | FR-35 | Admin dapat mencari data mahasiswa | Sedang |

---

## 6. Daftar Kebutuhan Nonfungsional

| No | Kode | Kebutuhan Nonfungsional | Kategori |
|----|------|------------------------|----------|
| 1 | NFR-01 | Sistem menggunakan prepared statement untuk mencegah SQL Injection | Keamanan |
| 2 | NFR-02 | Output di-escape menggunakan htmlspecialchars() untuk mencegah XSS | Keamanan |
| 3 | NFR-03 | Password di-hash menggunakan password_hash() dengan algoritma bcrypt | Keamanan |
| 4 | NFR-04 | Session di-regenerate setelah login menggunakan session_regenerate_id() | Keamanan |
| 5 | NFR-05 | Form penting dilindungi dengan CSRF token | Keamanan |
| 6 | NFR-06 | Akses halaman dibatasi berdasarkan role pengguna | Keamanan |
| 7 | NFR-07 | Konfirmasi ditampilkan sebelum menghapus data | Keamanan |
| 8 | NFR-08 | Validasi input dilakukan pada sisi server | Keamanan |
| 9 | NFR-09 | Tampilan responsif dan dapat diakses di berbagai ukuran layar | Usability |
| 10 | NFR-10 | Menggunakan Bootstrap/AdminLTE untuk konsistensi tampilan | Usability |
| 11 | NFR-11 | Tabel data menggunakan tampilan responsif | Usability |
| 12 | NFR-12 | Notifikasi sukses/gagal ditampilkan setelah operasi CRUD | Usability |
| 13 | NFR-13 | Aplikasi kompatibel dengan PHP 8 dan MySQL 5.7+ | Kompatibilitas |
| 14 | NFR-14 | Aplikasi dapat dijalankan pada XAMPP atau Laragon | Kompatibilitas |
| 15 | NFR-15 | Kode menggunakan penamaan variabel dan fungsi yang konsisten | Maintainability |
| 16 | NFR-16 | Kode memisahkan koneksi database, autentikasi, fungsi, dan tampilan | Maintainability |
| 17 | NFR-17 | Struktur folder mengikuti konvensi yang terorganisir | Maintainability |

---

## 7. Rumus Perhitungan

### 7.1 Nilai Akhir

```
Nilai Akhir = (30% × Nilai Tugas) + (30% × Nilai UTS) + (40% × Nilai UAS)
```

### 7.2 Konversi Nilai

| Rentang Nilai | Huruf | Bobot |
|---------------|-------|-------|
| 85 – 100 | A | 4,00 |
| 80 – 84 | A- | 3,75 |
| 75 – 79 | B+ | 3,50 |
| 70 – 74 | B | 3,00 |
| 65 – 69 | B- | 2,75 |
| 60 – 64 | C+ | 2,50 |
| 55 – 59 | C | 2,00 |
| 40 – 54 | D | 1,00 |
| 0 – 39 | E | 0,00 |

### 7.3 IPS dan IPK

```
Mutu Mata Kuliah = Bobot Nilai × Jumlah SKS

IPS = Total Mutu Semester / Total SKS Semester

IPK = Total Seluruh Mutu / Total Seluruh SKS yang Ditempuh
```

---

## Cara Pengujian Tahap 1

Tahap 1 merupakan tahap analisis dan dokumentasi. Pengujian dilakukan dengan cara:

1. Memastikan seluruh aktor dan hak akses telah teridentifikasi.
2. Memastikan seluruh use case mencakup fitur yang diminta.
3. Memastikan alur proses konsisten dengan use case.
4. Memastikan kebutuhan fungsional mencakup seluruh fitur.
5. Memastikan kebutuhan nonfungsional mencakup aspek keamanan, usability, dan kompatibilitas.
6. Memastikan rumus perhitungan sesuai ketentuan.

---

## Kemungkinan Error dan Cara Mengatasinya

| Potensi Masalah | Solusi |
|-----------------|--------|
| Kebutuhan fungsional tidak lengkap | Review kembali terhadap daftar fitur pada ketentuan proyek |
| Use case tidak konsisten dengan alur proses | Pastikan setiap use case memiliki representasi dalam alur |
| Rumus perhitungan tidak sesuai | Validasi terhadap ketentuan yang diberikan |
