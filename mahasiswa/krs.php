<?php
/**
 * Mahasiswa - Riwayat KRS
 * Menampilkan KRS yang telah diambil per semester
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('mahasiswa');

$pdo = getConnection();
$page_title = 'KRS Saya';
$current_page = 'krs';
$id_mahasiswa = $_SESSION['id_mahasiswa'];

// Ambil semua KRS mahasiswa
$stmt = $pdo->prepare("SELECT k.*, ta.tahun_akademik, ta.semester as smt
                        FROM krs k
                        JOIN tahun_akademik ta ON ta.id_tahun_akademik = k.id_tahun_akademik
                        WHERE k.id_mahasiswa = ?
                        ORDER BY ta.tahun_akademik DESC, ta.semester DESC");
$stmt->execute([$id_mahasiswa]);
$krs_list = $stmt->fetchAll();

// Filter semester
$selected_krs = (int) ($_GET['krs'] ?? 0);
if ($selected_krs <= 0 && !empty($krs_list)) {
    $selected_krs = $krs_list[0]['id_krs'];
}

// Ambil detail KRS yang dipilih
$detail_krs = [];
$total_sks = 0;
$krs_info = null;

if ($selected_krs > 0) {
    // Verifikasi bahwa KRS milik mahasiswa ini
    $stmt = $pdo->prepare("SELECT k.*, ta.tahun_akademik, ta.semester as smt 
                           FROM krs k JOIN tahun_akademik ta ON ta.id_tahun_akademik = k.id_tahun_akademik
                           WHERE k.id_krs = ? AND k.id_mahasiswa = ?");
    $stmt->execute([$selected_krs, $id_mahasiswa]);
    $krs_info = $stmt->fetch();

    if ($krs_info) {
        $stmt = $pdo->prepare("SELECT dk.*, jk.kelas, jk.hari, jk.jam_mulai, jk.jam_selesai, jk.ruangan,
                                      mk.kode_mata_kuliah, mk.nama_mata_kuliah, mk.sks,
                                      d.nama_dosen,
                                      n.nilai_tugas, n.nilai_uts, n.nilai_uas, n.nilai_akhir, n.nilai_huruf, n.bobot
                               FROM detail_krs dk
                               JOIN jadwal_kuliah jk ON jk.id_jadwal = dk.id_jadwal
                               JOIN mata_kuliah mk ON mk.id_mata_kuliah = jk.id_mata_kuliah
                               JOIN dosen d ON d.id_dosen = jk.id_dosen
                               LEFT JOIN nilai n ON n.id_detail_krs = dk.id_detail_krs
                               WHERE dk.id_krs = ? AND dk.status = 'aktif'
                               ORDER BY mk.kode_mata_kuliah");
        $stmt->execute([$selected_krs]);
        $detail_krs = $stmt->fetchAll();

        foreach ($detail_krs as $dk) {
            $total_sks += $dk['sks'];
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-clipboard-list"></i> KRS Saya</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/mahasiswa/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">KRS</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <?php if (empty($krs_list)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Anda belum memiliki KRS. 
                    <a href="<?= BASE_URL ?>/mahasiswa/isi-krs.php" class="font-weight-bold">Isi KRS sekarang</a>.
                </div>
            <?php else: ?>
                <!-- Pilih Semester -->
                <div class="card">
                    <div class="card-header">
                        <form action="" method="GET" class="form-inline">
                            <label class="mr-2">Pilih Semester:</label>
                            <select name="krs" class="form-control mr-2" onchange="this.form.submit()">
                                <?php foreach ($krs_list as $k): ?>
                                    <option value="<?= $k['id_krs'] ?>" <?= $selected_krs == $k['id_krs'] ? 'selected' : '' ?>>
                                        <?= e($k['tahun_akademik']) ?> - <?= e($k['smt']) ?>
                                        (<?= e(ucfirst($k['status_krs'])) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                </div>

                <?php if ($krs_info): ?>
                    <!-- Info KRS -->
                    <div class="callout callout-info">
                        <div class="row">
                            <div class="col-md-3"><strong>Periode:</strong> <?= e($krs_info['tahun_akademik']) ?> - <?= e($krs_info['smt']) ?></div>
                            <div class="col-md-3"><strong>Tanggal Pengisian:</strong> <?= e(formatTanggal($krs_info['tanggal_pengisian'])) ?></div>
                            <div class="col-md-3"><strong>Status:</strong> 
                                <span class="badge badge-<?= match($krs_info['status_krs']) { 'draft'=>'secondary', 'disetujui'=>'success', 'dikunci'=>'primary', default=>'secondary' } ?>">
                                    <?= e(ucfirst($krs_info['status_krs'])) ?>
                                </span>
                            </div>
                            <div class="col-md-3"><strong>Total SKS:</strong> <?= $total_sks ?></div>
                        </div>
                    </div>

                    <!-- Tabel Detail KRS -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-list"></i> Daftar Mata Kuliah</h3>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kode</th>
                                        <th>Mata Kuliah</th>
                                        <th>SKS</th>
                                        <th>Kelas</th>
                                        <th>Dosen</th>
                                        <th>Jadwal</th>
                                        <th>Ruangan</th>
                                        <th>Nilai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($detail_krs)): ?>
                                        <tr><td colspan="9" class="text-center text-muted py-3">Belum ada mata kuliah</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($detail_krs as $i => $dk): ?>
                                            <tr>
                                                <td><?= $i + 1 ?></td>
                                                <td><span class="badge badge-primary"><?= e($dk['kode_mata_kuliah']) ?></span></td>
                                                <td><?= e($dk['nama_mata_kuliah']) ?></td>
                                                <td><?= e($dk['sks']) ?></td>
                                                <td><?= e($dk['kelas']) ?></td>
                                                <td><?= e($dk['nama_dosen']) ?></td>
                                                <td><?= e($dk['hari']) ?> <?= e(substr($dk['jam_mulai'], 0, 5)) ?>-<?= e(substr($dk['jam_selesai'], 0, 5)) ?></td>
                                                <td><?= e($dk['ruangan']) ?></td>
                                                <td>
                                                    <?php if ($dk['nilai_huruf']): ?>
                                                        <span class="badge badge-<?= ($dk['bobot'] ?? 0) >= 3 ? 'success' : (($dk['bobot'] ?? 0) >= 2 ? 'warning' : 'danger') ?>">
                                                            <?= e($dk['nilai_huruf']) ?> (<?= e($dk['nilai_akhir']) ?>)
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">Belum ada</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="font-weight-bold">
                                        <td colspan="3" class="text-right">Total SKS:</td>
                                        <td><?= $total_sks ?></td>
                                        <td colspan="5"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
