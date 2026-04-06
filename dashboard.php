<?php
require_once '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Get counts
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role='camper'")->fetchColumn();
$total_sites = $pdo->query("SELECT COUNT(*) FROM sites")->fetchColumn();
$pending_bookings = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status='pending'")->fetchColumn();
$total_bookings = $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn();

// Recent 5 bookings
$recent_bookings = $pdo->query("
    SELECT r.*, u.full_name, s.name as site_name 
    FROM reservations r
    JOIN users u ON r.user_id = u.id
    JOIN sites s ON r.site_id = s.id
    ORDER BY r.booking_date DESC
    LIMIT 5
")->fetchAll();

include 'header.php';
?>

<h2 class="mb-4"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h2>

<div class="row">
    <div class="col-md-3">
        <div class="stat-card d-flex justify-content-between align-items-center">
            <div><h6 class="text-muted">Total Users</h6><h3 class="mb-0"><?= $total_users ?></h3></div>
            <div class="stat-icon"><i class="fas fa-users"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card d-flex justify-content-between align-items-center">
            <div><h6 class="text-muted">Campsites</h6><h3 class="mb-0"><?= $total_sites ?></h3></div>
            <div class="stat-icon"><i class="fas fa-tree"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card d-flex justify-content-between align-items-center">
            <div><h6 class="text-muted">Pending Bookings</h6><h3 class="mb-0"><?= $pending_bookings ?></h3></div>
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card d-flex justify-content-between align-items-center">
            <div><h6 class="text-muted">Total Bookings</h6><h3 class="mb-0"><?= $total_bookings ?></h3></div>
            <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
        </div>
    </div>
</div>

<div class="recent-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Bookings</h5>
        <a href="manage-reservations.php" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <?php if (count($recent_bookings) > 0): ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th>ID</th><th>Camper</th><th>Site</th><th>Check-in</th><th>Check-out</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach ($recent_bookings as $b): ?>
                <tr>
                    <td>#<?= $b['id'] ?></td>
                    <td><?= htmlspecialchars($b['full_name']) ?></td>
                    <td><?= htmlspecialchars($b['site_name']) ?></td>
                    <td><?= date('d M Y', strtotime($b['check_in'])) ?></td>
                    <td><?= date('d M Y', strtotime($b['check_out'])) ?></td>
                    <td>
                        <?php if ($b['status'] === 'pending'): ?>
                            <span class="badge badge-pending">Pending</span>
                        <?php elseif ($b['status'] === 'confirmed'): ?>
                            <span class="badge badge-confirmed">Confirmed</span>
                        <?php else: ?>
                            <span class="badge badge-cancelled">Cancelled</span>
                        <?php endif; ?>
                    </td>
                    <td><a href="view-reservation.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i> View</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p class="text-muted text-center py-3">No recent bookings found.</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>