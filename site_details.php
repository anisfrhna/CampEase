<?php
require_once 'config.php';
include 'header.php';

$site_id = $_GET['site_id'] ?? 0;
$check_in = $_GET['check_in'] ?? '';
$check_out = $_GET['check_out'] ?? '';

if (!$site_id) {
    echo '<div class="container my-5"><div class="alert alert-danger">Campsite not specified.</div><a href="sites.php" class="btn btn-primary">Back to Campsites</a></div>';
    include 'footer.php';
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM sites WHERE id = ?");
$stmt->execute([$site_id]);
$site = $stmt->fetch();

if (!$site) {
    echo '<div class="container my-5"><div class="alert alert-danger">Campsite not found.</div><a href="sites.php" class="btn btn-primary">Back to Campsites</a></div>';
    include 'footer.php';
    exit;
}

$dates_selected = ($check_in && $check_out);
$is_available = false;
$nights = 0;
$base_total = 0;
$addons = [];

if ($dates_selected) {
    $check_sql = "SELECT COUNT(*) FROM reservations WHERE site_id = ? AND status IN ('pending','confirmed') AND (check_in < ? AND check_out > ?)";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$site_id, $check_out, $check_in]);
    $overlap = $check_stmt->fetchColumn();
    $is_available = ($overlap == 0);

    if ($is_available) {
        $nights = (new DateTime($check_out))->diff(new DateTime($check_in))->days;
        $base_total = $nights * $site['price_per_night'];

        // Get add‑ons for this campsite (via the link table)
        $addon_stmt = $pdo->prepare("
            SELECT a.* FROM addons a
            INNER JOIN site_addons sa ON a.id = sa.addon_id
            WHERE sa.site_id = ? AND a.status = 'active'
            ORDER BY a.price ASC
        ");
        $addon_stmt->execute([$site_id]);
        $addons = $addon_stmt->fetchAll();
    }
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8">
            <h1><?= htmlspecialchars($site['name']) ?></h1>
            
            <div class="mb-4">
                <img src="<?= htmlspecialchars($site['image'] ?? 'https://via.placeholder.com/800x400?text=No+Image') ?>" 
                     alt="<?= htmlspecialchars($site['name']) ?>" 
                     class="img-fluid rounded shadow" 
                     style="width: 100%; max-height: 400px; object-fit: cover;">
            </div>
            
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">About this campsite</h5>
                    <p class="card-text"><?= nl2br(htmlspecialchars($site['description'])) ?></p>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Amenities</h5>
                    <ul class="list-unstyled row">
                        <li class="col-md-6"><i class="fas fa-mosque me-2"></i> Surau</li>
                        <li class="col-md-6"><i class="fas fa-utensils me-2"></i> Food & Beverage</li>
                        <li class="col-md-6"><i class="fas fa-parking me-2"></i> Parking</li>
                        <li class="col-md-6"><i class="fas fa-toilet me-2"></i> Toilets</li>
                        <li class="col-md-6"><i class="fas fa-fire me-2"></i> Campfire Area</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header" style="background-color: #6B4F3C; color: white;">
                    <h5 class="mb-0">Your booking</h5>
                </div>
                <div class="card-body">
                    <?php if (!$dates_selected): ?>
                        <form method="GET" action="">
                            <input type="hidden" name="site_id" value="<?= $site_id ?>">
                            <div class="mb-3">
                                <label for="check_in" class="form-label">Check-in</label>
                                <input type="date" class="form-control" id="check_in" name="check_in" required>
                            </div>
                            <div class="mb-3">
                                <label for="check_out" class="form-label">Check-out</label>
                                <input type="date" class="form-control" id="check_out" name="check_out" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Check Availability</button>
                        </form>
                    <?php elseif (!$is_available): ?>
                        <div class="alert alert-warning">Sorry, this campsite is not available for the selected dates.</div>
                        <a href="?site_id=<?= $site_id ?>" class="btn btn-primary w-100">Choose different dates</a>
                    <?php else: ?>
                        <p><strong>Check-in:</strong> <?= date('d M Y', strtotime($check_in)) ?></p>
                        <p><strong>Check-out:</strong> <?= date('d M Y', strtotime($check_out)) ?></p>
                        <p><strong>Nights:</strong> <?= $nights ?></p>
                        <hr>
                        <p><strong>Price per night:</strong> RM <?= number_format($site['price_per_night'], 2) ?></p>
                        
                        <?php if (!empty($addons)): ?>
                        <div class="mb-3">
                            <label class="fw-bold">Extras (optional)</label>
                            <div class="border rounded p-3 mt-2">
                                <?php foreach ($addons as $addon): ?>
                                <div class="form-check mb-2">
                                    <input class="form-check-input addon-checkbox" type="checkbox" 
                                           name="addons[]" value="<?= $addon['id'] ?>" 
                                           data-price="<?= $addon['price'] ?>"
                                           data-name="<?= htmlspecialchars($addon['name']) ?>">
                                    <label class="form-check-label">
                                        <?= htmlspecialchars($addon['name']) ?> – RM <?= number_format($addon['price'], 2) ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <hr>
                        <h5>Total: <span id="dynamicTotal">RM <?= number_format($base_total, 2) ?></span></h5>
                        
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <form action="booking-success.php" method="POST" class="mt-4" id="bookingForm">
                                <input type="hidden" name="site_id" value="<?= $site_id ?>">
                                <input type="hidden" name="check_in" value="<?= $check_in ?>">
                                <input type="hidden" name="check_out" value="<?= $check_out ?>">
                                <input type="hidden" name="price_per_night" value="<?= $site['price_per_night'] ?>">
                                <input type="hidden" name="full_name" value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>">
                                <input type="hidden" name="email" value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>">
                                <input type="hidden" name="phone" value="<?= htmlspecialchars($_SESSION['user_phone'] ?? '') ?>">
                                <input type="hidden" name="total_price" id="total_price_input" value="<?= $base_total ?>">
                                <input type="hidden" name="addons_json" id="addons_json" value="">
                                <input type="hidden" name="nights" value="<?= $nights ?>">
                                <button type="submit" class="btn btn-success w-100">Confirm Booking</button>
                            </form>
                        <?php else: ?>
                            <a href="login.php?redirect=<?= urlencode('site-details.php?site_id=' . $site_id . '&check_in=' . $check_in . '&check_out=' . $check_out) ?>" class="btn btn-primary w-100 mt-4">Login to Book</a>
                        <?php endif; ?>
                        <div class="mt-3 text-center">
                            <a href="?site_id=<?= $site_id ?>" class="small">Choose different dates</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.addon-checkbox');
    const totalSpan = document.getElementById('dynamicTotal');
    const totalInput = document.getElementById('total_price_input');
    const addonsJson = document.getElementById('addons_json');
    const nights = <?= $nights ?>;
    const basePrice = <?= $site['price_per_night'] ?>;
    const baseTotal = basePrice * nights;

    function updateTotal() {
        let addonTotal = 0;
        let selected = [];
        checkboxes.forEach(cb => {
            if (cb.checked) {
                const price = parseFloat(cb.dataset.price);
                addonTotal += price;
                selected.push({
                    name: cb.dataset.name,
                    price: price
                });
            }
        });
        const newTotal = baseTotal + addonTotal;
        totalSpan.innerText = 'RM ' + newTotal.toFixed(2);
        totalInput.value = newTotal;
        addonsJson.value = JSON.stringify(selected);
    }

    checkboxes.forEach(cb => cb.addEventListener('change', updateTotal));
    updateTotal();
});
</script>

<?php include 'footer.php'; ?>