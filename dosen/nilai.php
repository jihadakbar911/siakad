<?php
/**
 * Dosen - Input Nilai Mahasiswa
 * 
 * Halaman untuk memasukkan/mengubah nilai mahasiswa:
 * - Input nilai tugas, UTS, UAS
 * - Perhitungan otomatis nilai akhir, huruf, bobot
 * - Hanya bisa mengisi nilai pada mata kuliah yang diampu
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('dosen');

$pdo = getConnection();
$page_title = 'Input Nilai';
$current_page = 'nilai';
$id_dosen = $_SESSION['id_dosen'];

// Ambil daftar jadwal dosen untuk pilihan
$stmt = $pdo->prepare("SELECT jk.id_jadwal, mk.kode_mata_kuliah, mk.nama_mata_kuliah, jk.kelas,
                               ta.tahun_akademik, ta.semester as smt, ta.status as ta_status
                        FROM jadwal_kuliah jk
                        JOIN mata_kuliah mk ON mk.id_mata_kuliah = jk.id_mata_kuliah
                        JOIN tahun_akademik ta ON ta.id_tahun_akademik = jk.id_tahun_akademik
                        WHERE jk.id_dosen = ?
                        ORDER BY ta.tahun_akademik DESC, mk.kode_mata_kuliah, jk.kelas");
$stmt->execute([$id_dosen]);
$jadwal_options = $stmt->fetchAll();

$id_jadwal = (int) ($_GET['jadwal'] ?? ($_POST['id_jadwal'] ?? 0));

$jadwal = null;
$mahasiswa_list = [];

if ($id_jadwal > 0) {
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
        redirect(BASE_URL . '/dosen/nilai.php');
    }

    // Ambil daftar mahasiswa beserta nilai
    $stmt = $pdo->prepare("SELECT dk.id_detail_krs, m.nim, m.nama_mahasiswa,
                                   n.id_nilai, n.nilai_tugas, n.nilai_uts, n.nilai_uas, 
                                   n.nilai_akhir, n.nilai_huruf, n.bobot
                            FROM detail_krs dk
                            JOIN krs k ON k.id_krs = dk.id_krs
                            JOIN mahasiswa m ON m.id_mahasiswa = k.id_mahasiswa
                            LEFT JOIN nilai n ON n.id_detail_krs = dk.id_detail_krs
                            WHERE dk.id_jadwal = ? AND dk.status = 'aktif'
                            ORDER BY m.nim");
    $stmt->execute([$id_jadwal]);
    $mahasiswa_list = $stmt->fetchAll();
}

// PROSES SIMPAN NILAI
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_nilai'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('danger', 'Token keamanan tidak valid.');
        redirect(BASE_URL . '/dosen/nilai.php?jadwal=' . $id_jadwal);
    }

    $id_jadwal_post = (int) ($_POST['id_jadwal'] ?? 0);

    // Verifikasi ulang bahwa jadwal milik dosen
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM jadwal_kuliah WHERE id_jadwal = ? AND id_dosen = ?");
    $stmt->execute([$id_jadwal_post, $id_dosen]);
    if ($stmt->fetchColumn() == 0) {
        setFlashMessage('danger', 'Akses ditolak.');
        redirect(BASE_URL . '/dosen/nilai.php');
    }

    $detail_ids = $_POST['id_detail_krs'] ?? [];
    $tugas_arr = $_POST['nilai_tugas'] ?? [];
    $uts_arr = $_POST['nilai_uts'] ?? [];
    $uas_arr = $_POST['nilai_uas'] ?? [];

    $saved = 0;
    $errors = [];

    try {
        $pdo->beginTransaction();

        foreach ($detail_ids as $idx => $id_detail) {
            $id_detail = (int) $id_detail;
            $tugas = $tugas_arr[$idx] ?? '';
            $uts = $uts_arr[$idx] ?? '';
            $uas = $uas_arr[$idx] ?? '';

            // Skip jika semua kosong
            if ($tugas === '' && $uts === '' && $uas === '') continue;

            // Validasi rentang nilai
            $tugas = ($tugas !== '') ? (float) $tugas : null;
            $uts = ($uts !== '') ? (float) $uts : null;
            $uas = ($uas !== '') ? (float) $uas : null;

            if ($tugas !== null && ($tugas < 0 || $tugas > 100)) { $errors[] = "Nilai tugas harus 0-100."; continue; }
            if ($uts !== null && ($uts < 0 || $uts > 100)) { $errors[] = "Nilai UTS harus 0-100."; continue; }
            if ($uas !== null && ($uas < 0 || $uas > 100)) { $errors[] = "Nilai UAS harus 0-100."; continue; }

            // Hitung nilai akhir jika semua komponen terisi
            $nilai_akhir = null;
            $nilai_huruf = null;
            $bobot = null;

            if ($tugas !== null && $uts !== null && $uas !== null) {
                $nilai_akhir = hitungNilaiAkhir($tugas, $uts, $uas);
                $nilai_huruf = getNilaiHuruf($nilai_akhir);
                $bobot = getBobotNilai($nilai_akhir);
            }

            // Cek apakah sudah ada nilai
            $stmt = $pdo->prepare("SELECT id_nilai FROM nilai WHERE id_detail_krs = ?");
            $stmt->execute([$id_detail]);
            $existing = $stmt->fetch();

            if ($existing) {
                // Update nilai
                $stmt = $pdo->prepare("UPDATE nilai SET nilai_tugas = ?, nilai_uts = ?, nilai_uas = ?, 
                                       nilai_akhir = ?, nilai_huruf = ?, bobot = ? WHERE id_detail_krs = ?");
                $stmt->execute([$tugas, $uts, $uas, $nilai_akhir, $nilai_huruf, $bobot, $id_detail]);
            } else {
                // Insert nilai baru
                $stmt = $pdo->prepare("INSERT INTO nilai (id_detail_krs, nilai_tugas, nilai_uts, nilai_uas, 
                                       nilai_akhir, nilai_huruf, bobot) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$id_detail, $tugas, $uts, $uas, $nilai_akhir, $nilai_huruf, $bobot]);
            }
            $saved++;
        }

        $pdo->commit();

        if (!empty($errors)) {
            setFlashMessage('warning', 'Nilai disimpan dengan peringatan: ' . implode(' ', $errors));
        } else {
            setFlashMessage('success', $saved . ' nilai mahasiswa berhasil disimpan.');
        }
    } catch (Exception $ex) {
        $pdo->rollBack();
        setFlashMessage('danger', 'Gagal menyimpan nilai: ' . $ex->getMessage());
    }

    redirect(BASE_URL . '/dosen/nilai.php?jadwal=' . $id_jadwal_post);
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-edit"></i> Input Nilai</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dosen/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Input Nilai</li>
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

            <!-- Pilih Mata Kuliah -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-book"></i> Pilih Mata Kuliah</h3>
                </div>
                <div class="card-body">
                    <form action="" method="GET" class="form-inline">
                        <select name="jadwal" class="form-control mr-2" onchange="this.form.submit()" style="max-width: 500px;">
                            <option value="">-- Pilih Mata Kuliah --</option>
                            <?php foreach ($jadwal_options as $jo): ?>
                                <option value="<?= $jo['id_jadwal'] ?>" <?= $id_jadwal == $jo['id_jadwal'] ? 'selected' : '' ?>>
                                    <?= e($jo['kode_mata_kuliah']) ?> - <?= e($jo['nama_mata_kuliah']) ?> 
                                    (Kelas <?= e($jo['kelas']) ?>) | <?= e($jo['tahun_akademik']) ?> <?= e($jo['smt']) ?>
                                    <?= $jo['ta_status'] === 'aktif' ? ' ★' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            </div>

            <?php if ($jadwal): ?>
                <!-- Info Mata Kuliah -->
                <div class="callout callout-info">
                    <h5><?= e($jadwal['kode_mata_kuliah']) ?> - <?= e($jadwal['nama_mata_kuliah']) ?> (<?= e($jadwal['sks']) ?> SKS)</h5>
                    <p class="mb-0">
                        Kelas <?= e($jadwal['kelas']) ?> | <?= e($jadwal['hari']) ?> <?= e(substr($jadwal['jam_mulai'], 0, 5)) ?>-<?= e(substr($jadwal['jam_selesai'], 0, 5)) ?> | 
                        <?= e($jadwal['tahun_akademik']) ?> <?= e($jadwal['smt']) ?>
                    </p>
                </div>

                <!-- Info Rumus -->
                <div class="alert alert-light border">
                    <strong><i class="fas fa-calculator"></i> Rumus:</strong>
                    Nilai Akhir = (30% × Tugas) + (30% × UTS) + (40% × UAS)
                    &nbsp;|&nbsp; Rentang: 0-100
                </div>

                <!-- Form Input Nilai -->
                <?php if (empty($mahasiswa_list)): ?>
                    <div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Belum ada mahasiswa yang mengambil mata kuliah ini.</div>
                <?php else: ?>
                    <form action="" method="POST">
                        <?= csrfField() ?>
                        <input type="hidden" name="id_jadwal" value="<?= $id_jadwal ?>">
                        <input type="hidden" name="simpan_nilai" value="1">

                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-list"></i> Daftar Nilai (<?= count($mahasiswa_list) ?> mahasiswa)</h3>
                            </div>
                            <div class="card-body table-responsive p-0">
                                <table class="table table-hover table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>No</th>
                                            <th>NIM</th>
                                            <th>Nama Mahasiswa</th>
                                            <th style="width:100px">Tugas (30%)</th>
                                            <th style="width:100px">UTS (30%)</th>
                                            <th style="width:100px">UAS (40%)</th>
                                            <th>Akhir</th>
                                            <th>Huruf</th>
                                            <th>Bobot</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($mahasiswa_list as $i => $m): ?>
                                            <tr>
                                                <td><?= $i + 1 ?></td>
                                                <td><span class="badge badge-primary"><?= e($m['nim']) ?></span></td>
                                                <td><?= e($m['nama_mahasiswa']) ?></td>
                                                <td>
                                                    <input type="hidden" name="id_detail_krs[]" value="<?= $m['id_detail_krs'] ?>">
                                                    <input type="number" name="nilai_tugas[]" class="form-control form-control-sm nilai-input" 
                                                           min="0" max="100" step="0.01" data-row="<?= $i ?>"
                                                           value="<?= $m['nilai_tugas'] !== null ? e($m['nilai_tugas']) : '' ?>" 
                                                           placeholder="0-100">
                                                </td>
                                                <td>
                                                    <input type="number" name="nilai_uts[]" class="form-control form-control-sm nilai-input" 
                                                           min="0" max="100" step="0.01" data-row="<?= $i ?>"
                                                           value="<?= $m['nilai_uts'] !== null ? e($m['nilai_uts']) : '' ?>" 
                                                           placeholder="0-100">
                                                </td>
                                                <td>
                                                    <input type="number" name="nilai_uas[]" class="form-control form-control-sm nilai-input" 
                                                           min="0" max="100" step="0.01" data-row="<?= $i ?>"
                                                           value="<?= $m['nilai_uas'] !== null ? e($m['nilai_uas']) : '' ?>" 
                                                           placeholder="0-100">
                                                </td>
                                                <td class="font-weight-bold nilai-akhir" id="akhir-<?= $i ?>">
                                                    <?= $m['nilai_akhir'] !== null ? e($m['nilai_akhir']) : '-' ?>
                                                </td>
                                                <td class="nilai-huruf" id="huruf-<?= $i ?>">
                                                    <?php if ($m['nilai_huruf']): ?>
                                                        <span class="badge badge-<?= ($m['bobot'] ?? 0) >= 3 ? 'success' : (($m['bobot'] ?? 0) >= 2 ? 'warning' : 'danger') ?>">
                                                            <?= e($m['nilai_huruf']) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                                <td class="nilai-bobot" id="bobot-<?= $i ?>">
                                                    <?= $m['bobot'] !== null ? e($m['bobot']) : '-' ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary" onclick="return confirm('Simpan semua nilai?')">
                                    <i class="fas fa-save"></i> Simpan Semua Nilai
                                </button>
                                <a href="<?= BASE_URL ?>/dosen/mata-kuliah.php" class="btn btn-secondary ml-2">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            <?php endif; ?>

<!-- Script perhitungan otomatis (preview di browser) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.nilai-input').forEach(function(input) {
        input.addEventListener('input', function() {
            var row = this.dataset.row;
            var inputs = document.querySelectorAll('.nilai-input[data-row="' + row + '"]');
            var tugas = parseFloat(inputs[0].value);
            var uts = parseFloat(inputs[1].value);
            var uas = parseFloat(inputs[2].value);

            if (!isNaN(tugas) && !isNaN(uts) && !isNaN(uas)) {
                var akhir = (tugas * 0.3) + (uts * 0.3) + (uas * 0.4);
                akhir = Math.round(akhir * 100) / 100;

                var huruf, bobot, badgeClass;
                if (akhir >= 85) { huruf = 'A'; bobot = 4.00; badgeClass = 'success'; }
                else if (akhir >= 80) { huruf = 'A-'; bobot = 3.75; badgeClass = 'success'; }
                else if (akhir >= 75) { huruf = 'B+'; bobot = 3.50; badgeClass = 'success'; }
                else if (akhir >= 70) { huruf = 'B'; bobot = 3.00; badgeClass = 'success'; }
                else if (akhir >= 65) { huruf = 'B-'; bobot = 2.75; badgeClass = 'warning'; }
                else if (akhir >= 60) { huruf = 'C+'; bobot = 2.50; badgeClass = 'warning'; }
                else if (akhir >= 55) { huruf = 'C'; bobot = 2.00; badgeClass = 'warning'; }
                else if (akhir >= 40) { huruf = 'D'; bobot = 1.00; badgeClass = 'danger'; }
                else { huruf = 'E'; bobot = 0.00; badgeClass = 'danger'; }

                document.getElementById('akhir-' + row).textContent = akhir.toFixed(2);
                document.getElementById('huruf-' + row).innerHTML = '<span class="badge badge-' + badgeClass + '">' + huruf + '</span>';
                document.getElementById('bobot-' + row).textContent = bobot.toFixed(2);
            } else {
                document.getElementById('akhir-' + row).textContent = '-';
                document.getElementById('huruf-' + row).textContent = '-';
                document.getElementById('bobot-' + row).textContent = '-';
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
