<?php
// includes/ads.php

require_once __DIR__ . '/../config/db.php';

/**
 * Fetch ad record by slot key
 */
function getAdSlot($slotKey) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM ads WHERE slot_key = ? AND is_active = 1");
    $stmt->execute([$slotKey]);
    return $stmt->fetch();
}

/**
 * Render HTML banner code for a given ad slot
 */
function renderAdSlot($slotKey) {
    $ad = getAdSlot($slotKey);
    if ($ad && !empty($ad['ad_code'])) {
        return '<div class="ad-slot ad-slot-' . htmlspecialchars($slotKey) . '">' . $ad['ad_code'] . '</div>';
    }
    return '';
}

/**
 * Get Fluid Player VAST URL 1 for in-stream ads
 */
function getInstreamVastUrl() {
    $ad = getAdSlot('instream_vast');
    if ($ad && !empty($ad['vast_url'])) {
        return $ad['vast_url'];
    }
    return '';
}

/**
 * Get Fluid Player VAST URL 2 for in-stream ads
 */
function getInstreamVastUrl2() {
    $ad = getAdSlot('instream_vast_2');
    if ($ad && !empty($ad['vast_url'])) {
        return $ad['vast_url'];
    }
    return '';
}
