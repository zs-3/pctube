<?php
// admin/search_terms.php

require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../config/db.php';

$pdo = getDB();
$message = '';
$error = '';

// Handle Add Search Term
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $term = trim($_POST['term'] ?? '');
    if (!empty($term)) {
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM search_terms WHERE term = ?");
        $stmtCheck->execute([$term]);
        if ($stmtCheck->fetchColumn() > 0) {
            $error = "Search term already exists.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO search_terms (term) VALUES (?)");
            $stmt->execute([$term]);
            $message = "Search term '$term' added successfully.";
        }
    } else {
        $error = "Search term cannot be empty.";
    }
}

// Handle Delete Search Term
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $termId = intval($_GET['id']);
    $stmt = $pdo->prepare("DELETE FROM search_terms WHERE id = ?");
    $stmt->execute([$termId]);
    $message = "Search term deleted successfully.";
}

// Fetch all search terms
$terms = $pdo->query("SELECT * FROM search_terms ORDER BY term ASC")->fetchAll();
?>

<?php if ($message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 320px 1fr; gap: 30px;">
    <div>
        <h3 style="margin-bottom: 15px;">Add Search Term</h3>
        <form action="search_terms.php" method="POST" style="background: #1a1a1a; padding: 20px; border-radius: 6px; border: 1px solid #2a2a2a;">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="term">Search Term / Keyword</label>
                <input type="text" id="term" name="term" class="form-control" required placeholder="e.g. amateur hd">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Add Term</button>
        </form>
    </div>

    <div>
        <h3 style="margin-bottom: 15px;">Existing Search Terms (<?= count($terms) ?>)</h3>
        <?php if (empty($terms)): ?>
            <p style="color: #888;">No search terms added yet.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Term</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($terms as $t): ?>
                        <tr>
                            <td><?= $t['id'] ?></td>
                            <td><strong><?= htmlspecialchars($t['term']) ?></strong></td>
                            <td>
                                <a href="search_terms.php?action=delete&id=<?= $t['id'] ?>" onclick="return confirm('Are you sure you want to delete this term?');" class="btn btn-danger" style="padding: 4px 8px; font-size: 12px;">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
