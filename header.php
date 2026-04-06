<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampEase Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Italianno&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar {
            background: #6B4F3C;
            min-height: 100vh;
            color: white;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }
        .sidebar a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            padding: 12px 20px;
            display: block;
            transition: all 0.3s;
        }
        .sidebar a:hover {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        .sidebar a i { width: 25px; }
        .content { padding: 30px; }
        .admin-brand {
            font-family: 'Italianno', cursive;
            font-size: 2.2rem;
            font-weight: normal;
            letter-spacing: 1px;
            color: white;
            margin-bottom: 1rem;
            text-align: center;
        }
        /* Common admin styles */
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-icon { font-size: 2.5rem; color: #6B4F3C; opacity: 0.5; }
        .recent-card, .filter-card, .table-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 20px;
            margin-top: 30px;
        }
        .filter-card { margin-top: 0; margin-bottom: 25px; }
        .badge-pending { background-color: #ffc107; color: #000; }
        .badge-confirmed { background-color: #28a745; color: #fff; }
        .badge-cancelled { background-color: #dc3545; color: #fff; }
        .badge-payment-pending { background-color: #fd7e14; color: #fff; }
        .badge-paid { background-color: #20c997; color: #fff; }
        .addon-group { border: 1px solid #ddd; border-radius: 10px; padding: 15px; margin-bottom: 15px; }
        .current-image { max-width: 200px; max-height: 150px; object-fit: cover; border: 1px solid #ddd; padding: 5px; border-radius: 8px; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0 sidebar">
            <div class="p-4 text-center">
                <div class="admin-brand">CampEase Admin</div>
            </div>
            <nav class="nav flex-column">
                <a href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
                <a href="manage-sites.php"><i class="fas fa-tree me-2"></i>Manage Sites</a>
                <a href="manage-reservations.php"><i class="fas fa-calendar-check me-2"></i>Reservations</a>
                <a href="manual-booking.php"><i class="fas fa-user-plus me-2"></i>Manual Booking</a>
                <a href="manage-addons.php"><i class="fas fa-boxes me-2"></i>Add-ons</a>
                <a href="monitor-availability.php"><i class="fas fa-chart-line me-2"></i>Monitor Availability</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
            </nav>
        </div>
        <div class="col-md-10 content">