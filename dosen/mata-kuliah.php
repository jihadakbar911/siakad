<?php
/**
 * Dosen - Mata Kuliah yang Diampu
 * Menampilkan daftar mata kuliah yang diampu pada tahun akademik aktif
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('dosen');

$pdo = getConnection();
$page_title = 'Mata Kuliah Diampu';
$current_page = 'mata-kuliah';
$id_dosen = $_SESSION['id_dosen'];

// Tahun akademik aktif
$stmt = $pdo->query("SELECT * FROM tahun_akademik WHERE status = 'aktif' LIMIT 1");
$tahun_aktif = $stmt->fetch();

// Semua tahun akademik untuk filter
$ta_list = $pdo->query("SELECT * FROM tahun_akademik ORDER BY tahun_akademik DESC")->fetchAll();
$filter_ta = (int) ($_GET['tahun_akademik'] ?? ($tahun_aktif['id_tahun_akademik'] ?? 0));

// Jadwal mengajar
$jadwal_list = [];
if ($filter_ta > 0) {
    $stmt = $pdo->prepare("SELECT jk.*, mk.kode_mata_kuliah, mk.nama_mata_kuliah, mk.sks, mk.semester,
                                  ta.tahun_akademik, ta.semester as smt,
                                  (SELECT COUNT(*) FROM detail_krs dk 
                                   JOIN krs k ON k.id_krs = dk.id_krs 
                                   WHERE dk.id_jadwal = jk.id_jadwal AND dk.status = 'aktif') as jumlah_mahasiswa
                           FROM jadwal_kuliah jk
                           JOIN mata_kuliah mk ON mk.id_mata_kuliah = jk.id_mata_kuliah
                           JOIN tahun_akademik ta ON ta.id_tahun_akademik = jk.id_tahun_akademik
                           WHERE jk.id_dosen = ? AND jk.id_tahun_akademik = ?
                           ORDER BY mk.kode_mata_kuliah, jk.kelas");
    $stmt->execute([$id_dosen, $filter_ta]);
    $jadwal_list = $stmt->fetchAll();
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-book"></i> Mata Kuliah Diampu</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dosen/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Mata Kuliah</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <!-- Filter Periode -->
            <div class="card">
                <div class="card-header">
                    <form action="" method="GET" class="form-inline">
                        <label class="mr-2">Periode:</label>
                        <select name="tahun_akademik" class="form-control mr-2" onchange="this.form.submit()">
                            <?php foreach ($ta_list as $ta): ?>
                                <option value="<?= $ta['id_tahun_akademik'] ?>" <?= $filter_ta == $ta['id_tahun_akademik'] ? 'selected' : '' ?>>
                                    <?= e($ta['tahun_akademik']) ?> - <?= e($ta['semester']) ?>
                                    <?= $ta['status'] === 'aktif' ? '(Aktif)' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            </div>

            <!-- Daftar Mata Kuliah -->
            <?php if (empty($jadwal_list)): ?>
                <div class="alert alert-info"><i class="fas fa-info-circle"></i> Tidak ada mata kuliah yang diampu pada periode ini.</div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($jadwal_list as $j): ?>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-primary">
                                    <h3 class="card-title">
                                        <span class="badge badge-light mr-1"><?= e($j['kode_mata_kuliah']) ?></span>
                                        <?= e($j['nama_mata_kuliah']) ?>
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr><td style="width:120px"><strong>SKS</strong></td><td><?= e($j['sks']) ?></td></tr>
                                        <tr><td><strong>Kelas</strong></td><td><?= e($j['kelas']) ?></td></tr>
                                        <tr><td><strong>Hari</strong></td><td><?= e($j['hari']) ?></td></tr>
                                        <tr><td><strong>Jam</strong></td><td><?= e(substr($j['jam_mulai'], 0, 5)) ?> - <?= e(substr($j['jam_selesai'], 0, 5)) ?></td></tr>
                                        <tr><td><strong>Ruangan</strong></td><td><?= e($j['ruangan']) ?></td></tr>
                                        <tr><td><strong>Mahasiswa</strong></td><td><span class="badge badge-info"><?= $j['jumlah_mahasiswa'] ?></span> / <?= $j['kuota'] ?></td></tr>
                                    </table>
                                </div>
                                <div class="card-footer">
                                    <a href="<?= BASE_URL ?>/dosen/mahasiswa.php?jadwal=<?= $j['id_jadwal'] ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-users"></i> Lihat Mahasiswa
                                    </a>
                                    <a href="<?= BASE_URL ?>/dosen/nilai.php?jadwal=<?= $j['id_jadwal'] ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Input Nilai
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
