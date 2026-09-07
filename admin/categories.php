<?php
// admin/categories.php

require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../config/db.php';

$pdo = getDB();
$message = '';
$error = '';

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['name'] ?? '');
    if (!empty($name)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

        // Check uniqueness
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE name = ? OR slug = ?");
        $stmtCheck->execute([$name, $slug]);
        if ($stmtCheck->fetchColumn() > 0) {
            $error = "Category with this name or slug already exists.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
            $stmt->execute([$name, $slug]);
            $message = "Category '$name' added successfully.";
        }
    } else {
        $error = "Category name cannot be empty.";
    }
}

// Handle Delete Category
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $catId = intval($_GET['id']);
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$catId]);
    $message = "Category deleted successfully.";
}

// Fetch all categories with video count
$categories = $pdo->query("
    SELECT c.*, COUNT(vc.video_id) AS video_count
    FROM categories c
    LEFT JOIN video_categories vc ON c.id = vc.category_id
    GROUP BY c.id
    ORDER BY c.name ASC
")->fetchAll();
?>

<?php if ($message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 300px 1fr; gap: 30px;">
    <div>
        <h3 style="margin-bottom: 15px;">Add New Category</h3>
        <form action="categories.php" method="POST" style="background: #1a1a1a; padding: 20px; border-radius: 6px; border: 1px solid #2a2a2a;">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="name">Category Name</label>
                <input type="text" id="name" name="name" class="form-control" required placeholder="e.g. Hardcore">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Add Category</button>
        </form>
    </div>

    <div>
        <h3 style="margin-bottom: 15px;">Existing Categories (<?= count($categories) ?>)</h3>
        <?php if (empty($categories)): ?>
            <p style="color: #888;">No categories available.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Videos</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><?= $cat['id'] ?></td>
                            <td><strong><?= htmlspecialchars($cat['name']) ?></strong></td>
                            <td><code><?= htmlspecialchars($cat['slug']) ?></code></td>
                            <td><span class="badge"><?= $cat['video_count'] ?></span></td>
                            <td>
                                <a href="categories.php?action=delete&id=<?= $cat['id'] ?>" onclick="return confirm('Deleting category will unlink it from videos. Continue?');" class="btn btn-danger" style="padding: 4px 8px; font-size: 12px;">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
