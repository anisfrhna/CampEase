<?php
require_once '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';
$addons = $pdo->query("SELECT * FROM addons WHERE status = 'active' ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price_per_night = floatval($_POST['price_per_night'] ?? 0);
    $capacity = intval($_POST['capacity'] ?? 1);
    $status = $_POST['status'] ?? 'active';
    $selected_addons = $_POST['addons'] ?? [];

    // Image upload handling
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $tmp_name = $_FILES['image']['tmp_name'];
        $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($extension, $allowed)) {
            $new_filename = uniqid('site_') . '.' . $extension;
            if (move_uploaded_file($tmp_name, $upload_dir . $new_filename)) {
                $image_path = 'uploads/' . $new_filename;
            } else {
                $error = 'Failed to upload image.';
            }
        } else {
            $error = 'Invalid file type. Only JPG, PNG, GIF, WEBP allowed.';
        }
    }

    if (empty($name) || $price_per_night <= 0 || $capacity <= 0) {
        $error = 'Name, price and capacity are required with valid values.';
    }

    if (!$error) {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO sites (name, description, image, price_per_night, capacity, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $image_path, $price_per_night, $capacity, $status]);
            $site_id = $pdo->lastInsertId();

            if (!empty($selected_addons)) {
                $insert_addon = $pdo->prepare("INSERT INTO site_addons (site_id, addon_id) VALUES (?, ?)");
                foreach ($selected_addons as $addon_id) {
                    $insert_addon->execute([$site_id, $addon_id]);
                }
            }
            $pdo->commit();
            $success = 'Campsite added successfully!';
            $name = $description = '';
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

include 'header.php';
?>

<div class="form-card">
    <div class="form-header d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
        <h3><i class="fas fa-plus-circle me-2 text-success"></i>Add New Campsite</h3>
        <a href="manage-sites.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>

    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="name" class="form-label fw-bold">Campsite Name *</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label fw-bold">Description</label>
            <textarea class="form-control" id="description" name="description" rows="4"></textarea>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="price_per_night" class="form-label fw-bold">Price per Night (RM) *</label>
                <input type="number" step="0.01" min="0" class="form-control" id="price_per_night" name="price_per_night" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="capacity" class="form-label fw-bold">Capacity (persons) *</label>
                <input type="number" min="1" class="form-control" id="capacity" name="capacity" required>
            </div>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label fw-bold">Campsite Image</label>
            <input type="file" class="form-control" id="image" name="image" accept="image/*">
        </div>

        <?php if (!empty($addons)): ?>
        <div class="mb-3">
            <label class="form-label fw-bold">Available Add-ons</label>
            <div class="addon-group">
                <?php foreach ($addons as $addon): ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="addons[]" value="<?= $addon['id'] ?>" id="addon_<?= $addon['id'] ?>">
                    <label class="form-check-label" for="addon_<?= $addon['id'] ?>">
                        <strong><?= htmlspecialchars($addon['name']) ?></strong> – RM <?= number_format($addon['price'], 2) ?>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="mb-4">
            <label for="status" class="form-label fw-bold">Status</label>
            <select class="form-control" id="status" name="status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>

        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <button type="submit" class="btn btn-submit text-white px-4" style="background:#6B4F3C;"><i class="fas fa-save me-2"></i>Save Campsite</button>
            <a href="manage-sites.php" class="btn btn-outline-secondary px-4">Cancel</a>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>