<?php
/**
 * Mahasiswa - Isi KRS
 * 
 * Halaman pengisian Kartu Rencana Studi:
 * - Menampilkan mata kuliah yang tersedia pada semester aktif
 * - Menambah/menghapus mata kuliah dari KRS
 * - Validasi maksimal 24 SKS
 * - Validasi mata kuliah tidak boleh ganda
 * - Validasi kuota kelas
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('mahasiswa');

$pdo = getConnection();
$page_title = 'Isi KRS';
$current_page = 'isi-krs';
$id_mahasiswa = $_SESSION['id_mahasiswa'];

// Batas maksimal SKS
define('MAX_SKS', 24);

// Cek tahun akademik aktif
$stmt = $pdo->query("SELECT * FROM tahun_akademik WHERE status = 'aktif' LIMIT 1");
$tahun_aktif = $stmt->fetch();

if (!$tahun_aktif) {
    require_once __DIR__ . '/../includes/header.php';
    require_once __DIR__ . '/../includes/navbar.php';
    require_once __DIR__ . '/../includes/sidebar.php';
    ?>
    <div class="content-wrapper">
        <div class="content-header"><div class="container-fluid"><h1 class="m-0"><i class="fas fa-plus-circle"></i> Isi KRS</h1></div></div>
        <section class="content"><div class="container-fluid">
            <div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Tidak ada tahun akademik yang aktif saat ini. Hubungi Admin.</div>
        </div></section>
    </div>
    <?php require_once __DIR__ . '/../includes/footer.php'; exit;
}

$id_ta = $tahun_aktif['id_tahun_akademik'];

// Cek/buat KRS untuk semester ini
$stmt = $pdo->prepare("SELECT * FROM krs WHERE id_mahasiswa = ? AND id_tahun_akademik = ?");
$stmt->execute([$id_mahasiswa, $id_ta]);
$krs = $stmt->fetch();

if (!$krs) {
    // Buat KRS baru dengan status draft
    $stmt = $pdo->prepare("INSERT INTO krs (id_mahasiswa, id_tahun_akademik, tanggal_pengisian, status_krs) VALUES (?, ?, CURDATE(), 'draft')");
    $stmt->execute([$id_mahasiswa, $id_ta]);
    $id_krs = $pdo->lastInsertId();

    $stmt = $pdo->prepare("SELECT * FROM krs WHERE id_krs = ?");
    $stmt->execute([$id_krs]);
    $krs = $stmt->fetch();
} else {
    $id_krs = $krs['id_krs'];
}

$krs_locked = ($krs['status_krs'] === 'dikunci');

// ============================
// PROSES AKSI (Tambah / Hapus)
// ============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$krs_locked) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('danger', 'Token keamanan tidak valid.');
        redirect(BASE_URL . '/mahasiswa/isi-krs.php');
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'tambah') {
        $id_jadwal = (int) ($_POST['id_jadwal'] ?? 0);

        if ($id_jadwal > 0) {
            // Ambil info jadwal yang dipilih
            $stmt = $pdo->prepare("SELECT jk.*, mk.sks, mk.id_mata_kuliah, mk.nama_mata_kuliah
                                   FROM jadwal_kuliah jk 
                                   JOIN mata_kuliah mk ON mk.id_mata_kuliah = jk.id_mata_kuliah
                                   WHERE jk.id_jadwal = ? AND jk.id_tahun_akademik = ?");
            $stmt->execute([$id_jadwal, $id_ta]);
            $jadwal = $stmt->fetch();

            if (!$jadwal) {
                setFlashMessage('danger', 'Jadwal tidak ditemukan.');
            } else {
                // VALIDASI 1: Cek mata kuliah ganda (tidak boleh ambil mata kuliah yang sama)
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM detail_krs dk
                                       JOIN jadwal_kuliah jk ON jk.id_jadwal = dk.id_jadwal
                                       WHERE dk.id_krs = ? AND jk.id_mata_kuliah = ? AND dk.status = 'aktif'");
                $stmt->execute([$id_krs, $jadwal['id_mata_kuliah']]);
                if ($stmt->fetchColumn() > 0) {
                    setFlashMessage('danger', 'Mata kuliah "' . $jadwal['nama_mata_kuliah'] . '" sudah ada di KRS Anda.');
                }
                // VALIDASI 2: Cek total SKS tidak melebihi batas
                else {
                    $stmt = $pdo->prepare("SELECT COALESCE(SUM(mk2.sks), 0) FROM detail_krs dk2
                                           JOIN jadwal_kuliah jk2 ON jk2.id_jadwal = dk2.id_jadwal
                                           JOIN mata_kuliah mk2 ON mk2.id_mata_kuliah = jk2.id_mata_kuliah
                                           WHERE dk2.id_krs = ? AND dk2.status = 'aktif'");
                    $stmt->execute([$id_krs]);
                    $current_sks = (int) $stmt->fetchColumn();

                    if (($current_sks + $jadwal['sks']) > MAX_SKS) {
                        setFlashMessage('danger', 'Total SKS akan melebihi batas ' . MAX_SKS . ' SKS. (Saat ini: ' . $current_sks . ' SKS, akan ditambah: ' . $jadwal['sks'] . ' SKS)');
                    }
                    // VALIDASI 3: Cek kuota kelas
                    else {
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM detail_krs dk3
                                               JOIN krs k3 ON k3.id_krs = dk3.id_krs
                                               WHERE dk3.id_jadwal = ? AND dk3.status = 'aktif'");
                        $stmt->execute([$id_jadwal]);
                        $jumlah_peserta = (int) $stmt->fetchColumn();

                        if ($jumlah_peserta >= $jadwal['kuota']) {
                            setFlashMessage('danger', 'Kuota kelas sudah penuh.');
                        } else {
                            // Tambahkan ke detail KRS
                            try {
                                $stmt = $pdo->prepare("INSERT INTO detail_krs (id_krs, id_jadwal, status) VALUES (?, ?, 'aktif')");
                                $stmt->execute([$id_krs, $id_jadwal]);
                                setFlashMessage('success', 'Mata kuliah "' . $jadwal['nama_mata_kuliah'] . '" berhasil ditambahkan ke KRS.');
                            } catch (Exception $ex) {
                                setFlashMessage('danger', 'Gagal menambahkan: ' . $ex->getMessage());
                            }
                        }
                    }
                }
            }
        }
    } elseif ($action === 'hapus') {
        $id_detail = (int) ($_POST['id_detail_krs'] ?? 0);
        if ($id_detail > 0) {
            $stmt = $pdo->prepare("DELETE FROM detail_krs WHERE id_detail_krs = ? AND id_krs = ?");
            $stmt->execute([$id_detail, $id_krs]);
            setFlashMessage('success', 'Mata kuliah berhasil dihapus dari KRS.');
        }
    }

    redirect(BASE_URL . '/mahasiswa/isi-krs.php');
}

// ============================
// AMBIL DATA UNTUK TAMPILAN
// ============================

// KRS saat ini (mata kuliah yang sudah dipilih)
$stmt = $pdo->prepare("SELECT dk.id_detail_krs, dk.id_jadwal, jk.kelas, jk.hari, jk.jam_mulai, jk.jam_selesai, jk.ruangan,
                               mk.kode_mata_kuliah, mk.nama_mata_kuliah, mk.sks,
                               d.nama_dosen
                        FROM detail_krs dk
                        JOIN jadwal_kuliah jk ON jk.id_jadwal = dk.id_jadwal
                        JOIN mata_kuliah mk ON mk.id_mata_kuliah = jk.id_mata_kuliah
                        JOIN dosen d ON d.id_dosen = jk.id_dosen
                        WHERE dk.id_krs = ? AND dk.status = 'aktif'
                        ORDER BY mk.kode_mata_kuliah");
$stmt->execute([$id_krs]);
$krs_items = $stmt->fetchAll();

// Hitung total SKS saat ini
$total_sks = 0;
$mk_ids_in_krs = [];
foreach ($krs_items as $item) {
    $total_sks += $item['sks'];
    $mk_ids_in_krs[] = $item['kode_mata_kuliah'];
}

// Jadwal yang tersedia (belum dipilih)
$stmt = $pdo->prepare("SELECT jk.*, mk.kode_mata_kuliah, mk.nama_mata_kuliah, mk.sks, d.nama_dosen,
                               (SELECT COUNT(*) FROM detail_krs dk4 JOIN krs k4 ON k4.id_krs = dk4.id_krs 
                                WHERE dk4.id_jadwal = jk.id_jadwal AND dk4.status = 'aktif') as peserta
                        FROM jadwal_kuliah jk
                        JOIN mata_kuliah mk ON mk.id_mata_kuliah = jk.id_mata_kuliah
                        JOIN dosen d ON d.id_dosen = jk.id_dosen
                        WHERE jk.id_tahun_akademik = ?
                        ORDER BY mk.kode_mata_kuliah, jk.kelas");
$stmt->execute([$id_ta]);
$jadwal_tersedia = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-plus-circle"></i> Isi KRS</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/mahasiswa/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Isi KRS</li>
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

            <!-- Info Periode -->
            <div class="callout callout-info">
                <h5><i class="fas fa-calendar-check"></i> Periode: <?= e($tahun_aktif['tahun_akademik']) ?> - Semester <?= e($tahun_aktif['semester']) ?></h5>
                <p class="mb-0">
                    Status KRS: <span class="badge badge-<?= match($krs['status_krs']) { 'draft'=>'secondary', 'disetujui'=>'success', 'dikunci'=>'primary', default=>'secondary' } ?>"><?= e(ucfirst($krs['status_krs'])) ?></span>
                    | Total SKS: <strong><?= $total_sks ?></strong> / <?= MAX_SKS ?>
                    <?php if ($krs_locked): ?>
                        <span class="text-danger ml-2"><i class="fas fa-lock"></i> KRS sudah dikunci, tidak dapat diubah.</span>
                    <?php endif; ?>
                </p>
            </div>

            <!-- Progress Bar SKS -->
            <div class="card">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between mb-1">
                        <small>SKS Terpakai</small>
                        <small><strong><?= $total_sks ?></strong> / <?= MAX_SKS ?> SKS</small>
                    </div>
                    <?php $pct = min(($total_sks / MAX_SKS) * 100, 100); ?>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-<?= $pct >= 90 ? 'danger' : ($pct >= 70 ? 'warning' : 'success') ?>" 
                             role="progressbar" style="width: <?= $pct ?>%;"><?= $total_sks ?> SKS</div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- KRS Saat Ini -->
                <div class="col-lg-6">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-clipboard-list"></i> KRS Saya (<?= count($krs_items) ?> Mata Kuliah)</h3>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Mata Kuliah</th>
                                        <th>SKS</th>
                                        <th>Kelas</th>
                                        <th>Jadwal</th>
                                        <?php if (!$krs_locked): ?><th>Aksi</th><?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($krs_items)): ?>
                                        <tr><td colspan="<?= $krs_locked ? 5 : 6 ?>" class="text-center text-muted py-3">Belum ada mata kuliah dipilih</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($krs_items as $item): ?>
                                            <tr>
                                                <td><span class="badge badge-primary"><?= e($item['kode_mata_kuliah']) ?></span></td>
                                                <td>
                                                    <?= e($item['nama_mata_kuliah']) ?><br>
                                                    <small class="text-muted"><?= e($item['nama_dosen']) ?></small>
                                                </td>
                                                <td><?= e($item['sks']) ?></td>
                                                <td><?= e($item['kelas']) ?></td>
                                                <td><small><?= e($item['hari']) ?> <?= e(substr($item['jam_mulai'], 0, 5)) ?>-<?= e(substr($item['jam_selesai'], 0, 5)) ?></small></td>
                                                <?php if (!$krs_locked): ?>
                                                    <td>
                                                        <form action="" method="POST" class="d-inline" onsubmit="return confirm('Hapus mata kuliah ini dari KRS?')">
                                                            <?= csrfField() ?>
                                                            <input type="hidden" name="action" value="hapus">
                                                            <input type="hidden" name="id_detail_krs" value="<?= $item['id_detail_krs'] ?>">
                                                            <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-times"></i></button>
                                                        </form>
                                                    </td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="font-weight-bold">
                                        <td colspan="2" class="text-right">Total SKS:</td>
                                        <td><?= $total_sks ?></td>
                                        <td colspan="<?= $krs_locked ? 2 : 3 ?>"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Mata Kuliah Tersedia -->
                <div class="col-lg-6">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-book"></i> Mata Kuliah Tersedia</h3>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Mata Kuliah</th>
                                        <th>SKS</th>
                                        <th>Kelas</th>
                                        <th>Jadwal</th>
                                        <th>Peserta</th>
                                        <?php if (!$krs_locked): ?><th>Aksi</th><?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($jadwal_tersedia)): ?>
                                        <tr><td colspan="<?= $krs_locked ? 6 : 7 ?>" class="text-center text-muted py-3">Tidak ada jadwal tersedia</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($jadwal_tersedia as $jd): ?>
                                            <?php 
                                            $already_in_krs = in_array($jd['kode_mata_kuliah'], $mk_ids_in_krs);
                                            $quota_full = $jd['peserta'] >= $jd['kuota'];
                                            ?>
                                            <tr class="<?= $already_in_krs ? 'table-secondary' : '' ?>">
                                                <td><span class="badge badge-warning"><?= e($jd['kode_mata_kuliah']) ?></span></td>
                                                <td>
                                                    <?= e($jd['nama_mata_kuliah']) ?><br>
                                                    <small class="text-muted"><?= e($jd['nama_dosen']) ?></small>
                                                </td>
                                                <td><?= e($jd['sks']) ?></td>
                                                <td><?= e($jd['kelas']) ?></td>
                                                <td><small><?= e($jd['hari']) ?> <?= e(substr($jd['jam_mulai'], 0, 5)) ?>-<?= e(substr($jd['jam_selesai'], 0, 5)) ?></small></td>
                                                <td>
                                                    <span class="badge badge-<?= $quota_full ? 'danger' : 'info' ?>">
                                                        <?= $jd['peserta'] ?>/<?= $jd['kuota'] ?>
                                                    </span>
                                                </td>
                                                <?php if (!$krs_locked): ?>
                                                    <td>
                                                        <?php if ($already_in_krs): ?>
                                                            <span class="badge badge-secondary"><i class="fas fa-check"></i> Dipilih</span>
                                                        <?php elseif ($quota_full): ?>
                                                            <span class="badge badge-danger">Penuh</span>
                                                        <?php else: ?>
                                                            <form action="" method="POST" class="d-inline">
                                                                <?= csrfField() ?>
                                                                <input type="hidden" name="action" value="tambah">
                                                                <input type="hidden" name="id_jadwal" value="<?= $jd['id_jadwal'] ?>">
                                                                <button type="submit" class="btn btn-xs btn-success"><i class="fas fa-plus"></i></button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </td>
                                                <?php endif; ?>
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
