<?php
/**
 * Login Page
 * Halaman login untuk semua role (Admin, Dosen, Mahasiswa)
 */
require_once __DIR__ . '/config/auth.php';

// Jika sudah login, redirect ke dashboard
if (isLoggedIn()) {
    redirectToDashboard();
}

$error = '';

// Proses form login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validasi input kosong
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi.';
    } else {
        $result = login($username, $password);

        if ($result['success']) {
            setFlashMessage('success', 'Selamat datang, ' . e(getCurrentUserName()) . '!');
            redirectToDashboard();
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login Sistem Informasi Akademik">
    <title>Login - SIAKAD</title>

    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <style>
        .login-page {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .login-box {
            width: 400px;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        .card-header {
            border-radius: 12px 12px 0 0 !important;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            padding: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .input-group-text {
            background-color: #f8f9fa;
            border-right: none;
        }
        .form-control {
            border-left: none;
            border-radius: 0 8px 8px 0 !important;
        }
        .input-group-text {
            border-radius: 8px 0 0 8px !important;
        }
        .login-logo b {
            color: #fff;
            font-size: 1.5rem;
        }
        .login-logo p {
            color: rgba(255,255,255,0.8);
            font-size: 0.9rem;
            margin-top: 5px;
        }
    </style>
</head>
<body class="hold-transition login-page">
    <div class="login-box">
        <!-- Logo -->
        <div class="login-logo text-center mb-3">
            <b><i class="fas fa-graduation-cap"></i> SIAKAD</b>
            <p>Sistem Informasi Akademik</p>
        </div>

        <!-- Card Login -->
        <div class="card">
            <div class="card-header text-center">
                <h4 class="text-white mb-0"><i class="fas fa-sign-in-alt"></i> Login</h4>
            </div>
            <div class="card-body p-4">
                <!-- Pesan error -->
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?= e($error) ?>
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Flash message -->
                <?php $flash = getFlashMessage(); ?>
                <?php if ($flash): ?>
                    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
                        <?= e($flash['message']) ?>
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" id="loginForm">
                    <!-- Username -->
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                        </div>
                        <input type="text" name="username" id="username" class="form-control" 
                               placeholder="Username" value="<?= e($username ?? '') ?>" 
                               required autofocus>
                    </div>

                    <!-- Password -->
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        </div>
                        <input type="password" name="password" id="password" class="form-control" 
                               placeholder="Password" required>
                    </div>

                    <!-- Tombol Login -->
                    <button type="submit" id="btnLogin" class="btn btn-primary btn-block">
                        <i class="fas fa-sign-in-alt"></i> Masuk
                    </button>
                </form>
            </div>

            <div class="card-footer text-center text-muted py-3">
                <small>&copy; <?= date('Y') ?> SIAKAD - Sistem Informasi Akademik</small>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
