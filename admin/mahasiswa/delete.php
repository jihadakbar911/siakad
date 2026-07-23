<?php
/**
 * Admin - Hapus Mahasiswa
 * Menghapus data mahasiswa beserta akun user (CASCADE)
 */
require_once __DIR__ . '/../../config/auth.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/admin/mahasiswa/');
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('danger', 'Token keamanan tidak valid.');
    redirect(BASE_URL . '/admin/mahasiswa/');
}

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    setFlashMessage('danger', 'ID mahasiswa tidak valid.');
    redirect(BASE_URL . '/admin/mahasiswa/');
}

$pdo = getConnection();

// Ambil data untuk pesan dan hapus user (CASCADE akan menghapus mahasiswa)
$stmt = $pdo->prepare("SELECT m.nama_mahasiswa, m.id_user FROM mahasiswa m WHERE m.id_mahasiswa = ?");
$stmt->execute([$id]);
$mhs = $stmt->fetch();

if (!$mhs) {
    setFlashMessage('danger', 'Data mahasiswa tidak ditemukan.');
    redirect(BASE_URL . '/admin/mahasiswa/');
}

try {
    // Hapus user (CASCADE menghapus mahasiswa, krs, detail_krs, nilai)
    $stmt = $pdo->prepare("DELETE FROM users WHERE id_user = ?");
    $stmt->execute([$mhs['id_user']]);

    setFlashMessage('success', 'Mahasiswa "' . $mhs['nama_mahasiswa'] . '" berhasil dihapus.');
} catch (Exception $ex) {
    setFlashMessage('danger', 'Gagal menghapus mahasiswa: ' . $ex->getMessage());
}

redirect(BASE_URL . '/admin/mahasiswa/');
