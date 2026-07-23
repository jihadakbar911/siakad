<?php
/**
 * Dashboard Mahasiswa
 * Menampilkan ringkasan informasi akademik mahasiswa
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('mahasiswa');

$pdo = getConnection();
$page_title = 'Dashboard Mahasiswa';
$current_page = 'dashboard';
$id_mahasiswa = $_SESSION['id_mahasiswa'];

// Data mahasiswa
$stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE id_mahasiswa = ?");
$stmt->execute([$id_mahasiswa]);
$mahasiswa = $stmt->fetch();

// Tahun akademik aktif
$stmt = $pdo->query("SELECT * FROM tahun_akademik WHERE status = 'aktif' LIMIT 1");
$tahun_aktif = $stmt->fetch();

// Hitung IPK
$ipk_data = hitungIPK($pdo, $id_mahasiswa);

// Total SKS yang sedang diambil (semester aktif)
$sks_semester = 0;
if ($tahun_aktif) {
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(mk.sks), 0)
                           FROM krs k
                           JOIN detail_krs dk ON dk.id_krs = k.id_krs
                           JOIN jadwal_kuliah jk ON jk.id_jadwal = dk.id_jadwal
                           JOIN mata_kuliah mk ON mk.id_mata_kuliah = jk.id_mata_kuliah
                           WHERE k.id_mahasiswa = ? AND k.id_tahun_akademik = ? AND dk.status = 'aktif'");
    $stmt->execute([$id_mahasiswa, $tahun_aktif['id_tahun_akademik']]);
    $sks_semester = $stmt->fetchColumn();
}

// KRS semester aktif
$krs_aktif = null;
if ($tahun_aktif) {
    $stmt = $pdo->prepare("SELECT * FROM krs WHERE id_mahasiswa = ? AND id_tahun_akademik = ?");
    $stmt->execute([$id_mahasiswa, $tahun_aktif['id_tahun_akademik']]);
    $krs_aktif = $stmt->fetch();
}

// Jadwal hari ini
$hari_ini = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 
             'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 
             'Saturday' => 'Sabtu'];
$nama_hari = $hari_ini[date('l')] ?? '';

$jadwal_hari_ini = [];
if ($tahun_aktif && $krs_aktif) {
    $stmt = $pdo->prepare("SELECT jk.*, mk.kode_mata_kuliah, mk.nama_mata_kuliah, mk.sks,
                                  d.nama_dosen
                           FROM detail_krs dk
                           JOIN jadwal_kuliah jk ON jk.id_jadwal = dk.id_jadwal
                           JOIN mata_kuliah mk ON mk.id_mata_kuliah = jk.id_mata_kuliah
                           JOIN dosen d ON d.id_dosen = jk.id_dosen
                           WHERE dk.id_krs = ? AND dk.status = 'aktif' AND jk.hari = ?
                           ORDER BY jk.jam_mulai");
    $stmt->execute([$krs_aktif['id_krs'], $nama_hari]);
    $jadwal_hari_ini = $stmt->fetchAll();
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
                    <h1 class="m-0"><i class="fas fa-tachometer-alt"></i> Dashboard Mahasiswa</h1>
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

            <!-- Info Mahasiswa -->
            <div class="callout callout-info">
                <h5><i class="fas fa-user-graduate"></i> Selamat Datang, <?= e($mahasiswa['nama_mahasiswa']) ?></h5>
                <p class="mb-0">
                    NIM: <strong><?= e($mahasiswa['nim']) ?></strong> | 
                    Program Studi: <strong><?= e($mahasiswa['program_studi']) ?></strong> | 
                    Angkatan: <strong><?= e($mahasiswa['angkatan']) ?></strong>
                    <?php if ($tahun_aktif): ?>
                        | Periode: <strong><?= e($tahun_aktif['tahun_akademik']) ?> - <?= e($tahun_aktif['semester']) ?></strong>
                    <?php endif; ?>
                </p>
            </div>

            <!-- Kartu Statistik -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= number_format($ipk_data['ipk'], 2) ?></h3>
                            <p>IPK Kumulatif</p>
                        </div>
                        <div class="icon"><i class="fas fa-chart-line"></i></div>
                        <a href="<?= BASE_URL ?>/mahasiswa/ipk.php" class="small-box-footer">
                            Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= $ipk_data['total_sks'] ?></h3>
                            <p>Total SKS Ditempuh</p>
                        </div>
                        <div class="icon"><i class="fas fa-book"></i></div>
                        <a href="<?= BASE_URL ?>/mahasiswa/khs.php" class="small-box-footer">
                            Lihat KHS <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?= $sks_semester ?></h3>
                            <p>SKS Semester Ini</p>
                        </div>
                        <div class="icon"><i class="fas fa-clipboard-list"></i></div>
                        <a href="<?= BASE_URL ?>/mahasiswa/krs.php" class="small-box-footer">
                            Lihat KRS <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?= number_format($ipk_data['total_mutu'], 1) ?></h3>
                            <p>Total Mutu</p>
                        </div>
                        <div class="icon"><i class="fas fa-star"></i></div>
                        <a href="<?= BASE_URL ?>/mahasiswa/ipk.php" class="small-box-footer">
                            Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Status KRS -->
            <?php if ($krs_aktif): ?>
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-<?= $krs_aktif['status_krs'] === 'draft' ? 'warning' : ($krs_aktif['status_krs'] === 'disetujui' ? 'success' : 'primary') ?>">
                            <i class="fas fa-info-circle"></i>
                            Status KRS Semester Ini: <strong><?= e(ucfirst($krs_aktif['status_krs'])) ?></strong>
                            <?php if ($krs_aktif['status_krs'] === 'draft'): ?>
                                — <a href="<?= BASE_URL ?>/mahasiswa/isi-krs.php">Lanjutkan pengisian KRS</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php if ($tahun_aktif): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Anda belum mengisi KRS untuk semester ini.
                        <a href="<?= BASE_URL ?>/mahasiswa/isi-krs.php" class="font-weight-bold">Isi KRS Sekarang</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Jadwal Hari Ini -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-day"></i> Jadwal Hari Ini 
                        <small class="text-muted">(<?= e($nama_hari) ?>, <?= date('d/m/Y') ?>)</small>
                    </h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Jam</th>
                                <th>Mata Kuliah</th>
                                <th>Dosen</th>
                                <th>Kelas</th>
                                <th>Ruangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($jadwal_hari_ini)): ?>
                                <tr><td colspan="5" class="text-center text-muted">Tidak ada jadwal hari ini</td></tr>
                            <?php else: ?>
                                <?php foreach ($jadwal_hari_ini as $j): ?>
                                    <tr>
                                        <td><?= e(substr($j['jam_mulai'], 0, 5)) ?> - <?= e(substr($j['jam_selesai'], 0, 5)) ?></td>
                                        <td>
                                            <strong><?= e($j['nama_mata_kuliah']) ?></strong>
                                            <br><small class="text-muted"><?= e($j['kode_mata_kuliah']) ?> (<?= e($j['sks']) ?> SKS)</small>
                                        </td>
                                        <td><?= e($j['nama_dosen']) ?></td>
                                        <td><?= e($j['kelas']) ?></td>
                                        <td><?= e($j['ruangan']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
