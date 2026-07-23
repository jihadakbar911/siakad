-- ============================================
-- SIAKAD - Sistem Informasi Akademik
-- Database Schema
-- ============================================

-- Buat database
CREATE DATABASE IF NOT EXISTS siakad
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE siakad;

-- ============================================
-- Tabel: users
-- Menyimpan data akun login semua pengguna
-- ============================================
CREATE TABLE users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'dosen', 'mahasiswa') NOT NULL,
    status ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- Tabel: mahasiswa
-- Menyimpan data profil mahasiswa
-- Relasi: id_user → users(id_user)
-- ============================================
CREATE TABLE mahasiswa (
    id_mahasiswa INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    nim VARCHAR(20) NOT NULL UNIQUE,
    nama_mahasiswa VARCHAR(100) NOT NULL,
    jenis_kelamin ENUM('L', 'P') NOT NULL,
    tanggal_lahir DATE NULL,
    alamat TEXT NULL,
    email VARCHAR(100) NULL,
    no_telepon VARCHAR(20) NULL,
    program_studi VARCHAR(100) NOT NULL,
    angkatan YEAR NOT NULL,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- Tabel: dosen
-- Menyimpan data profil dosen
-- Relasi: id_user → users(id_user)
-- ============================================
CREATE TABLE dosen (
    id_dosen INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    nidn VARCHAR(20) NOT NULL UNIQUE,
    nama_dosen VARCHAR(100) NOT NULL,
    email VARCHAR(100) NULL,
    no_telepon VARCHAR(20) NULL,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- Tabel: mata_kuliah
-- Menyimpan data mata kuliah
-- ============================================
CREATE TABLE mata_kuliah (
    id_mata_kuliah INT AUTO_INCREMENT PRIMARY KEY,
    kode_mata_kuliah VARCHAR(10) NOT NULL UNIQUE,
    nama_mata_kuliah VARCHAR(100) NOT NULL,
    sks INT NOT NULL,
    semester INT NOT NULL
) ENGINE=InnoDB;

-- ============================================
-- Tabel: tahun_akademik
-- Menyimpan data tahun akademik dan semester
-- ============================================
CREATE TABLE tahun_akademik (
    id_tahun_akademik INT AUTO_INCREMENT PRIMARY KEY,
    tahun_akademik VARCHAR(9) NOT NULL,
    semester ENUM('Ganjil', 'Genap') NOT NULL,
    status ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'nonaktif',
    UNIQUE KEY unique_tahun_semester (tahun_akademik, semester)
) ENGINE=InnoDB;

-- ============================================
-- Tabel: jadwal_kuliah
-- Menyimpan jadwal perkuliahan
-- Relasi: 
--   id_mata_kuliah → mata_kuliah(id_mata_kuliah)
--   id_dosen → dosen(id_dosen)
--   id_tahun_akademik → tahun_akademik(id_tahun_akademik)
-- ============================================
CREATE TABLE jadwal_kuliah (
    id_jadwal INT AUTO_INCREMENT PRIMARY KEY,
    id_mata_kuliah INT NOT NULL,
    id_dosen INT NOT NULL,
    id_tahun_akademik INT NOT NULL,
    kelas VARCHAR(10) NOT NULL,
    hari ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu') NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    ruangan VARCHAR(20) NOT NULL,
    kuota INT NOT NULL DEFAULT 40,
    FOREIGN KEY (id_mata_kuliah) REFERENCES mata_kuliah(id_mata_kuliah) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_dosen) REFERENCES dosen(id_dosen) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_tahun_akademik) REFERENCES tahun_akademik(id_tahun_akademik) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- Tabel: krs
-- Menyimpan header KRS mahasiswa per semester
-- Relasi:
--   id_mahasiswa → mahasiswa(id_mahasiswa)
--   id_tahun_akademik → tahun_akademik(id_tahun_akademik)
-- ============================================
CREATE TABLE krs (
    id_krs INT AUTO_INCREMENT PRIMARY KEY,
    id_mahasiswa INT NOT NULL,
    id_tahun_akademik INT NOT NULL,
    tanggal_pengisian DATE NOT NULL,
    status_krs ENUM('draft', 'disetujui', 'dikunci') NOT NULL DEFAULT 'draft',
    FOREIGN KEY (id_mahasiswa) REFERENCES mahasiswa(id_mahasiswa) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_tahun_akademik) REFERENCES tahun_akademik(id_tahun_akademik) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY unique_krs (id_mahasiswa, id_tahun_akademik)
) ENGINE=InnoDB;

-- ============================================
-- Tabel: detail_krs
-- Menyimpan detail mata kuliah yang diambil di KRS
-- Relasi:
--   id_krs → krs(id_krs)
--   id_jadwal → jadwal_kuliah(id_jadwal)
-- ============================================
CREATE TABLE detail_krs (
    id_detail_krs INT AUTO_INCREMENT PRIMARY KEY,
    id_krs INT NOT NULL,
    id_jadwal INT NOT NULL,
    status ENUM('aktif', 'dibatalkan') NOT NULL DEFAULT 'aktif',
    FOREIGN KEY (id_krs) REFERENCES krs(id_krs) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_jadwal) REFERENCES jadwal_kuliah(id_jadwal) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY unique_detail_krs (id_krs, id_jadwal)
) ENGINE=InnoDB;

-- ============================================
-- Tabel: nilai
-- Menyimpan nilai mahasiswa per mata kuliah
-- Relasi:
--   id_detail_krs → detail_krs(id_detail_krs)
-- ============================================
CREATE TABLE nilai (
    id_nilai INT AUTO_INCREMENT PRIMARY KEY,
    id_detail_krs INT NOT NULL UNIQUE,
    nilai_tugas DECIMAL(5,2) NULL DEFAULT NULL,
    nilai_uts DECIMAL(5,2) NULL DEFAULT NULL,
    nilai_uas DECIMAL(5,2) NULL DEFAULT NULL,
    nilai_akhir DECIMAL(5,2) NULL DEFAULT NULL,
    nilai_huruf VARCHAR(2) NULL DEFAULT NULL,
    bobot DECIMAL(3,2) NULL DEFAULT NULL,
    FOREIGN KEY (id_detail_krs) REFERENCES detail_krs(id_detail_krs) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
