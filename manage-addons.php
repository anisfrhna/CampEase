<?php
require_once '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Also remove from site_addons
    $pdo->prepare("DELETE FROM site_addons WHERE addon_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM addons WHERE id = ?")->execute([$id]);
    header('Location: manage-addons.php?msg=deleted');
    exit;
}

$addons = $pdo->query("SELECT * FROM addons ORDER BY id DESC")->fetchAll();
include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="fas fa-boxes me-2"></i>Manage Add-ons</h2>
    <a href="add-addon.php" class="btn btn-success"><i class="fas fa-plus-circle me-2"></i>Add New Add-on</a>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
    <div class="alert alert-success">Add-on deleted successfully.</div>
<?php endif; ?>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Price (RM)</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($addons as $addon): ?>
                 <tr>
                    <td>#<?= $addon['id'] ?></td>
                    <td><strong><?= htmlspecialchars($addon['name']) ?></strong></td>
                    <td>RM <?= number_format($addon['price'], 2) ?></td>
                    <td>
                        <?php if ($addon['status'] === 'active'): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit-addon.php?id=<?= $addon['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i> Edit</a>
                        <a href="?action=delete&id=<?= $addon['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this add-on? It will be removed from all campsites.')"><i class="fas fa-trash"></i> Delete</a>
                    </td>
                 </tr>
                <?php endforeach; ?>
                <?php if (empty($addons)): ?>
                 <tr><td colspan="5" class="text-center py-4 text-muted">No add-ons found. Click "Add New Add-on".</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>