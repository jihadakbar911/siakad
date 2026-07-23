<?php
/**
 * Admin - Tambah Mahasiswa
 * Form untuk menambah data mahasiswa baru beserta akun login
 */
require_once __DIR__ . '/../../config/auth.php';
requireRole('admin');

$pdo = getConnection();
$page_title = 'Tambah Mahasiswa';
$current_page = 'mahasiswa';

$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi CSRF
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token keamanan tidak valid. Silakan coba lagi.';
    }

    // Ambil dan sanitasi input
    $nim = trim($_POST['nim'] ?? '');
    $nama = trim($_POST['nama_mahasiswa'] ?? '');
    $jk = $_POST['jenis_kelamin'] ?? '';
    $tgl_lahir = $_POST['tanggal_lahir'] ?? '';
    $alamat = trim($_POST['alamat'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telepon = trim($_POST['no_telepon'] ?? '');
    $prodi = trim($_POST['program_studi'] ?? '');
    $angkatan = $_POST['angkatan'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $old = $_POST;

    // Validasi input
    if (empty($nim)) $errors[] = 'NIM wajib diisi.';
    if (empty($nama)) $errors[] = 'Nama mahasiswa wajib diisi.';
    if (!in_array($jk, ['L', 'P'])) $errors[] = 'Jenis kelamin tidak valid.';
    if (empty($prodi)) $errors[] = 'Program studi wajib diisi.';
    if (empty($angkatan)) $errors[] = 'Angkatan wajib diisi.';
    if (empty($username)) $errors[] = 'Username wajib diisi.';
    if (empty($password)) $errors[] = 'Password wajib diisi.';
    if (strlen($password) < 6) $errors[] = 'Password minimal 6 karakter.';

    // Cek duplikasi NIM
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM mahasiswa WHERE nim = ?");
        $stmt->execute([$nim]);
        if ($stmt->fetchColumn() > 0) $errors[] = 'NIM sudah terdaftar.';
    }

    // Cek duplikasi username
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) $errors[] = 'Username sudah digunakan.';
    }

    // Simpan data
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Insert user
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, status) VALUES (?, ?, 'mahasiswa', 'aktif')");
            $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
            $id_user = $pdo->lastInsertId();

            // Insert mahasiswa
            $stmt = $pdo->prepare("INSERT INTO mahasiswa (id_user, nim, nama_mahasiswa, jenis_kelamin, tanggal_lahir, alamat, email, no_telepon, program_studi, angkatan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id_user, $nim, $nama, $jk, $tgl_lahir ?: null, $alamat, $email, $telepon, $prodi, $angkatan]);

            $pdo->commit();

            setFlashMessage('success', 'Mahasiswa "' . $nama . '" berhasil ditambahkan.');
            redirect(BASE_URL . '/admin/mahasiswa/');
        } catch (Exception $ex) {
            $pdo->rollBack();
            $errors[] = 'Gagal menyimpan data: ' . $ex->getMessage();
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
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-user-plus"></i> Tambah Mahasiswa</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/mahasiswa/">Mahasiswa</a></li>
                        <li class="breadcrumb-item active">Tambah</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $err): ?>
                            <li><?= e($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <?= csrfField() ?>

                <div class="row">
                    <!-- Data Mahasiswa -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-id-card"></i> Data Mahasiswa</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nim">NIM <span class="text-danger">*</span></label>
                                            <input type="text" name="nim" id="nim" class="form-control" value="<?= e($old['nim'] ?? '') ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nama_mahasiswa">Nama Lengkap <span class="text-danger">*</span></label>
                                            <input type="text" name="nama_mahasiswa" id="nama_mahasiswa" class="form-control" value="<?= e($old['nama_mahasiswa'] ?? '') ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="jenis_kelamin">Jenis Kelamin <span class="text-danger">*</span></label>
                                            <select name="jenis_kelamin" id="jenis_kelamin" class="form-control" required>
                                                <option value="">-- Pilih --</option>
                                                <option value="L" <?= ($old['jenis_kelamin'] ?? '') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                                <option value="P" <?= ($old['jenis_kelamin'] ?? '') === 'P' ? 'selected' : '' ?>>Perempuan</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="tanggal_lahir">Tanggal Lahir</label>
                                            <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="form-control" value="<?= e($old['tanggal_lahir'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="angkatan">Angkatan <span class="text-danger">*</span></label>
                                            <input type="number" name="angkatan" id="angkatan" class="form-control" min="2000" max="2099" value="<?= e($old['angkatan'] ?? date('Y')) ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="program_studi">Program Studi <span class="text-danger">*</span></label>
                                    <input type="text" name="program_studi" id="program_studi" class="form-control" value="<?= e($old['program_studi'] ?? '') ?>" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input type="email" name="email" id="email" class="form-control" value="<?= e($old['email'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="no_telepon">No. Telepon</label>
                                            <input type="text" name="no_telepon" id="no_telepon" class="form-control" value="<?= e($old['no_telepon'] ?? '') ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="alamat">Alamat</label>
                                    <textarea name="alamat" id="alamat" class="form-control" rows="3"><?= e($old['alamat'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Akun Login -->
                    <div class="col-md-4">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-key"></i> Akun Login</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="username">Username <span class="text-danger">*</span></label>
                                    <input type="text" name="username" id="username" class="form-control" value="<?= e($old['username'] ?? '') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="password">Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password" id="password" class="form-control" minlength="6" required>
                                    <small class="text-muted">Minimal 6 karakter</small>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block mb-2">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                        <a href="<?= BASE_URL ?>/admin/mahasiswa/" class="btn btn-secondary btn-block">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
