<?php
/**
 * Sidebar - Menu Navigasi Samping
 * Menu berbeda berdasarkan role pengguna (Admin, Dosen, Mahasiswa)
 */
$role = getCurrentRole();
?>
<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?= BASE_URL ?>" class="brand-link text-center">
        <i class="fas fa-graduation-cap brand-image" style="font-size: 1.5rem; margin-top: 3px;"></i>
        <span class="brand-text font-weight-bold">SIAKAD</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- User Panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <i class="fas fa-user-circle fa-2x text-light" style="margin-top: 2px;"></i>
            </div>
            <div class="info">
                <a href="#" class="d-block text-white"><?= e(getCurrentUserName() ?? 'User') ?></a>
                <small class="text-muted"><?= e(ucfirst($role ?? '')) ?></small>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                <?php if ($role === 'admin'): ?>
                <!-- ====== MENU ADMIN ====== -->
                <li class="nav-header">MENU UTAMA</li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/admin/dashboard.php" class="nav-link <?= ($current_page ?? '') === 'dashboard' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <li class="nav-header">KELOLA DATA</li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/admin/mahasiswa/" class="nav-link <?= ($current_page ?? '') === 'mahasiswa' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-user-graduate"></i>
                        <p>Data Mahasiswa</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/admin/dosen/" class="nav-link <?= ($current_page ?? '') === 'dosen' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-chalkboard-teacher"></i>
                        <p>Data Dosen</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/admin/mata-kuliah/" class="nav-link <?= ($current_page ?? '') === 'mata-kuliah' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-book"></i>
                        <p>Mata Kuliah</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/admin/tahun-akademik/" class="nav-link <?= ($current_page ?? '') === 'tahun-akademik' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-calendar-alt"></i>
                        <p>Tahun Akademik</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/admin/jadwal/" class="nav-link <?= ($current_page ?? '') === 'jadwal' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-clock"></i>
                        <p>Jadwal Kuliah</p>
                    </a>
                </li>

                <li class="nav-header">AKADEMIK</li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/admin/krs/" class="nav-link <?= ($current_page ?? '') === 'krs' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-clipboard-list"></i>
                        <p>Data KRS</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/admin/nilai/" class="nav-link <?= ($current_page ?? '') === 'nilai' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-star"></i>
                        <p>Data Nilai</p>
                    </a>
                </li>

                <?php elseif ($role === 'dosen'): ?>
                <!-- ====== MENU DOSEN ====== -->
                <li class="nav-header">MENU UTAMA</li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/dosen/dashboard.php" class="nav-link <?= ($current_page ?? '') === 'dashboard' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <li class="nav-header">AKADEMIK</li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/dosen/mata-kuliah.php" class="nav-link <?= ($current_page ?? '') === 'mata-kuliah' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-book"></i>
                        <p>Mata Kuliah Diampu</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/dosen/mahasiswa.php" class="nav-link <?= ($current_page ?? '') === 'mahasiswa' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Daftar Mahasiswa</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/dosen/nilai.php" class="nav-link <?= ($current_page ?? '') === 'nilai' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-edit"></i>
                        <p>Input Nilai</p>
                    </a>
                </li>

                <?php elseif ($role === 'mahasiswa'): ?>
                <!-- ====== MENU MAHASISWA ====== -->
                <li class="nav-header">MENU UTAMA</li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/mahasiswa/dashboard.php" class="nav-link <?= ($current_page ?? '') === 'dashboard' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/mahasiswa/profil.php" class="nav-link <?= ($current_page ?? '') === 'profil' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-id-card"></i>
                        <p>Profil Saya</p>
                    </a>
                </li>

                <li class="nav-header">AKADEMIK</li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/mahasiswa/isi-krs.php" class="nav-link <?= ($current_page ?? '') === 'isi-krs' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-plus-circle"></i>
                        <p>Isi KRS</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/mahasiswa/krs.php" class="nav-link <?= ($current_page ?? '') === 'krs' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-clipboard-list"></i>
                        <p>KRS Saya</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/mahasiswa/khs.php" class="nav-link <?= ($current_page ?? '') === 'khs' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-file-alt"></i>
                        <p>Kartu Hasil Studi</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/mahasiswa/ipk.php" class="nav-link <?= ($current_page ?? '') === 'ipk' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-chart-line"></i>
                        <p>IPS &amp; IPK</p>
                    </a>
                </li>

                <?php endif; ?>

            </ul>
        </nav>
    </div>
</aside>
