<?php
/**
 * Admin - Tambah Jadwal Kuliah
 */
require_once __DIR__ . '/../../config/auth.php';
requireRole('admin');

$pdo = getConnection();
$page_title = 'Tambah Jadwal Kuliah';
$current_page = 'jadwal';
$errors = [];
$old = [];

// Data untuk dropdown
$mk_list = $pdo->query("SELECT * FROM mata_kuliah ORDER BY kode_mata_kuliah")->fetchAll();
$dosen_list = $pdo->query("SELECT * FROM dosen ORDER BY nama_dosen")->fetchAll();
$ta_list = $pdo->query("SELECT * FROM tahun_akademik ORDER BY tahun_akademik DESC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) { $errors[] = 'Token tidak valid.'; }

    $id_mk = (int) ($_POST['id_mata_kuliah'] ?? 0);
    $id_dosen = (int) ($_POST['id_dosen'] ?? 0);
    $id_ta = (int) ($_POST['id_tahun_akademik'] ?? 0);
    $kelas = trim($_POST['kelas'] ?? '');
    $hari = $_POST['hari'] ?? '';
    $jam_mulai = $_POST['jam_mulai'] ?? '';
    $jam_selesai = $_POST['jam_selesai'] ?? '';
    $ruangan = trim($_POST['ruangan'] ?? '');
    $kuota = (int) ($_POST['kuota'] ?? 40);
    $old = $_POST;

    if ($id_mk <= 0) $errors[] = 'Mata kuliah wajib dipilih.';
    if ($id_dosen <= 0) $errors[] = 'Dosen wajib dipilih.';
    if ($id_ta <= 0) $errors[] = 'Tahun akademik wajib dipilih.';
    if (empty($kelas)) $errors[] = 'Kelas wajib diisi.';
    if (empty($hari)) $errors[] = 'Hari wajib dipilih.';
    if (empty($jam_mulai) || empty($jam_selesai)) $errors[] = 'Jam mulai dan selesai wajib diisi.';
    if (empty($ruangan)) $errors[] = 'Ruangan wajib diisi.';

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO jadwal_kuliah (id_mata_kuliah, id_dosen, id_tahun_akademik, kelas, hari, jam_mulai, jam_selesai, ruangan, kuota) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id_mk, $id_dosen, $id_ta, $kelas, $hari, $jam_mulai, $jam_selesai, $ruangan, $kuota]);
            setFlashMessage('success', 'Jadwal kuliah berhasil ditambahkan.');
            redirect(BASE_URL . '/admin/jadwal/');
        } catch (Exception $ex) {
            $errors[] = 'Gagal menyimpan: ' . $ex->getMessage();
        }
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
                <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-plus-circle"></i> Tambah Jadwal Kuliah</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/jadwal/">Jadwal</a></li>
                        <li class="breadcrumb-item active">Tambah</li>
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
                <div class="card-header"><h3 class="card-title">Form Jadwal Kuliah</h3></div>
                <div class="card-body">
                    <form action="" method="POST">
                        <?= csrfField() ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="id_mata_kuliah">Mata Kuliah <span class="text-danger">*</span></label>
                                    <select name="id_mata_kuliah" id="id_mata_kuliah" class="form-control" required>
                                        <option value="">-- Pilih Mata Kuliah --</option>
                                        <?php foreach ($mk_list as $mk): ?>
                                            <option value="<?= $mk['id_mata_kuliah'] ?>" <?= ($old['id_mata_kuliah'] ?? '') == $mk['id_mata_kuliah'] ? 'selected' : '' ?>>
                                                <?= e($mk['kode_mata_kuliah']) ?> - <?= e($mk['nama_mata_kuliah']) ?> (<?= $mk['sks'] ?> SKS)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="id_dosen">Dosen Pengampu <span class="text-danger">*</span></label>
                                    <select name="id_dosen" id="id_dosen" class="form-control" required>
                                        <option value="">-- Pilih Dosen --</option>
                                        <?php foreach ($dosen_list as $dsn): ?>
                                            <option value="<?= $dsn['id_dosen'] ?>" <?= ($old['id_dosen'] ?? '') == $dsn['id_dosen'] ? 'selected' : '' ?>>
                                                <?= e($dsn['nama_dosen']) ?> (<?= e($dsn['nidn']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="id_tahun_akademik">Tahun Akademik <span class="text-danger">*</span></label>
                                    <select name="id_tahun_akademik" id="id_tahun_akademik" class="form-control" required>
                                        <option value="">-- Pilih --</option>
                                        <?php foreach ($ta_list as $ta): ?>
                                            <option value="<?= $ta['id_tahun_akademik'] ?>" <?= ($old['id_tahun_akademik'] ?? '') == $ta['id_tahun_akademik'] ? 'selected' : '' ?>>
                                                <?= e($ta['tahun_akademik']) ?> - <?= e($ta['semester']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="kelas">Kelas <span class="text-danger">*</span></label>
                                    <input type="text" name="kelas" id="kelas" class="form-control" placeholder="A, B, C..." value="<?= e($old['kelas'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="hari">Hari <span class="text-danger">*</span></label>
                                    <select name="hari" id="hari" class="form-control" required>
                                        <option value="">-- Pilih --</option>
                                        <?php foreach (['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $h): ?>
                                            <option value="<?= $h ?>" <?= ($old['hari'] ?? '') === $h ? 'selected' : '' ?>><?= $h ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="jam_mulai">Jam Mulai <span class="text-danger">*</span></label>
                                    <input type="time" name="jam_mulai" id="jam_mulai" class="form-control" value="<?= e($old['jam_mulai'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="jam_selesai">Jam Selesai <span class="text-danger">*</span></label>
                                    <input type="time" name="jam_selesai" id="jam_selesai" class="form-control" value="<?= e($old['jam_selesai'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="ruangan">Ruangan <span class="text-danger">*</span></label>
                                    <input type="text" name="ruangan" id="ruangan" class="form-control" value="<?= e($old['ruangan'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="kuota">Kuota</label>
                                    <input type="number" name="kuota" id="kuota" class="form-control" min="1" value="<?= e($old['kuota'] ?? '40') ?>">
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                        <a href="<?= BASE_URL ?>/admin/jadwal/" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                    </form>
                </div>
            </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
