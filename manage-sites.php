<?php
require_once '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT image FROM sites WHERE id = ?");
    $stmt->execute([$id]);
    $img = $stmt->fetchColumn();
    if ($img && file_exists('../' . $img)) unlink('../' . $img);
    $pdo->prepare("DELETE FROM sites WHERE id = ?")->execute([$id]);
    header('Location: manage-sites.php?msg=deleted');
    exit;
}

$sites = $pdo->query("SELECT * FROM sites ORDER BY id DESC")->fetchAll();
include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="fas fa-tree me-2"></i>Campsites</h2>
    <a href="add-site.php" class="btn btn-success"><i class="fas fa-plus-circle me-2"></i>Add New Campsite</a>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
    <div class="alert alert-success">Campsite deleted successfully!</div>
<?php endif; ?>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price/Night</th>
                    <th>Capacity</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sites as $site): ?>
                <tr>
                    <td>#<?= $site['id'] ?></td>
                    <td>
                        <?php if (!empty($site['image']) && file_exists('../' . $site['image'])): ?>
                            <img src="../<?= htmlspecialchars($site['image']) ?>" width="50" height="50" style="object-fit: cover; border-radius: 5px;">
                        <?php else: ?>
                            <span class="text-muted">No image</span>
                        <?php endif; ?>
                    </td>
                    <td><strong><?= htmlspecialchars($site['name']) ?></strong></td>
                    <td><?= htmlspecialchars(substr($site['description'] ?? '', 0, 50)) ?>...</td>
                    <td>RM <?= number_format($site['price_per_night'], 2) ?></td>
                    <td><?= $site['capacity'] ?> persons</td>
                    <td><?= $site['status'] === 'active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                    <td>
                        <a href="edit-site.php?id=<?= $site['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i> Edit</a>
                        <a href="?action=delete&id=<?= $site['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this campsite?')"><i class="fas fa-trash"></i> Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($sites)): ?>
                <tr><td colspan="8" class="text-center py-4 text-muted">No campsites found. Click "Add New Campsite".</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>