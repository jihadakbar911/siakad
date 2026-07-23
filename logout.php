<?php
/**
 * Logout
 * Menghapus session dan redirect ke halaman login
 */
require_once __DIR__ . '/config/auth.php';

logout();
setFlashMessage('info', 'Anda telah berhasil logout.');
redirect(BASE_URL . '/login.php');
