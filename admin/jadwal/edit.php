<?php
/**
 * Admin - Edit Jadwal Kuliah
 */
require_once __DIR__ . '/../../config/auth.php';
requireRole('admin');

$pdo = getConnection();
$page_title = 'Edit Jadwal Kuliah';
$current_page = 'jadwal';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) { setFlashMessage('danger', 'ID tidak valid.'); redirect(BASE_URL . '/admin/jadwal/'); }

$stmt = $pdo->prepare("SELECT * FROM jadwal_kuliah WHERE id_jadwal = ?");
$stmt->execute([$id]);
$j = $stmt->fetch();
if (!$j) { setFlashMessage('danger', 'Data tidak ditemukan.'); redirect(BASE_URL . '/admin/jadwal/'); }

$mk_list = $pdo->query("SELECT * FROM mata_kuliah ORDER BY kode_mata_kuliah")->fetchAll();
$dosen_list = $pdo->query("SELECT * FROM dosen ORDER BY nama_dosen")->fetchAll();
$ta_list = $pdo->query("SELECT * FROM tahun_akademik ORDER BY tahun_akademik DESC")->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) { $errors[] = 'Token tidak valid.'; }

    $j['id_mata_kuliah'] = (int) ($_POST['id_mata_kuliah'] ?? 0);
    $j['id_dosen'] = (int) ($_POST['id_dosen'] ?? 0);
    $j['id_tahun_akademik'] = (int) ($_POST['id_tahun_akademik'] ?? 0);
    $j['kelas'] = trim($_POST['kelas'] ?? '');
    $j['hari'] = $_POST['hari'] ?? '';
    $j['jam_mulai'] = $_POST['jam_mulai'] ?? '';
    $j['jam_selesai'] = $_POST['jam_selesai'] ?? '';
    $j['ruangan'] = trim($_POST['ruangan'] ?? '');
    $j['kuota'] = (int) ($_POST['kuota'] ?? 40);

    if ($j['id_mata_kuliah'] <= 0) $errors[] = 'Mata kuliah wajib dipilih.';
    if ($j['id_dosen'] <= 0) $errors[] = 'Dosen wajib dipilih.';
    if (empty($j['kelas'])) $errors[] = 'Kelas wajib diisi.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE jadwal_kuliah SET id_mata_kuliah=?, id_dosen=?, id_tahun_akademik=?, kelas=?, hari=?, jam_mulai=?, jam_selesai=?, ruangan=?, kuota=? WHERE id_jadwal=?");
        $stmt->execute([$j['id_mata_kuliah'], $j['id_dosen'], $j['id_tahun_akademik'], $j['kelas'], $j['hari'], $j['jam_mulai'], $j['jam_selesai'], $j['ruangan'], $j['kuota'], $id]);
        setFlashMessage('success', 'Jadwal berhasil diperbarui.');
        redirect(BASE_URL . '/admin/jadwal/');
    }
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-edit"></i> Edit Jadwal Kuliah</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/jadwal/">Jadwal</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Edit Jadwal Kuliah</h3></div>
                <div class="card-body">
                    <form action="" method="POST">
                        <?= csrfField() ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="id_mata_kuliah">Mata Kuliah <span class="text-danger">*</span></label>
                                    <select name="id_mata_kuliah" id="id_mata_kuliah" class="form-control" required>
                                        <?php foreach ($mk_list as $mk): ?>
                                            <option value="<?= $mk['id_mata_kuliah'] ?>" <?= $j['id_mata_kuliah'] == $mk['id_mata_kuliah'] ? 'selected' : '' ?>>
                                                <?= e($mk['kode_mata_kuliah']) ?> - <?= e($mk['nama_mata_kuliah']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="id_dosen">Dosen Pengampu <span class="text-danger">*</span></label>
                                    <select name="id_dosen" id="id_dosen" class="form-control" required>
                                        <?php foreach ($dosen_list as $dsn): ?>
                                            <option value="<?= $dsn['id_dosen'] ?>" <?= $j['id_dosen'] == $dsn['id_dosen'] ? 'selected' : '' ?>>
                                                <?= e($dsn['nama_dosen']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="id_tahun_akademik">Tahun Akademik</label>
                                    <select name="id_tahun_akademik" id="id_tahun_akademik" class="form-control" required>
                                        <?php foreach ($ta_list as $ta): ?>
                                            <option value="<?= $ta['id_tahun_akademik'] ?>" <?= $j['id_tahun_akademik'] == $ta['id_tahun_akademik'] ? 'selected' : '' ?>>
                                                <?= e($ta['tahun_akademik']) ?> - <?= e($ta['semester']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="kelas">Kelas</label>
                                    <input type="text" name="kelas" id="kelas" class="form-control" value="<?= e($j['kelas']) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="hari">Hari</label>
                                    <select name="hari" id="hari" class="form-control" required>
                                        <?php foreach (['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $h): ?>
                                            <option value="<?= $h ?>" <?= $j['hari'] === $h ? 'selected' : '' ?>><?= $h ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="jam_mulai">Jam Mulai</label>
                                    <input type="time" name="jam_mulai" id="jam_mulai" class="form-control" value="<?= e(substr($j['jam_mulai'], 0, 5)) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="jam_selesai">Jam Selesai</label>
                                    <input type="time" name="jam_selesai" id="jam_selesai" class="form-control" value="<?= e(substr($j['jam_selesai'], 0, 5)) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="ruangan">Ruangan</label>
                                    <input type="text" name="ruangan" id="ruangan" class="form-control" value="<?= e($j['ruangan']) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="kuota">Kuota</label>
                                    <input type="number" name="kuota" id="kuota" class="form-control" value="<?= e($j['kuota']) ?>">
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                        <a href="<?= BASE_URL ?>/admin/jadwal/" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                    </form>
                </div>
            </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
