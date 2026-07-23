<?php
/**
 * Seed Data - SIAKAD
 * 
 * Script ini digunakan untuk mengisi data awal (dummy) ke database.
 * Jalankan script ini SETELAH mengimport siakad.sql ke database.
 * 
 * Cara menjalankan:
 * 1. Pastikan database siakad sudah dibuat dan schema sudah diimport
 * 2. Akses http://localhost/siakad/database/seed.php melalui browser
 * 3. Atau jalankan via CLI: php database/seed.php
 */

// Konfigurasi database
$host = 'localhost';
$dbname = 'siakad';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Koneksi database berhasil.\n";
    echo "Memulai proses seed data...\n\n";

    // ============================================
    // 1. Insert Users
    // ============================================
    echo "1. Memasukkan data users...\n";
    
    $users = [
        // Admin
        ['admin', password_hash('admin123', PASSWORD_DEFAULT), 'admin', 'aktif'],
        // Dosen
        ['dosen001', password_hash('dosen123', PASSWORD_DEFAULT), 'dosen', 'aktif'],
        ['dosen002', password_hash('dosen123', PASSWORD_DEFAULT), 'dosen', 'aktif'],
        // Mahasiswa
        ['mahasiswa001', password_hash('mahasiswa123', PASSWORD_DEFAULT), 'mahasiswa', 'aktif'],
        ['mahasiswa002', password_hash('mahasiswa123', PASSWORD_DEFAULT), 'mahasiswa', 'aktif'],
        ['mahasiswa003', password_hash('mahasiswa123', PASSWORD_DEFAULT), 'mahasiswa', 'aktif'],
        ['mahasiswa004', password_hash('mahasiswa123', PASSWORD_DEFAULT), 'mahasiswa', 'aktif'],
        ['mahasiswa005', password_hash('mahasiswa123', PASSWORD_DEFAULT), 'mahasiswa', 'aktif'],
    ];

    $stmt = $pdo->prepare("INSERT INTO users (username, password, role, status) VALUES (?, ?, ?, ?)");
    foreach ($users as $user) {
        $stmt->execute($user);
    }
    echo "   -> " . count($users) . " users berhasil ditambahkan.\n";

    // ============================================
    // 2. Insert Dosen
    // ============================================
    echo "2. Memasukkan data dosen...\n";
    
    $dosens = [
        [2, '0001018501', 'Dr. Ahmad Fauzi, M.Kom.', 'ahmad.fauzi@univ.ac.id', '081234567001'],
        [3, '0002028502', 'Siti Nurhaliza, S.T., M.T.', 'siti.nurhaliza@univ.ac.id', '081234567002'],
    ];

    $stmt = $pdo->prepare("INSERT INTO dosen (id_user, nidn, nama_dosen, email, no_telepon) VALUES (?, ?, ?, ?, ?)");
    foreach ($dosens as $dosen) {
        $stmt->execute($dosen);
    }
    echo "   -> " . count($dosens) . " dosen berhasil ditambahkan.\n";

    // ============================================
    // 3. Insert Mahasiswa
    // ============================================
    echo "3. Memasukkan data mahasiswa...\n";

    $mahasiswas = [
        [4, '2024001001', 'Budi Santoso', 'L', '2004-03-15', 'Jl. Merdeka No. 10, Jakarta', 'budi.santoso@mail.com', '081300000001', 'Teknik Informatika', 2024],
        [5, '2024001002', 'Aisyah Putri', 'P', '2004-07-22', 'Jl. Sudirman No. 25, Bandung', 'aisyah.putri@mail.com', '081300000002', 'Teknik Informatika', 2024],
        [6, '2024001003', 'Rizky Ramadhan', 'L', '2003-11-08', 'Jl. Gatot Subroto No. 5, Surabaya', 'rizky.ramadhan@mail.com', '081300000003', 'Teknik Informatika', 2024],
        [7, '2024001004', 'Dewi Lestari', 'P', '2004-01-30', 'Jl. Diponegoro No. 18, Yogyakarta', 'dewi.lestari@mail.com', '081300000004', 'Teknik Informatika', 2024],
        [8, '2024001005', 'Fajar Nugroho', 'L', '2003-09-12', 'Jl. Ahmad Yani No. 7, Semarang', 'fajar.nugroho@mail.com', '081300000005', 'Teknik Informatika', 2024],
    ];

    $stmt = $pdo->prepare("INSERT INTO mahasiswa (id_user, nim, nama_mahasiswa, jenis_kelamin, tanggal_lahir, alamat, email, no_telepon, program_studi, angkatan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($mahasiswas as $mhs) {
        $stmt->execute($mhs);
    }
    echo "   -> " . count($mahasiswas) . " mahasiswa berhasil ditambahkan.\n";

    // ============================================
    // 4. Insert Mata Kuliah
    // ============================================
    echo "4. Memasukkan data mata kuliah...\n";

    $mata_kuliahs = [
        ['IF101', 'Algoritma dan Pemrograman', 3, 1],
        ['IF102', 'Matematika Diskrit', 3, 1],
        ['IF201', 'Struktur Data', 3, 2],
        ['IF202', 'Basis Data', 3, 2],
        ['IF301', 'Pemrograman Web', 3, 3],
        ['IF302', 'Jaringan Komputer', 3, 3],
        ['IF401', 'Rekayasa Perangkat Lunak', 3, 4],
        ['IF402', 'Kecerdasan Buatan', 3, 4],
    ];

    $stmt = $pdo->prepare("INSERT INTO mata_kuliah (kode_mata_kuliah, nama_mata_kuliah, sks, semester) VALUES (?, ?, ?, ?)");
    foreach ($mata_kuliahs as $mk) {
        $stmt->execute($mk);
    }
    echo "   -> " . count($mata_kuliahs) . " mata kuliah berhasil ditambahkan.\n";

    // ============================================
    // 5. Insert Tahun Akademik
    // ============================================
    echo "5. Memasukkan data tahun akademik...\n";

    $tahun_akademiks = [
        ['2024/2025', 'Ganjil', 'nonaktif'],
        ['2024/2025', 'Genap', 'aktif'],
        ['2025/2026', 'Ganjil', 'nonaktif'],
    ];

    $stmt = $pdo->prepare("INSERT INTO tahun_akademik (tahun_akademik, semester, status) VALUES (?, ?, ?)");
    foreach ($tahun_akademiks as $ta) {
        $stmt->execute($ta);
    }
    echo "   -> " . count($tahun_akademiks) . " tahun akademik berhasil ditambahkan.\n";

    // ============================================
    // 6. Insert Jadwal Kuliah
    // ============================================
    echo "6. Memasukkan data jadwal kuliah...\n";

    // id_mata_kuliah, id_dosen, id_tahun_akademik, kelas, hari, jam_mulai, jam_selesai, ruangan, kuota
    $jadwals = [
        // Semester Ganjil 2024/2025 (id_tahun_akademik = 1)
        [1, 1, 1, 'A', 'Senin', '08:00:00', '10:30:00', 'R.101', 40],
        [2, 2, 1, 'A', 'Selasa', '08:00:00', '10:30:00', 'R.102', 40],
        [6, 1, 1, 'A', 'Rabu', '13:00:00', '15:30:00', 'R.201', 40],
        // Semester Genap 2024/2025 (id_tahun_akademik = 2)
        [3, 1, 2, 'A', 'Senin', '08:00:00', '10:30:00', 'R.101', 40],
        [4, 2, 2, 'A', 'Selasa', '10:00:00', '12:30:00', 'R.103', 40],
        [5, 1, 2, 'A', 'Kamis', '08:00:00', '10:30:00', 'R.201', 40],
        [5, 2, 2, 'B', 'Jumat', '08:00:00', '10:30:00', 'R.202', 40],
    ];

    $stmt = $pdo->prepare("INSERT INTO jadwal_kuliah (id_mata_kuliah, id_dosen, id_tahun_akademik, kelas, hari, jam_mulai, jam_selesai, ruangan, kuota) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($jadwals as $jadwal) {
        $stmt->execute($jadwal);
    }
    echo "   -> " . count($jadwals) . " jadwal kuliah berhasil ditambahkan.\n";

    // ============================================
    // 7. Insert KRS
    // ============================================
    echo "7. Memasukkan data KRS...\n";

    // KRS untuk Semester Ganjil 2024/2025
    $krss = [
        [1, 1, '2024-08-15', 'dikunci'],  // Budi Santoso
        [2, 1, '2024-08-15', 'dikunci'],  // Aisyah Putri
        [3, 1, '2024-08-16', 'dikunci'],  // Rizky Ramadhan
        // KRS untuk Semester Genap 2024/2025
        [1, 2, '2025-01-20', 'disetujui'], // Budi Santoso
        [2, 2, '2025-01-20', 'disetujui'], // Aisyah Putri
    ];

    $stmt = $pdo->prepare("INSERT INTO krs (id_mahasiswa, id_tahun_akademik, tanggal_pengisian, status_krs) VALUES (?, ?, ?, ?)");
    foreach ($krss as $krs) {
        $stmt->execute($krs);
    }
    echo "   -> " . count($krss) . " KRS berhasil ditambahkan.\n";

    // ============================================
    // 8. Insert Detail KRS
    // ============================================
    echo "8. Memasukkan data detail KRS...\n";

    // Detail KRS Semester Ganjil 2024/2025
    $detail_krss = [
        // Budi (KRS id=1): Algo, Matdis, Jarkom
        [1, 1, 'aktif'],
        [1, 2, 'aktif'],
        [1, 3, 'aktif'],
        // Aisyah (KRS id=2): Algo, Matdis
        [2, 1, 'aktif'],
        [2, 2, 'aktif'],
        // Rizky (KRS id=3): Algo, Matdis, Jarkom
        [3, 1, 'aktif'],
        [3, 2, 'aktif'],
        [3, 3, 'aktif'],
        // Budi Genap (KRS id=4): Strukdat, Basdat, PemWeb
        [4, 4, 'aktif'],
        [4, 5, 'aktif'],
        [4, 6, 'aktif'],
        // Aisyah Genap (KRS id=5): Strukdat, Basdat
        [5, 4, 'aktif'],
        [5, 5, 'aktif'],
    ];

    $stmt = $pdo->prepare("INSERT INTO detail_krs (id_krs, id_jadwal, status) VALUES (?, ?, ?)");
    foreach ($detail_krss as $dk) {
        $stmt->execute($dk);
    }
    echo "   -> " . count($detail_krss) . " detail KRS berhasil ditambahkan.\n";

    // ============================================
    // 9. Insert Nilai (untuk semester yang sudah selesai)
    // ============================================
    echo "9. Memasukkan data nilai...\n";

    // Fungsi untuk menghitung nilai akhir, huruf, dan bobot
    function hitungNilai($tugas, $uts, $uas) {
        $akhir = round(($tugas * 0.3) + ($uts * 0.3) + ($uas * 0.4), 2);
        
        if ($akhir >= 85) { $huruf = 'A'; $bobot = 4.00; }
        elseif ($akhir >= 80) { $huruf = 'A-'; $bobot = 3.75; }
        elseif ($akhir >= 75) { $huruf = 'B+'; $bobot = 3.50; }
        elseif ($akhir >= 70) { $huruf = 'B'; $bobot = 3.00; }
        elseif ($akhir >= 65) { $huruf = 'B-'; $bobot = 2.75; }
        elseif ($akhir >= 60) { $huruf = 'C+'; $bobot = 2.50; }
        elseif ($akhir >= 55) { $huruf = 'C'; $bobot = 2.00; }
        elseif ($akhir >= 40) { $huruf = 'D'; $bobot = 1.00; }
        else { $huruf = 'E'; $bobot = 0.00; }
        
        return [$akhir, $huruf, $bobot];
    }

    // Nilai untuk semester Ganjil 2024/2025 (sudah dikunci)
    $nilais_raw = [
        // Budi: Algo (detail_krs=1), Matdis (2), Jarkom (3)
        [1, 85, 80, 88],   // Algo
        [2, 78, 75, 80],   // Matdis
        [3, 70, 72, 75],   // Jarkom
        // Aisyah: Algo (detail_krs=4), Matdis (5)
        [4, 90, 88, 92],   // Algo
        [5, 85, 82, 88],   // Matdis
        // Rizky: Algo (detail_krs=6), Matdis (7), Jarkom (8)
        [6, 65, 60, 70],   // Algo
        [7, 72, 68, 74],   // Matdis
        [8, 58, 55, 62],   // Jarkom
    ];

    $stmt = $pdo->prepare("INSERT INTO nilai (id_detail_krs, nilai_tugas, nilai_uts, nilai_uas, nilai_akhir, nilai_huruf, bobot) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($nilais_raw as $n) {
        list($akhir, $huruf, $bobot) = hitungNilai($n[1], $n[2], $n[3]);
        $stmt->execute([$n[0], $n[1], $n[2], $n[3], $akhir, $huruf, $bobot]);
    }
    echo "   -> " . count($nilais_raw) . " nilai berhasil ditambahkan.\n";

    // ============================================
    // Selesai
    // ============================================
    echo "\n========================================\n";
    echo "Seed data berhasil dijalankan!\n";
    echo "========================================\n\n";
    echo "Akun pengujian:\n";
    echo "  Admin     : admin / admin123\n";
    echo "  Dosen 1   : dosen001 / dosen123\n";
    echo "  Dosen 2   : dosen002 / dosen123\n";
    echo "  Mhs 1     : mahasiswa001 / mahasiswa123\n";
    echo "  Mhs 2     : mahasiswa002 / mahasiswa123\n";
    echo "  Mhs 3     : mahasiswa003 / mahasiswa123\n";
    echo "  Mhs 4     : mahasiswa004 / mahasiswa123\n";
    echo "  Mhs 5     : mahasiswa005 / mahasiswa123\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
