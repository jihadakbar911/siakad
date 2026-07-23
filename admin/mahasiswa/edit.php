<?php
/**
 * Admin - Edit Mahasiswa
 * Form untuk mengubah data mahasiswa dan status akun
 */
require_once __DIR__ . '/../../config/auth.php';
requireRole('admin');

$pdo = getConnection();
$page_title = 'Edit Mahasiswa';
$current_page = 'mahasiswa';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    setFlashMessage('danger', 'ID mahasiswa tidak valid.');
    redirect(BASE_URL . '/admin/mahasiswa/');
}

// Ambil data mahasiswa
$stmt = $pdo->prepare("SELECT m.*, u.username, u.status FROM mahasiswa m JOIN users u ON u.id_user = m.id_user WHERE m.id_mahasiswa = ?");
$stmt->execute([$id]);
$mhs = $stmt->fetch();

if (!$mhs) {
    setFlashMessage('danger', 'Data mahasiswa tidak ditemukan.');
    redirect(BASE_URL . '/admin/mahasiswa/');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token keamanan tidak valid.';
    }

    $nama = trim($_POST['nama_mahasiswa'] ?? '');
    $jk = $_POST['jenis_kelamin'] ?? '';
    $tgl_lahir = $_POST['tanggal_lahir'] ?? '';
    $alamat = trim($_POST['alamat'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telepon = trim($_POST['no_telepon'] ?? '');
    $prodi = trim($_POST['program_studi'] ?? '');
    $angkatan = $_POST['angkatan'] ?? '';
    $status = $_POST['status'] ?? 'aktif';
    $new_password = $_POST['new_password'] ?? '';

    if (empty($nama)) $errors[] = 'Nama mahasiswa wajib diisi.';
    if (!in_array($jk, ['L', 'P'])) $errors[] = 'Jenis kelamin tidak valid.';
    if (empty($prodi)) $errors[] = 'Program studi wajib diisi.';
    if (empty($angkatan)) $errors[] = 'Angkatan wajib diisi.';

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Update mahasiswa
            $stmt = $pdo->prepare("UPDATE mahasiswa SET nama_mahasiswa = ?, jenis_kelamin = ?, tanggal_lahir = ?, alamat = ?, email = ?, no_telepon = ?, program_studi = ?, angkatan = ? WHERE id_mahasiswa = ?");
            $stmt->execute([$nama, $jk, $tgl_lahir ?: null, $alamat, $email, $telepon, $prodi, $angkatan, $id]);

            // Update status user
            $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id_user = ?");
            $stmt->execute([$status, $mhs['id_user']]);

            // Update password jika diisi
            if (!empty($new_password)) {
                if (strlen($new_password) < 6) {
                    throw new Exception('Password minimal 6 karakter.');
                }
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id_user = ?");
                $stmt->execute([password_hash($new_password, PASSWORD_DEFAULT), $mhs['id_user']]);
            }

            $pdo->commit();
            setFlashMessage('success', 'Data mahasiswa "' . $nama . '" berhasil diperbarui.');
            redirect(BASE_URL . '/admin/mahasiswa/');
        } catch (Exception $ex) {
            $pdo->rollBack();
            $errors[] = $ex->getMessage();
        }
    }

    // Update local data for form re-display
    $mhs['nama_mahasiswa'] = $nama;
    $mhs['jenis_kelamin'] = $jk;
    $mhs['tanggal_lahir'] = $tgl_lahir;
    $mhs['alamat'] = $alamat;
    $mhs['email'] = $email;
    $mhs['no_telepon'] = $telepon;
    $mhs['program_studi'] = $prodi;
    $mhs['angkatan'] = $angkatan;
    $mhs['status'] = $status;
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
                    <h1 class="m-0"><i class="fas fa-user-edit"></i> Edit Mahasiswa</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/mahasiswa/">Mahasiswa</a></li>
                        <li class="breadcrumb-item active">Edit</li>
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
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-id-card"></i> Data Mahasiswa</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>NIM</label>
                                            <input type="text" class="form-control" value="<?= e($mhs['nim']) ?>" disabled>
                                            <small class="text-muted">NIM tidak dapat diubah</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nama_mahasiswa">Nama Lengkap <span class="text-danger">*</span></label>
                                            <input type="text" name="nama_mahasiswa" id="nama_mahasiswa" class="form-control" value="<?= e($mhs['nama_mahasiswa']) ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="jenis_kelamin">Jenis Kelamin <span class="text-danger">*</span></label>
                                            <select name="jenis_kelamin" id="jenis_kelamin" class="form-control" required>
                                                <option value="L" <?= $mhs['jenis_kelamin'] === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                                <option value="P" <?= $mhs['jenis_kelamin'] === 'P' ? 'selected' : '' ?>>Perempuan</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="tanggal_lahir">Tanggal Lahir</label>
                                            <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="form-control" value="<?= e($mhs['tanggal_lahir'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="angkatan">Angkatan <span class="text-danger">*</span></label>
                                            <input type="number" name="angkatan" id="angkatan" class="form-control" min="2000" max="2099" value="<?= e($mhs['angkatan']) ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="program_studi">Program Studi <span class="text-danger">*</span></label>
                                    <input type="text" name="program_studi" id="program_studi" class="form-control" value="<?= e($mhs['program_studi']) ?>" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input type="email" name="email" id="email" class="form-control" value="<?= e($mhs['email'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="no_telepon">No. Telepon</label>
                                            <input type="text" name="no_telepon" id="no_telepon" class="form-control" value="<?= e($mhs['no_telepon'] ?? '') ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="alamat">Alamat</label>
                                    <textarea name="alamat" id="alamat" class="form-control" rows="3"><?= e($mhs['alamat'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-key"></i> Akun Login</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Username</label>
                                    <input type="text" class="form-control" value="<?= e($mhs['username']) ?>" disabled>
                                </div>
                                <div class="form-group">
                                    <label for="new_password">Password Baru</label>
                                    <input type="password" name="new_password" id="new_password" class="form-control" minlength="6">
                                    <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                                </div>
                                <div class="form-group">
                                    <label for="status">Status Akun</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="aktif" <?= $mhs['status'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                        <option value="nonaktif" <?= $mhs['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block mb-2">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                        <a href="<?= BASE_URL ?>/admin/mahasiswa/" class="btn btn-secondary btn-block">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
