<?php
// tests/run_tests.php

echo "==================================================\n";
echo "    RUNNING COMPREHENSIVE TUBE SITE SUITE         \n";
echo "==================================================\n\n";

$tests = [
    'verify_db.php',
    'verify_admin.php',
    'verify_frontend.php'
];

$failed = false;

foreach ($tests as $testFile) {
    echo "Executing $testFile...\n";
    echo "--------------------------------------------------\n";

    $cmd = "php " . escapeshellarg(__DIR__ . '/' . $testFile);
    exec($cmd, $output, $returnCode);

    echo implode("\n", $output) . "\n";

    if ($returnCode !== 0) {
        echo "[ERROR] $testFile failed with exit code $returnCode.\n\n";
        $failed = true;
    } else {
        echo "[SUCCESS] $testFile passed successfully.\n\n";
    }

    $output = [];
}

if ($failed) {
    echo "==================================================\n";
    echo "    TEST SUITE FAILED - PLEASE FIX ERRORS         \n";
    echo "==================================================\n";
    exit(1);
} else {
    echo "==================================================\n";
    echo "    ALL TEST SUITES PASSED SUCCESSFULLY!          \n";
    echo "==================================================\n";
    exit(0);
}
