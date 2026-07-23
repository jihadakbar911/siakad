<?php
/**
 * Index - Halaman Utama
 * Redirect pengguna ke dashboard atau login
 */
require_once __DIR__ . '/config/auth.php';

if (isLoggedIn()) {
    redirectToDashboard();
} else {
    redirect(BASE_URL . '/login.php');
}
