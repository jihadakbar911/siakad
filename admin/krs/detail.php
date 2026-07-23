<?php
/**
 * Admin - Detail & Validasi KRS
 */
require_once __DIR__ . '/../../config/auth.php';
requireRole('admin');

$pdo = getConnection();
$page_title = 'Detail & Validasi KRS';
$current_page = 'krs';

$id_krs = (int) ($_GET['id'] ?? 0);
if ($id_krs <= 0) {
    setFlashMessage('danger', 'ID KRS tidak valid.');
    redirect(BASE_URL . '/admin/krs/');
}

// Proses Update Status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('danger', 'Token keamanan tidak valid.');
        redirect(BASE_URL . '/admin/krs/detail.php?id=' . $id_krs);
    }
    
    $status_baru = $_POST['status_krs'] ?? '';
    if (in_array($status_baru, ['draft', 'disetujui', 'dikunci'])) {
        $stmt = $pdo->prepare("UPDATE krs SET status_krs = ? WHERE id_krs = ?");
        $stmt->execute([$status_baru, $id_krs]);
        setFlashMessage('success', 'Status KRS berhasil diperbarui menjadi ' . ucfirst($status_baru) . '.');
    }
    redirect(BASE_URL . '/admin/krs/detail.php?id=' . $id_krs);
}

// Ambil info KRS & Mahasiswa
$stmt = $pdo->prepare("SELECT k.*, m.nim, m.nama_mahasiswa, m.program_studi, m.angkatan,
                              ta.tahun_akademik, ta.semester as smt
                       FROM krs k
                       JOIN mahasiswa m ON m.id_mahasiswa = k.id_mahasiswa
                       JOIN tahun_akademik ta ON ta.id_tahun_akademik = k.id_tahun_akademik
                       WHERE k.id_krs = ?");
$stmt->execute([$id_krs]);
$krs = $stmt->fetch();

if (!$krs) {
    setFlashMessage('danger', 'Data KRS tidak ditemukan.');
    redirect(BASE_URL . '/admin/krs/');
}

// Ambil detail mata kuliah
$stmt = $pdo->prepare("SELECT dk.id_detail_krs, jk.kelas, jk.hari, jk.jam_mulai, jk.jam_selesai, jk.ruangan,
                              mk.kode_mata_kuliah, mk.nama_mata_kuliah, mk.sks, d.nama_dosen
                       FROM detail_krs dk
                       JOIN jadwal_kuliah jk ON jk.id_jadwal = dk.id_jadwal
                       JOIN mata_kuliah mk ON mk.id_mata_kuliah = jk.id_mata_kuliah
                       JOIN dosen d ON d.id_dosen = jk.id_dosen
                       WHERE dk.id_krs = ? AND dk.status = 'aktif'
                       ORDER BY mk.kode_mata_kuliah");
$stmt->execute([$id_krs]);
$krs_items = $stmt->fetchAll();

$total_sks = 0;
foreach ($krs_items as $item) {
    $total_sks += $item['sks'];
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-check-circle"></i> Validasi KRS</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/krs/">KRS</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <?php $flash = getFlashMessage(); ?>
            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show">
                    <?= e($flash['message']) ?>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Info Mahasiswa -->
                <div class="col-md-4">
                    <div class="card card-primary">
                        <div class="card-header"><h3 class="card-title">Informasi Mahasiswa</h3></div>
                        <div class="card-body box-profile">
                            <h3 class="profile-username text-center"><?= e($krs['nama_mahasiswa']) ?></h3>
                            <p class="text-muted text-center"><?= e($krs['nim']) ?></p>
                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item"><b>Program Studi</b> <span class="float-right"><?= e($krs['program_studi']) ?></span></li>
                                <li class="list-group-item"><b>Angkatan</b> <span class="float-right"><?= e($krs['angkatan']) ?></span></li>
                                <li class="list-group-item"><b>Periode KRS</b> <span class="float-right"><?= e($krs['tahun_akademik']) ?> <?= e($krs['smt']) ?></span></li>
                                <li class="list-group-item"><b>Total SKS</b> <span class="float-right"><strong><?= $total_sks ?></strong> / 24</span></li>
                            </ul>
                            
                            <form action="" method="POST">
                                <?= csrfField() ?>
                                <div class="form-group">
                                    <label>Ubah Status KRS</label>
                                    <select name="status_krs" class="form-control">
                                        <option value="draft" <?= $krs['status_krs'] === 'draft' ? 'selected' : '' ?>>Draft (Mahasiswa bisa ubah)</option>
                                        <option value="disetujui" <?= $krs['status_krs'] === 'disetujui' ? 'selected' : '' ?>>Disetujui (Sudah diperiksa)</option>
                                        <option value="dikunci" <?= $krs['status_krs'] === 'dikunci' ? 'selected' : '' ?>>Dikunci (Final, tidak bisa diubah)</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-save"></i> Simpan Status</button>
                            </form>
                            <a href="<?= BASE_URL ?>/admin/krs/" class="btn btn-secondary btn-block mt-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                        </div>
                    </div>
                </div>

                <!-- Daftar Mata Kuliah -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-list"></i> Mata Kuliah yang Diambil</h3>
                            <div class="card-tools">
                                <span class="badge badge-<?= match($krs['status_krs']) { 'draft'=>'secondary', 'disetujui'=>'success', 'dikunci'=>'primary', default=>'secondary' } ?>">
                                    Status Saat Ini: <?= e(ucfirst($krs['status_krs'])) ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Mata Kuliah</th>
                                        <th>SKS</th>
                                        <th>Dosen</th>
                                        <th>Jadwal</th>
                                        <th>Ruangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($krs_items)): ?>
                                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada mata kuliah yang diambil</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($krs_items as $item): ?>
                                            <tr>
                                                <td><span class="badge badge-primary"><?= e($item['kode_mata_kuliah']) ?></span></td>
                                                <td><?= e($item['nama_mata_kuliah']) ?></td>
                                                <td><?= e($item['sks']) ?></td>
                                                <td><?= e($item['nama_dosen']) ?></td>
                                                <td><small><?= e($item['hari']) ?> <?= e(substr($item['jam_mulai'], 0, 5)) ?>-<?= e(substr($item['jam_selesai'], 0, 5)) ?></small></td>
                                                <td><?= e($item['ruangan']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
