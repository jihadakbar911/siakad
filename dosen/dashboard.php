<?php
/**
 * Dashboard Dosen
 * Menampilkan ringkasan mata kuliah yang diampu dan statistik
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('dosen');

$pdo = getConnection();
$page_title = 'Dashboard Dosen';
$current_page = 'dashboard';
$id_dosen = $_SESSION['id_dosen'];

// Tahun akademik aktif
$stmt = $pdo->query("SELECT * FROM tahun_akademik WHERE status = 'aktif' LIMIT 1");
$tahun_aktif = $stmt->fetch();

// Mata kuliah yang diampu pada tahun akademik aktif
$total_mk = 0;
$total_mahasiswa = 0;
$jadwal_list = [];

if ($tahun_aktif) {
    $stmt = $pdo->prepare("SELECT jk.*, mk.kode_mata_kuliah, mk.nama_mata_kuliah, mk.sks,
                                  (SELECT COUNT(*) FROM detail_krs dk 
                                   JOIN krs k ON k.id_krs = dk.id_krs 
                                   WHERE dk.id_jadwal = jk.id_jadwal AND dk.status = 'aktif') as jumlah_mahasiswa
                           FROM jadwal_kuliah jk
                           JOIN mata_kuliah mk ON mk.id_mata_kuliah = jk.id_mata_kuliah
                           WHERE jk.id_dosen = ? AND jk.id_tahun_akademik = ?
                           ORDER BY jk.hari, jk.jam_mulai");
    $stmt->execute([$id_dosen, $tahun_aktif['id_tahun_akademik']]);
    $jadwal_list = $stmt->fetchAll();
    $total_mk = count($jadwal_list);

    foreach ($jadwal_list as $j) {
        $total_mahasiswa += $j['jumlah_mahasiswa'];
    }
}

// Jumlah nilai yang belum diinput
$belum_dinilai = 0;
if ($tahun_aktif) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM detail_krs dk
                           JOIN krs k ON k.id_krs = dk.id_krs
                           JOIN jadwal_kuliah jk ON jk.id_jadwal = dk.id_jadwal
                           LEFT JOIN nilai n ON n.id_detail_krs = dk.id_detail_krs
                           WHERE jk.id_dosen = ? AND jk.id_tahun_akademik = ?
                             AND dk.status = 'aktif' AND n.id_nilai IS NULL");
    $stmt->execute([$id_dosen, $tahun_aktif['id_tahun_akademik']]);
    $belum_dinilai = $stmt->fetchColumn();
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-tachometer-alt"></i> Dashboard Dosen</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            <!-- Flash Message -->
            <?php $flash = getFlashMessage(); ?>
            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show">
                    <?= e($flash['message']) ?>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            <?php endif; ?>

            <!-- Info Dosen -->
            <div class="callout callout-info">
                <h5><i class="fas fa-user"></i> Selamat Datang, <?= e(getCurrentUserName()) ?></h5>
                <p class="mb-0">
                    NIDN: <strong><?= e($_SESSION['nidn'] ?? '-') ?></strong>
                    <?php if ($tahun_aktif): ?>
                        | Periode Aktif: <strong><?= e($tahun_aktif['tahun_akademik']) ?> - <?= e($tahun_aktif['semester']) ?></strong>
                    <?php endif; ?>
                </p>
            </div>

            <!-- Kartu Statistik -->
            <div class="row">
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= $total_mk ?></h3>
                            <p>Mata Kuliah Diampu</p>
                        </div>
                        <div class="icon"><i class="fas fa-book"></i></div>
                        <a href="<?= BASE_URL ?>/dosen/mata-kuliah.php" class="small-box-footer">
                            Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= $total_mahasiswa ?></h3>
                            <p>Total Mahasiswa</p>
                        </div>
                        <div class="icon"><i class="fas fa-users"></i></div>
                        <a href="<?= BASE_URL ?>/dosen/mahasiswa.php" class="small-box-footer">
                            Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?= $belum_dinilai ?></h3>
                            <p>Belum Dinilai</p>
                        </div>
                        <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                        <a href="<?= BASE_URL ?>/dosen/nilai.php" class="small-box-footer">
                            Input Nilai <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Jadwal Mengajar -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-calendar-alt"></i> Jadwal Mengajar Semester Ini</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Mata Kuliah</th>
                                <th>Kelas</th>
                                <th>Hari</th>
                                <th>Jam</th>
                                <th>Ruangan</th>
                                <th>Mahasiswa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($jadwal_list)): ?>
                                <tr><td colspan="7" class="text-center text-muted">Tidak ada jadwal mengajar</td></tr>
                            <?php else: ?>
                                <?php foreach ($jadwal_list as $j): ?>
                                    <tr>
                                        <td><span class="badge badge-primary"><?= e($j['kode_mata_kuliah']) ?></span></td>
                                        <td><?= e($j['nama_mata_kuliah']) ?> <small class="text-muted">(<?= e($j['sks']) ?> SKS)</small></td>
                                        <td><?= e($j['kelas']) ?></td>
                                        <td><?= e($j['hari']) ?></td>
                                        <td><?= e(substr($j['jam_mulai'], 0, 5)) ?> - <?= e(substr($j['jam_selesai'], 0, 5)) ?></td>
                                        <td><?= e($j['ruangan']) ?></td>
                                        <td><span class="badge badge-info"><?= $j['jumlah_mahasiswa'] ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
