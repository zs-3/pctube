<?php
// admin/upload.php

require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../config/db.php';

$pdo = getDB();
$message = '';
$error = '';

// Fetch all categories for checkbox selection
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $selectedCategories = $_POST['categories'] ?? [];

    if (empty($title)) {
        $error = 'Video title is required.';
    } elseif (!isset($_FILES['video_file']) || $_FILES['video_file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please select a valid video file.';
    } elseif (!isset($_FILES['thumbnail_file']) || $_FILES['thumbnail_file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please select a valid thumbnail image.';
    } else {
        $videoFile = $_FILES['video_file'];
        $thumbFile = $_FILES['thumbnail_file'];

        // Validate video extension
        $videoExt = strtolower(pathinfo($videoFile['name'], PATHINFO_EXTENSION));
        $allowedVideoExts = ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv'];
        if (!in_array($videoExt, $allowedVideoExts)) {
            $error = 'Invalid video file type. Allowed: ' . implode(', ', $allowedVideoExts);
        }

        // Validate image extension
        $thumbExt = strtolower(pathinfo($thumbFile['name'], PATHINFO_EXTENSION));
        $allowedThumbExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($thumbExt, $allowedThumbExts)) {
            $error = 'Invalid thumbnail file type. Allowed: ' . implode(', ', $allowedThumbExts);
        }

        if (empty($error)) {
            $uploadVideoDir = __DIR__ . '/../uploads/videos/';
            $uploadThumbDir = __DIR__ . '/../uploads/thumbnails/';

            if (!file_exists($uploadVideoDir)) mkdir($uploadVideoDir, 0777, true);
            if (!file_exists($uploadThumbDir)) mkdir($uploadThumbDir, 0777, true);

            $videoFilename = uniqid('vid_') . '.' . $videoExt;
            $thumbFilename = uniqid('thumb_') . '.' . $thumbExt;

            $videoDestination = $uploadVideoDir . $videoFilename;
            $thumbDestination = $uploadThumbDir . $thumbFilename;

            if (move_uploaded_file($videoFile['tmp_name'], $videoDestination) &&
                move_uploaded_file($thumbFile['tmp_name'], $thumbDestination)) {

                $videoRelPath = 'uploads/videos/' . $videoFilename;
                $thumbRelPath = 'uploads/thumbnails/' . $thumbFilename;

                // Insert into videos table
                $stmt = $pdo->prepare("INSERT INTO videos (title, description, video_path, thumbnail_path) VALUES (?, ?, ?, ?)");
                $stmt->execute([$title, $description, $videoRelPath, $thumbRelPath]);
                $videoId = $pdo->lastInsertId();

                // Insert into video_categories table
                if (!empty($selectedCategories) && is_array($selectedCategories)) {
                    $stmtCat = $pdo->prepare("INSERT INTO video_categories (video_id, category_id) VALUES (?, ?)");
                    foreach ($selectedCategories as $catId) {
                        $stmtCat->execute([$videoId, intval($catId)]);
                    }
                }

                $message = 'Video uploaded successfully!';
            } else {
                $error = 'Failed to save uploaded files on server.';
            }
        }
    }
}
?>

<div style="max-width: 800px;">
    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="upload.php" method="POST" enctype="multipart/form-data" style="background: #1a1a1a; padding: 25px; border-radius: 6px; border: 1px solid #2a2a2a;">
        <div class="form-group">
            <label for="title">Video Title *</label>
            <input type="text" id="title" name="title" class="form-control" required placeholder="Enter video title">
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control" placeholder="Enter video description..."></textarea>
        </div>

        <div class="form-group">
            <label for="video_file">Video File (MP4, WebM, etc.) *</label>
            <input type="file" id="video_file" name="video_file" class="form-control" accept="video/*" required>
        </div>

        <div class="form-group">
            <label for="thumbnail_file">Thumbnail Image (JPG, PNG, WebP) *</label>
            <input type="file" id="thumbnail_file" name="thumbnail_file" class="form-control" accept="image/*" required>
        </div>

        <div class="form-group">
            <label>Select Categories</label>
            <div class="checkbox-group">
                <?php if (empty($categories)): ?>
                    <p style="color: #888; font-size: 13px;">No categories created yet. <a href="categories.php" style="color: #ff3366;">Add categories first</a>.</p>
                <?php else: ?>
                    <?php foreach ($categories as $cat): ?>
                        <label class="checkbox-label">
                            <input type="checkbox" name="categories[]" value="<?= $cat['id'] ?>">
                            <?= htmlspecialchars($cat['name']) ?>
                        </label>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="padding: 12px 24px; font-size: 16px;">Upload Video</button>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
