<?php
/**
 * Admin - Tambah Mata Kuliah
 */
require_once __DIR__ . '/../../config/auth.php';
requireRole('admin');

$pdo = getConnection();
$page_title = 'Tambah Mata Kuliah';
$current_page = 'mata-kuliah';
$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) { $errors[] = 'Token keamanan tidak valid.'; }

    $kode = trim($_POST['kode_mata_kuliah'] ?? '');
    $nama = trim($_POST['nama_mata_kuliah'] ?? '');
    $sks = (int) ($_POST['sks'] ?? 0);
    $semester = (int) ($_POST['semester'] ?? 0);
    $old = $_POST;

    if (empty($kode)) $errors[] = 'Kode mata kuliah wajib diisi.';
    if (empty($nama)) $errors[] = 'Nama mata kuliah wajib diisi.';
    if ($sks < 1 || $sks > 6) $errors[] = 'SKS harus antara 1-6.';
    if ($semester < 1 || $semester > 8) $errors[] = 'Semester harus antara 1-8.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM mata_kuliah WHERE kode_mata_kuliah = ?");
        $stmt->execute([$kode]);
        if ($stmt->fetchColumn() > 0) $errors[] = 'Kode mata kuliah sudah terdaftar.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO mata_kuliah (kode_mata_kuliah, nama_mata_kuliah, sks, semester) VALUES (?, ?, ?, ?)");
            $stmt->execute([$kode, $nama, $sks, $semester]);
            setFlashMessage('success', 'Mata kuliah "' . $nama . '" berhasil ditambahkan.');
            redirect(BASE_URL . '/admin/mata-kuliah/');
        } catch (Exception $ex) {
            $errors[] = 'Gagal menyimpan data: ' . $ex->getMessage();
        }
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
                <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-plus-circle"></i> Tambah Mata Kuliah</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/mata-kuliah/">Mata Kuliah</a></li>
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
                <div class="card-header"><h3 class="card-title">Form Mata Kuliah</h3></div>
                <div class="card-body">
                    <form action="" method="POST">
                        <?= csrfField() ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="kode_mata_kuliah">Kode Mata Kuliah <span class="text-danger">*</span></label>
                                    <input type="text" name="kode_mata_kuliah" id="kode_mata_kuliah" class="form-control" value="<?= e($old['kode_mata_kuliah'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nama_mata_kuliah">Nama Mata Kuliah <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_mata_kuliah" id="nama_mata_kuliah" class="form-control" value="<?= e($old['nama_mata_kuliah'] ?? '') ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sks">SKS <span class="text-danger">*</span></label>
                                    <input type="number" name="sks" id="sks" class="form-control" min="1" max="6" value="<?= e($old['sks'] ?? '3') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="semester">Semester <span class="text-danger">*</span></label>
                                    <select name="semester" id="semester" class="form-control" required>
                                        <?php for ($s = 1; $s <= 8; $s++): ?>
                                            <option value="<?= $s ?>" <?= ($old['semester'] ?? '') == $s ? 'selected' : '' ?>>Semester <?= $s ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                        <a href="<?= BASE_URL ?>/admin/mata-kuliah/" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                    </form>
                </div>
            </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
