<?php
/**
 * Sistem Autentikasi dan Pembatasan Akses
 * 
 * File ini menangani:
 * - Inisialisasi session
 * - Proses login dan logout
 * - Pengecekan status login
 * - Pembatasan akses berdasarkan role
 * - Mendapatkan data user yang sedang login
 */

// Mulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include konfigurasi database dan fungsi umum
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';

// ============================================
// FUNGSI AUTENTIKASI
// ============================================

/**
 * Proses login pengguna
 *
 * @param string $username Username pengguna
 * @param string $password Password pengguna (plain text)
 * @return array ['success' => bool, 'message' => string, 'role' => string|null]
 */
function login(string $username, string $password): array
{
    $pdo = getConnection();

    // Cari user berdasarkan username
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Validasi: user tidak ditemukan
    if (!$user) {
        return ['success' => false, 'message' => 'Username atau password salah.', 'role' => null];
    }

    // Validasi: akun nonaktif
    if ($user['status'] === 'nonaktif') {
        return ['success' => false, 'message' => 'Akun Anda telah dinonaktifkan. Hubungi Admin.', 'role' => null];
    }

    // Validasi: password salah
    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Username atau password salah.', 'role' => null];
    }

    // Regenerate session ID untuk keamanan
    session_regenerate_id(true);

    // Simpan data user ke session
    $_SESSION['user_id'] = $user['id_user'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['login_time'] = time();

    // Ambil data profil berdasarkan role
    if ($user['role'] === 'mahasiswa') {
        $stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE id_user = ?");
        $stmt->execute([$user['id_user']]);
        $profil = $stmt->fetch();
        if ($profil) {
            $_SESSION['id_mahasiswa'] = $profil['id_mahasiswa'];
            $_SESSION['nama'] = $profil['nama_mahasiswa'];
            $_SESSION['nim'] = $profil['nim'];
        }
    } elseif ($user['role'] === 'dosen') {
        $stmt = $pdo->prepare("SELECT * FROM dosen WHERE id_user = ?");
        $stmt->execute([$user['id_user']]);
        $profil = $stmt->fetch();
        if ($profil) {
            $_SESSION['id_dosen'] = $profil['id_dosen'];
            $_SESSION['nama'] = $profil['nama_dosen'];
            $_SESSION['nidn'] = $profil['nidn'];
        }
    } else {
        $_SESSION['nama'] = 'Administrator';
    }

    return ['success' => true, 'message' => 'Login berhasil.', 'role' => $user['role']];
}

/**
 * Proses logout pengguna
 *
 * @return void
 */
function logout(): void
{
    // Hapus semua data session
    $_SESSION = [];

    // Hapus cookie session
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Hancurkan session
    session_destroy();
}

// ============================================
// FUNGSI PENGECEKAN STATUS
// ============================================

/**
 * Cek apakah pengguna sudah login
 *
 * @return bool True jika sudah login
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

/**
 * Ambil role pengguna yang sedang login
 *
 * @return string|null Role pengguna atau null jika belum login
 */
function getCurrentRole(): ?string
{
    return $_SESSION['role'] ?? null;
}

/**
 * Ambil ID user yang sedang login
 *
 * @return int|null ID user atau null jika belum login
 */
function getCurrentUserId(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

/**
 * Ambil nama pengguna yang sedang login
 *
 * @return string|null Nama pengguna atau null jika belum login
 */
function getCurrentUserName(): ?string
{
    return $_SESSION['nama'] ?? null;
}

// ============================================
// FUNGSI PEMBATASAN AKSES (ROLE-BASED ACCESS)
// ============================================

/**
 * Paksa pengguna harus sudah login
 * Jika belum login, redirect ke halaman login
 *
 * @return void
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        setFlashMessage('danger', 'Silakan login terlebih dahulu.');
        redirect(BASE_URL . '/login.php');
    }
}

/**
 * Paksa pengguna harus memiliki role tertentu
 * Jika role tidak sesuai, redirect ke dashboard sesuai role
 *
 * @param string $role Role yang diizinkan (admin, dosen, mahasiswa)
 * @return void
 */
function requireRole(string $role): void
{
    requireLogin();

    if (getCurrentRole() !== $role) {
        setFlashMessage('danger', 'Anda tidak memiliki akses ke halaman tersebut.');
        redirectToDashboard();
    }
}

/**
 * Redirect pengguna ke dashboard sesuai role
 *
 * @return void
 */
function redirectToDashboard(): void
{
    $role = getCurrentRole();

    switch ($role) {
        case 'admin':
            redirect(BASE_URL . '/admin/dashboard.php');
            break;
        case 'dosen':
            redirect(BASE_URL . '/dosen/dashboard.php');
            break;
        case 'mahasiswa':
            redirect(BASE_URL . '/mahasiswa/dashboard.php');
            break;
        default:
            redirect(BASE_URL . '/login.php');
    }
}
