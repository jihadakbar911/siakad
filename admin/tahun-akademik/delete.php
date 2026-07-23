<?php
/**
 * Admin - Hapus Tahun Akademik
 */
require_once __DIR__ . '/../../config/auth.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect(BASE_URL . '/admin/tahun-akademik/'); }
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) { setFlashMessage('danger', 'Token tidak valid.'); redirect(BASE_URL . '/admin/tahun-akademik/'); }

$id = (int) ($_POST['id'] ?? 0);
$pdo = getConnection();

try {
    $stmt = $pdo->prepare("DELETE FROM tahun_akademik WHERE id_tahun_akademik = ?");
    $stmt->execute([$id]);
    setFlashMessage('success', 'Tahun akademik berhasil dihapus.');
} catch (Exception $ex) {
    setFlashMessage('danger', 'Gagal menghapus: ' . $ex->getMessage());
}
redirect(BASE_URL . '/admin/tahun-akademik/');
