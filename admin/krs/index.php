<?php
/**
 * Admin - Lihat Data KRS Seluruh Mahasiswa
 */
require_once __DIR__ . '/../../config/auth.php';
requireRole('admin');

$pdo = getConnection();
$page_title = 'Data KRS Mahasiswa';
$current_page = 'krs';

$filter_ta = (int) ($_GET['tahun_akademik'] ?? 0);
$ta_list = $pdo->query("SELECT * FROM tahun_akademik ORDER BY tahun_akademik DESC")->fetchAll();

$sql = "SELECT k.*, m.nim, m.nama_mahasiswa, ta.tahun_akademik, ta.semester as smt,
               (SELECT COALESCE(SUM(mk2.sks), 0) FROM detail_krs dk2 
                JOIN jadwal_kuliah jk2 ON jk2.id_jadwal = dk2.id_jadwal 
                JOIN mata_kuliah mk2 ON mk2.id_mata_kuliah = jk2.id_mata_kuliah 
                WHERE dk2.id_krs = k.id_krs AND dk2.status = 'aktif') as total_sks
        FROM krs k
        JOIN mahasiswa m ON m.id_mahasiswa = k.id_mahasiswa
        JOIN tahun_akademik ta ON ta.id_tahun_akademik = k.id_tahun_akademik";
$params = [];
if ($filter_ta > 0) { $sql .= " WHERE k.id_tahun_akademik = ?"; $params[] = $filter_ta; }
$sql .= " ORDER BY k.id_krs DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$krs_list = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-clipboard-list"></i> Data KRS</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">KRS</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <form action="" method="GET" class="form-inline">
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
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th>Periode</th>
                                <th>Total SKS</th>
                                <th>Tanggal Pengisian</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($krs_list)): ?>
                                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data KRS</td></tr>
                            <?php else: ?>
                                <?php foreach ($krs_list as $i => $k): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><span class="badge badge-primary"><?= e($k['nim']) ?></span></td>
                                        <td><?= e($k['nama_mahasiswa']) ?></td>
                                        <td><?= e($k['tahun_akademik']) ?> <?= e($k['smt']) ?></td>
                                        <td><?= $k['total_sks'] ?> SKS</td>
                                        <td><?= e(formatTanggal($k['tanggal_pengisian'])) ?></td>
                                        <td>
                                            <?php $bc = match($k['status_krs']) { 'draft'=>'secondary', 'disetujui'=>'success', 'dikunci'=>'primary', default=>'secondary' }; ?>
                                            <span class="badge badge-<?= $bc ?>"><?= e(ucfirst($k['status_krs'])) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer"><small class="text-muted">Total: <?= count($krs_list) ?> KRS</small></div>
            </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
