<?php
/**
 * Admin - Hapus Jadwal Kuliah
 */
require_once __DIR__ . '/../../config/auth.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect(BASE_URL . '/admin/jadwal/'); }
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) { setFlashMessage('danger', 'Token tidak valid.'); redirect(BASE_URL . '/admin/jadwal/'); }

$id = (int) ($_POST['id'] ?? 0);
$pdo = getConnection();

try {
    $stmt = $pdo->prepare("DELETE FROM jadwal_kuliah WHERE id_jadwal = ?");
    $stmt->execute([$id]);
    setFlashMessage('success', 'Jadwal kuliah berhasil dihapus.');
} catch (Exception $ex) {
    setFlashMessage('danger', 'Gagal menghapus jadwal: ' . $ex->getMessage());
}
redirect(BASE_URL . '/admin/jadwal/');
