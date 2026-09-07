<?php
// watch.php

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/ads.php';

$pdo = getDB();

$videoId = intval($_GET['id'] ?? 0);

if ($videoId <= 0) {
    header('Location: index.php');
    exit;
}

// Increment video views
$stmtInc = $pdo->prepare("UPDATE videos SET views = views + 1 WHERE id = ?");
$stmtInc->execute([$videoId]);

// Fetch video details
$stmtVid = $pdo->prepare("
    SELECT v.*, GROUP_CONCAT(c.id) AS cat_ids, GROUP_CONCAT(c.name, '||') AS cat_names, GROUP_CONCAT(c.slug, '||') AS cat_slugs
    FROM videos v
    LEFT JOIN video_categories vc ON v.id = vc.video_id
    LEFT JOIN categories c ON vc.category_id = c.id
    WHERE v.id = ?
    GROUP BY v.id
");
$stmtVid->execute([$videoId]);
$video = $stmtVid->fetch();

if (!$video) {
    header('Location: index.php');
    exit;
}

$pageTitle = $video['title'];
$instreamVastUrl = getInstreamVastUrl();

// Fetch Related Videos (share same categories or recent videos)
$catIdsArr = !empty($video['cat_ids']) ? explode(',', $video['cat_ids']) : [];
if (!empty($catIdsArr)) {
    $inClause = implode(',', array_map('intval', $catIdsArr));
    $relatedQuery = "
        SELECT DISTINCT v.*
        FROM videos v
        JOIN video_categories vc ON v.id = vc.video_id
        WHERE vc.category_id IN ($inClause) AND v.id != $videoId
        ORDER BY v.id DESC
        LIMIT 6
    ";
    $relatedVideos = $pdo->query($relatedQuery)->fetchAll();
} else {
    $relatedVideos = [];
}

// Fallback if not enough related videos
if (count($relatedVideos) < 6) {
    $needed = 6 - count($relatedVideos);
    $excludeIds = array_merge([$videoId], array_column($relatedVideos, 'id'));
    $exClause = implode(',', array_map('intval', $excludeIds));
    $fallbackQuery = "SELECT * FROM videos WHERE id NOT IN ($exClause) ORDER BY id DESC LIMIT $needed";
    $fallbackVideos = $pdo->query($fallbackQuery)->fetchAll();
    $relatedVideos = array_merge($relatedVideos, $fallbackVideos);
}

require_once __DIR__ . '/includes/header.php';
?>

<div style="display: grid; grid-template-columns: 1fr 340px; gap: 30px;" class="watch-layout">
    <!-- Left Column: Video Player & Meta -->
    <div>
        <!-- Above Player Ad Slot -->
        <div class="ad-container" style="margin-top: 0; margin-bottom: 15px;">
            <?= renderAdSlot('above_player') ?>
        </div>

        <!-- Fluid Player Video Container -->
        <div style="background: #000; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.8);">
            <video id="tube-video-player" style="width:100%; aspect-ratio: 16/9;" poster="<?= htmlspecialchars($video['thumbnail_path']) ?>" controls>
                <source src="<?= htmlspecialchars($video['video_path']) ?>" type="video/mp4" />
            </video>
        </div>

        <!-- Below Player Ad Slot -->
        <div class="ad-container" style="margin-top: 15px; margin-bottom: 20px;">
            <?= renderAdSlot('below_player') ?>
        </div>

        <!-- Video Title & Details -->
        <div style="background: #1a1a1a; border: 1px solid #282828; border-radius: 8px; padding: 20px; margin-bottom: 30px;">
            <h1 style="font-size: 22px; font-weight: 700; color: #fff; margin-bottom: 10px; line-height: 1.3;">
                <?= htmlspecialchars($video['title']) ?>
            </h1>

            <div style="display: flex; justify-content: space-between; align-items: center; color: #888; font-size: 14px; border-bottom: 1px solid #282828; padding-bottom: 12px; margin-bottom: 15px;">
                <span>👁 <?= number_format($video['views']) ?> views &bull; Uploaded on <?= date('F j, Y', strtotime($video['created_at'])) ?></span>
            </div>

            <?php if (!empty($video['cat_names'])): ?>
                <div style="margin-bottom: 15px; display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
                    <span style="font-size: 13px; color: #aaa; font-weight: bold;">Categories:</span>
                    <?php
                    $catNamesArr = explode('||', $video['cat_names']);
                    $catSlugsArr = explode('||', $video['cat_slugs']);
                    foreach ($catNamesArr as $idx => $cName):
                        if (empty($cName)) continue;
                        $cSlug = $catSlugsArr[$idx] ?? '';
                    ?>
                        <a href="index.php?category=<?= urlencode($cSlug) ?>" class="cat-chip" style="padding: 4px 12px; font-size: 12px;">
                            <?= htmlspecialchars($cName) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($video['description'])): ?>
                <div style="background: #111; padding: 15px; border-radius: 6px; font-size: 14px; color: #ccc; line-height: 1.6; border: 1px solid #222;">
                    <?= nl2br(htmlspecialchars($video['description'])) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Column: Sidebar Banner & Related Videos -->
    <div>
        <!-- Sidebar Banner Ad -->
        <div style="margin-bottom: 25px;">
            <?= renderAdSlot('sidebar_banner') ?>
        </div>

        <!-- Related Videos Header -->
        <h3 style="font-size: 16px; font-weight: bold; color: #fff; border-left: 4px solid #ff3366; padding-left: 10px; margin-bottom: 15px;">
            Related Videos
        </h3>

        <!-- Related Videos Grid/List -->
        <div style="display: flex; flex-direction: column; gap: 15px;">
            <?php foreach ($relatedVideos as $rel): ?>
                <div style="display: flex; gap: 12px; background: #1a1a1a; border-radius: 6px; overflow: hidden; border: 1px solid #262626;">
                    <a href="watch.php?id=<?= $rel['id'] ?>" style="flex-shrink: 0; width: 120px; aspect-ratio: 16/9; background: #000; overflow: hidden;">
                        <img src="<?= htmlspecialchars($rel['thumbnail_path']) ?>" alt="<?= htmlspecialchars($rel['title']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    </a>
                    <div style="padding: 8px 10px 8px 0; display: flex; flex-direction: column; justify-content: center; flex: 1; overflow: hidden;">
                        <a href="watch.php?id=<?= $rel['id'] ?>" style="font-size: 13px; font-weight: 600; color: #fff; line-height: 1.3; margin-bottom: 6px; text-overflow: ellipsis; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                            <?= htmlspecialchars($rel['title']) ?>
                        </a>
                        <div style="font-size: 11px; color: #888;">
                            👁 <?= number_format($rel['views']) ?> views
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
@media (max-width: 992px) {
    .watch-layout { grid-template-columns: 1fr !important; }
}
</style>

<!-- Initialize Fluid Player with ExoClick VAST in-stream ads -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var playerOptions = {
        layoutControls: {
            fillToContainer: true,
            primaryColor: "#ff3366",
            posterImage: <?= json_encode($video['thumbnail_path']) ?>,
            playButtonShowing: true,
            autoPlay: false,
            mute: false
        }
    };

    <?php if (!empty($instreamVastUrl)): ?>
    playerOptions.vastOptions = {
        adList: [
            {
                roll: 'preRoll',
                vastTag: <?= json_encode($instreamVastUrl) ?>
            }
        ]
    };
    <?php endif; ?>

    fluidPlayer('tube-video-player', playerOptions);
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
