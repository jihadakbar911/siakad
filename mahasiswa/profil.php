<?php
/**
 * Mahasiswa - Profil Saya
 * Menampilkan data diri mahasiswa
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('mahasiswa');

$pdo = getConnection();
$page_title = 'Profil Saya';
$current_page = 'profil';
$id_mahasiswa = $_SESSION['id_mahasiswa'];

$stmt = $pdo->prepare("SELECT m.*, u.username, u.status, u.created_at 
                        FROM mahasiswa m 
                        JOIN users u ON u.id_user = m.id_user 
                        WHERE m.id_mahasiswa = ?");
$stmt->execute([$id_mahasiswa]);
$mhs = $stmt->fetch();

// Hitung IPK
$ipk_data = hitungIPK($pdo, $id_mahasiswa);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-id-card"></i> Profil Saya</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/mahasiswa/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Profil</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Kartu Profil -->
                <div class="col-md-4">
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile text-center">
                            <div class="mb-3">
                                <i class="fas fa-user-circle fa-5x text-primary"></i>
                            </div>
                            <h3 class="profile-username"><?= e($mhs['nama_mahasiswa']) ?></h3>
                            <p class="text-muted"><?= e($mhs['nim']) ?></p>
                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b>Program Studi</b> <span class="float-right"><?= e($mhs['program_studi']) ?></span>
                                </li>
                                <li class="list-group-item">
                                    <b>Angkatan</b> <span class="float-right"><?= e($mhs['angkatan']) ?></span>
                                </li>
                                <li class="list-group-item">
                                    <b>IPK</b> <span class="float-right"><strong><?= number_format($ipk_data['ipk'], 2) ?></strong></span>
                                </li>
                                <li class="list-group-item">
                                    <b>Total SKS</b> <span class="float-right"><?= $ipk_data['total_sks'] ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Detail Data -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-info-circle"></i> Data Lengkap</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th style="width: 200px;">NIM</th>
                                    <td><?= e($mhs['nim']) ?></td>
                                </tr>
                                <tr>
                                    <th>Nama Lengkap</th>
                                    <td><?= e($mhs['nama_mahasiswa']) ?></td>
                                </tr>
                                <tr>
                                    <th>Jenis Kelamin</th>
                                    <td><?= $mhs['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                                </tr>
                                <tr>
                                    <th>Tanggal Lahir</th>
                                    <td><?= $mhs['tanggal_lahir'] ? formatTanggal($mhs['tanggal_lahir']) : '-' ?></td>
                                </tr>
                                <tr>
                                    <th>Alamat</th>
                                    <td><?= e($mhs['alamat'] ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td><?= e($mhs['email'] ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <th>No. Telepon</th>
                                    <td><?= e($mhs['no_telepon'] ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <th>Program Studi</th>
                                    <td><?= e($mhs['program_studi']) ?></td>
                                </tr>
                                <tr>
                                    <th>Angkatan</th>
                                    <td><?= e($mhs['angkatan']) ?></td>
                                </tr>
                                <tr>
                                    <th>Username</th>
                                    <td><?= e($mhs['username']) ?></td>
                                </tr>
                                <tr>
                                    <th>Status Akun</th>
                                    <td><span class="badge badge-<?= $mhs['status'] === 'aktif' ? 'success' : 'danger' ?>"><?= e(ucfirst($mhs['status'])) ?></span></td>
                                </tr>
                                <tr>
                                    <th>Terdaftar Sejak</th>
                                    <td><?= e(formatTanggal($mhs['created_at'])) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
