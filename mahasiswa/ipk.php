<?php
/**
 * Mahasiswa - Transkrip / IPK Kumulatif
 * Menampilkan seluruh nilai mata kuliah dan IPK akhir
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('mahasiswa');

$pdo = getConnection();
$page_title = 'Transkrip Nilai (IPK)';
$current_page = 'ipk';
$id_mahasiswa = $_SESSION['id_mahasiswa'];

// Hitung IPK Kumulatif (menggunakan fungsi dari functions.php)
$ipk_data = hitungIPK($pdo, $id_mahasiswa);
$ipk = $ipk_data['ipk'];
$total_sks = $ipk_data['total_sks'];
$total_mutu = $ipk_data['total_mutu'];

// Ambil semua detail nilai
$stmt = $pdo->prepare("SELECT mk.kode_mata_kuliah, mk.nama_mata_kuliah, mk.sks, mk.semester as mk_smt,
                              n.nilai_akhir, n.nilai_huruf, n.bobot,
                              ta.tahun_akademik, ta.semester as ta_smt
                       FROM detail_krs dk
                       JOIN krs k ON k.id_krs = dk.id_krs
                       JOIN jadwal_kuliah jk ON jk.id_jadwal = dk.id_jadwal
                       JOIN mata_kuliah mk ON mk.id_mata_kuliah = jk.id_mata_kuliah
                       JOIN tahun_akademik ta ON ta.id_tahun_akademik = k.id_tahun_akademik
                       LEFT JOIN nilai n ON n.id_detail_krs = dk.id_detail_krs
                       WHERE k.id_mahasiswa = ? AND dk.status = 'aktif' AND n.nilai_huruf IS NOT NULL
                       ORDER BY ta.tahun_akademik ASC, ta.semester ASC, mk.kode_mata_kuliah ASC");
$stmt->execute([$id_mahasiswa]);
$transkrip = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-chart-line"></i> Transkrip Nilai (IPK)</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/mahasiswa/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Transkrip</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <!-- Ringkasan IPK -->
            <div class="row">
                <div class="col-md-4">
                    <div class="info-box bg-primary">
                        <span class="info-box-icon"><i class="fas fa-graduation-cap"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Indeks Prestasi Kumulatif (IPK)</span>
                            <span class="info-box-number" style="font-size: 2rem;"><?= number_format($ipk, 2) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-success">
                        <span class="info-box-icon"><i class="fas fa-book"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total SKS Lulus</span>
                            <span class="info-box-number" style="font-size: 2rem;"><?= $total_sks ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-warning">
                        <span class="info-box-icon"><i class="fas fa-star"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Mutu Kumulatif</span>
                            <span class="info-box-number" style="font-size: 2rem;"><?= number_format($total_mutu, 1) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Transkrip -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-list"></i> Daftar Seluruh Nilai</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.print()">
                            <i class="fas fa-print"></i> Cetak Transkrip
                        </button>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-bordered table-striped table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center">No</th>
                                <th class="text-center">Kode</th>
                                <th>Mata Kuliah</th>
                                <th class="text-center">Semester MK</th>
                                <th class="text-center">SKS (K)</th>
                                <th class="text-center">Nilai Angka</th>
                                <th class="text-center">Huruf</th>
                                <th class="text-center">Bobot (N)</th>
                                <th class="text-center">Mutu (K × N)</th>
                                <th class="text-center">Periode Ambil</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($transkrip)): ?>
                                <tr><td colspan="10" class="text-center text-muted py-4">Belum ada data nilai kumulatif.</td></tr>
                            <?php else: ?>
                                <?php $no = 1; ?>
                                <?php foreach ($transkrip as $item): ?>
                                    <?php $mutu = $item['sks'] * $item['bobot']; ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td class="text-center"><span class="badge badge-primary"><?= e($item['kode_mata_kuliah']) ?></span></td>
                                        <td><?= e($item['nama_mata_kuliah']) ?></td>
                                        <td class="text-center"><?= e($item['mk_smt']) ?></td>
                                        <td class="text-center"><?= e($item['sks']) ?></td>
                                        <td class="text-center"><?= e($item['nilai_akhir']) ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-<?= ($item['bobot'] >= 3) ? 'success' : (($item['bobot'] >= 2) ? 'warning' : 'danger') ?>">
                                                <?= e($item['nilai_huruf']) ?>
                                            </span>
                                        </td>
                                        <td class="text-center"><?= number_format($item['bobot'], 2) ?></td>
                                        <td class="text-center font-weight-bold"><?= number_format($mutu, 2) ?></td>
                                        <td class="text-center"><small><?= e($item['tahun_akademik']) ?> <?= e($item['ta_smt']) ?></small></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <style>
                @media print {
                    .main-header, .main-sidebar, .breadcrumb, .card-tools, .main-footer { display: none !important; }
                    .content-wrapper { margin-left: 0 !important; padding: 0 !important; background-color: #fff !important; }
                    .card { border: none !important; box-shadow: none !important; }
                }
            </style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
