<?php
// tests/verify_db.php

require_once __DIR__ . '/../config/db.php';

$pdo = getDB();

echo "Running Database Schema and Seed Data Verification...\n";

// Check tables existence
$tables = ['users', 'categories', 'videos', 'video_categories', 'ads', 'search_terms'];
foreach ($tables as $table) {
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='$table'");
    if (!$stmt->fetch()) {
        echo "[FAIL] Table '$table' does not exist.\n";
        exit(1);
    }
    echo "[OK] Table '$table' exists.\n";
}

// Check admin user seed
$stmt = $pdo->query("SELECT * FROM users WHERE username='admin'");
$user = $stmt->fetch();
if (!$user || !password_verify('zs112634', $user['password'])) {
    echo "[FAIL] Admin user not seeded correctly.\n";
    exit(1);
}
echo "[OK] Admin user verified.\n";

// Check default ads seed
$stmt = $pdo->query("SELECT COUNT(*) FROM ads");
$adCount = $stmt->fetchColumn();
if ($adCount < 9) {
    echo "[FAIL] Ad slots not seeded correctly (count: $adCount).\n";
    exit(1);
}
echo "[OK] Ad slots verified ($adCount found).\n";

// Check default categories seed
$stmt = $pdo->query("SELECT COUNT(*) FROM categories");
$catCount = $stmt->fetchColumn();
if ($catCount < 50) {
    echo "[FAIL] Categories not seeded correctly (count: $catCount).\n";
    exit(1);
}
echo "[OK] Categories verified ($catCount found).\n";

echo "Database verification successfully completed!\n";
