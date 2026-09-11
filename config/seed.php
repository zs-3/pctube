<?php
// config/seed.php

require_once __DIR__ . '/db.php';

$pdo = getDB();

echo "Seeding database...\n";

// 1. Seed or update Admin password
$adminPassword = password_hash('zs112634', PASSWORD_BCRYPT);

$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
$stmt->execute(['admin']);
if ($stmt->fetchColumn() == 0) {
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->execute(['admin', $adminPassword]);
    echo "Default admin user created (admin / zs112634)\n";
} else {
    $updatePw = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
    $updatePw->execute([$adminPassword]);
    echo "Admin password updated to zs112634\n";
}

// 2. Seed default Ad slots using INSERT OR IGNORE
$defaultAds = [
    [
        'slot_key' => 'header_banner',
        'title' => 'Header Top Banner (728x90)',
        'ad_type' => 'banner',
        'ad_code' => '<div style="background:#221e10;color:#ffb703;padding:15px;text-align:center;border:1px dashed #ffb703;font-size:14px;">Header Banner Ad Placement (728x90)</div>',
        'vast_url' => '',
        'is_active' => 1
    ],
    [
        'slot_key' => 'sidebar_banner',
        'title' => 'Sidebar Banner (300x250)',
        'ad_type' => 'banner',
        'ad_code' => '<div style="background:#221e10;color:#ffb703;padding:30px 15px;text-align:center;border:1px dashed #ffb703;font-size:14px;">Sidebar Ad Placement (300x250)</div>',
        'vast_url' => '',
        'is_active' => 1
    ],
    [
        'slot_key' => 'above_player',
        'title' => 'Above Player Banner',
        'ad_type' => 'banner',
        'ad_code' => '<div style="background:#221e10;color:#ffb703;padding:10px;text-align:center;border:1px dashed #ffb703;font-size:13px;margin-bottom:10px;">Above Player Banner Ad</div>',
        'vast_url' => '',
        'is_active' => 1
    ],
    [
        'slot_key' => 'below_player',
        'title' => 'Below Player Banner',
        'ad_type' => 'banner',
        'ad_code' => '<div style="background:#221e10;color:#ffb703;padding:10px;text-align:center;border:1px dashed #ffb703;font-size:13px;margin-top:10px;">Below Player Banner Ad</div>',
        'vast_url' => '',
        'is_active' => 1
    ],
    [
        'slot_key' => 'instream_vast',
        'title' => 'Fluid Player Preroll Ad 1 VAST URL',
        'ad_type' => 'vast',
        'ad_code' => '',
        'vast_url' => 'https://s.exoclick.com/vast.php?idzone=1234567',
        'is_active' => 1
    ],
    [
        'slot_key' => 'instream_vast_2',
        'title' => 'Fluid Player Preroll Ad 2 VAST URL',
        'ad_type' => 'vast',
        'ad_code' => '',
        'vast_url' => '',
        'is_active' => 1
    ],
    [
        'slot_key' => 'grid_inline_1',
        'title' => 'In-Grid Banner Ad 1 (After Video 6)',
        'ad_type' => 'banner',
        'ad_code' => '<div style="background:#221e10;color:#ffb703;padding:20px;text-align:center;border:1px dashed #ffb703;font-size:13px;border-radius:8px;">Inline Grid Ad Slot 1</div>',
        'vast_url' => '',
        'is_active' => 1
    ],
    [
        'slot_key' => 'grid_inline_2',
        'title' => 'In-Grid Banner Ad 2 (After Video 12)',
        'ad_type' => 'banner',
        'ad_code' => '<div style="background:#221e10;color:#ffb703;padding:20px;text-align:center;border:1px dashed #ffb703;font-size:13px;border-radius:8px;">Inline Grid Ad Slot 2</div>',
        'vast_url' => '',
        'is_active' => 1
    ],
    [
        'slot_key' => 'below_related',
        'title' => 'Below Related Videos Banner',
        'ad_type' => 'banner',
        'ad_code' => '<div style="background:#221e10;color:#ffb703;padding:15px;text-align:center;border:1px dashed #ffb703;font-size:13px;margin-top:15px;border-radius:6px;">Below Related Videos Ad Placement</div>',
        'vast_url' => '',
        'is_active' => 1
    ]
];

$stmtInsertAd = $pdo->prepare("INSERT OR IGNORE INTO ads (slot_key, title, ad_type, ad_code, vast_url, is_active) VALUES (?, ?, ?, ?, ?, ?)");

foreach ($defaultAds as $ad) {
    $stmtInsertAd->execute([
        $ad['slot_key'],
        $ad['title'],
        $ad['ad_type'],
        $ad['ad_code'],
        $ad['vast_url'],
        $ad['is_active']
    ]);
}

// 3. Seed expanded categories list
$categories = [
    'Amateur', 'Anal', 'Asian', 'BBW', 'BDSM', 'Blowjob', 'Brunette', 'Compilation',
    'Creampie', 'Cumshot', 'Ebony', 'Femdom', 'Fetish', 'Fingering', 'Gangbang', 'German',
    'Hairy', 'Hardcore', 'Hentai', 'Indian', 'Japanese', 'Latina', 'Lesbian', 'Lingerie',
    'MILF', 'Massage', 'Masturbation', 'Mature', 'Natural Tits', 'Orgy', 'POV', 'Public',
    'Pussy Licking', 'Redhead', 'Rough Sex', 'Russian', 'Shemale', 'Small Tits', 'Solo',
    'Squirt', 'Stockings', 'Strapon', 'Teen', 'Threesome', 'Toys', 'Uniform', 'Vintage',
    'Voyeur', 'Wet', 'Interracial', 'Pissing', 'Golden Shower', 'Watersports', 'Outdoor Piss',
    'Toilet', 'Desperation', 'Panty Wetting', 'Bedwetting', 'Human Toilet', 'Drinking'
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
