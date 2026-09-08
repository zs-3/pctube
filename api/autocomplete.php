<?php
// api/autocomplete.php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';

$q = trim($_GET['q'] ?? '');

if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$pdo = getDB();
$results = [];

// Match Categories
$cats = $pdo->prepare("SELECT name FROM categories WHERE name LIKE ? LIMIT 5");
$cats->execute(["%$q%"]);
foreach ($cats->fetchAll() as $r) {
    $results[] = $r['name'];
}

// Match Search Terms
$terms = $pdo->prepare("SELECT term FROM search_terms WHERE term LIKE ? LIMIT 5");
$terms->execute(["%$q%"]);
foreach ($terms->fetchAll() as $r) {
    $results[] = $r['term'];
}

echo json_encode(array_values(array_unique(array_slice($results, 0, 8))));
