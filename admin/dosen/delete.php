<?php
/**
 * Admin - Hapus Dosen
 */
require_once __DIR__ . '/../../config/auth.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect(BASE_URL . '/admin/dosen/'); }
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('danger', 'Token keamanan tidak valid.');
    redirect(BASE_URL . '/admin/dosen/');
}

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) { setFlashMessage('danger', 'ID dosen tidak valid.'); redirect(BASE_URL . '/admin/dosen/'); }

$pdo = getConnection();
$stmt = $pdo->prepare("SELECT d.nama_dosen, d.id_user FROM dosen d WHERE d.id_dosen = ?");
$stmt->execute([$id]);
$dsn = $stmt->fetch();

if (!$dsn) { setFlashMessage('danger', 'Data dosen tidak ditemukan.'); redirect(BASE_URL . '/admin/dosen/'); }

try {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id_user = ?");
    $stmt->execute([$dsn['id_user']]);
    setFlashMessage('success', 'Dosen "' . $dsn['nama_dosen'] . '" berhasil dihapus.');
} catch (Exception $ex) {
    setFlashMessage('danger', 'Gagal menghapus dosen: ' . $ex->getMessage());
}

redirect(BASE_URL . '/admin/dosen/');
