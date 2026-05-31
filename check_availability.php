<?php
// check_availability.php
include 'db_connect.php';

// Get parameters from AJAX request
$date  = $_GET['date']  ?? '';
$time  = $_GET['time']  ?? '';
$sport = $_GET['sport'] ?? 'all';

if (!$date || !$time) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Missing date or time']);
    exit;
}

// Reject past dates
$today = new DateTime('today');
$bookingDate = DateTime::createFromFormat('Y-m-d', $date);
if (!$bookingDate || $bookingDate < $today) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Cannot check availability for a past date.']);
    exit;
}

// Build sport filter clause
$sportClause = '';
$params = ['date' => $date, 'time' => $time];
if ($sport === 'football' || $sport === 'tennis') {
    $sportClause = ' AND sport_type = :sport';
    $params['sport'] = $sport;
}

// Find all courts that are NOT booked for the given date and time
$sql = "SELECT id, name, sport_type FROM courts
        WHERE is_active = 1$sportClause
        AND id NOT IN (
            SELECT court_id FROM bookings
            WHERE booking_date = :date AND time_slot = :time
        )";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$available = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($available);
?>