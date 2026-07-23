<?php
/**
 * Admin - Edit / Aktifkan Tahun Akademik
 */
require_once __DIR__ . '/../../config/auth.php';
requireRole('admin');

$pdo = getConnection();
$page_title = 'Edit Tahun Akademik';
$current_page = 'tahun-akademik';

// Handle POST activation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'activate') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('danger', 'Token tidak valid.');
        redirect(BASE_URL . '/admin/tahun-akademik/');
    }
    $id = (int) ($_POST['id'] ?? 0);
    // Nonaktifkan semua, lalu aktifkan yang dipilih
    $pdo->exec("UPDATE tahun_akademik SET status = 'nonaktif'");
    $stmt = $pdo->prepare("UPDATE tahun_akademik SET status = 'aktif' WHERE id_tahun_akademik = ?");
    $stmt->execute([$id]);
    setFlashMessage('success', 'Tahun akademik berhasil diaktifkan.');
    redirect(BASE_URL . '/admin/tahun-akademik/');
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) { setFlashMessage('danger', 'ID tidak valid.'); redirect(BASE_URL . '/admin/tahun-akademik/'); }

$stmt = $pdo->prepare("SELECT * FROM tahun_akademik WHERE id_tahun_akademik = ?");
$stmt->execute([$id]);
$ta = $stmt->fetch();
if (!$ta) { setFlashMessage('danger', 'Data tidak ditemukan.'); redirect(BASE_URL . '/admin/tahun-akademik/'); }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) { $errors[] = 'Token tidak valid.'; }

    $tahun = trim($_POST['tahun_akademik'] ?? '');
    $semester = $_POST['semester'] ?? '';

    if (empty($tahun)) $errors[] = 'Tahun akademik wajib diisi.';
    if (!in_array($semester, ['Ganjil', 'Genap'])) $errors[] = 'Semester tidak valid.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tahun_akademik WHERE tahun_akademik = ? AND semester = ? AND id_tahun_akademik != ?");
        $stmt->execute([$tahun, $semester, $id]);
        if ($stmt->fetchColumn() > 0) $errors[] = 'Kombinasi tahun dan semester sudah ada.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE tahun_akademik SET tahun_akademik = ?, semester = ? WHERE id_tahun_akademik = ?");
        $stmt->execute([$tahun, $semester, $id]);
        setFlashMessage('success', 'Tahun akademik berhasil diperbarui.');
        redirect(BASE_URL . '/admin/tahun-akademik/');
    }
    $ta['tahun_akademik'] = $tahun; $ta['semester'] = $semester;
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-edit"></i> Edit Tahun Akademik</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/tahun-akademik/">Tahun Akademik</a></li>
                        <li class="breadcrumb-item active">Edit</li>
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
                <div class="card-header"><h3 class="card-title">Edit Tahun Akademik</h3></div>
                <div class="card-body">
                    <form action="" method="POST">
                        <?= csrfField() ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tahun_akademik">Tahun Akademik <span class="text-danger">*</span></label>
                                    <input type="text" name="tahun_akademik" id="tahun_akademik" class="form-control" value="<?= e($ta['tahun_akademik']) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="semester">Semester <span class="text-danger">*</span></label>
                                    <select name="semester" id="semester" class="form-control" required>
                                        <option value="Ganjil" <?= $ta['semester'] === 'Ganjil' ? 'selected' : '' ?>>Ganjil</option>
                                        <option value="Genap" <?= $ta['semester'] === 'Genap' ? 'selected' : '' ?>>Genap</option>
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
