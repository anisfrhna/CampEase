<?php
require_once '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: manage-reservations.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT r.*, u.full_name, u.email, u.phone, s.name as site_name
    FROM reservations r
    JOIN users u ON r.user_id = u.id
    JOIN sites s ON r.site_id = s.id
    WHERE r.id = ?
");
$stmt->execute([$id]);
$res = $stmt->fetch();

if (!$res) {
    header('Location: manage-reservations.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tent_location_notes = $_POST['tent_location_notes'] ?? '';
    $pdo->prepare("UPDATE reservations SET tent_location_notes = ? WHERE id = ?")->execute([$tent_location_notes, $id]);
    header("Location: view-reservation.php?id=$id&msg=updated");
    exit;
}

include 'header.php';
?>

<div class="card">
    <div class="card-header" style="background:#6B4F3C; color:white;">
        <h4 class="mb-0"><i class="fas fa-receipt me-2"></i>Reservation #<?= $res['id'] ?></h4>
    </div>
    <div class="card-body">
        <?php if (isset($_GET['msg'])): ?><div class="alert alert-success">Tent location updated.</div><?php endif; ?>
        <div class="row">
            <div class="col-md-6">
                <h5>Camper Information</h5>
                <table class="table table-sm">
                    <tr><th>Name:</th><td><?= htmlspecialchars($res['full_name']) ?></td></tr>
                    <tr><th>Email:</th><td><?= htmlspecialchars($res['email']) ?></td></tr>
                    <tr><th>Phone:</th><td><?= htmlspecialchars($res['phone'] ?? '-') ?></td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h5>Booking Summary</h5>
                <table class="table table-sm">
                    <tr><th>Site:</th><td><?= htmlspecialchars($res['site_name']) ?></td></tr>
                    <tr><th>Check-in:</th><td><?= date('d M Y', strtotime($res['check_in'])) ?></td></tr>
                    <tr><th>Check-out:</th><td><?= date('d M Y', strtotime($res['check_out'])) ?></td></tr>
                    <tr><th>Nights:</th><td><?= $res['total_nights'] ?></td></tr>
                    <tr><th>Total Price:</th><td><strong>RM <?= number_format($res['total_price'], 2) ?></strong></td></tr>
                    <tr><th>Status:</th><td>
                        <?php if ($res['status'] === 'pending'): ?>
                            <span class="badge badge-pending">Pending</span>
                        <?php elseif ($res['status'] === 'confirmed'): ?>
                            <span class="badge badge-confirmed">Confirmed</span>
                        <?php else: ?>
                            <span class="badge badge-cancelled">Cancelled</span>
                        <?php endif; ?>
                    </td></tr>
                    <tr><th>Payment Status:</th><td>
                        <?php if ($res['payment_status'] === 'pending'): ?>
                            <span class="badge badge-payment-pending">Pending</span>
                        <?php elseif ($res['payment_status'] === 'paid'): ?>
                            <span class="badge badge-paid">Paid</span>
                        <?php else: ?>
                            <span class="badge bg-secondary"><?= $res['payment_status'] ?></span>
                        <?php endif; ?>
                    </td></tr>
                    <tr><th>Booked On:</th><td><?= date('d M Y H:i', strtotime($res['booking_date'])) ?></td></tr>
                </table>
            </div>
        </div>

        <?php if (!empty($res['addons'])): ?>
        <div class="row">
            <div class="col-12">
                <h5>Add-ons Selected</h5>
                <ul>
                    <?php $addons = json_decode($res['addons'], true); if (is_array($addons)) foreach ($addons as $a): ?>
                        <li><?= htmlspecialchars($a['name']) ?> – RM <?= number_format($a['price'], 2) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="tent_location_notes" class="form-label fw-bold">Tent Location Notes</label>
                <textarea class="form-control" id="tent_location_notes" name="tent_location_notes" rows="3"><?= htmlspecialchars($res['tent_location_notes'] ?? '') ?></textarea>
                <small>Assign a specific spot for the camper.</small>
            </div>
            <button type="submit" class="btn btn-primary">Update Location</button>
            <a href="manage-reservations.php" class="btn btn-secondary">Back to List</a>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>