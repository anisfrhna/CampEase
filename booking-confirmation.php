<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$booking_id = $_GET['id'] ?? 0;
if (!$booking_id) {
    die('Invalid booking ID.');
}

$stmt = $pdo->prepare("
    SELECT r.*, s.name as site_name, u.full_name, u.email, u.phone
    FROM reservations r
    JOIN sites s ON r.site_id = s.id
    JOIN users u ON r.user_id = u.id
    WHERE r.id = ? AND r.user_id = ? AND r.status = 'confirmed'
");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    die('Booking not found, not confirmed, or does not belong to you.');
}

include 'header.php';
?>

<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-header" style="background-color: #6B4F3C; color: white; text-align: center;">
            <h2 class="mb-0">Booking Confirmation</h2>
        </div>
        <div class="card-body p-4">
            <h4>CampEase – Bagan Lalang</h4>
            <p>Thank you for your booking. Please present this confirmation when you register at the campsite.</p>
            <hr>
            <h5>Booking Details</h5>
            <table class="table table-borderless">
                <tr><th width="30%">Booking ID:</th><td><strong>#<?= $booking['id'] ?></strong></td></tr>
                <tr><th>Campsite:</th><td><?= htmlspecialchars($booking['site_name']) ?></td></tr>
                <tr><th>Check-in:</th><td><?= date('d M Y', strtotime($booking['check_in'])) ?> (after 3:00 PM)</td></tr>
                <tr><th>Check-out:</th><td><?= date('d M Y', strtotime($booking['check_out'])) ?> (before 12:00 PM)</td></tr>
                <tr><th>Nights:</th><td><?= $booking['total_nights'] ?></td></tr>
                <tr><th>Total Paid:</th><td>RM <?= number_format($booking['total_price'], 2) ?></td></tr>
                <tr><th>Payment Status:</th><td><?= ucfirst($booking['payment_status']) ?></td></tr>
            </table>

            <h5>Camper Information</h5>
            <table class="table table-borderless">
                <tr><th width="30%">Name:</th><td><?= htmlspecialchars($booking['full_name']) ?></td></tr>
                <tr><th>Email:</th><td><?= htmlspecialchars($booking['email']) ?></td></tr>
                <tr><th>Phone:</th><td><?= htmlspecialchars($booking['phone'] ?? '-') ?></td></tr>
            </table>

            <?php if (!empty($booking['addons'])): ?>
                <h5>Add-ons Selected</h5>
                <ul>
                    <?php
                    $addons = json_decode($booking['addons'], true);
                    if (is_array($addons)) {
                        foreach ($addons as $a) {
                            echo '<li>' . htmlspecialchars($a['name']) . ' – RM ' . number_format($a['price'], 2) . '</li>';
                        }
                    }
                    ?>
                </ul>
            <?php endif; ?>

            <div class="alert alert-info mt-3" style="background-color: #e8d9c5; border-color: #dcc9b5; color: #5e4b3a;">
                <i class="fas fa-info-circle"></i> Please bring this confirmation (digital or printed) when you arrive. Check‑in after 3:00 PM, check‑out before 12:00 PM.
            </div>
        </div>
        <div class="card-footer text-center bg-white">
            <button onclick="window.print();" class="btn btn-primary">
                <i class="fas fa-print"></i> Print / Save as PDF
            </button>
            <a href="user_dashboard.php" class="btn btn-secondary ms-2">Back to Dashboard</a>
        </div>
    </div>
</div>

<style>
    @media print {
        .btn, .card-footer, .sidebar, header, footer { display: none; }
        .card-header { background-color: #6B4F3C !important; color: white !important; }
        .card { border: none; box-shadow: none; }
    }
</style>

<?php include 'footer.php'; ?>