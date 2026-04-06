<?php
require_once '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$site_id = $_GET['site_id'] ?? 0;
$selected_site = null;
$addons = [];
$error = '';
$new_booking_id = $_GET['new_booking'] ?? 0;

if ($site_id) {
    $stmt = $pdo->prepare("SELECT * FROM sites WHERE id = ?");
    $stmt->execute([$site_id]);
    $selected_site = $stmt->fetch();
    if ($selected_site) {
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book') {
    $site_id = $_POST['site_id'] ?? 0;
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $check_in = $_POST['check_in'] ?? '';
    $check_out = $_POST['check_out'] ?? '';
    $price_per_night = floatval($_POST['price_per_night'] ?? 0);
    $selected_addons = $_POST['addons'] ?? [];
    $payment_collected = isset($_POST['payment_collected']);

    if (!$site_id || !$full_name || !$email || !$phone || !$check_in || !$check_out || !$price_per_night) {
        $error = 'All fields are required.';
    } else {
        $check_sql = "SELECT COUNT(*) FROM reservations WHERE site_id = ? AND status IN ('pending','confirmed') AND (check_in < ? AND check_out > ?)";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$site_id, $check_out, $check_in]);
        if ($check_stmt->fetchColumn() > 0) {
            $error = 'Campsite is not available for the selected dates.';
        } else {
            $nights = (new DateTime($check_out))->diff(new DateTime($check_in))->days;
            $base_total = $nights * $price_per_night;

            $addons_json = null;
            $addon_total = 0;
            if (!empty($selected_addons)) {
                $addon_details = [];
                foreach ($selected_addons as $addon_id) {
                    foreach ($addons as $a) {
                        if ($a['id'] == $addon_id) {
                            $addon_details[] = ['name' => $a['name'], 'price' => $a['price']];
                            $addon_total += $a['price'];
                            break;
                        }
                    }
                }
                $addons_json = json_encode($addon_details);
            }
            $total_price = $base_total + $addon_total;

            // Get or create user
            $user_stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $user_stmt->execute([$email]);
            $user = $user_stmt->fetch();
            if (!$user) {
                $username = explode('@', $email)[0] . rand(100, 999);
                $password_hash = password_hash('walkin123', PASSWORD_DEFAULT);
                $insert_user = $pdo->prepare("INSERT INTO users (username, password_hash, full_name, email, phone, role) VALUES (?, ?, ?, ?, ?, 'camper')");
                $insert_user->execute([$username, $password_hash, $full_name, $email, $phone]);
                $user_id = $pdo->lastInsertId();
            } else {
                $user_id = $user['id'];
            }

            $status = 'confirmed';
            $payment_status = $payment_collected ? 'paid' : 'pending';

            // Insert with source = 'walkin'
            $insert = $pdo->prepare("INSERT INTO reservations (user_id, site_id, check_in, check_out, price_per_night, total_price, addons, status, payment_status, source) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'walkin')");
            $insert->execute([$user_id, $site_id, $check_in, $check_out, $price_per_night, $total_price, $addons_json, $status, $payment_status]);

            $new_booking_id = $pdo->lastInsertId();
            header("Location: manual-booking.php?new_booking=$new_booking_id");
            exit;
        }
    }
}

// AJAX availability check
if (isset($_GET['check_availability']) && isset($_GET['site_id']) && isset($_GET['check_in']) && isset($_GET['check_out'])) {
    $site_id = $_GET['site_id'];
    $check_in = $_GET['check_in'];
    $check_out = $_GET['check_out'];
    $check_sql = "SELECT COUNT(*) FROM reservations WHERE site_id = ? AND status IN ('pending','confirmed') AND (check_in < ? AND check_out > ?)";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$site_id, $check_out, $check_in]);
    $overlap = $check_stmt->fetchColumn();
    header('Content-Type: application/json');
    echo json_encode(['available' => ($overlap == 0)]);
    exit;
}

