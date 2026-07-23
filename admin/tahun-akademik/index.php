<?php
/**
 * Admin - Daftar Tahun Akademik
 */
require_once __DIR__ . '/../../config/auth.php';
requireRole('admin');

$pdo = getConnection();
$page_title = 'Data Tahun Akademik';
$current_page = 'tahun-akademik';

$stmt = $pdo->query("SELECT * FROM tahun_akademik ORDER BY tahun_akademik DESC, FIELD(semester, 'Genap', 'Ganjil')");
$ta_list = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-calendar-alt"></i> Tahun Akademik</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Tahun Akademik</li>
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
                    <a href="<?= BASE_URL ?>/admin/tahun-akademik/create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Tahun Akademik</a>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tahun Akademik</th>
                                <th>Semester</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($ta_list)): ?>
                                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada data</td></tr>
                            <?php else: ?>
                                <?php foreach ($ta_list as $i => $ta): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><?= e($ta['tahun_akademik']) ?></td>
                                        <td><?= e($ta['semester']) ?></td>
                                        <td>
                                            <?php if ($ta['status'] === 'aktif'): ?>
                                                <span class="badge badge-success"><i class="fas fa-check-circle"></i> Aktif</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($ta['status'] !== 'aktif'): ?>
                                                <form action="<?= BASE_URL ?>/admin/tahun-akademik/edit.php" method="POST" class="d-inline">
                                                    <input type="hidden" name="id" value="<?= $ta['id_tahun_akademik'] ?>">
                                                    <input type="hidden" name="action" value="activate">
                                                    <?= csrfField() ?>
                                                    <button type="submit" class="btn btn-sm btn-success" title="Aktifkan" onclick="return confirm('Aktifkan periode ini? Periode lain akan dinonaktifkan.')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <a href="<?= BASE_URL ?>/admin/tahun-akademik/edit.php?id=<?= $ta['id_tahun_akademik'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                            <form action="<?= BASE_URL ?>/admin/tahun-akademik/delete.php" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                                                <input type="hidden" name="id" value="<?= $ta['id_tahun_akademik'] ?>">
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
            </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
