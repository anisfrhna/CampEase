<?php
require_once '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Handle simple status update (booking status only)
if (isset($_GET['action']) && isset($_GET['id']) && isset($_GET['status'])) {
    $id = $_GET['id'];
    $status = $_GET['status'];
    $pdo->prepare("UPDATE reservations SET status = ? WHERE id = ?")->execute([$status, $id]);
    header('Location: manage-reservations.php?msg=updated');
    exit;
}

$sql = "SELECT r.*, u.full_name, u.email, u.phone, s.name as site_name 
        FROM reservations r
        JOIN users u ON r.user_id = u.id
        JOIN sites s ON r.site_id = s.id
        WHERE 1=1";
$params = [];

if (isset($_GET['filter_status']) && $_GET['filter_status'] !== '') {
    $sql .= " AND r.status = ?";
    $params[] = $_GET['filter_status'];
}
if (isset($_GET['filter_source']) && $_GET['filter_source'] !== '') {
    $sql .= " AND r.source = ?";
    $params[] = $_GET['filter_source'];
}
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $sql .= " AND (u.full_name LIKE ? OR u.email LIKE ?)";
    $search = '%' . $_GET['search'] . '%';
    $params[] = $search;
    $params[] = $search;
}
$sql .= " ORDER BY r.booking_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reservations = $stmt->fetchAll();

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-calendar-check me-2"></i>Manage Reservations</h2>
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
        <div class="alert alert-success mb-0">Status updated!</div>
    <?php endif; ?>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="filter-card">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label for="filter_status" class="form-label fw-bold">Booking Status</label>
            <select class="form-select" id="filter_status" name="filter_status">
                <option value="">All</option>
                <option value="pending" <?= isset($_GET['filter_status']) && $_GET['filter_status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="confirmed" <?= isset($_GET['filter_status']) && $_GET['filter_status'] == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                <option value="cancelled" <?= isset($_GET['filter_status']) && $_GET['filter_status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="filter_source" class="form-label fw-bold">Source</label>
            <select class="form-select" id="filter_source" name="filter_source">
                <option value="">All</option>
                <option value="online" <?= isset($_GET['filter_source']) && $_GET['filter_source'] == 'online' ? 'selected' : '' ?>>Online</option>
                <option value="walkin" <?= isset($_GET['filter_source']) && $_GET['filter_source'] == 'walkin' ? 'selected' : '' ?>>Walk‑in</option>
            </select>
        </div>
        <div class="col-md-4">
            <label for="search" class="form-label fw-bold">Search</label>
            <input type="text" class="form-control" id="search" name="search" placeholder="Name or email..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-2"></i>Apply</button>
        </div>
        <div class="col-md-2">
            <a href="manage-reservations.php" class="btn btn-outline-secondary w-100">Clear</a>
        </div>
    </form>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                
                    <th>ID</th>
                    <th>Camper</th>
                    <th>Site</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Nights</th>
                    <th>Total</th>
                    <th>Booking Status</th>
                    <th>Payment Status</th>
                    <th>Source</th>
                    <th>Receipt</th>
                    <th>Cancellation Reason</th>
                    <th>Booked On</th>
                    <th>Actions</th>
                </thead>
            <tbody>
                <?php if (count($reservations) > 0): ?>
                    <?php foreach ($reservations as $res): ?>
                    <tr>
                        <td>#<?= $res['id'] ?></td>
                        <td><strong><?= htmlspecialchars($res['full_name']) ?></strong><br><small><?= htmlspecialchars($res['email']) ?></small></td>
                        <td><?= htmlspecialchars($res['site_name']) ?></td>
                        <td><?= date('d M Y', strtotime($res['check_in'])) ?></td>
                        <td><?= date('d M Y', strtotime($res['check_out'])) ?></td>
                        <td><?= $res['total_nights'] ?></td>
                        <td>RM <?= number_format($res['total_price'], 2) ?></td>
                        <td>
                            <?php if ($res['status'] === 'pending'): ?>
                                <span class="badge badge-pending">Pending</span>
                            <?php elseif ($res['status'] === 'confirmed'): ?>
                                <span class="badge badge-confirmed">Confirmed</span>
                            <?php else: ?>
                                <span class="badge badge-cancelled">Cancelled</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($res['payment_status'] === 'pending'): ?>
                                <span class="badge badge-payment-pending">Pending</span>
                            <?php elseif ($res['payment_status'] === 'paid'): ?>
                                <span class="badge badge-paid">Paid</span>
                            <?php elseif ($res['payment_status'] === 'refunded'): ?>
                                <span class="badge bg-secondary">Refunded</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?= $res['payment_status'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($res['source'] === 'online'): ?>
                                <span class="badge bg-info">Online</span>
                            <?php elseif ($res['source'] === 'walkin'): ?>
                                <span class="badge bg-warning text-dark">Walk‑in</span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($res['receipt_path'])): ?>
                                <a href="../<?= $res['receipt_path'] ?>" target="_blank" class="btn btn-sm btn-outline-info"><i class="fas fa-file-pdf"></i> View</a>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($res['status'] === 'cancelled' && !empty($res['cancellation_reason'])): ?>
                                <?= htmlspecialchars($res['cancellation_reason']) ?>
                            <?php elseif ($res['status'] === 'cancelled'): ?>
                                No reason provided
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td><?= date('d M Y', strtotime($res['booking_date'])) ?></td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Actions</button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="view-reservation.php?id=<?= $res['id'] ?>"><i class="fas fa-eye me-2"></i>View Details</a></li>
                                    <li><a class="dropdown-item" href="print-receipt.php?id=<?= $res['id'] ?>" target="_blank"><i class="fas fa-receipt me-2"></i>Print Receipt</a></li>
                                    <?php if ($res['status'] === 'pending' || $res['status'] === 'confirmed'): ?>
                                        <li><a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#cancelModal" data-id="<?= $res['id'] ?>">Cancel Booking</a></li>
                                    <?php endif; ?>
                                    <?php if ($res['status'] === 'pending'): ?>
                                        <li><a class="dropdown-item text-success" href="?action=update&id=<?= $res['id'] ?>&status=confirmed" onclick="return confirm('Confirm this booking?')"><i class="fas fa-check-circle me-2"></i>Confirm Booking</a></li>
                                    <?php endif; ?>
                                    <?php if ($res['status'] === 'cancelled' && $res['payment_status'] !== 'refunded'): ?>
                                        <li><a class="dropdown-item text-success" href="mark-refunded.php?id=<?= $res['id'] ?>" onclick="return confirm('Mark this booking as refunded?')"><i class="fas fa-credit-card me-2"></i>Mark as Refunded</a></li>
                                    <?php endif; ?>
                                    <?php if ($res['payment_status'] === 'pending' && !empty($res['receipt_path'])): ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-success" href="confirm-payment.php?id=<?= $res['id'] ?>" onclick="return confirm('Confirm payment for this booking?')"><i class="fas fa-credit-card me-2"></i>Confirm Payment</a></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="14" class="text-center py-5 text-muted"><i class="fas fa-calendar-times fa-3x mb-3"></i><br>No reservations found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #6B4F3C; color: white;">
                <h5 class="modal-title">Cancel Booking</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="GET" action="cancel-booking.php">
                <div class="modal-body">
                    <input type="hidden" name="id" id="cancelBookingId">
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason for cancellation (optional)</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" placeholder="e.g., Bad weather, site maintenance, etc."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('[data-bs-target="#cancelModal"]').forEach(button => {
    button.addEventListener('click', function() {
        const bookingId = this.getAttribute('data-id');
        document.getElementById('cancelBookingId').value = bookingId;
    });
});
</script>

<?php include 'footer.php'; ?>