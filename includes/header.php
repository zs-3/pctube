<?php
// includes/header.php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/ads.php';

$pdo = getDB();

// Fetch all categories for navigation bar
$categoriesNav = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

$searchQuery = $_GET['q'] ?? '';
$activeCatSlug = $_GET['category'] ?? '';

// Default SEO values
$siteName = "PISSCAT";
$defaultDescription = "Watch free high quality 18+ adult videos, sex clips, and tube content on PISSCAT. Updated daily.";
$metaDesc = isset($metaDescription) && !empty($metaDescription) ? htmlspecialchars($metaDescription) : $defaultDescription;
$pageTitleText = isset($pageTitle) ? htmlspecialchars($pageTitle) . " - " . $siteName : $siteName . " - Free 18+ HD Adult Videos";
$httpHost = $_SERVER['HTTP_HOST'] ?? 'pisscat.com';
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$httpHost}{$requestUri}";
$ogImage = isset($metaImage) && !empty($metaImage) ? $metaImage : "https://pisscat.com/assets/og-cover.jpg";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitleText ?></title>
    <meta name="description" content="<?= $metaDesc ?>">
    <meta name="robots" content="index, follow">

    <!-- Canonical URL -->
    <link rel="canonical" href="<?= htmlspecialchars($currentUrl) ?>">

    <!-- Open Graph Tags -->
    <meta property="og:site_name" content="PISSCAT">
    <meta property="og:title" content="<?= $pageTitleText ?>">
    <meta property="og:description" content="<?= $metaDesc ?>">
    <meta property="og:image" content="<?= htmlspecialchars($ogImage) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($currentUrl) ?>">
    <meta property="og:type" content="website">

    <!-- Pagination Prev/Next SEO Links -->
    <?php if (isset($relPrev) && !empty($relPrev)): ?>
        <link rel="prev" href="<?= htmlspecialchars($relPrev) ?>">
    <?php endif; ?>
    <?php if (isset($relNext) && !empty($relNext)): ?>
        <link rel="next" href="<?= htmlspecialchars($relNext) ?>">
    <?php endif; ?>

    <!-- JSON-LD VideoObject Schema -->
    <?php if (isset($schema) && !empty($schema)): ?>
        <script type="application/ld+json">
            <?= is_array($schema) ? json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : $schema ?>
        </script>
    <?php endif; ?>

    <!-- FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Fluid Player CDN -->
    <script src="https://cdn.fluidplayer.com/v3/current/fluidplayer.min.js"></script>

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #0f0f0f; color: #f1f1f1; line-height: 1.5; }
        a { color: inherit; text-decoration: none; }

        /* Header Layout - Yellow Accent Theme (#ffb703) */
        header { background-color: #1a1a1a; border-bottom: 1px solid #2a2a2a; position: sticky; top: 0; z-index: 100; }
        .header-container { max-width: 1400px; margin: 0 auto; padding: 12px 20px; display: flex; align-items: center; justify-content: space-between; gap: 20px; }
        .logo { font-size: 26px; font-weight: 900; color: #fff; letter-spacing: -0.5px; display: flex; align-items: center; gap: 4px; }
        .logo span.logo-highlight { color: #ffb703; }

        /* Search Bar & Autocomplete Dropdown */
        .search-box-wrapper { flex: 1; max-width: 500px; position: relative; }
        .search-box { display: flex; }
        .search-box input { width: 100%; padding: 10px 16px; background: #262626; border: 1px solid #333; border-radius: 20px 0 0 20px; color: #fff; font-size: 14px; outline: none; }
        .search-box input:focus { border-color: #ffb703; }
        .search-box button { padding: 10px 20px; background: #ffb703; border: none; border-radius: 0 20px 20px 0; color: #000; font-weight: bold; cursor: pointer; transition: background 0.2s; display: flex; align-items: center; gap: 6px; }
        .search-box button:hover { background: #e6a800; }

        .autocomplete-dropdown { position: absolute; top: 100%; left: 0; right: 0; background: #1f1f1f; border: 1px solid #333; border-top: none; border-radius: 0 0 10px 10px; box-shadow: 0 8px 16px rgba(0,0,0,0.6); z-index: 1000; display: none; overflow: hidden; }
        .autocomplete-item { padding: 10px 16px; font-size: 14px; color: #ddd; cursor: pointer; border-bottom: 1px solid #2a2a2a; display: flex; align-items: center; gap: 8px; }
        .autocomplete-item:last-child { border-bottom: none; }
        .autocomplete-item:hover { background: #2a2a2a; color: #ffb703; }

        .header-nav { display: flex; align-items: center; gap: 15px; }
        .header-nav a { font-weight: 600; font-size: 14px; color: #ccc; transition: color 0.2s; display: flex; align-items: center; gap: 6px; }
        .header-nav a:hover { color: #ffb703; }

        /* Category Filter Bar */
        .category-bar { background: #141414; border-bottom: 1px solid #222; overflow-x: auto; white-space: nowrap; padding: 10px 20px; }
        .category-bar-inner { max-width: 1400px; margin: 0 auto; display: flex; gap: 10px; align-items: center; }
        .cat-chip { display: inline-block; padding: 6px 16px; background: #222; color: #ccc; border-radius: 20px; font-size: 13px; font-weight: 500; transition: all 0.2s; border: 1px solid #333; }
        .cat-chip:hover, .cat-chip.active { background: #ffb703; color: #000; border-color: #ffb703; font-weight: bold; }

        /* Container Main */
        .main-container { max-width: 1400px; margin: 20px auto; padding: 0 20px; min-height: 80vh; }

        /* Ad Slots styling */
        .ad-container { text-align: center; margin: 20px 0; overflow: hidden; }

        /* Video Grid Layout */
        .video-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px; margin-top: 20px; }
        .video-card { background: #1a1a1a; border-radius: 8px; overflow: hidden; border: 1px solid #262626; transition: transform 0.2s, box-shadow 0.2s; }
        .video-card:hover { transform: translateY(-4px); box-shadow: 0 8px 20px rgba(0,0,0,0.6); border-color: #ffb703; }
        .thumb-wrapper { position: relative; width: 100%; aspect-ratio: 16/9; background: #000; overflow: hidden; }
        .thumb-wrapper img { width: 100%; height: 100%; object-fit: cover; }
        .video-card-info { padding: 12px; }
        .video-card-title { font-size: 15px; font-weight: 600; color: #fff; margin-bottom: 8px; line-height: 1.3; height: 38px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
        .video-card-meta { display: flex; justify-content: space-between; font-size: 12px; color: #888; }
        .video-card-cats { margin-top: 8px; display: flex; flex-wrap: wrap; gap: 4px; }
        .mini-cat-chip { background: #282828; color: #aaa; padding: 2px 6px; border-radius: 3px; font-size: 11px; }

        /* Pagination Controls */
        .pagination { display: flex; justify-content: center; align-items: center; gap: 6px; margin: 40px 0; flex-wrap: wrap; }
        .pagination a, .pagination span { padding: 8px 14px; background: #1a1a1a; border: 1px solid #333; color: #ccc; border-radius: 4px; font-size: 14px; font-weight: 600; }
        .pagination a:hover { background: #2a2a2a; color: #fff; border-color: #ffb703; }
        .pagination .active { background: #ffb703; color: #000; border-color: #ffb703; }
        .pagination .dots { background: transparent; border: none; color: #666; padding: 8px 6px; }

        /* Sort Toggle Buttons */
        .sort-btn-group { display: flex; gap: 8px; }
        .sort-btn { padding: 6px 14px; background: #222; border: 1px solid #333; border-radius: 20px; color: #aaa; font-size: 13px; font-weight: 600; transition: 0.2s; }
        .sort-btn:hover, .sort-btn.active { background: #ffb703; color: #000; border-color: #ffb703; }

        /* Responsive Layouts */
        @media (max-width: 768px) {
            .header-container { flex-direction: column; gap: 12px; align-items: stretch; }
            .search-box-wrapper { max-width: 100%; }
            .header-nav { justify-content: space-between; }
        }
    </style>
</head>
<body>

<header>
    <div class="header-container">
        <a href="index.php" class="logo">
            <span class="logo-highlight">PISS</span>CAT
        </a>

        <div class="search-box-wrapper">
            <form action="index.php" method="GET" class="search-box">
                <input type="text" id="search-input" name="q" placeholder="Search videos or categories..." value="<?= htmlspecialchars($searchQuery) ?>" autocomplete="off" required>
                <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
            </form>
            <div id="autocomplete-dropdown" class="autocomplete-dropdown"></div>
        </div>

        <div class="header-nav">
            <a href="index.php"><i class="fa-solid fa-house"></i> Home</a>
            <a href="categories.php"><i class="fa-solid fa-layer-group"></i> Categories</a>
            <a href="admin/login.php" target="_blank" style="color: #ffb703;"><i class="fa-solid fa-user-gear"></i> Admin</a>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('search-input');
    var dropdown = document.getElementById('autocomplete-dropdown');

    if (searchInput && dropdown) {
        searchInput.addEventListener('input', function() {
            var q = this.value.trim();
            if (q.length < 2) {
                dropdown.style.display = 'none';
                dropdown.innerHTML = '';
                return;
            }

            fetch('api/autocomplete.php?q=' + encodeURIComponent(q))
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data && data.length > 0) {
                        var html = '';
                        data.forEach(function(item) {
                            html += '<div class="autocomplete-item"><i class="fa-solid fa-magnifying-glass" style="font-size:12px;color:#666;"></i> ' + escapeHtml(item) + '</div>';
                        });
                        dropdown.innerHTML = html;
                        dropdown.style.display = 'block';

                        dropdown.querySelectorAll('.autocomplete-item').forEach(function(el) {
                            el.addEventListener('click', function() {
                                searchInput.value = this.innerText.trim();
                                dropdown.style.display = 'none';
                                searchInput.form.submit();
                            });
                        });
                    } else {
                        dropdown.style.display = 'none';
                    }
                })
                .catch(function() {
                    dropdown.style.display = 'none';
                });
        });

        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
    }

    function escapeHtml(str) {
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }
});
</script>
