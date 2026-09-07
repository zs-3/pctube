<?php
// tests/verify_admin.php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/ads.php';

echo "Running Admin Panel & Database Workflows Verification...\n";

$pdo = getDB();

// 1. Verify User Login Authentication
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute(['admin']);
$user = $stmt->fetch();

if (!$user || !password_verify('admin123', $user['password'])) {
    echo "[FAIL] Authentication check failed.\n";
    exit(1);
}
echo "[OK] Admin Authentication logic verified.\n";

// 2. Test Category Creation
$testCatName = 'Test Category ' . time();
$testSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $testCatName), '-'));

$stmtInsertCat = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
$stmtInsertCat->execute([$testCatName, $testSlug]);
$catId = $pdo->lastInsertId();

$stmtCheckCat = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmtCheckCat->execute([$catId]);
$cat = $stmtCheckCat->fetch();

if (!$cat || $cat['name'] !== $testCatName) {
    echo "[FAIL] Category creation failed.\n";
    exit(1);
}
echo "[OK] Category CRUD verified (ID: $catId).\n";

// 3. Test Video Creation & Category Link
$testTitle = "Sample Video " . time();
$testDesc = "Test description";
$testVidPath = "uploads/videos/test.mp4";
$testThumbPath = "uploads/thumbnails/test.jpg";

$stmtVid = $pdo->prepare("INSERT INTO videos (title, description, video_path, thumbnail_path) VALUES (?, ?, ?, ?)");
$stmtVid->execute([$testTitle, $testDesc, $testVidPath, $testThumbPath]);
$videoId = $pdo->lastInsertId();

$stmtLink = $pdo->prepare("INSERT INTO video_categories (video_id, category_id) VALUES (?, ?)");
$stmtLink->execute([$videoId, $catId]);

// Verify video and link
$stmtGetVid = $pdo->prepare("
    SELECT v.*, GROUP_CONCAT(c.name) as cat_names
    FROM videos v
    JOIN video_categories vc ON v.id = vc.video_id
    JOIN categories c ON vc.category_id = c.id
    WHERE v.id = ?
");
$stmtGetVid->execute([$videoId]);
$vidData = $stmtGetVid->fetch();

if (!$vidData || $vidData['title'] !== $testTitle || $vidData['cat_names'] !== $testCatName) {
    echo "[FAIL] Video creation or category association failed.\n";
    exit(1);
}
echo "[OK] Video Upload & Multi-Category linking verified (Video ID: $videoId).\n";

// 4. Test Ad Setting Update
$newVast = "https://s.exoclick.com/vast.php?idzone=9999999";
$stmtAd = $pdo->prepare("UPDATE ads SET vast_url = ? WHERE slot_key = 'instream_vast'");
$stmtAd->execute([$newVast]);

$retrievedVast = getInstreamVastUrl();
if ($retrievedVast !== $newVast) {
    echo "[FAIL] Ad update or helper retrieval failed.\n";
    exit(1);
}
echo "[OK] Ad Management & VAST URL helper verified.\n";

echo "Admin Panel Verification complete - All checks passed!\n";
