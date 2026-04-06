<?php
require_once '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: manage-addons.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM addons WHERE id = ?");
$stmt->execute([$id]);
$addon = $stmt->fetch();
if (!$addon) {
    header('Location: manage-addons.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $status = $_POST['status'] ?? 'active';

    if (empty($name) || $price <= 0) {
        $error = 'Name and price are required, price must be > 0.';
    } else {
        $update = $pdo->prepare("UPDATE addons SET name=?, price=?, status=? WHERE id=?");
        if ($update->execute([$name, $price, $status, $id])) {
            $success = 'Add-on updated successfully!';
            $stmt->execute([$id]);
            $addon = $stmt->fetch();
        } else {
            $error = 'Database error.';
        }
    }
}
include 'header.php';
?>

<div class="form-card">
    <div class="form-header d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
        <h3><i class="fas fa-edit me-2 text-primary"></i>Edit Add-on</h3>
        <a href="manage-addons.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>

    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="name" class="form-label fw-bold">Add-on Name *</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($addon['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="price" class="form-label fw-bold">Price (RM) *</label>
            <input type="number" step="0.01" min="0" class="form-control" id="price" name="price" value="<?= $addon['price'] ?>" required>
        </div>
        <div class="mb-4">
            <label for="status" class="form-label fw-bold">Status</label>
            <select class="form-control" id="status" name="status">
                <option value="active" <?= $addon['status']=='active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $addon['status']=='inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>
        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Add-on</button>
            <a href="manage-addons.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>