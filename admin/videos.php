<?php
// admin/videos.php

require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../config/db.php';

$pdo = getDB();
$message = '';
$error = '';

// Handle Delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $videoId = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT * FROM videos WHERE id = ?");
    $stmt->execute([$videoId]);
    $video = $stmt->fetch();

    if ($video) {
        // Delete files from server if they exist
        if (!empty($video['video_path']) && file_exists(__DIR__ . '/../' . $video['video_path'])) {
            @unlink(__DIR__ . '/../' . $video['video_path']);
        }
        if (!empty($video['thumbnail_path']) && file_exists(__DIR__ . '/../' . $video['thumbnail_path'])) {
            @unlink(__DIR__ . '/../' . $video['thumbnail_path']);
        }

        // Delete from database
        $stmtDel = $pdo->prepare("DELETE FROM videos WHERE id = ?");
        $stmtDel->execute([$videoId]);
        $message = "Video deleted successfully.";
    } else {
        $error = "Video not found.";
    }
}

// Fetch all videos with their categories
$query = "
    SELECT v.*, GROUP_CONCAT(c.name, ', ') AS category_names
    FROM videos v
    LEFT JOIN video_categories vc ON v.id = vc.video_id
    LEFT JOIN categories c ON vc.category_id = c.id
    GROUP BY v.id
    ORDER BY v.id DESC
";
$videos = $pdo->query($query)->fetchAll();
?>

<?php if ($message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <p style="color: #888;">Total videos: <?= count($videos) ?></p>
    <a href="upload.php" class="btn btn-primary">+ Upload New Video</a>
</div>

<?php if (empty($videos)): ?>
    <div style="background: #1a1a1a; padding: 40px; text-align: center; border-radius: 6px; border: 1px solid #2a2a2a; color: #888;">
        No videos found. Click upload above to add your first video.
    </div>
<?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Thumbnail</th>
                <th>Title</th>
                <th>Categories</th>
                <th>Views</th>
                <th>Uploaded</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($videos as $v): ?>
                <tr>
                    <td><?= $v['id'] ?></td>
                    <td><img src="../<?= htmlspecialchars($v['thumbnail_path']) ?>" alt="thumb"></td>
                    <td>
                        <strong><?= htmlspecialchars($v['title']) ?></strong>
                    </td>
                    <td>
                        <?php if ($v['category_names']): ?>
                            <?php foreach (explode(', ', $v['category_names']) as $catName): ?>
                                <span class="badge"><?= htmlspecialchars($catName) ?></span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span style="color: #666; font-size: 12px;">Uncategorized</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $v['views'] ?></td>
                    <td><?= date('M j, Y', strtotime($v['created_at'])) ?></td>
                    <td>
                        <a href="../watch.php?id=<?= $v['id'] ?>" target="_blank" class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px;">View</a>
                        <a href="videos.php?action=delete&id=<?= $v['id'] ?>" onclick="return confirm('Are you sure you want to delete this video?');" class="btn btn-danger" style="padding: 4px 8px; font-size: 12px;">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
