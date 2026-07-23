<?php
/**
 * Admin - Daftar Dosen
 */
require_once __DIR__ . '/../../config/auth.php';
requireRole('admin');

$pdo = getConnection();
$page_title = 'Data Dosen';
$current_page = 'dosen';

$stmt = $pdo->query("SELECT d.*, u.username, u.status FROM dosen d JOIN users u ON u.id_user = d.id_user ORDER BY d.nidn ASC");
$dosen_list = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-chalkboard-teacher"></i> Data Dosen</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Dosen</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <?php $flash = getFlashMessage(); ?>
            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show">
                    <?= e($flash['message']) ?>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <a href="<?= BASE_URL ?>/admin/dosen/create.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Dosen
                    </a>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIDN</th>
                                <th>Nama Dosen</th>
                                <th>Email</th>
                                <th>No. Telepon</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($dosen_list)): ?>
                                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data dosen</td></tr>
                            <?php else: ?>
                                <?php foreach ($dosen_list as $i => $dsn): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><span class="badge badge-success"><?= e($dsn['nidn']) ?></span></td>
                                        <td><?= e($dsn['nama_dosen']) ?></td>
                                        <td><?= e($dsn['email'] ?? '-') ?></td>
                                        <td><?= e($dsn['no_telepon'] ?? '-') ?></td>
                                        <td>
                                            <span class="badge badge-<?= $dsn['status'] === 'aktif' ? 'success' : 'danger' ?>">
                                                <?= e(ucfirst($dsn['status'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/admin/dosen/edit.php?id=<?= $dsn['id_dosen'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                            <form action="<?= BASE_URL ?>/admin/dosen/delete.php" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus dosen <?= e($dsn['nama_dosen']) ?>?')">
                                                <input type="hidden" name="id" value="<?= $dsn['id_dosen'] ?>">
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
                <div class="card-footer">
                    <small class="text-muted">Total: <?= count($dosen_list) ?> dosen</small>
                </div>
            </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
