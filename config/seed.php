<?php
// config/seed.php

require_once __DIR__ . '/db.php';

$pdo = getDB();

echo "Seeding database...\n";

// 1. Seed default Admin user
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
$stmt->execute(['admin']);
if ($stmt->fetchColumn() == 0) {
    $adminPassword = password_hash('admin123', PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->execute(['admin', $adminPassword]);
    echo "Default admin user created (admin / admin123)\n";
}

// 2. Seed default Ad slots
$defaultAds = [
    [
        'slot_key' => 'header_banner',
        'title' => 'Header Top Banner (728x90)',
        'ad_type' => 'banner',
        'ad_code' => '<div style="background:#222;color:#aaa;padding:15px;text-align:center;border:1px dashed #444;font-size:14px;">Header Banner Ad Placement (728x90)</div>',
        'vast_url' => '',
        'is_active' => 1
    ],
    [
        'slot_key' => 'sidebar_banner',
        'title' => 'Sidebar Banner (300x250)',
        'ad_type' => 'banner',
        'ad_code' => '<div style="background:#222;color:#aaa;padding:30px 15px;text-align:center;border:1px dashed #444;font-size:14px;">Sidebar Ad Placement (300x250)</div>',
        'vast_url' => '',
        'is_active' => 1
    ],
    [
        'slot_key' => 'above_player',
        'title' => 'Above Player Banner',
        'ad_type' => 'banner',
        'ad_code' => '<div style="background:#222;color:#aaa;padding:10px;text-align:center;border:1px dashed #444;font-size:13px;margin-bottom:10px;">Above Player Banner Ad</div>',
        'vast_url' => '',
        'is_active' => 1
    ],
    [
        'slot_key' => 'below_player',
        'title' => 'Below Player Banner',
        'ad_type' => 'banner',
        'ad_code' => '<div style="background:#222;color:#aaa;padding:10px;text-align:center;border:1px dashed #444;font-size:13px;margin-top:10px;">Below Player Banner Ad</div>',
        'vast_url' => '',
        'is_active' => 1
    ],
    [
        'slot_key' => 'instream_vast',
        'title' => 'Fluid Player In-Stream VAST Ad URL (ExoClick)',
        'ad_type' => 'vast',
        'ad_code' => '',
        'vast_url' => 'https://s.exoclick.com/vast.php?idzone=1234567',
        'is_active' => 1
    ]
];

$stmtCheckAd = $pdo->prepare("SELECT COUNT(*) FROM ads WHERE slot_key = ?");
$stmtInsertAd = $pdo->prepare("INSERT INTO ads (slot_key, title, ad_type, ad_code, vast_url, is_active) VALUES (?, ?, ?, ?, ?, ?)");

foreach ($defaultAds as $ad) {
    $stmtCheckAd->execute([$ad['slot_key']]);
    if ($stmtCheckAd->fetchColumn() == 0) {
        $stmtInsertAd->execute([
            $ad['slot_key'],
            $ad['title'],
            $ad['ad_type'],
            $ad['ad_code'],
            $ad['vast_url'],
            $ad['is_active']
        ]);
        echo "Ad slot created: {$ad['slot_key']}\n";
    }
}

// 3. Seed initial Categories
$categories = [
    'Amateur',
    'Anal',
    'Asian',
    'BBW',
    'Blowjob',
    'Brunette',
    'MILF',
    'Ebony',
    'Hardcore',
    'Latina',
    'Lesbian',
    'VR'
];

$stmtCheckCat = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
$stmtInsertCat = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");

foreach ($categories as $cat) {
    $stmtCheckCat->execute([$cat]);
    if (!$stmtCheckCat->fetch()) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $cat), '-'));
        $stmtInsertCat->execute([$cat, $slug]);
        echo "Category created: $cat\n";
    }
}

// Ensure sample uploads directory exists
$uploadDirVideos = __DIR__ . '/../uploads/videos';
$uploadDirThumbs = __DIR__ . '/../uploads/thumbnails';

if (!file_exists($uploadDirVideos)) mkdir($uploadDirVideos, 0777, true);
if (!file_exists($uploadDirThumbs)) mkdir($uploadDirThumbs, 0777, true);

echo "Database seeding completed successfully.\n";
