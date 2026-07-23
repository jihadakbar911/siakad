<?php
/**
 * Fungsi-Fungsi Umum (Helper Functions)
 * 
 * Berisi fungsi utilitas yang digunakan di seluruh aplikasi:
 * - Perhitungan nilai akademik (nilai akhir, huruf, bobot, IPS, IPK)
 * - CSRF token
 * - Sanitasi input
 * - Flash message
 * - Format tanggal
 * - Redirect
 */

// ============================================
// FUNGSI PERHITUNGAN NILAI AKADEMIK
// ============================================

/**
 * Menghitung nilai akhir dari komponen nilai
 * Rumus: 30% Tugas + 30% UTS + 40% UAS
 *
 * @param float $tugas Nilai tugas (0-100)
 * @param float $uts Nilai UTS (0-100)
 * @param float $uas Nilai UAS (0-100)
 * @return float Nilai akhir (0-100)
 */
function hitungNilaiAkhir(float $tugas, float $uts, float $uas): float
{
    return round(($tugas * 0.3) + ($uts * 0.3) + ($uas * 0.4), 2);
}

/**
 * Mengkonversi nilai akhir menjadi nilai huruf
 *
 * @param float $nilai_akhir Nilai akhir (0-100)
 * @return string Nilai huruf (A, A-, B+, B, B-, C+, C, D, E)
 */
function getNilaiHuruf(float $nilai_akhir): string
{
    if ($nilai_akhir >= 85) return 'A';
    if ($nilai_akhir >= 80) return 'A-';
    if ($nilai_akhir >= 75) return 'B+';
    if ($nilai_akhir >= 70) return 'B';
    if ($nilai_akhir >= 65) return 'B-';
    if ($nilai_akhir >= 60) return 'C+';
    if ($nilai_akhir >= 55) return 'C';
    if ($nilai_akhir >= 40) return 'D';
    return 'E';
}

/**
 * Mengkonversi nilai akhir menjadi bobot
 *
 * @param float $nilai_akhir Nilai akhir (0-100)
 * @return float Bobot nilai (0.00 - 4.00)
 */
function getBobotNilai(float $nilai_akhir): float
{
    if ($nilai_akhir >= 85) return 4.00;
    if ($nilai_akhir >= 80) return 3.75;
    if ($nilai_akhir >= 75) return 3.50;
    if ($nilai_akhir >= 70) return 3.00;
    if ($nilai_akhir >= 65) return 2.75;
    if ($nilai_akhir >= 60) return 2.50;
    if ($nilai_akhir >= 55) return 2.00;
    if ($nilai_akhir >= 40) return 1.00;
    return 0.00;
}

/**
 * Menghitung IPS (Indeks Prestasi Semester)
 * Rumus: Total Mutu Semester / Total SKS Semester
 *
 * @param PDO $pdo Instance koneksi database
 * @param int $id_mahasiswa ID mahasiswa
 * @param int $id_tahun_akademik ID tahun akademik
 * @return array ['ips' => float, 'total_sks' => int, 'total_mutu' => float]
 */
function hitungIPS(PDO $pdo, int $id_mahasiswa, int $id_tahun_akademik): array
{
    $sql = "SELECT mk.sks, n.bobot
            FROM krs k
            JOIN detail_krs dk ON dk.id_krs = k.id_krs
            JOIN jadwal_kuliah jk ON jk.id_jadwal = dk.id_jadwal
            JOIN mata_kuliah mk ON mk.id_mata_kuliah = jk.id_mata_kuliah
            LEFT JOIN nilai n ON n.id_detail_krs = dk.id_detail_krs
            WHERE k.id_mahasiswa = ?
              AND k.id_tahun_akademik = ?
              AND dk.status = 'aktif'
              AND n.bobot IS NOT NULL";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_mahasiswa, $id_tahun_akademik]);
    $rows = $stmt->fetchAll();

    $total_sks = 0;
    $total_mutu = 0;

    foreach ($rows as $row) {
        $mutu = $row['bobot'] * $row['sks'];
        $total_sks += $row['sks'];
        $total_mutu += $mutu;
    }

    $ips = ($total_sks > 0) ? round($total_mutu / $total_sks, 2) : 0;

    return [
        'ips' => $ips,
        'total_sks' => $total_sks,
        'total_mutu' => round($total_mutu, 2)
    ];
}

