<?php
require_once '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Get month and year from GET, default to current month
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$year  = isset($_GET['year'])  ? intval($_GET['year'])  : date('Y');

if ($month < 1) { $month = 12; $year--; }
if ($month > 12) { $month = 1; $year++; }

$firstDay = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = date('t', $firstDay);
$startingWeekday = date('w', $firstDay); // 0 = Sunday

// Fetch all active campsites
$allSites = $pdo->query("SELECT id, name FROM sites WHERE status = 'active' ORDER BY id")->fetchAll();
$totalSites = count($allSites);

// Build arrays per day: count and list of booked site names
$bookedSiteNames = [];
$bookedCounts = [];
for ($day = 1; $day <= $daysInMonth; $day++) {
    $date = sprintf("%04d-%02d-%02d", $year, $month, $day);
    $nextDate = date('Y-m-d', strtotime($date . ' +1 day'));

    // Get booked site names for this day
    $sql = "SELECT s.name FROM reservations r
            JOIN sites s ON r.site_id = s.id
            WHERE r.status IN ('pending','confirmed')
            AND r.check_in < ? AND r.check_out > ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nextDate, $date]);
    $names = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $bookedSiteNames[$day] = $names;
    $bookedCounts[$day] = count($names);
}

include 'header.php';
?>

<div class="card mb-4">
    <div class="card-header" style="background-color: #6B4F3C; color: white;">
        <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Occupancy Calendar</h5>
    </div>
    <div class="card-body">
        <!-- Month navigation -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="?month=<?= $month-1 ?>&year=<?= $year ?>" class="btn btn-outline-secondary">&laquo; Previous Month</a>
            <h4 class="mb-0"><?= date('F Y', $firstDay) ?></h4>
            <a href="?month=<?= $month+1 ?>&year=<?= $year ?>" class="btn btn-outline-secondary">Next Month &raquo;</a>
        </div>

        <!-- Calendar grid -->
        <div class="calendar-grid">
            <div class="calendar-header">
                <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
            </div>
            <div class="calendar-days">
                <?php
                // Empty cells before the first day
                for ($i = 0; $i < $startingWeekday; $i++) {
                    echo '<div class="calendar-day empty"></div>';
                }
                // Loop through days of the month
                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $count = $bookedCounts[$day];
                    $available = $totalSites - $count;
                    $bookedNames = $bookedSiteNames[$day];

                    // Determine status text and CSS class
                    if ($count == 0) {
                        $statusText = 'No Reservation';
                        $statusClass = 'status-no';
                    } elseif ($count == $totalSites) {
                        $statusText = 'Fully Booked';
                        $statusClass = 'status-full';
                    } else {
                        $statusText = 'Campsite Available';
                        $statusClass = 'status-available';
                    }

                    // Build list of booked names (limit to 5, show + more)
                    $namesHtml = '';
                    if ($count > 0) {
                        $displayNames = array_slice($bookedNames, 0, 5);
                        $namesHtml = '<ul class="booked-names">';
                        foreach ($displayNames as $name) {
                            $namesHtml .= '<li>' . htmlspecialchars($name) . '</li>';
                        }
                        if ($count > 5) {
                            $namesHtml .= '<li class="more">+ ' . ($count - 5) . ' more</li>';
                        }
                        $namesHtml .= '</ul>';
                    }

                    echo '<div class="calendar-day">
                            <div class="day-number">' . $day . '</div>
                            <div class="status-text ' . $statusClass . '">' . $statusText . '</div>
                            <div class="count-text">' . $count . ' booked / ' . $available . ' available</div>
                            ' . $namesHtml . '
                          </div>';
                }
                ?>
            </div>
        </div>

        <!-- Legend -->
        <div class="legend mt-4 d-flex justify-content-center gap-4">
            <div><span class="legend-color status-available"></span> Available (some free)</div>
            <div><span class="legend-color status-full"></span> Fully booked</div>
            <div><span class="legend-color status-no"></span> No reservation</div>
        </div>
    </div>
</div>

<style>
.calendar-grid {
    width: 100%;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow-x: auto;
}
.calendar-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background: #f5f5f5;
    text-align: center;
    font-weight: bold;
    padding: 10px 0;
    border-bottom: 1px solid #ddd;
}
.calendar-days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
}
.calendar-day {
    min-height: 140px;
    border: 1px solid #eee;
    padding: 8px;
    text-align: center;
    background: #fffaf2;
    transition: background 0.2s;
    display: flex;
    flex-direction: column;
}
.calendar-day.empty {
    background: #f9f9f9;
}
.day-number {
    font-weight: bold;
    font-size: 1.2rem;
    margin-bottom: 6px;
    color: #4a3a2c;
}
.status-text {
    font-weight: 600;
    margin-bottom: 4px;
    font-size: 0.85rem;
}
.status-available {
    color: #28a745;
}
.status-full {
    color: #dc3545;
}
.status-no {
    color: #6c757d;
}
.count-text {
    font-size: 0.7rem;
    color: #a67b5b;
    background: #f8efe3;
    display: inline-block;
    padding: 2px 6px;
    border-radius: 20px;
    margin-bottom: 6px;
}
.booked-names {
    list-style: none;
    padding: 0;
    margin: 4px 0 0 0;
    text-align: left;
    font-size: 0.7rem;
    max-height: 60px;
    overflow-y: auto;
}
.booked-names li {
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.booked-names li.more {
    font-style: italic;
    color: #a67b5b;
}
.legend-color {
    display: inline-block;
    width: 20px;
    height: 20px;
    border-radius: 4px;
    margin-right: 5px;
    vertical-align: middle;
}
.legend-color.status-available { background: #28a745; }
.legend-color.status-full { background: #dc3545; }
.legend-color.status-no { background: #6c757d; }
@media (max-width: 768px) {
    .calendar-day {
        min-height: 120px;
        padding: 6px;
    }
    .day-number {
        font-size: 1rem;
    }
    .status-text {
        font-size: 0.75rem;
    }
    .count-text {
        font-size: 0.65rem;
    }
    .booked-names {
        font-size: 0.65rem;
        max-height: 50px;
    }
}
</style>

<?php include 'footer.php'; ?>