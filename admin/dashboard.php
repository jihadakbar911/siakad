<?php
/**
 * Dashboard Admin
 * Menampilkan statistik dan ringkasan data
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('admin');

$pdo = getConnection();
$page_title = 'Dashboard Admin';
$current_page = 'dashboard';

// Ambil statistik
$total_mahasiswa = $pdo->query("SELECT COUNT(*) FROM mahasiswa")->fetchColumn();
$total_dosen = $pdo->query("SELECT COUNT(*) FROM dosen")->fetchColumn();
$total_mata_kuliah = $pdo->query("SELECT COUNT(*) FROM mata_kuliah")->fetchColumn();
$total_krs = $pdo->query("SELECT COUNT(*) FROM krs")->fetchColumn();

// Tahun akademik aktif
$stmt = $pdo->query("SELECT * FROM tahun_akademik WHERE status = 'aktif' LIMIT 1");
$tahun_aktif = $stmt->fetch();

// Mahasiswa terbaru
$stmt = $pdo->query("SELECT m.nim, m.nama_mahasiswa, m.program_studi, m.angkatan 
                      FROM mahasiswa m 
                      ORDER BY m.id_mahasiswa DESC LIMIT 5");
$mahasiswa_terbaru = $stmt->fetchAll();

// KRS terbaru
$stmt = $pdo->query("SELECT k.tanggal_pengisian, k.status_krs, m.nama_mahasiswa, m.nim,
                            ta.tahun_akademik, ta.semester
                     FROM krs k
                     JOIN mahasiswa m ON m.id_mahasiswa = k.id_mahasiswa
                     JOIN tahun_akademik ta ON ta.id_tahun_akademik = k.id_tahun_akademik
                     ORDER BY k.id_krs DESC LIMIT 5");
$krs_terbaru = $stmt->fetchAll();

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
                    <h1 class="m-0"><i class="fas fa-tachometer-alt"></i> Dashboard Admin</h1>
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

            <!-- Tahun Akademik Aktif -->
            <?php if ($tahun_aktif): ?>
                <div class="callout callout-info">
                    <h5><i class="fas fa-calendar-check"></i> Tahun Akademik Aktif</h5>
                    <p class="mb-0">
                        <strong><?= e($tahun_aktif['tahun_akademik']) ?> - Semester <?= e($tahun_aktif['semester']) ?></strong>
                    </p>
                </div>
            <?php endif; ?>

            <!-- Kartu Statistik -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= $total_mahasiswa ?></h3>
                            <p>Total Mahasiswa</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <a href="<?= BASE_URL ?>/admin/mahasiswa/" class="small-box-footer">
                            Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= $total_dosen ?></h3>
                            <p>Total Dosen</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <a href="<?= BASE_URL ?>/admin/dosen/" class="small-box-footer">
                            Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?= $total_mata_kuliah ?></h3>
                            <p>Mata Kuliah</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <a href="<?= BASE_URL ?>/admin/mata-kuliah/" class="small-box-footer">
                            Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?= $total_krs ?></h3>
                            <p>Total KRS</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <a href="<?= BASE_URL ?>/admin/krs/" class="small-box-footer">
                            Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Mahasiswa Terbaru -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user-graduate"></i> Mahasiswa Terbaru</h3>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>NIM</th>
                                        <th>Nama</th>
                                        <th>Program Studi</th>
                                        <th>Angkatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($mahasiswa_terbaru)): ?>
                                        <tr><td colspan="4" class="text-center text-muted">Belum ada data</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($mahasiswa_terbaru as $mhs): ?>
                                            <tr>
                                                <td><?= e($mhs['nim']) ?></td>
                                                <td><?= e($mhs['nama_mahasiswa']) ?></td>
                                                <td><?= e($mhs['program_studi']) ?></td>
                                                <td><?= e($mhs['angkatan']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- KRS Terbaru -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-clipboard-list"></i> KRS Terbaru</h3>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Mahasiswa</th>
                                        <th>Periode</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($krs_terbaru)): ?>
                                        <tr><td colspan="3" class="text-center text-muted">Belum ada data</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($krs_terbaru as $krs): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= e($krs['nama_mahasiswa']) ?></strong><br>
                                                    <small class="text-muted"><?= e($krs['nim']) ?></small>
                                                </td>
                                                <td><?= e($krs['tahun_akademik']) ?> <?= e($krs['semester']) ?></td>
                                                <td>
                                                    <?php
                                                    $badge_class = match($krs['status_krs']) {
                                                        'draft' => 'badge-secondary',
                                                        'disetujui' => 'badge-success',
                                                        'dikunci' => 'badge-primary',
                                                        default => 'badge-secondary'
                                                    };
                                                    ?>
                                                    <span class="badge <?= $badge_class ?>"><?= e(ucfirst($krs['status_krs'])) ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
