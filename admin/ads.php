<?php
// admin/ads.php

require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../config/db.php';

$pdo = getDB();
$message = '';
$error = '';

// Handle Update Ad Slot
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['slot_key'])) {
    $slotKey = $_POST['slot_key'];
    $adCode = $_POST['ad_code'] ?? '';
    $vastUrl = trim($_POST['vast_url'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE ads SET ad_code = ?, vast_url = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP WHERE slot_key = ?");
    $stmt->execute([$adCode, $vastUrl, $isActive, $slotKey]);
    $message = "Ad slot '$slotKey' updated successfully.";
}

// Fetch all ad slots
$ads = $pdo->query("SELECT * FROM ads ORDER BY id ASC")->fetchAll();

$slotDescriptions = [
    'instream_vast' => 'Preroll Ad 1 VAST URL — Played first in Fluid Player before video playback starts.',
    'instream_vast_2' => 'Preroll Ad 2 VAST URL — Played second sequentially in Fluid Player before video starts.',
    'header_banner' => 'Header Top Banner (728x90) — Displayed at the top of every page below main header.',
    'above_player' => 'Above Player Banner — Displayed directly above the video player on watch.php.',
    'below_player' => 'Below Player Banner — Displayed directly below the video player on watch.php.',
    'sidebar_banner' => 'Sidebar Banner (300x250) — Displayed in the right sidebar of watch.php.',
    'grid_inline_1' => 'In-Grid Banner Ad 1 — Embedded inside the video grid on index.php after card 6.',
    'grid_inline_2' => 'In-Grid Banner Ad 2 — Embedded inside the video grid on index.php after card 12.',
    'below_related' => 'Below Related Videos Banner — Displayed at the bottom of the sidebar below related videos.'
];
?>

<?php if ($message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div style="max-width: 900px;">
    <p style="color: #888; margin-bottom: 20px;">
        Manage all banner ad codes and Fluid Player in-stream VAST URLs across all placements.
    </p>

    <?php foreach ($ads as $ad): ?>
        <form action="ads.php" method="POST" style="background: #1a1a1a; padding: 20px; border-radius: 6px; border: 1px solid #2a2a2a; margin-bottom: 20px;">
            <input type="hidden" name="slot_key" value="<?= htmlspecialchars($ad['slot_key']) ?>">

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <h4 style="color: #00b894; font-size: 16px;">
                    <?= htmlspecialchars($ad['title']) ?>
                    <span style="font-size: 12px; color: #666; font-weight: normal; margin-left: 8px;">(Key: <?= htmlspecialchars($ad['slot_key']) ?>)</span>
                </h4>
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" value="1" <?= $ad['is_active'] ? 'checked' : '' ?>>
                    Active
                </label>
            </div>

            <?php if (isset($slotDescriptions[$ad['slot_key']])): ?>
                <div style="font-size: 13px; color: #aaa; margin-bottom: 12px; font-style: italic;">
                    <?= htmlspecialchars($slotDescriptions[$ad['slot_key']]) ?>
                </div>
            <?php endif; ?>

            <?php if ($ad['ad_type'] === 'vast'): ?>
                <div class="form-group">
                    <label>ExoClick / VAST XML Ad URL</label>
                    <input type="url" name="vast_url" class="form-control" value="<?= htmlspecialchars($ad['vast_url'] ?? '') ?>" placeholder="https://s.exoclick.com/vast.php?idzone=XXXXX">
                </div>
            <?php else: ?>
                <div class="form-group">
                    <label>Banner Ad Code (HTML / JavaScript / iFrame)</label>
                    <textarea name="ad_code" class="form-control" style="font-family: monospace; font-size: 13px;" placeholder="Paste ExoClick or ad network HTML/JS code here..."><?= htmlspecialchars($ad['ad_code'] ?? '') ?></textarea>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary" style="padding: 8px 16px;">Save Changes</button>
        </form>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
