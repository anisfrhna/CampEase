<?php
require_once 'config.php';
// session_start() is already in header.php

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'camper') {
    header('Location: login.php');
    exit;
}

include 'header.php';

// Fetch user's bookings
$stmt = $pdo->prepare("
    SELECT r.*, s.name as site_name 
    FROM reservations r 
    JOIN sites s ON r.site_id = s.id 
    WHERE r.user_id = ? 
    ORDER BY r.booking_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll();
?>

<div class="container my-5">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Camper') ?>!</h1>
    <p class="lead">Your bookings are listed below.</p>

    <?php if (isset($_SESSION['cancel_success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['cancel_success'] ?></div>
        <?php unset($_SESSION['cancel_success']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['cancel_error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['cancel_error'] ?></div>
        <?php unset($_SESSION['cancel_error']); ?>
    <?php endif; ?>

    <?php if (count($bookings) > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Site</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Nights</th>
                        <th>Total</th>
                        <th>Booking Status</th>
                        <th>Payment Status</th>
                        <th>Cancellation Reason</th>
                        <th>Confirmation</th>
                        <th>Cancel</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $b): 
                        $can_cancel = false;
                        $cancel_reason = '';
                        $now = new DateTime();
                        $check_in = new DateTime($b['check_in']);
                        $hours_until_checkin = $now->diff($check_in)->h + ($now->diff($check_in)->days * 24);

                        if ($b['status'] === 'pending' || $b['status'] === 'quoted') {
                            $can_cancel = true;
                        } elseif ($b['status'] === 'confirmed') {
                            if ($hours_until_checkin > 24) {
                                $can_cancel = true;
                            } else {
                                $cancel_reason = 'Cannot cancel within 24 hours of check‑in.';
                            }
                        } elseif ($b['status'] === 'cancelled') {
                            $cancel_reason = 'Booking already cancelled.';
                        } elseif ($b['status'] === 'completed') {
                            $cancel_reason = 'Booking already completed.';
                        }
                    ?>
                    <tr>
                        <td>#<?= $b['id'] ?></td>
                        <td><?= htmlspecialchars($b['site_name']) ?></td>
                        <td><?= $b['check_in'] ?></td>
                        <td><?= $b['check_out'] ?></td>
                        <td><?= $b['total_nights'] ?></td>
                        <td>RM <?= number_format($b['total_price'], 2) ?></td>
                        <td>
                            <?php if ($b['status'] === 'confirmed'): ?>
                                <span class="badge bg-success">Confirmed</span>
                            <?php elseif ($b['status'] === 'pending'): ?>
                                <span class="badge bg-warning text-dark">Pending</span>
                            <?php elseif ($b['status'] === 'quoted'): ?>
                                <span class="badge bg-info text-dark">Quoted</span>
                            <?php elseif ($b['status'] === 'cancelled'): ?>
                                <span class="badge bg-danger">Cancelled</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?= ucfirst($b['status']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($b['payment_status'] === 'pending'): ?>
                                <span class="badge bg-warning text-dark">Pending</span>
                            <?php elseif ($b['payment_status'] === 'paid'): ?>
                                <span class="badge bg-success">Paid</span>
                            <?php elseif ($b['payment_status'] === 'refunded'): ?>
                                <span class="badge bg-secondary">Refunded</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?= ucfirst($b['payment_status']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($b['status'] === 'cancelled' && !empty($b['cancellation_reason'])): ?>
                                <?= htmlspecialchars($b['cancellation_reason']) ?>
                            <?php elseif ($b['status'] === 'cancelled'): ?>
                                No reason provided
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($b['status'] === 'confirmed'): ?>
                                <a href="booking-confirmation.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-receipt"></i> View Confirmation
                                </a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($b['status'] === 'cancelled'): ?>
                                <span class="text-muted"><i class="fas fa-check-circle"></i> Cancelled</span>
                            <?php elseif ($can_cancel): ?>
                                <form method="POST" action="cancel_booking.php" onsubmit="return confirm('Are you sure you want to cancel this booking? Your payment will be refunded within 3-5 working days.');">
                                    <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-times-circle"></i> Cancel
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted" title="<?= htmlspecialchars($cancel_reason) ?>">
                                    <i class="fas fa-ban"></i> Not allowed
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            You have no bookings yet. <a href="sites.php" class="alert-link">Book a campsite now!</a>
        </div>
    <?php endif; ?>
    
    <div class="mt-4">
        <a href="sites.php" class="btn btn-primary"><i class="fas fa-tree me-2"></i>Browse Campsites</a>
        <a href="logout.php" class="btn btn-secondary"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
    </div>
</div>

<?php include 'footer.php'; ?>