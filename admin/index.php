<?php
// admin/index.php

require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../config/db.php';

$pdo = getDB();

$totalVideos = $pdo->query("SELECT COUNT(*) FROM videos")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalAds = $pdo->query("SELECT COUNT(*) FROM ads WHERE is_active = 1")->fetchColumn();
$totalViews = $pdo->query("SELECT SUM(views) FROM videos")->fetchColumn() ?: 0;

$recentVideos = $pdo->query("SELECT * FROM videos ORDER BY id DESC LIMIT 5")->fetchAll();
?>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div style="background: #1a1a1a; padding: 20px; border-radius: 6px; border: 1px solid #2a2a2a;">
        <div style="font-size: 13px; color: #888;">Total Videos</div>
        <div style="font-size: 28px; font-weight: bold; color: #ff3366; margin-top: 5px;"><?= $totalVideos ?></div>
    </div>
    <div style="background: #1a1a1a; padding: 20px; border-radius: 6px; border: 1px solid #2a2a2a;">
        <div style="font-size: 13px; color: #888;">Total Categories</div>
        <div style="font-size: 28px; font-weight: bold; color: #fff; margin-top: 5px;"><?= $totalCategories ?></div>
    </div>
    <div style="background: #1a1a1a; padding: 20px; border-radius: 6px; border: 1px solid #2a2a2a;">
        <div style="font-size: 13px; color: #888;">Active Ad Placements</div>
        <div style="font-size: 28px; font-weight: bold; color: #28a745; margin-top: 5px;"><?= $totalAds ?></div>
    </div>
    <div style="background: #1a1a1a; padding: 20px; border-radius: 6px; border: 1px solid #2a2a2a;">
        <div style="font-size: 13px; color: #888;">Total Video Views</div>
        <div style="font-size: 28px; font-weight: bold; color: #17a2b8; margin-top: 5px;"><?= number_format($totalViews) ?></div>
    </div>
</div>

<h3 style="margin-bottom: 15px;">Recently Uploaded Videos</h3>
<?php if (empty($recentVideos)): ?>
    <p style="color: #888;">No videos uploaded yet. <a href="upload.php" style="color: #ff3366;">Upload your first video</a>.</p>
<?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>Thumbnail</th>
                <th>Title</th>
                <th>Views</th>
                <th>Uploaded At</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recentVideos as $v): ?>
                <tr>
                    <td><img src="../<?= htmlspecialchars($v['thumbnail_path']) ?>" alt="thumb"></td>
                    <td><a href="../watch.php?id=<?= $v['id'] ?>" target="_blank" style="color: #fff; text-decoration: none; font-weight: bold;"><?= htmlspecialchars($v['title']) ?></a></td>
                    <td><?= $v['views'] ?></td>
                    <td><?= $v['created_at'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
