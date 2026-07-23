<?php
/**
 * Mahasiswa - Kartu Hasil Studi (KHS)
 * Menampilkan nilai mahasiswa per semester dan menghitung IPS
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('mahasiswa');

$pdo = getConnection();
$page_title = 'Kartu Hasil Studi';
$current_page = 'khs';
$id_mahasiswa = $_SESSION['id_mahasiswa'];

// Ambil riwayat KRS/Semester mahasiswa
$stmt = $pdo->prepare("SELECT k.id_krs, ta.id_tahun_akademik, ta.tahun_akademik, ta.semester as smt, k.status_krs
                        FROM krs k
                        JOIN tahun_akademik ta ON ta.id_tahun_akademik = k.id_tahun_akademik
                        WHERE k.id_mahasiswa = ?
                        ORDER BY ta.tahun_akademik DESC, ta.semester DESC");
$stmt->execute([$id_mahasiswa]);
$semester_list = $stmt->fetchAll();

// Filter semester
$selected_krs = (int) ($_GET['krs'] ?? 0);
if ($selected_krs <= 0 && !empty($semester_list)) {
    $selected_krs = $semester_list[0]['id_krs'];
}

$khs_items = [];
$info_smt = null;
$total_sks_smt = 0;
$total_mutu_smt = 0;

if ($selected_krs > 0) {
    // Ambil info semester
    $stmt = $pdo->prepare("SELECT ta.tahun_akademik, ta.semester as smt 
                           FROM krs k JOIN tahun_akademik ta ON ta.id_tahun_akademik = k.id_tahun_akademik
                           WHERE k.id_krs = ? AND k.id_mahasiswa = ?");
    $stmt->execute([$selected_krs, $id_mahasiswa]);
    $info_smt = $stmt->fetch();

    if ($info_smt) {
        // Ambil detail nilai
        $stmt = $pdo->prepare("SELECT mk.kode_mata_kuliah, mk.nama_mata_kuliah, mk.sks,
                                      n.nilai_angka, n.nilai_huruf, n.bobot, n.nilai_tugas, n.nilai_uts, n.nilai_uas, n.nilai_akhir
                               FROM detail_krs dk
                               JOIN jadwal_kuliah jk ON jk.id_jadwal = dk.id_jadwal
                               JOIN mata_kuliah mk ON mk.id_mata_kuliah = jk.id_mata_kuliah
                               LEFT JOIN nilai n ON n.id_detail_krs = dk.id_detail_krs
                               WHERE dk.id_krs = ? AND dk.status = 'aktif'
                               ORDER BY mk.kode_mata_kuliah");
        $stmt->execute([$selected_krs]);
        $khs_items = $stmt->fetchAll();

        foreach ($khs_items as $item) {
            $sks = $item['sks'];
            $bobot = $item['bobot'] ?? 0;
            $mutu = $sks * $bobot;
            
            // Hitung hanya jika sudah ada nilai
            if ($item['nilai_huruf']) {
                $total_sks_smt += $sks;
                $total_mutu_smt += $mutu;
            }
        }
    }
}

$ips = $total_sks_smt > 0 ? ($total_mutu_smt / $total_sks_smt) : 0;

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-file-alt"></i> Kartu Hasil Studi (KHS)</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/mahasiswa/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">KHS</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <?php if (empty($semester_list)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Anda belum memiliki riwayat akademik.
                </div>
            <?php else: ?>
                <!-- Filter Semester -->
                <div class="card">
                    <div class="card-header">
                        <form action="" method="GET" class="form-inline">
                            <label class="mr-2">Pilih Semester:</label>
                            <select name="krs" class="form-control mr-2" onchange="this.form.submit()">
                                <?php foreach ($semester_list as $smt): ?>
                                    <option value="<?= $smt['id_krs'] ?>" <?= $selected_krs == $smt['id_krs'] ? 'selected' : '' ?>>
                                        <?= e($smt['tahun_akademik']) ?> - <?= e($smt['smt']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <noscript><button type="submit" class="btn btn-primary">Tampilkan</button></noscript>
                        </form>
                    </div>
                </div>

                <?php if ($info_smt): ?>
                    <!-- Ringkasan KHS -->
                    <div class="row">
                        <div class="col-lg-4 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3><?= number_format($ips, 2) ?></h3>
                                    <p>Indeks Prestasi Semester (IPS)</p>
                                </div>
                                <div class="icon"><i class="fas fa-chart-bar"></i></div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3><?= $total_sks_smt ?></h3>
                                    <p>SKS Lulus</p>
                                </div>
                                <div class="icon"><i class="fas fa-check-circle"></i></div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3><?= number_format($total_mutu_smt, 1) ?></h3>
                                    <p>Total Mutu</p>
                                </div>
                                <div class="icon"><i class="fas fa-star"></i></div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel KHS -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title font-weight-bold">
                                KHS Periode: <?= e($info_smt['tahun_akademik']) ?> - <?= e($info_smt['smt']) ?>
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.print()">
                                    <i class="fas fa-print"></i> Cetak KHS
                                </button>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover table-bordered table-striped">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center" style="width: 50px;">No</th>
                                        <th class="text-center">Kode</th>
                                        <th>Mata Kuliah</th>
                                        <th class="text-center">SKS (K)</th>
                                        <th class="text-center">Nilai Angka</th>
                                        <th class="text-center">Nilai Huruf</th>
                                        <th class="text-center">Bobot (N)</th>
                                        <th class="text-center">Mutu (K × N)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($khs_items)): ?>
                                        <tr><td colspan="8" class="text-center text-muted py-4">Belum ada mata kuliah yang diambil.</td></tr>
                                    <?php else: ?>
                                        <?php $no = 1; $total_sks_krs = 0; ?>
                                        <?php foreach ($khs_items as $item): ?>
                                            <?php 
                                            $sks = $item['sks'];
                                            $bobot = $item['bobot'] ?? 0;
                                            $mutu = $item['nilai_huruf'] ? ($sks * $bobot) : 0;
                                            $total_sks_krs += $sks;
                                            ?>
                                            <tr>
                                                <td class="text-center"><?= $no++ ?></td>
                                                <td class="text-center"><span class="badge badge-primary"><?= e($item['kode_mata_kuliah']) ?></span></td>
                                                <td><?= e($item['nama_mata_kuliah']) ?></td>
                                                <td class="text-center"><?= $sks ?></td>
                                                <td class="text-center"><?= $item['nilai_akhir'] !== null ? e($item['nilai_akhir']) : '-' ?></td>
                                                <td class="text-center">
                                                    <?php if ($item['nilai_huruf']): ?>
                                                        <span class="badge badge-<?= ($bobot >= 3) ? 'success' : (($bobot >= 2) ? 'warning' : 'danger') ?>">
                                                            <?= e($item['nilai_huruf']) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center"><?= $item['nilai_huruf'] ? number_format($bobot, 2) : '-' ?></td>
                                                <td class="text-center font-weight-bold"><?= $item['nilai_huruf'] ? number_format($mutu, 2) : '-' ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr class="bg-light font-weight-bold">
                                            <td colspan="3" class="text-right">TOTAL:</td>
                                            <td class="text-center"><?= $total_sks_krs ?> <small class="text-muted">(Lulus: <?= $total_sks_smt ?>)</small></td>
                                            <td colspan="3"></td>
                                            <td class="text-center"><?= number_format($total_mutu_smt, 2) ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <style>
                        @media print {
                            .main-header, .main-sidebar, .breadcrumb, .card-tools, .card-header form, .main-footer { display: none !important; }
                            .content-wrapper { margin-left: 0 !important; padding: 0 !important; background-color: #fff !important; }
                            .card { border: none !important; box-shadow: none !important; }
                        }
                    </style>
                <?php endif; ?>
            <?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
