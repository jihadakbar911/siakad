<?php
/**
 * Admin - Daftar Mata Kuliah
 */
require_once __DIR__ . '/../../config/auth.php';
requireRole('admin');

$pdo = getConnection();
$page_title = 'Data Mata Kuliah';
$current_page = 'mata-kuliah';

$stmt = $pdo->query("SELECT * FROM mata_kuliah ORDER BY semester ASC, kode_mata_kuliah ASC");
$mk_list = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-book"></i> Data Mata Kuliah</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Mata Kuliah</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php $flash = getFlashMessage(); ?>
            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show"><?= e($flash['message']) ?><button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <a href="<?= BASE_URL ?>/admin/mata-kuliah/create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Mata Kuliah</a>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Nama Mata Kuliah</th>
                                <th>SKS</th>
                                <th>Semester</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($mk_list)): ?>
                                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data</td></tr>
                            <?php else: ?>
                                <?php foreach ($mk_list as $i => $mk): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><span class="badge badge-warning"><?= e($mk['kode_mata_kuliah']) ?></span></td>
                                        <td><?= e($mk['nama_mata_kuliah']) ?></td>
                                        <td><?= e($mk['sks']) ?></td>
                                        <td>Semester <?= e($mk['semester']) ?></td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/admin/mata-kuliah/edit.php?id=<?= $mk['id_mata_kuliah'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                            <form action="<?= BASE_URL ?>/admin/mata-kuliah/delete.php" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus mata kuliah <?= e($mk['nama_mata_kuliah']) ?>?')">
                                                <input type="hidden" name="id" value="<?= $mk['id_mata_kuliah'] ?>">
                                                <?= csrfField() ?>
                                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer"><small class="text-muted">Total: <?= count($mk_list) ?> mata kuliah</small></div>
            </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
