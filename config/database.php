<?php
/**
 * Konfigurasi Koneksi Database
 * 
 * File ini menyediakan koneksi ke database MySQL menggunakan PDO.
 * Disertakan di setiap file yang membutuhkan akses database.
 */

// Konfigurasi database
define('DB_HOST', 'localhost');
define('DB_NAME', 'siakad');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Base URL aplikasi
define('BASE_URL', 'http://localhost/siakad');

/**
 * Membuat koneksi PDO ke database
 * 
 * @return PDO Instance koneksi database
 * @throws PDOException Jika koneksi gagal
 */
function getConnection(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Tampilkan error hanya di mode development
            die("Koneksi database gagal: " . $e->getMessage());
        }
    }

    return $pdo;
}
