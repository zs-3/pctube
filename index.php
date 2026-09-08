<?php
// index.php

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/ads.php';

$pdo = getDB();

$searchQuery = trim($_GET['q'] ?? '');
$activeCatSlug = trim($_GET['category'] ?? '');
$sort = trim($_GET['sort'] ?? 'most_viewed');
if (!in_array($sort, ['most_viewed', 'newest'])) {
    $sort = 'most_viewed';
}

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$whereClauses = [];
$params = [];

// Filter by search query (title, description, or category name match)
if (!empty($searchQuery)) {
    $whereClauses[] = "(v.title LIKE ? OR v.description LIKE ? OR c.name LIKE ?)";
    $searchTerm = "%" . $searchQuery . "%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $pageTitle = "Search results for: " . htmlspecialchars($searchQuery);
    $metaDescription = "Search results for " . htmlspecialchars($searchQuery) . " on PISSCAT free adult tube.";
}

// Filter by category slug
if (!empty($activeCatSlug)) {
    $whereClauses[] = "c.slug = ?";
    $params[] = $activeCatSlug;

    $stmtCatName = $pdo->prepare("SELECT name FROM categories WHERE slug = ?");
    $stmtCatName->execute([$activeCatSlug]);
    $catObj = $stmtCatName->fetch();
    if ($catObj) {
        $pageTitle = htmlspecialchars($catObj['name']) . " Videos";
        $metaDescription = "Watch free " . htmlspecialchars($catObj['name']) . " adult videos and sex clips on PISSCAT.";
    }
}

$whereSQL = "";
if (!empty($whereClauses)) {
    $whereSQL = "WHERE " . implode(" AND ", $whereClauses);
}

// Count total items
$countQuery = "
    SELECT COUNT(DISTINCT v.id)
    FROM videos v
    LEFT JOIN video_categories vc ON v.id = vc.video_id
    LEFT JOIN categories c ON vc.category_id = c.id
    $whereSQL
";
$stmtCount = $pdo->prepare($countQuery);
$stmtCount->execute($params);
$totalVideos = $stmtCount->fetchColumn();
$totalPages = ceil($totalVideos / $perPage);

// Pagination prev/next URLs for SEO
$queryParams = $_GET;
if ($page > 1) {
    $queryParams['page'] = $page - 1;
    $relPrev = 'index.php?' . http_build_query($queryParams);
}
if ($page < $totalPages) {
    $queryParams['page'] = $page + 1;
    $relNext = 'index.php?' . http_build_query($queryParams);
}

// Sorting SQL clause
$orderBySQL = ($sort === 'newest') ? "ORDER BY v.id DESC" : "ORDER BY v.views DESC, v.id DESC";

// Fetch video items
$videoQuery = "
    SELECT v.*, GROUP_CONCAT(c.name, '||') AS cat_names, GROUP_CONCAT(c.slug, '||') AS cat_slugs
    FROM videos v
    LEFT JOIN video_categories vc ON v.id = vc.video_id
    LEFT JOIN categories c ON vc.category_id = c.id
    $whereSQL
    GROUP BY v.id
    $orderBySQL
    LIMIT $perPage OFFSET $offset
";

$stmtVideos = $pdo->prepare($videoQuery);
$stmtVideos->execute($params);
$videos = $stmtVideos->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div style="margin-bottom: 20px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; border-bottom: 2px solid #222; padding-bottom: 12px; gap: 15px;">
    <h1 style="font-size: 22px; font-weight: 700; color: #fff;">
        <?php
        if (!empty($searchQuery)) {
            echo 'Search Results for: <span style="color: #00b894;">' . htmlspecialchars($searchQuery) . '</span>';
        } elseif (!empty($activeCatSlug)) {
            echo 'Category: <span style="color: #00b894;">' . htmlspecialchars($catObj['name'] ?? $activeCatSlug) . '</span>';
        } else {
            echo 'Trending Videos';
        }
        ?>
    </h1>

    <div style="display: flex; align-items: center; gap: 15px;">
        <!-- Sort Toggle Buttons -->
        <div class="sort-btn-group">
            <a href="index.php?<?= http_build_query(array_merge($_GET, ['sort' => 'most_viewed', 'page' => 1])) ?>" class="sort-btn <?= $sort === 'most_viewed' ? 'active' : '' ?>">
                <i class="fa-solid fa-fire"></i> Most Viewed
            </a>
            <a href="index.php?<?= http_build_query(array_merge($_GET, ['sort' => 'newest', 'page' => 1])) ?>" class="sort-btn <?= $sort === 'newest' ? 'active' : '' ?>">
                <i class="fa-solid fa-clock"></i> Newest
            </a>
        </div>
        <span style="color: #888; font-size: 14px;"><?= $totalVideos ?> videos</span>
    </div>
