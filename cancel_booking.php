<?php
include 'db_connect.php';

header('Content-Type: application/json');

$id        = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$name      = trim($_POST['name'] ?? '');

if (!$id || !$name) {
    echo json_encode(['error' => 'Missing booking ID or name.']);
    exit;
}

// Fetch the booking
$stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = :id");
$stmt->execute(['id' => $id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    echo json_encode(['error' => 'Booking not found.']);
    exit;
}

// Ownership check — name must match (case-insensitive)
if (strtolower($booking['customer_name']) !== strtolower(htmlspecialchars($name, ENT_QUOTES, 'UTF-8'))) {
    echo json_encode(['error' => 'You can only cancel your own bookings.']);
    exit;
}

// Cannot cancel past bookings
$bookingDateTime = new DateTime($booking['booking_date'] . ' ' . $booking['time_slot']);
$now             = new DateTime();
if ($bookingDateTime <= $now) {
    echo json_encode(['error' => 'This booking has already taken place and cannot be cancelled.']);
    exit;
}

// Delete the booking
$del = $pdo->prepare("DELETE FROM bookings WHERE id = :id");
$del->execute(['id' => $id]);

echo json_encode(['success' => true, 'message' => 'Booking ' . htmlspecialchars($booking['reference'] ?? '#' . $id) . ' has been cancelled.']);
?>
