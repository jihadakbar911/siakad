# Entity Relationship Diagram (ERD) - SIAKAD

## Diagram ERD

```mermaid
erDiagram
    users ||--o| mahasiswa : "memiliki"
    users ||--o| dosen : "memiliki"
    mahasiswa ||--o{ krs : "mengisi"
    tahun_akademik ||--o{ krs : "pada periode"
    tahun_akademik ||--o{ jadwal_kuliah : "pada periode"
    krs ||--o{ detail_krs : "berisi"
    jadwal_kuliah ||--o{ detail_krs : "dipilih di"
    mata_kuliah ||--o{ jadwal_kuliah : "dijadwalkan"
    dosen ||--o{ jadwal_kuliah : "mengajar"
    detail_krs ||--o| nilai : "memiliki"

    users {
        int id_user PK
        varchar username UK
        varchar password
        enum role "admin/dosen/mahasiswa"
        enum status "aktif/nonaktif"
        timestamp created_at
    }

    mahasiswa {
        int id_mahasiswa PK
        int id_user FK
        varchar nim UK
        varchar nama_mahasiswa
        enum jenis_kelamin "L/P"
        date tanggal_lahir
        text alamat
        varchar email
        varchar no_telepon
        varchar program_studi
        year angkatan
    }

    dosen {
        int id_dosen PK
        int id_user FK
        varchar nidn UK
        varchar nama_dosen
        varchar email
        varchar no_telepon
    }

    mata_kuliah {
        int id_mata_kuliah PK
        varchar kode_mata_kuliah UK
        varchar nama_mata_kuliah
        int sks
        int semester
    }

    tahun_akademik {
        int id_tahun_akademik PK
        varchar tahun_akademik
        enum semester "Ganjil/Genap"
        enum status "aktif/nonaktif"
    }

    jadwal_kuliah {
        int id_jadwal PK
        int id_mata_kuliah FK
        int id_dosen FK
        int id_tahun_akademik FK
        varchar kelas
        enum hari
        time jam_mulai
        time jam_selesai
        varchar ruangan
        int kuota
    }

    krs {
        int id_krs PK
        int id_mahasiswa FK
        int id_tahun_akademik FK
        date tanggal_pengisian
        enum status_krs "draft/disetujui/dikunci"
    }

    detail_krs {
        int id_detail_krs PK
        int id_krs FK
        int id_jadwal FK
        enum status "aktif/dibatalkan"
    }

    nilai {
        int id_nilai PK
        int id_detail_krs FK
        decimal nilai_tugas
        decimal nilai_uts
        decimal nilai_uas
        decimal nilai_akhir
        varchar nilai_huruf
        decimal bobot
    }
```

---

## Penjelasan Setiap Tabel

### 1. Tabel `users`
Menyimpan data akun login untuk semua pengguna (Admin, Dosen, Mahasiswa). Setiap pengguna memiliki satu akun dengan role tertentu. Password disimpan dalam bentuk hash menggunakan `password_hash()`.

### 2. Tabel `mahasiswa`
Menyimpan data profil lengkap mahasiswa. Setiap record terhubung ke tabel `users` melalui `id_user` untuk keperluan autentikasi. NIM bersifat unik.

### 3. Tabel `dosen`
Menyimpan data profil dosen. Setiap record terhubung ke tabel `users` melalui `id_user`. NIDN bersifat unik.

### 4. Tabel `mata_kuliah`
Menyimpan data master mata kuliah meliputi kode, nama, jumlah SKS, dan semester. Kode mata kuliah bersifat unik.

### 5. Tabel `tahun_akademik`
Menyimpan data periode akademik (contoh: 2024/2025 Ganjil). Hanya satu periode yang boleh berstatus aktif pada satu waktu. Kombinasi tahun dan semester bersifat unik.

### 6. Tabel `jadwal_kuliah`
Menyimpan jadwal perkuliahan yang menghubungkan mata kuliah, dosen pengampu, dan tahun akademik. Termasuk informasi kelas, hari, waktu, ruangan, dan kuota.

### 7. Tabel `krs`
Menyimpan header KRS (Kartu Rencana Studi) per mahasiswa per tahun akademik. Setiap mahasiswa hanya boleh memiliki satu KRS per periode (diterapkan oleh UNIQUE constraint). Status KRS bisa `draft`, `disetujui`, atau `dikunci`.

### 8. Tabel `detail_krs`
Menyimpan detail mata kuliah yang dipilih dalam KRS. Setiap record menunjuk ke jadwal kuliah tertentu. Kombinasi KRS dan jadwal bersifat unik untuk mencegah duplikasi.

### 9. Tabel `nilai`
Menyimpan nilai mahasiswa per mata kuliah. Terhubung ke `detail_krs` secara one-to-one (UNIQUE). Berisi nilai komponen (tugas, UTS, UAS), nilai akhir yang dihitung otomatis, nilai huruf, dan bobot.

---

## Relasi Antar Tabel

| No | Relasi | Tipe | Keterangan |
|----|--------|------|------------|
| 1 | users → mahasiswa | One-to-One | Satu akun user untuk satu mahasiswa |
| 2 | users → dosen | One-to-One | Satu akun user untuk satu dosen |
| 3 | mahasiswa → krs | One-to-Many | Satu mahasiswa bisa punya banyak KRS (per semester) |
| 4 | tahun_akademik → krs | One-to-Many | Satu periode memiliki banyak KRS |
| 5 | tahun_akademik → jadwal_kuliah | One-to-Many | Satu periode memiliki banyak jadwal |
| 6 | krs → detail_krs | One-to-Many | Satu KRS berisi banyak mata kuliah |
| 7 | jadwal_kuliah → detail_krs | One-to-Many | Satu jadwal bisa dipilih banyak mahasiswa |
| 8 | mata_kuliah → jadwal_kuliah | One-to-Many | Satu mata kuliah bisa dijadwalkan berkali-kali |
| 9 | dosen → jadwal_kuliah | One-to-Many | Satu dosen bisa mengajar banyak jadwal |
| 10 | detail_krs → nilai | One-to-One | Satu detail KRS memiliki satu record nilai |

---

## Constraint dan Validasi Database

- **UNIQUE** pada `users.username`, `mahasiswa.nim`, `dosen.nidn`, `mata_kuliah.kode_mata_kuliah`
- **UNIQUE** pada kombinasi `krs(id_mahasiswa, id_tahun_akademik)` — mencegah KRS ganda
- **UNIQUE** pada kombinasi `detail_krs(id_krs, id_jadwal)` — mencegah mata kuliah ganda dalam KRS
- **UNIQUE** pada `nilai.id_detail_krs` — satu nilai per detail KRS
- **UNIQUE** pada kombinasi `tahun_akademik(tahun_akademik, semester)` — mencegah duplikasi periode
- **ON DELETE CASCADE** pada semua foreign key — menghapus data terkait secara otomatis
- **ENUM** digunakan untuk membatasi nilai kolom (role, status, jenis_kelamin, hari, semester)
