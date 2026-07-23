<?php
/**
 * Admin - Hapus Mata Kuliah
 */
require_once __DIR__ . '/../../config/auth.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect(BASE_URL . '/admin/mata-kuliah/'); }
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) { setFlashMessage('danger', 'Token tidak valid.'); redirect(BASE_URL . '/admin/mata-kuliah/'); }

$id = (int) ($_POST['id'] ?? 0);
$pdo = getConnection();

try {
    $stmt = $pdo->prepare("SELECT nama_mata_kuliah FROM mata_kuliah WHERE id_mata_kuliah = ?");
    $stmt->execute([$id]);
    $mk = $stmt->fetch();
    if (!$mk) { setFlashMessage('danger', 'Data tidak ditemukan.'); redirect(BASE_URL . '/admin/mata-kuliah/'); }

    $stmt = $pdo->prepare("DELETE FROM mata_kuliah WHERE id_mata_kuliah = ?");
    $stmt->execute([$id]);
    setFlashMessage('success', 'Mata kuliah "' . $mk['nama_mata_kuliah'] . '" berhasil dihapus.');
} catch (Exception $ex) {
    setFlashMessage('danger', 'Gagal menghapus: ' . $ex->getMessage());
}
redirect(BASE_URL . '/admin/mata-kuliah/');
