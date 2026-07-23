<?php
/**
 * Header - Bagian awal HTML
 * 
 * Memuat tag HTML, CSS dependencies, dan membuka tag body.
 * Variabel yang harus di-set sebelum include:
 * - $page_title (string) - Judul halaman
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SIAKAD - Sistem Informasi Akademik">
    <title><?= e($page_title ?? 'SIAKAD') ?> - SIAKAD</title>

    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome 5 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- AdminLTE 3 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <style>
        /* Custom styles */
        .content-wrapper {
            background-color: #f4f6f9;
        }
        .small-box {
            border-radius: 10px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .small-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .small-box .icon {
            transition: all 0.3s ease;
        }
        .small-box:hover .icon > i {
            font-size: 80px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: box-shadow 0.3s ease;
        }
        .card:hover {
            box-shadow: 0 5px 20px rgba(0,0,0,0.12);
        }
        .main-sidebar {
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .brand-link {
            border-bottom: 1px solid rgba(255,255,255,0.1) !important;
        }
        .nav-sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.15) !important;
            border-radius: 8px;
            margin: 0 8px;
        }
        .nav-sidebar .nav-item {
            margin-bottom: 2px;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .btn {
            border-radius: 6px;
            font-weight: 500;
        }
        .badge {
            border-radius: 6px;
            padding: 5px 10px;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
<div class="wrapper">
