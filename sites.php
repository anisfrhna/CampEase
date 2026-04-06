<?php
require_once 'config.php';
include 'header.php';

// Initialize variables
$check_in = $_GET['check_in'] ?? '';
$check_out = $_GET['check_out'] ?? '';
$site_id = $_GET['site'] ?? 0;

// Get all active sites
$sql = "SELECT * FROM sites WHERE status='active'";
$stmt = $pdo->query($sql);
$sites = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check availability if dates are given
$available_sites = [];
if ($check_in && $check_out) {
    foreach ($sites as $s) {
        $check_sql = "SELECT COUNT(*) FROM reservations WHERE site_id = ? AND status IN ('pending','confirmed') AND (check_in < ? AND check_out > ?)";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$s['id'], $check_out, $check_in]);
        $overlap = $check_stmt->fetchColumn();
        if ($overlap == 0) {
            $available_sites[] = $s['id'];
        }
    }
}
?>

<div class="container my-5">
    <h1 class="mb-4">Available Campsites</h1>

    <!-- Date filter form -->
    <form method="GET" action="sites.php" class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="check_in" class="form-label">Check-in</label>
            <input type="date" class="form-control" id="check_in" name="check_in" value="<?= htmlspecialchars($check_in) ?>" required>
        </div>
        <div class="col-md-4">
            <label for="check_out" class="form-label">Check-out</label>
            <input type="date" class="form-control" id="check_out" name="check_out" value="<?= htmlspecialchars($check_out) ?>" required>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Check Availability</button>
        </div>
    </form>

    <!-- Display sites -->
    <div class="row">
        <?php foreach ($sites as $site): 
            $is_available = true;
            if ($check_in && $check_out) {
                $is_available = in_array($site['id'], $available_sites);
            }
            // Determine image path
            $img = $site['image'] ?? 'https://via.placeholder.com/300x200?text=No+Image';
        ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100 <?= !$is_available ? 'border-danger' : '' ?>">
                <img src="<?= htmlspecialchars($img) ?>" class="card-img-top" alt="<?= htmlspecialchars($site['name']) ?>" style="height: 200px; object-fit: cover;">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($site['name']) ?></h5>
                    <p class="card-text"><?= htmlspecialchars($site['description']) ?></p>
                    <p><strong>RM <?= number_format($site['price_per_night'], 2) ?> / night</strong></p>
                    <p>Capacity: <?= $site['capacity'] ?> persons</p>
                    
                    <?php if ($check_in && $check_out): ?>
                        <?php if ($is_available): ?>
                            <span class="badge bg-success mb-2">Available</span>
                            <a href="site-details.php?site_id=<?= $site['id'] ?>&check_in=<?= urlencode($check_in) ?>&check_out=<?= urlencode($check_out) ?>" class="btn btn-primary">View Details</a>
                        <?php else: ?>
                            <span class="badge bg-danger mb-2">Fully Booked</span>
                            <button class="btn btn-secondary" disabled>Not Available</button>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="?site=<?= $site['id'] ?>" class="btn btn-outline-primary">Select Dates</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'footer.php'; ?>