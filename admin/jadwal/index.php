<?php
/**
 * Admin - Daftar Jadwal Kuliah
 */
require_once __DIR__ . '/../../config/auth.php';
requireRole('admin');

$pdo = getConnection();
$page_title = 'Jadwal Kuliah';
$current_page = 'jadwal';

// Filter tahun akademik
$filter_ta = (int) ($_GET['tahun_akademik'] ?? 0);

$ta_list = $pdo->query("SELECT * FROM tahun_akademik ORDER BY tahun_akademik DESC")->fetchAll();

// Ambil jadwal
$sql = "SELECT jk.*, mk.kode_mata_kuliah, mk.nama_mata_kuliah, mk.sks, 
               d.nama_dosen, ta.tahun_akademik, ta.semester as smt
        FROM jadwal_kuliah jk
        JOIN mata_kuliah mk ON mk.id_mata_kuliah = jk.id_mata_kuliah
        JOIN dosen d ON d.id_dosen = jk.id_dosen
        JOIN tahun_akademik ta ON ta.id_tahun_akademik = jk.id_tahun_akademik";
$params = [];

if ($filter_ta > 0) {
    $sql .= " WHERE jk.id_tahun_akademik = ?";
    $params[] = $filter_ta;
}

$sql .= " ORDER BY ta.tahun_akademik DESC, jk.hari, jk.jam_mulai";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$jadwal_list = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-clock"></i> Jadwal Kuliah</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Jadwal Kuliah</li>
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
                    <div class="row">
                        <div class="col-md-6">
                            <a href="<?= BASE_URL ?>/admin/jadwal/create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Jadwal</a>
                        </div>
                        <div class="col-md-6">
                            <form action="" method="GET" class="form-inline float-right">
                                <select name="tahun_akademik" class="form-control mr-2" onchange="this.form.submit()">
                                    <option value="0">Semua Periode</option>
                                    <?php foreach ($ta_list as $ta): ?>
                                        <option value="<?= $ta['id_tahun_akademik'] ?>" <?= $filter_ta == $ta['id_tahun_akademik'] ? 'selected' : '' ?>>
                                            <?= e($ta['tahun_akademik']) ?> - <?= e($ta['semester']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Mata Kuliah</th>
                                <th>Dosen</th>
                                <th>Kelas</th>
                                <th>Hari</th>
                                <th>Jam</th>
                                <th>Ruangan</th>
                                <th>Kuota</th>
                                <th>Periode</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($jadwal_list)): ?>
                                <tr><td colspan="10" class="text-center text-muted py-4">Belum ada jadwal</td></tr>
                            <?php else: ?>
                                <?php foreach ($jadwal_list as $i => $j): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td>
                                            <strong><?= e($j['nama_mata_kuliah']) ?></strong><br>
                                            <small class="text-muted"><?= e($j['kode_mata_kuliah']) ?> (<?= e($j['sks']) ?> SKS)</small>
                                        </td>
                                        <td><?= e($j['nama_dosen']) ?></td>
                                        <td><?= e($j['kelas']) ?></td>
                                        <td><?= e($j['hari']) ?></td>
                                        <td><?= e(substr($j['jam_mulai'], 0, 5)) ?> - <?= e(substr($j['jam_selesai'], 0, 5)) ?></td>
                                        <td><?= e($j['ruangan']) ?></td>
                                        <td><?= e($j['kuota']) ?></td>
                                        <td><small><?= e($j['tahun_akademik']) ?> <?= e($j['smt']) ?></small></td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/admin/jadwal/edit.php?id=<?= $j['id_jadwal'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                            <form action="<?= BASE_URL ?>/admin/jadwal/delete.php" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus jadwal ini?')">
                                                <input type="hidden" name="id" value="<?= $j['id_jadwal'] ?>">
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
                <div class="card-footer"><small class="text-muted">Total: <?= count($jadwal_list) ?> jadwal</small></div>
            </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