$new_booking = null;
if ($new_booking_id) {
    $stmt = $pdo->prepare("
        SELECT r.*, s.name as site_name, u.full_name, u.email, u.phone
        FROM reservations r
        JOIN sites s ON r.site_id = s.id
        JOIN users u ON r.user_id = u.id
        WHERE r.id = ?
    ");
    $stmt->execute([$new_booking_id]);
    $new_booking = $stmt->fetch();
}

$sites = $pdo->query("SELECT id, name FROM sites WHERE status = 'active' ORDER BY name")->fetchAll();

include 'header.php';
?>

<?php if ($new_booking): ?>
<div class="success-card">
    <div class="success-header d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
        <h3 class="text-success"><i class="fas fa-check-circle me-2"></i>Booking Successful!</h3>
        <a href="manual-booking.php" class="btn btn-outline-secondary">Create Another Booking</a>
    </div>
    <div class="p-3">
        <p><strong>Booking ID:</strong> #<?= $new_booking['id'] ?></p>
        <p><strong>Camper:</strong> <?= htmlspecialchars($new_booking['full_name']) ?></p>
        <p><strong>Site:</strong> <?= htmlspecialchars($new_booking['site_name']) ?></p>
        <p><strong>Dates:</strong> <?= $new_booking['check_in'] ?> to <?= $new_booking['check_out'] ?></p>
        <p><strong>Total:</strong> RM <?= number_format($new_booking['total_price'], 2) ?></p>
        <p><strong>Payment Status:</strong> <?= ucfirst($new_booking['payment_status']) ?></p>
        <p><strong>Source:</strong> Walk‑in</p>
        <?php if (!empty($new_booking['addons'])): ?>
            <p><strong>Add-ons:</strong> <?php $addons_list = json_decode($new_booking['addons'], true); echo implode(', ', array_column($addons_list, 'name')); ?></p>
        <?php endif; ?>
        <hr>
        <div class="text-center">
            <a href="print-receipt.php?id=<?= $new_booking['id'] ?>" target="_blank" class="btn btn-primary"><i class="fas fa-print"></i> Print Receipt</a>
            <a href="manage-reservations.php" class="btn btn-secondary ms-2">Manage Reservations</a>
        </div>
    </div>
</div>
<?php else: ?>
<div class="form-card">
    <div class="form-header d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
        <h3><i class="fas fa-user-plus me-2 text-primary"></i>Manual Booking (Walk‑in)</h3>
        <a href="dashboard.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>

    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <form method="GET" class="mb-4">
        <div class="row g-3">
            <div class="col-md-8">
                <label for="site_id" class="form-label">Select Campsite</label>
                <select class="form-select" id="site_id" name="site_id" required>
                    <option value="">-- Choose a campsite --</option>
                    <?php foreach ($sites as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= ($site_id == $s['id']) ? 'selected' : '' ?>><?= htmlspecialchars($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Load Site Details</button>
            </div>
        </div>
    </form>

    <?php if ($selected_site): ?>
    <form method="POST" id="bookingForm">
        <input type="hidden" name="action" value="book">
        <input type="hidden" name="site_id" value="<?= $selected_site['id'] ?>">
        <input type="hidden" name="price_per_night" value="<?= $selected_site['price_per_night'] ?>">

        <div class="row mb-3">
            <div class="col-md-5">
                <label for="check_in" class="form-label">Check-in Date *</label>
                <input type="date" class="form-control" id="check_in" name="check_in" required>
                <small class="text-muted">After 3:00 PM</small>
            </div>
            <div class="col-md-5">
                <label for="check_out" class="form-label">Check-out Date *</label>
                <input type="date" class="form-control" id="check_out" name="check_out" required>
                <small class="text-muted">Before 12:00 PM</small>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" id="checkAvailBtn" class="btn btn-outline-info w-100">Check Availability</button>
            </div>
        </div>
        <div id="availabilityMsg" class="mb-3"></div>

        <h5>Customer Information</h5>
        <div class="row mb-3">
            <div class="col-md-6"><label for="full_name" class="form-label">Full Name *</label><input type="text" class="form-control" id="full_name" name="full_name" required></div>
            <div class="col-md-6"><label for="email" class="form-label">Email *</label><input type="email" class="form-control" id="email" name="email" required></div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6"><label for="phone" class="form-label">Phone *</label><input type="text" class="form-control" id="phone" name="phone" required></div>
        </div>

        <?php if (!empty($addons)): ?>
        <div class="mb-3">
            <label class="fw-bold">Extras (optional)</label>
            <div class="border rounded p-3 mt-2">
                <?php foreach ($addons as $addon): ?>
                <div class="form-check mb-2">
                    <input class="form-check-input addon-checkbox" type="checkbox" name="addons[]" value="<?= $addon['id'] ?>" data-price="<?= $addon['price'] ?>" data-name="<?= htmlspecialchars($addon['name']) ?>">
                    <label class="form-check-label"><?= htmlspecialchars($addon['name']) ?> – RM <?= number_format($addon['price'], 2) ?></label>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="payment_collected" id="payment_collected">
            <label class="form-check-label" for="payment_collected">Payment collected on site (mark as paid)</label>
        </div>

        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <button type="submit" class="btn btn-success"><i class="fas fa-save me-2"></i>Create Booking</button>
            <a href="manual-booking.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>
    <?php endif; ?>
</div>
<?php endif; ?>

<script>
document.getElementById('checkAvailBtn')?.addEventListener('click', function() {
    const siteId = document.querySelector('input[name="site_id"]').value;
    const checkIn = document.getElementById('check_in').value;
    const checkOut = document.getElementById('check_out').value;
    if (!siteId || !checkIn || !checkOut) {
        document.getElementById('availabilityMsg').innerHTML = '<div class="alert alert-warning">Please select a campsite and both dates first.</div>';
        return;
    }
    fetch(`?check_availability=1&site_id=${siteId}&check_in=${checkIn}&check_out=${checkOut}`)
        .then(res => res.json())
        .then(data => {
            const msgDiv = document.getElementById('availabilityMsg');
            if (data.available) msgDiv.innerHTML = '<div class="alert alert-success">✅ The campsite is available for these dates!</div>';
            else msgDiv.innerHTML = '<div class="alert alert-danger">❌ The campsite is already booked for these dates. Please choose different dates.</div>';
        })
        .catch(err => {
            console.error(err);
            msgDiv.innerHTML = '<div class="alert alert-danger">Error checking availability. Please try again.</div>';
        });
});

<?php if ($selected_site && $dates_selected && $is_available): ?>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.addon-checkbox');
    const totalSpan = document.getElementById('dynamicTotal');
    const totalInput = document.getElementById('total_price_input');
    const addonsJson = document.getElementById('addons_json');
    const nights = <?= $nights ?>;
    const basePrice = <?= $selected_site['price_per_night'] ?>;
    const baseTotal = basePrice * nights;
    function updateTotal() {
        let addonTotal = 0;
        let selected = [];
        checkboxes.forEach(cb => {
            if (cb.checked) {
                addonTotal += parseFloat(cb.dataset.price);
                selected.push({ name: cb.dataset.name, price: parseFloat(cb.dataset.price) });
            }
        });
        const newTotal = baseTotal + addonTotal;
        if (totalSpan) totalSpan.innerText = 'RM ' + newTotal.toFixed(2);
        if (totalInput) totalInput.value = newTotal;
        if (addonsJson) addonsJson.value = JSON.stringify(selected);
    }
    checkboxes.forEach(cb => cb.addEventListener('change', updateTotal));
    updateTotal();
});
<?php endif; ?>
</script>

<?php include 'footer.php'; ?>