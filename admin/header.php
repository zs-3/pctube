<?php
// admin/header.php

require_once __DIR__ . '/auth.php';
checkAdminAuth();

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Tube Admin</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, -apple-system, sans-serif; background: #121212; color: #e0e0e0; display: flex; min-height: 100vh; }
        .sidebar { width: 240px; background: #1a1a1a; border-right: 1px solid #2a2a2a; display: flex; flex-direction: column; }
        .sidebar-brand { padding: 20px; font-size: 20px; font-weight: bold; color: #ff3366; text-decoration: none; border-bottom: 1px solid #2a2a2a; text-align: center; }
        .nav-menu { list-style: none; padding: 15px 0; }
        .nav-menu li a { display: block; padding: 12px 20px; color: #aaa; text-decoration: none; font-size: 15px; transition: 0.2s; }
        .nav-menu li a:hover, .nav-menu li a.active { background: #2a2a2a; color: #ff3366; border-left: 4px solid #ff3366; }
        .main-content { flex: 1; padding: 30px; background: #121212; overflow-y: auto; }
        .header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 1px solid #2a2a2a; }
        .header-title { font-size: 24px; font-weight: bold; }
        .user-info { font-size: 14px; color: #888; }
        .btn { display: inline-block; padding: 8px 16px; border-radius: 4px; text-decoration: none; font-size: 14px; font-weight: bold; cursor: pointer; border: none; }
        .btn-primary { background: #ff3366; color: #fff; }
        .btn-primary:hover { background: #e02855; }
        .btn-danger { background: #dc3545; color: #fff; }
        .btn-danger:hover { background: #bd2130; }
        .btn-secondary { background: #333; color: #ccc; }
        .btn-secondary:hover { background: #444; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #1a1a1a; border-radius: 6px; overflow: hidden; }
        .table th, .table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #2a2a2a; font-size: 14px; }
        .table th { background: #222; color: #888; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; }
        .table img { width: 60px; height: 40px; object-fit: cover; border-radius: 4px; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; background: #333; color: #aaa; margin-right: 4px; margin-bottom: 4px; }
        .alert { padding: 12px 15px; border-radius: 4px; margin-bottom: 20px; font-size: 14px; }
        .alert-success { background: #155724; color: #d4edda; }
        .alert-danger { background: #721c24; color: #f8d7da; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 14px; font-weight: bold; color: #ccc; }
        .form-control { width: 100%; padding: 10px; background: #222; border: 1px solid #333; border-radius: 4px; color: #fff; font-size: 14px; }
        .form-control:focus { border-color: #ff3366; outline: none; }
        textarea.form-control { min-height: 100px; resize: vertical; }
        .checkbox-group { display: flex; flex-wrap: wrap; gap: 10px; background: #222; padding: 12px; border-radius: 4px; border: 1px solid #333; }
        .checkbox-label { display: flex; align-items: center; gap: 6px; font-size: 14px; cursor: pointer; color: #ddd; }
    </style>
</head>
<body>

<div class="sidebar">
    <a href="index.php" class="sidebar-brand">TUBE ADMIN</a>
    <ul class="nav-menu">
        <li><a href="index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Dashboard</a></li>
        <li><a href="videos.php" class="<?= $currentPage === 'videos.php' ? 'active' : '' ?>">Manage Videos</a></li>
        <li><a href="upload.php" class="<?= $currentPage === 'upload.php' ? 'active' : '' ?>">Upload Video</a></li>
        <li><a href="categories.php" class="<?= $currentPage === 'categories.php' ? 'active' : '' ?>">Categories</a></li>
        <li><a href="ads.php" class="<?= $currentPage === 'ads.php' ? 'active' : '' ?>">Ad Management</a></li>
        <li><a href="../index.php" target="_blank">View Website ↗</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="header-bar">
        <div class="header-title">
            <?php
            switch($currentPage) {
                case 'index.php': echo 'Dashboard Overview'; break;
                case 'videos.php': echo 'Manage Videos'; break;
                case 'upload.php': echo 'Upload New Video'; break;
                case 'categories.php': echo 'Manage Categories'; break;
                case 'ads.php': echo 'Ad Management System'; break;
                default: echo 'Admin Panel';
            }
            ?>
        </div>
        <div class="user-info">Logged in as <strong><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></strong></div>
    </div>