</div>

<?php if (empty($videos)): ?>
    <div style="background: #181818; padding: 60px 20px; text-align: center; border-radius: 8px; border: 1px solid #282828; margin: 40px 0;">
        <h3 style="color: #ccc; margin-bottom: 10px;">No videos found</h3>
        <p style="color: #777; font-size: 14px;">Try searching for different keywords or select a different category.</p>
        <a href="index.php" style="display: inline-block; margin-top: 20px; padding: 10px 20px; background: #00b894; color: #fff; border-radius: 4px; font-weight: bold;">Browse All Videos</a>
    </div>
<?php else: ?>
    <div class="video-grid">
        <?php
        $cardIndex = 0;
        foreach ($videos as $v):
            $cardIndex++;
        ?>
            <div class="video-card">
                <a href="watch.php?id=<?= $v['id'] ?>">
                    <div class="thumb-wrapper">
                        <img src="<?= htmlspecialchars($v['thumbnail_path']) ?>" alt="<?= htmlspecialchars($v['title']) ?>" loading="lazy">
                    </div>
                </a>
                <div class="video-card-info">
                    <a href="watch.php?id=<?= $v['id'] ?>">
                        <div class="video-card-title" title="<?= htmlspecialchars($v['title']) ?>">
                            <?= htmlspecialchars($v['title']) ?>
                        </div>
                    </a>
                    <div class="video-card-meta">
                        <span>👁 <?= number_format($v['views']) ?> views</span>
                        <span><?= date('M j, Y', strtotime($v['created_at'])) ?></span>
                    </div>

                    <?php if (!empty($v['cat_names'])): ?>
                        <div class="video-card-cats">
                            <?php
                            $catNamesArr = explode('||', $v['cat_names']);
                            $catSlugsArr = explode('||', $v['cat_slugs']);
                            foreach ($catNamesArr as $idx => $cName):
                                if (empty($cName)) continue;
                                $cSlug = $catSlugsArr[$idx] ?? '';
                            ?>
                                <a href="index.php?category=<?= urlencode($cSlug) ?>" class="mini-cat-chip">
                                    <?= htmlspecialchars($cName) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($cardIndex === 6): ?>
                <div class="video-card grid-ad-card" style="grid-column: span 1; display: flex; align-items: center; justify-content: center; background: #11221c; border-color: #00b894;">
                    <?= renderAdSlot('grid_inline_1') ?>
                </div>
            <?php endif; ?>

            <?php if ($cardIndex === 12): ?>
                <div class="video-card grid-ad-card" style="grid-column: span 1; display: flex; align-items: center; justify-content: center; background: #11221c; border-color: #00b894;">
                    <?= renderAdSlot('grid_inline_2') ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- Pagination with Ellipsis -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="index.php?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">&laquo; Prev</a>
            <?php endif; ?>

            <?php
            $range = 2;
            for ($p = 1; $p <= $totalPages; $p++):
                if ($p == 1 || $p == $totalPages || ($p >= $page - $range && $p <= $page + $range)):
            ?>
                    <?php if ($p === $page): ?>
                        <span class="active"><?= $p ?></span>
                    <?php else: ?>
                        <a href="index.php?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"><?= $p ?></a>
                    <?php endif; ?>
                <?php elseif ($p == $page - $range - 1 || $p == $page + $range + 1): ?>
                    <span class="dots">&hellip;</span>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="index.php?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
