<?php
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$reservation_id = $_GET['id'] ?? 0;
if (!$reservation_id) {
    die('Invalid reservation ID.');
}

$stmt = $pdo->prepare("SELECT r.*, s.name as site_name FROM reservations r JOIN sites s ON r.site_id = s.id WHERE r.id = ? AND r.user_id = ?");
$stmt->execute([$reservation_id, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    die('Reservation not found.');
}

$error = '';
$success = '';

// If receipt already exists, just show success
if (!empty($booking['receipt_path'])) {
    $success = 'Your payment receipt has already been uploaded. Your booking is pending admin verification.';
} else {
    // Process upload only if form submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/receipts/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

            $file = $_FILES['receipt'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
            if (in_array($ext, $allowed)) {
                $new_filename = 'receipt_' . $reservation_id . '_' . time() . '.' . $ext;
                $destination = $upload_dir . $new_filename;
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $update = $pdo->prepare("UPDATE reservations SET receipt_path = ? WHERE id = ?");
                    $update->execute([$destination, $reservation_id]);
                    $success = 'Receipt uploaded successfully. Your booking is now pending admin verification.';
                } else {
                    $error = 'Failed to upload file.';
                }
            } else {
                $error = 'Only JPG, PNG, PDF files are allowed.';
            }
        } else {
            $error = 'Please select a receipt file to upload.';
        }
    }
}

include 'header.php';
?>

<div class="container my-5">
    <!-- QR Code Hero -->
    <div class="text-center mb-5">
        <img src="uploads/QR.jpg" alt="Payment QR Code" class="img-fluid rounded shadow-lg" style="max-width: 300px; border: 4px solid #6B4F3C; padding: 10px; background: white;">
        <h2 class="mt-4">Scan to Pay</h2>
        <p class="lead">Bank: Maybank | Account: 1234 5678 9012 | Name: CampEase Sdn Bhd</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header" style="background-color: #6B4F3C; color: white;">
                    <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Complete Your Payment</h5>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success text-center">
                            <?= $success ?>
                            <hr>
                            <a href="user_dashboard.php" class="btn btn-primary mt-2">View My Bookings</a>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Booking Summary</h5>
                                <p><strong>Booking ID:</strong> #<?= $booking['id'] ?></p>
                                <p><strong>Site:</strong> <?= htmlspecialchars($booking['site_name']) ?></p>
                                <p><strong>Dates:</strong> <?= $booking['check_in'] ?> to <?= $booking['check_out'] ?></p>
                                <p><strong>Total:</strong> RM <?= number_format($booking['total_price'], 2) ?></p>
                            </div>
                            <div class="col-md-6">
                                <h5>Upload Payment Receipt</h5>
                                <?php if ($error): ?>
                                    <div class="alert alert-danger"><?= $error ?></div>
                                <?php endif; ?>
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="receipt" class="form-label">Receipt (JPG, PNG, PDF)</label>
                                        <input type="file" class="form-control" id="receipt" name="receipt" required>
                                        <small class="text-muted">You must upload a receipt to complete your payment.</small>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100">Submit Receipt</button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>