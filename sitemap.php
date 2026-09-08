<?php
require_once __DIR__ . '/config/db.php';

header('Content-Type: application/xml');

$pdo = getDB();
$base = 'https://pisscat.com';

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

// Static pages
foreach(['', 'categories.php'] as $p) {
    echo "<url><loc>$base/$p</loc></url>";
}

// All videos
$videos = $pdo->query("SELECT id, created_at FROM videos ORDER BY id DESC")->fetchAll();
foreach($videos as $v) {
    echo "<url><loc>$base/watch.php?id={$v['id']}</loc><lastmod>".date('Y-m-d', strtotime($v['created_at']))."</lastmod></url>";
}

// All categories
$cats = $pdo->query("SELECT slug FROM categories")->fetchAll();
foreach($cats as $c) {
    echo "<url><loc>$base/index.php?category={$c['slug']}</loc></url>";
}

echo '</urlset>';