/**
 * Menghitung IPK (Indeks Prestasi Kumulatif)
 * Rumus: Total Seluruh Mutu / Total Seluruh SKS
 *
 * @param PDO $pdo Instance koneksi database
 * @param int $id_mahasiswa ID mahasiswa
 * @return array ['ipk' => float, 'total_sks' => int, 'total_mutu' => float]
 */
function hitungIPK(PDO $pdo, int $id_mahasiswa): array
{
    $sql = "SELECT mk.sks, n.bobot
            FROM krs k
            JOIN detail_krs dk ON dk.id_krs = k.id_krs
            JOIN jadwal_kuliah jk ON jk.id_jadwal = dk.id_jadwal
            JOIN mata_kuliah mk ON mk.id_mata_kuliah = jk.id_mata_kuliah
            LEFT JOIN nilai n ON n.id_detail_krs = dk.id_detail_krs
            WHERE k.id_mahasiswa = ?
              AND dk.status = 'aktif'
              AND n.bobot IS NOT NULL";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_mahasiswa]);
    $rows = $stmt->fetchAll();

    $total_sks = 0;
    $total_mutu = 0;

    foreach ($rows as $row) {
        $mutu = $row['bobot'] * $row['sks'];
        $total_sks += $row['sks'];
        $total_mutu += $mutu;
    }

    $ipk = ($total_sks > 0) ? round($total_mutu / $total_sks, 2) : 0;

    return [
        'ipk' => $ipk,
        'total_sks' => $total_sks,
        'total_mutu' => round($total_mutu, 2)
    ];
}

// ============================================
// FUNGSI KEAMANAN
// ============================================

/**
 * Generate CSRF token dan simpan di session
 *
 * @return string Token CSRF
 */
function generateCSRFToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validasi CSRF token dari form submission
 *
 * @param string $token Token yang dikirim dari form
 * @return bool True jika valid
 */
function validateCSRFToken(string $token): bool
{
    if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        // Reset token setelah validasi berhasil
        unset($_SESSION['csrf_token']);
        return true;
    }
    return false;
}

/**
 * Sanitasi input untuk mencegah XSS
 *
 * @param string $input Input yang akan disanitasi
 * @return string Input yang sudah disanitasi
 */
function sanitize(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitasi output untuk ditampilkan di HTML
 *
 * @param mixed $value Nilai yang akan di-escape
 * @return string Nilai yang sudah di-escape
 */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// ============================================
// FUNGSI UTILITAS
// ============================================

/**
 * Redirect ke URL tertentu
 *
 * @param string $url URL tujuan
 * @return void
 */
function redirect(string $url): void
{
    header("Location: $url");
    exit;
}

/**
 * Set flash message ke session
 *
 * @param string $type Tipe pesan (success, danger, warning, info)
 * @param string $message Isi pesan
 * @return void
 */
function setFlashMessage(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Ambil dan hapus flash message dari session
 *
 * @return array|null Array berisi type dan message, atau null jika tidak ada
 */
function getFlashMessage(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Format tanggal dari format database ke format Indonesia
 *
 * @param string $date Tanggal format Y-m-d
 * @return string Tanggal format d F Y (contoh: 15 Maret 2024)
 */
function formatTanggal(string $date): string
{
    $bulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    $timestamp = strtotime($date);
    $hari = date('d', $timestamp);
    $bln = (int) date('m', $timestamp);
    $tahun = date('Y', $timestamp);

    return "$hari {$bulan[$bln]} $tahun";
}

/**
 * Generate hidden input untuk CSRF token
 *
 * @return string HTML hidden input
 */
function csrfField(): string
{
    $token = generateCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}
