<?php
/**
 * Admin - Lihat Data Nilai Seluruh Mahasiswa
 */
require_once __DIR__ . '/../../config/auth.php';
requireRole('admin');

$pdo = getConnection();
$page_title = 'Data Nilai Mahasiswa';
$current_page = 'nilai';

$filter_ta = (int) ($_GET['tahun_akademik'] ?? 0);
$ta_list = $pdo->query("SELECT * FROM tahun_akademik ORDER BY tahun_akademik DESC")->fetchAll();

$sql = "SELECT n.*, dk.id_krs, m.nim, m.nama_mahasiswa, mk.kode_mata_kuliah, mk.nama_mata_kuliah, mk.sks,
               ta.tahun_akademik, ta.semester as smt
        FROM nilai n
        JOIN detail_krs dk ON dk.id_detail_krs = n.id_detail_krs
        JOIN krs k ON k.id_krs = dk.id_krs
        JOIN jadwal_kuliah jk ON jk.id_jadwal = dk.id_jadwal
        JOIN mata_kuliah mk ON mk.id_mata_kuliah = jk.id_mata_kuliah
        JOIN mahasiswa m ON m.id_mahasiswa = k.id_mahasiswa
        JOIN tahun_akademik ta ON ta.id_tahun_akademik = k.id_tahun_akademik";
$params = [];
if ($filter_ta > 0) { $sql .= " WHERE k.id_tahun_akademik = ?"; $params[] = $filter_ta; }
$sql .= " ORDER BY m.nim, mk.kode_mata_kuliah";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$nilai_list = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-star"></i> Data Nilai</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Nilai</li>
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
                    <table class="table table-hover table-striped table-sm">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIM</th>
                                <th>Nama</th>
                                <th>Mata Kuliah</th>
                                <th>SKS</th>
                                <th>Tugas</th>
                                <th>UTS</th>
                                <th>UAS</th>
                                <th>Akhir</th>
                                <th>Huruf</th>
                                <th>Bobot</th>
                                <th>Periode</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($nilai_list)): ?>
                                <tr><td colspan="12" class="text-center text-muted py-4">Belum ada data nilai</td></tr>
                            <?php else: ?>
                                <?php foreach ($nilai_list as $i => $n): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><?= e($n['nim']) ?></td>
                                        <td><?= e($n['nama_mahasiswa']) ?></td>
                                        <td><?= e($n['nama_mata_kuliah']) ?></td>
                                        <td><?= e($n['sks']) ?></td>
                                        <td><?= e($n['nilai_tugas'] ?? '-') ?></td>
                                        <td><?= e($n['nilai_uts'] ?? '-') ?></td>
                                        <td><?= e($n['nilai_uas'] ?? '-') ?></td>
                                        <td><strong><?= e($n['nilai_akhir'] ?? '-') ?></strong></td>
                                        <td><span class="badge badge-<?= ($n['bobot'] ?? 0) >= 3 ? 'success' : (($n['bobot'] ?? 0) >= 2 ? 'warning' : 'danger') ?>"><?= e($n['nilai_huruf'] ?? '-') ?></span></td>
                                        <td><?= e($n['bobot'] ?? '-') ?></td>
                                        <td><small><?= e($n['tahun_akademik']) ?> <?= e($n['smt']) ?></small></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer"><small class="text-muted">Total: <?= count($nilai_list) ?> record nilai</small></div>
            </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
