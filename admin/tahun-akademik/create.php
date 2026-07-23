<?php
/**
 * Admin - Tambah Tahun Akademik
 */
require_once __DIR__ . '/../../config/auth.php';
requireRole('admin');

$pdo = getConnection();
$page_title = 'Tambah Tahun Akademik';
$current_page = 'tahun-akademik';
$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) { $errors[] = 'Token keamanan tidak valid.'; }

    $tahun = trim($_POST['tahun_akademik'] ?? '');
    $semester = $_POST['semester'] ?? '';
    $old = $_POST;

    if (empty($tahun)) $errors[] = 'Tahun akademik wajib diisi.';
    if (!in_array($semester, ['Ganjil', 'Genap'])) $errors[] = 'Semester tidak valid.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tahun_akademik WHERE tahun_akademik = ? AND semester = ?");
        $stmt->execute([$tahun, $semester]);
        if ($stmt->fetchColumn() > 0) $errors[] = 'Tahun akademik dan semester ini sudah ada.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO tahun_akademik (tahun_akademik, semester, status) VALUES (?, ?, 'nonaktif')");
        $stmt->execute([$tahun, $semester]);
        setFlashMessage('success', 'Tahun akademik berhasil ditambahkan.');
        redirect(BASE_URL . '/admin/tahun-akademik/');
    }
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-plus-circle"></i> Tambah Tahun Akademik</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/tahun-akademik/">Tahun Akademik</a></li>
                        <li class="breadcrumb-item active">Tambah</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Form Tahun Akademik</h3></div>
                <div class="card-body">
                    <form action="" method="POST">
                        <?= csrfField() ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tahun_akademik">Tahun Akademik <span class="text-danger">*</span></label>
                                    <input type="text" name="tahun_akademik" id="tahun_akademik" class="form-control" placeholder="Contoh: 2024/2025" value="<?= e($old['tahun_akademik'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="semester">Semester <span class="text-danger">*</span></label>
                                    <select name="semester" id="semester" class="form-control" required>
                                        <option value="">-- Pilih --</option>
                                        <option value="Ganjil" <?= ($old['semester'] ?? '') === 'Ganjil' ? 'selected' : '' ?>>Ganjil</option>
                                        <option value="Genap" <?= ($old['semester'] ?? '') === 'Genap' ? 'selected' : '' ?>>Genap</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                        <a href="<?= BASE_URL ?>/admin/tahun-akademik/" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                    </form>
                </div>
            </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
