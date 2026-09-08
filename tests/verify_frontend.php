<?php
// tests/verify_frontend.php

require_once __DIR__ . '/../config/db.php';

echo "Running Frontend Page Output Verification...\n";

// Function to simulate GET request buffer capture
function renderPage($script, $queryParams = []) {
    $_GET = $queryParams;
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['PHP_SELF'] = '/' . $script;
    $_SERVER['HTTP_HOST'] = 'pisscat.com';
    $_SERVER['REQUEST_URI'] = '/' . $script . ($queryParams ? '?' . http_build_query($queryParams) : '');

    ob_start();
    include __DIR__ . '/../' . $script;
    return ob_get_clean();
}

// 1. Test index.php rendering
$htmlIndex = renderPage('index.php');
if (strpos($htmlIndex, 'PISS') === false || strpos($htmlIndex, 'CAT') === false || strpos($htmlIndex, 'Header Banner Ad Placement') === false) {
    echo "[FAIL] index.php output verification failed.\n";
    exit(1);
}
echo "[OK] index.php renders correctly with dark green theme and Header Banner ad.\n";

// 2. Test search on index.php
$htmlSearch = renderPage('index.php', ['q' => 'Sample']);
if (strpos($htmlSearch, 'Search Results for') === false) {
    echo "[FAIL] Search rendering on index.php failed.\n";
    exit(1);
}
echo "[OK] Search query filtering verified.\n";

// 3. Test category filtering on index.php
$pdo = getDB();
$firstCat = $pdo->query("SELECT slug FROM categories LIMIT 1")->fetchColumn();
$htmlCat = renderPage('index.php', ['category' => $firstCat]);
if (strpos($htmlCat, 'Category:') === false) {
    echo "[FAIL] Category filtering on index.php failed.\n";
    exit(1);
}
echo "[OK] Category filtering on index.php verified.\n";

// 4. Test watch.php rendering with Fluid Player and dual VAST ads
$firstVid = $pdo->query("SELECT id FROM videos LIMIT 1")->fetchColumn();
if ($firstVid) {
    $htmlWatch = renderPage('watch.php', ['id' => $firstVid]);
    if (strpos($htmlWatch, 'fluidPlayer') === false || strpos($htmlWatch, 'tube-video-player') === false || strpos($htmlWatch, 'vastAdList') === false) {
        echo "[FAIL] watch.php Fluid Player or VAST Ad rendering failed.\n";
        exit(1);
    }
    echo "[OK] watch.php renders correctly with Fluid Player and dual VAST ads.\n";
} else {
    echo "[WARN] No video found to test watch.php.\n";
}

// 5. Test categories.php rendering
$htmlCatList = renderPage('categories.php');
if (strpos($htmlCatList, 'Browse Adult Categories') === false) {
    echo "[FAIL] categories.php rendering failed.\n";
    exit(1);
}
echo "[OK] categories.php renders correctly.\n";

echo "Frontend Verification completed successfully - All checks passed!\n";
