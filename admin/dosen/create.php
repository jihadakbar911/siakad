<?php
/**
 * Admin - Tambah Dosen
 */
require_once __DIR__ . '/../../config/auth.php';
requireRole('admin');

$pdo = getConnection();
$page_title = 'Tambah Dosen';
$current_page = 'dosen';
$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token keamanan tidak valid.';
    }

    $nidn = trim($_POST['nidn'] ?? '');
    $nama = trim($_POST['nama_dosen'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telepon = trim($_POST['no_telepon'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $old = $_POST;

    if (empty($nidn)) $errors[] = 'NIDN wajib diisi.';
    if (empty($nama)) $errors[] = 'Nama dosen wajib diisi.';
    if (empty($username)) $errors[] = 'Username wajib diisi.';
    if (empty($password)) $errors[] = 'Password wajib diisi.';
    if (strlen($password) < 6) $errors[] = 'Password minimal 6 karakter.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM dosen WHERE nidn = ?");
        $stmt->execute([$nidn]);
        if ($stmt->fetchColumn() > 0) $errors[] = 'NIDN sudah terdaftar.';

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) $errors[] = 'Username sudah digunakan.';
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, status) VALUES (?, ?, 'dosen', 'aktif')");
            $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
            $id_user = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO dosen (id_user, nidn, nama_dosen, email, no_telepon) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$id_user, $nidn, $nama, $email, $telepon]);
            $pdo->commit();

            setFlashMessage('success', 'Dosen "' . $nama . '" berhasil ditambahkan.');
            redirect(BASE_URL . '/admin/dosen/');
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
                <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-user-plus"></i> Tambah Dosen</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dosen/">Dosen</a></li>
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

            <form action="" method="POST">
                <?= csrfField() ?>
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header"><h3 class="card-title"><i class="fas fa-id-card"></i> Data Dosen</h3></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nidn">NIDN <span class="text-danger">*</span></label>
                                            <input type="text" name="nidn" id="nidn" class="form-control" value="<?= e($old['nidn'] ?? '') ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nama_dosen">Nama Lengkap <span class="text-danger">*</span></label>
                                            <input type="text" name="nama_dosen" id="nama_dosen" class="form-control" value="<?= e($old['nama_dosen'] ?? '') ?>" required>
                                        </div>
                                    </div>
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
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-primary">
                            <div class="card-header"><h3 class="card-title"><i class="fas fa-key"></i> Akun Login</h3></div>
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
                        <button type="submit" class="btn btn-primary btn-block mb-2"><i class="fas fa-save"></i> Simpan</button>
                        <a href="<?= BASE_URL ?>/admin/dosen/" class="btn btn-secondary btn-block"><i class="fas fa-arrow-left"></i> Kembali</a>
                    </div>
                </div>
            </form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
