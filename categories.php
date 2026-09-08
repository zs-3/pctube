<?php
// categories.php

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/ads.php';

$pdo = getDB();

$pageTitle = "Categories - PISSCAT Free Adult Tube";
$metaDescription = "Browse all adult categories on PISSCAT free adult tube.";

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Total categories
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalPages = ceil($totalCategories / $perPage);

// Set SEO Rel Prev and Next Links
$relPrev = ($page > 1) ? 'https://pisscat.com/categories.php?' . http_build_query(array_merge($_GET, ['page' => $page - 1])) : '';
$relNext = ($page < $totalPages) ? 'https://pisscat.com/categories.php?' . http_build_query(array_merge($_GET, ['page' => $page + 1])) : '';

// Fetch categories with video count and latest thumbnail preview
$categories = $pdo->query("
    SELECT c.*, COUNT(vc.video_id) AS total_videos,
           (SELECT v.thumbnail_path FROM videos v JOIN video_categories vc2 ON v.id = vc2.video_id WHERE vc2.category_id = c.id ORDER BY v.id DESC LIMIT 1) AS thumb_preview
    FROM categories c
    LEFT JOIN video_categories vc ON c.id = vc.category_id
    GROUP BY c.id
    ORDER BY c.name ASC
    LIMIT $perPage OFFSET $offset
")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div style="margin-bottom: 25px; border-bottom: 2px solid #222; padding-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
    <h1 style="font-size: 24px; font-weight: 700; color: #fff;">
        Browse Adult Categories
    </h1>
    <span style="color: #888; font-size: 14px;"><?= $totalCategories ?> categories</span>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px;">
    <?php foreach ($categories as $cat): ?>
        <a href="index.php?category=<?= urlencode($cat['slug']) ?>" style="display: block; background: #1a1a1a; border-radius: 8px; overflow: hidden; border: 1px solid #262626; transition: transform 0.2s, border-color 0.2s;" onmouseover="this.style.borderColor='#ffb703'; this.style.transform='translateY(-4px)';" onmouseout="this.style.borderColor='#262626'; this.style.transform='none';">
            <div style="width: 100%; aspect-ratio: 16/9; background: #222; position: relative; overflow: hidden;">
                <?php if (!empty($cat['thumb_preview'])): ?>
                    <img src="<?= htmlspecialchars($cat['thumb_preview']) ?>" alt="<?= htmlspecialchars($cat['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #555; font-size: 14px; font-weight: bold;">
                        NO THUMBNAIL
                    </div>
                <?php endif; ?>
                <span style="position: absolute; bottom: 8px; right: 8px; background: rgba(0,0,0,0.8); color: #fff; font-size: 11px; padding: 2px 6px; border-radius: 4px; font-weight: bold;">
                    <?= number_format($cat['total_videos']) ?> videos
                </span>
            </div>
            <div style="padding: 12px; text-align: center;">
                <div style="font-size: 15px; font-weight: bold; color: #fff;">
                    <?= htmlspecialchars($cat['name']) ?>
                </div>
            </div>
        </a>
    <?php endforeach; ?>
</div>

<!-- Pagination with Ellipsis -->
<?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="categories.php?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">&laquo; Prev</a>
        <?php endif; ?>

        <?php
        $range = 2;
        for ($p = 1; $p <= $totalPages; $p++):
            if ($p == 1 || $p == $totalPages || ($p >= $page - $range && $p <= $page + $range)):
        ?>
                <?php if ($p === $page): ?>
                    <span class="active"><?= $p ?></span>
                <?php else: ?>
                    <a href="categories.php?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"><?= $p ?></a>
                <?php endif; ?>
            <?php elseif ($p == $page - $range - 1 || $p == $page + $range + 1): ?>
                <span class="dots">&hellip;</span>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="categories.php?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next &raquo;</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
