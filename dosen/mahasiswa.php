<?php
/**
 * Dosen - Daftar Mahasiswa per Mata Kuliah
 * Menampilkan mahasiswa yang terdaftar pada jadwal tertentu
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('dosen');

$pdo = getConnection();
$page_title = 'Daftar Mahasiswa';
$current_page = 'mahasiswa';
$id_dosen = $_SESSION['id_dosen'];

$id_jadwal = (int) ($_GET['jadwal'] ?? 0);
if ($id_jadwal <= 0) {
    setFlashMessage('danger', 'Jadwal tidak valid.');
    redirect(BASE_URL . '/dosen/mata-kuliah.php');
}

// Verifikasi jadwal milik dosen ini
$stmt = $pdo->prepare("SELECT jk.*, mk.kode_mata_kuliah, mk.nama_mata_kuliah, mk.sks,
                               ta.tahun_akademik, ta.semester as smt
                        FROM jadwal_kuliah jk
                        JOIN mata_kuliah mk ON mk.id_mata_kuliah = jk.id_mata_kuliah
                        JOIN tahun_akademik ta ON ta.id_tahun_akademik = jk.id_tahun_akademik
                        WHERE jk.id_jadwal = ? AND jk.id_dosen = ?");
$stmt->execute([$id_jadwal, $id_dosen]);
$jadwal = $stmt->fetch();

if (!$jadwal) {
    setFlashMessage('danger', 'Anda tidak memiliki akses ke jadwal ini.');
    redirect(BASE_URL . '/dosen/mata-kuliah.php');
}

// Ambil daftar mahasiswa
$stmt = $pdo->prepare("SELECT dk.id_detail_krs, m.nim, m.nama_mahasiswa, m.program_studi, m.angkatan,
                               n.nilai_tugas, n.nilai_uts, n.nilai_uas, n.nilai_akhir, n.nilai_huruf, n.bobot
                        FROM detail_krs dk
                        JOIN krs k ON k.id_krs = dk.id_krs
                        JOIN mahasiswa m ON m.id_mahasiswa = k.id_mahasiswa
                        LEFT JOIN nilai n ON n.id_detail_krs = dk.id_detail_krs
                        WHERE dk.id_jadwal = ? AND dk.status = 'aktif'
                        ORDER BY m.nim");
$stmt->execute([$id_jadwal]);
$mahasiswa_list = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-users"></i> Daftar Mahasiswa</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dosen/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dosen/mata-kuliah.php">Mata Kuliah</a></li>
                        <li class="breadcrumb-item active">Mahasiswa</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <!-- Info Jadwal -->
            <div class="callout callout-info">
                <h5><?= e($jadwal['kode_mata_kuliah']) ?> - <?= e($jadwal['nama_mata_kuliah']) ?></h5>
                <p class="mb-0">
                    Kelas <?= e($jadwal['kelas']) ?> | <?= e($jadwal['hari']) ?> <?= e(substr($jadwal['jam_mulai'], 0, 5)) ?>-<?= e(substr($jadwal['jam_selesai'], 0, 5)) ?> | 
                    <?= e($jadwal['ruangan']) ?> | <?= e($jadwal['tahun_akademik']) ?> <?= e($jadwal['smt']) ?>
                </p>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Total: <?= count($mahasiswa_list) ?> mahasiswa</h3>
                    <div class="card-tools">
                        <a href="<?= BASE_URL ?>/dosen/nilai.php?jadwal=<?= $id_jadwal ?>" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i> Input Nilai
                        </a>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th>Program Studi</th>
                                <th>Angkatan</th>
                                <th>Tugas</th>
                                <th>UTS</th>
                                <th>UAS</th>
                                <th>Akhir</th>
                                <th>Huruf</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($mahasiswa_list)): ?>
                                <tr><td colspan="10" class="text-center text-muted py-4">Belum ada mahasiswa yang mendaftar</td></tr>
                            <?php else: ?>
                                <?php foreach ($mahasiswa_list as $i => $m): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><span class="badge badge-primary"><?= e($m['nim']) ?></span></td>
                                        <td><?= e($m['nama_mahasiswa']) ?></td>
                                        <td><?= e($m['program_studi']) ?></td>
                                        <td><?= e($m['angkatan']) ?></td>
                                        <td><?= $m['nilai_tugas'] !== null ? e($m['nilai_tugas']) : '<span class="text-muted">-</span>' ?></td>
                                        <td><?= $m['nilai_uts'] !== null ? e($m['nilai_uts']) : '<span class="text-muted">-</span>' ?></td>
                                        <td><?= $m['nilai_uas'] !== null ? e($m['nilai_uas']) : '<span class="text-muted">-</span>' ?></td>
                                        <td><strong><?= $m['nilai_akhir'] !== null ? e($m['nilai_akhir']) : '-' ?></strong></td>
                                        <td>
                                            <?php if ($m['nilai_huruf']): ?>
                                                <span class="badge badge-<?= ($m['bobot'] ?? 0) >= 3 ? 'success' : (($m['bobot'] ?? 0) >= 2 ? 'warning' : 'danger') ?>">
                                                    <?= e($m['nilai_huruf']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <a href="<?= BASE_URL ?>/dosen/mata-kuliah.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
