<?php
/**
 * Navbar - Navigasi Atas
 * Menampilkan navbar dengan informasi user dan tombol logout
 */
?>
<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Tombol toggle sidebar (kiri) -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                <i class="fas fa-bars"></i>
            </a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="<?= BASE_URL ?>" class="nav-link">
                <i class="fas fa-home"></i> Beranda
            </a>
        </li>
    </ul>

    <!-- Navbar kanan -->
    <ul class="navbar-nav ml-auto">
        <!-- Info user -->
        <li class="nav-item d-none d-sm-inline-block">
            <span class="nav-link">
                <i class="fas fa-user-circle"></i>
                <?= e(getCurrentUserName() ?? 'User') ?>
                <span class="badge badge-info ml-1"><?= e(ucfirst(getCurrentRole() ?? '')) ?></span>
            </span>
        </li>

        <!-- Tombol Logout -->
        <li class="nav-item">
            <a class="nav-link text-danger" href="<?= BASE_URL ?>/logout.php" 
               onclick="return confirm('Apakah Anda yakin ingin logout?')">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>

        <!-- Tombol fullscreen -->
        <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li>
    </ul>
</nav>
<!-- /.navbar -->
