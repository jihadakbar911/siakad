<?php
/**
 * Admin - Daftar Mahasiswa
 * Menampilkan tabel data mahasiswa dengan fitur pencarian
 */
require_once __DIR__ . '/../../config/auth.php';
requireRole('admin');

$pdo = getConnection();
$page_title = 'Data Mahasiswa';
$current_page = 'mahasiswa';

// Pencarian
$search = trim($_GET['search'] ?? '');
$sql = "SELECT m.*, u.username, u.status 
        FROM mahasiswa m 
        JOIN users u ON u.id_user = m.id_user";
$params = [];

if (!empty($search)) {
    $sql .= " WHERE m.nim LIKE ? OR m.nama_mahasiswa LIKE ? OR m.program_studi LIKE ?";
    $search_param = "%$search%";
    $params = [$search_param, $search_param, $search_param];
}

$sql .= " ORDER BY m.nim ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$mahasiswa_list = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-user-graduate"></i> Data Mahasiswa</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Mahasiswa</li>
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
                    <div class="row">
                        <div class="col-md-6">
                            <a href="<?= BASE_URL ?>/admin/mahasiswa/create.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Tambah Mahasiswa
                            </a>
                        </div>
                        <div class="col-md-6">
                            <form action="" method="GET" class="form-inline float-right">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Cari NIM / Nama..." value="<?= e($search) ?>">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-outline-primary"><i class="fas fa-search"></i></button>
                                        <?php if (!empty($search)): ?>
                                            <a href="<?= BASE_URL ?>/admin/mahasiswa/" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIM</th>
                                <th>Nama</th>
                                <th>Jenis Kelamin</th>
                                <th>Program Studi</th>
                                <th>Angkatan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($mahasiswa_list)): ?>
                                <tr><td colspan="8" class="text-center text-muted py-4">
                                    <?= !empty($search) ? 'Tidak ditemukan data untuk pencarian "' . e($search) . '"' : 'Belum ada data mahasiswa' ?>
                                </td></tr>
                            <?php else: ?>
                                <?php foreach ($mahasiswa_list as $i => $mhs): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><span class="badge badge-primary"><?= e($mhs['nim']) ?></span></td>
                                        <td><?= e($mhs['nama_mahasiswa']) ?></td>
                                        <td><?= $mhs['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                                        <td><?= e($mhs['program_studi']) ?></td>
                                        <td><?= e($mhs['angkatan']) ?></td>
                                        <td>
                                            <span class="badge badge-<?= $mhs['status'] === 'aktif' ? 'success' : 'danger' ?>">
                                                <?= e(ucfirst($mhs['status'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/admin/mahasiswa/edit.php?id=<?= $mhs['id_mahasiswa'] ?>" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="<?= BASE_URL ?>/admin/mahasiswa/delete.php" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus mahasiswa <?= e($mhs['nama_mahasiswa']) ?>?')">
                                                <input type="hidden" name="id" value="<?= $mhs['id_mahasiswa'] ?>">
                                                <?= csrfField() ?>
                                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="card-footer">
                    <small class="text-muted">Total: <?= count($mahasiswa_list) ?> mahasiswa</small>
                </div>
            </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
