<?php
/**
 * Admin - Edit Dosen
 */
require_once __DIR__ . '/../../config/auth.php';
requireRole('admin');

$pdo = getConnection();
$page_title = 'Edit Dosen';
$current_page = 'dosen';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) { setFlashMessage('danger', 'ID dosen tidak valid.'); redirect(BASE_URL . '/admin/dosen/'); }

$stmt = $pdo->prepare("SELECT d.*, u.username, u.status FROM dosen d JOIN users u ON u.id_user = d.id_user WHERE d.id_dosen = ?");
$stmt->execute([$id]);
$dsn = $stmt->fetch();

if (!$dsn) { setFlashMessage('danger', 'Data dosen tidak ditemukan.'); redirect(BASE_URL . '/admin/dosen/'); }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) { $errors[] = 'Token keamanan tidak valid.'; }

    $nama = trim($_POST['nama_dosen'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telepon = trim($_POST['no_telepon'] ?? '');
    $status = $_POST['status'] ?? 'aktif';
    $new_password = $_POST['new_password'] ?? '';

    if (empty($nama)) $errors[] = 'Nama dosen wajib diisi.';

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE dosen SET nama_dosen = ?, email = ?, no_telepon = ? WHERE id_dosen = ?");
            $stmt->execute([$nama, $email, $telepon, $id]);

            $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id_user = ?");
            $stmt->execute([$status, $dsn['id_user']]);

            if (!empty($new_password)) {
                if (strlen($new_password) < 6) throw new Exception('Password minimal 6 karakter.');
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id_user = ?");
                $stmt->execute([password_hash($new_password, PASSWORD_DEFAULT), $dsn['id_user']]);
            }

            $pdo->commit();
            setFlashMessage('success', 'Data dosen "' . $nama . '" berhasil diperbarui.');
            redirect(BASE_URL . '/admin/dosen/');
        } catch (Exception $ex) {
            $pdo->rollBack();
            $errors[] = $ex->getMessage();
        }
    }
    $dsn['nama_dosen'] = $nama; $dsn['email'] = $email; $dsn['no_telepon'] = $telepon; $dsn['status'] = $status;
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-user-edit"></i> Edit Dosen</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dosen/">Dosen</a></li>
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
                                            <label>NIDN</label>
                                            <input type="text" class="form-control" value="<?= e($dsn['nidn']) ?>" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nama_dosen">Nama Lengkap <span class="text-danger">*</span></label>
                                            <input type="text" name="nama_dosen" id="nama_dosen" class="form-control" value="<?= e($dsn['nama_dosen']) ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input type="email" name="email" id="email" class="form-control" value="<?= e($dsn['email'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="no_telepon">No. Telepon</label>
                                            <input type="text" name="no_telepon" id="no_telepon" class="form-control" value="<?= e($dsn['no_telepon'] ?? '') ?>">
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
                                    <label>Username</label>
                                    <input type="text" class="form-control" value="<?= e($dsn['username']) ?>" disabled>
                                </div>
                                <div class="form-group">
                                    <label for="new_password">Password Baru</label>
                                    <input type="password" name="new_password" id="new_password" class="form-control" minlength="6">
                                    <small class="text-muted">Kosongkan jika tidak ingin mengubah</small>
                                </div>
                                <div class="form-group">
                                    <label for="status">Status Akun</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="aktif" <?= $dsn['status'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                        <option value="nonaktif" <?= $dsn['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block mb-2"><i class="fas fa-save"></i> Simpan Perubahan</button>
                        <a href="<?= BASE_URL ?>/admin/dosen/" class="btn btn-secondary btn-block"><i class="fas fa-arrow-left"></i> Kembali</a>
                    </div>
                </div>
            </form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
