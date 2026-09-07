<?php
// includes/header.php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/ads.php';

$pdo = getDB();

// Fetch all categories for navigation bar
$categoriesNav = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

$searchQuery = $_GET['q'] ?? '';
$activeCatSlug = $_GET['category'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . " - Adult Tube" : "Adult Tube - Watch Free HD Videos" ?></title>
    <!-- Fluid Player CDN -->
    <script src="https://cdn.fluidplayer.com/v3/current/fluidplayer.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #0f0f0f; color: #f1f1f1; line-height: 1.5; }
        a { color: inherit; text-decoration: none; }

        /* Header Layout */
        header { background-color: #1a1a1a; border-bottom: 1px solid #2a2a2a; position: sticky; top: 0; z-index: 100; }
        .header-container { max-width: 1400px; margin: 0 auto; padding: 12px 20px; display: flex; align-items: center; justify-content: space-between; gap: 20px; }
        .logo { font-size: 24px; font-weight: 900; color: #fff; letter-spacing: -0.5px; display: flex; align-items: center; gap: 6px; }
        .logo span { background: #ff3366; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 18px; }

        /* Search Bar */
        .search-box { flex: 1; max-width: 500px; display: flex; }
        .search-box input { width: 100%; padding: 10px 16px; background: #262626; border: 1px solid #333; border-radius: 20px 0 0 20px; color: #fff; font-size: 14px; outline: none; }
        .search-box input:focus { border-color: #ff3366; }
        .search-box button { padding: 10px 20px; background: #ff3366; border: none; border-radius: 0 20px 20px 0; color: #fff; font-weight: bold; cursor: pointer; transition: background 0.2s; }
        .search-box button:hover { background: #e02855; }

        .header-nav { display: flex; align-items: center; gap: 15px; }
        .header-nav a { font-weight: 600; font-size: 14px; color: #ccc; transition: color 0.2s; }
        .header-nav a:hover { color: #ff3366; }

        /* Category Filter Bar */
        .category-bar { background: #141414; border-bottom: 1px solid #222; overflow-x: auto; white-space: nowrap; padding: 10px 20px; }
        .category-bar-inner { max-width: 1400px; margin: 0 auto; display: flex; gap: 10px; align-items: center; }
        .cat-chip { display: inline-block; padding: 6px 16px; background: #222; color: #ccc; border-radius: 20px; font-size: 13px; font-weight: 500; transition: all 0.2s; border: 1px solid #333; }
        .cat-chip:hover, .cat-chip.active { background: #ff3366; color: #fff; border-color: #ff3366; }

        /* Container Main */
        .main-container { max-width: 1400px; margin: 20px auto; padding: 0 20px; min-height: 80vh; }

        /* Ad Slots styling */
        .ad-container { text-align: center; margin: 20px 0; overflow: hidden; }

        /* Video Grid Layout */
        .video-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px; margin-top: 20px; }
        .video-card { background: #1a1a1a; border-radius: 8px; overflow: hidden; border: 1px solid #262626; transition: transform 0.2s, box-shadow 0.2s; }
        .video-card:hover { transform: translateY(-4px); box-shadow: 0 8px 20px rgba(0,0,0,0.6); border-color: #ff3366; }
        .thumb-wrapper { position: relative; width: 100%; aspect-ratio: 16/9; background: #000; overflow: hidden; }
        .thumb-wrapper img { width: 100%; height: 100%; object-fit: cover; }
        .video-card-info { padding: 12px; }
        .video-card-title { font-size: 15px; font-weight: 600; color: #fff; margin-bottom: 8px; line-height: 1.3; height: 38px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
        .video-card-meta { display: flex; justify-content: space-between; font-size: 12px; color: #888; }
        .video-card-cats { margin-top: 8px; display: flex; flex-wrap: wrap; gap: 4px; }
        .mini-cat-chip { background: #282828; color: #aaa; padding: 2px 6px; border-radius: 3px; font-size: 11px; }

        /* Pagination */
        .pagination { display: flex; justify-content: center; align-items: center; gap: 8px; margin: 40px 0; }
        .pagination a, .pagination span { padding: 8px 14px; background: #1a1a1a; border: 1px solid #333; color: #ccc; border-radius: 4px; font-size: 14px; font-weight: 600; }
        .pagination a:hover { background: #2a2a2a; color: #fff; }
        .pagination .active { background: #ff3366; color: #fff; border-color: #ff3366; }

        /* Responsive Layouts */
        @media (max-width: 768px) {
            .header-container { flex-direction: column; gap: 12px; align-items: stretch; }
            .search-box { max-width: 100%; }
            .header-nav { justify-content: space-between; }
        }
    </style>
</head>
<body>

<header>
    <div class="header-container">
        <a href="index.php" class="logo">
            TUBE <span>18+</span>
        </a>

        <form action="index.php" method="GET" class="search-box">
            <input type="text" name="q" placeholder="Search videos or categories..." value="<?= htmlspecialchars($searchQuery) ?>" required>
            <button type="submit">Search</button>
        </form>

        <div class="header-nav">
            <a href="index.php">Home</a>
            <a href="categories.php">Categories</a>
            <a href="admin/login.php" target="_blank" style="color: #ff3366;">Admin Panel</a>
        </div>
    </div>
</header>

<div class="category-bar">
    <div class="category-bar-inner">
        <a href="index.php" class="cat-chip <?= empty($activeCatSlug) && empty($searchQuery) ? 'active' : '' ?>">All Videos</a>
        <?php foreach ($categoriesNav as $c): ?>
            <a href="index.php?category=<?= urlencode($c['slug']) ?>" class="cat-chip <?= $activeCatSlug === $c['slug'] ? 'active' : '' ?>">
                <?= htmlspecialchars($c['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="main-container">
    <!-- Header Banner Ad Placement -->
    <div class="ad-container">
        <?= renderAdSlot('header_banner') ?>
    </div>
