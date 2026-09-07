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
?>

<?php if ($message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div style="max-width: 900px;">
    <p style="color: #888; margin-bottom: 20px;">
        Manage banner ad codes (ExoClick banners, custom HTML/JS) and Fluid Player In-Stream VAST Ad URLs (ExoClick In-stream VAST tags).
    </p>

    <?php foreach ($ads as $ad): ?>
        <form action="ads.php" method="POST" style="background: #1a1a1a; padding: 20px; border-radius: 6px; border: 1px solid #2a2a2a; margin-bottom: 20px;">
            <input type="hidden" name="slot_key" value="<?= htmlspecialchars($ad['slot_key']) ?>">

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h4 style="color: #ff3366; font-size: 16px;">
                    <?= htmlspecialchars($ad['title']) ?>
                    <span style="font-size: 12px; color: #666; font-weight: normal; margin-left: 8px;">(Key: <?= htmlspecialchars($ad['slot_key']) ?>)</span>
                </h4>
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" value="1" <?= $ad['is_active'] ? 'checked' : '' ?>>
                    Active
                </label>
            </div>

            <?php if ($ad['ad_type'] === 'vast'): ?>
                <div class="form-group">
                    <label>ExoClick / VAST XML Ad URL</label>
                    <input type="url" name="vast_url" class="form-control" value="<?= htmlspecialchars($ad['vast_url'] ?? '') ?>" placeholder="https://s.exoclick.com/vast.php?idzone=XXXXX">
                    <small style="color: #888; display: block; margin-top: 5px;">This VAST URL will be directly loaded into Fluid Player for in-stream pre-roll/mid-roll video ads.</small>
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
