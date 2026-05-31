<?php
include 'db_connect.php';

// Sanitise and validate inputs
$court_id = filter_input(INPUT_POST, 'court_id', FILTER_VALIDATE_INT);
$date     = $_POST['date']    ?? '';
$time     = $_POST['time']    ?? '';
$name     = trim($_POST['name'] ?? '');
$email    = filter_input(INPUT_POST, 'email',   FILTER_SANITIZE_EMAIL);
$user_id  = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT) ?: null;

if (!$court_id || !$date || !$time || !$name) {
    die(json_encode(['error' => 'Missing required fields.']));
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    die(json_encode(['error' => 'Invalid date format.']));
}
if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
    die(json_encode(['error' => 'Invalid time format.']));
}
if (strlen($name) > 100) {
    die(json_encode(['error' => 'Name too long.']));
}

// Reject past dates
$today = new DateTime('today');
$bookingDate = DateTime::createFromFormat('Y-m-d', $date);
if ($bookingDate < $today) {
    die(json_encode(['error' => 'Cannot book a court for a past date.']));
}

// Reject same-day slots that have already passed
if ($bookingDate == $today) {
    $slotHour   = (int) substr($time, 0, 2);
    $currentHour = (int) (new DateTime())->format('H');
    if ($slotHour <= $currentHour) {
        die(json_encode(['error' => 'This time slot has already passed for today.']));
    }
}

// Double-check availability (prevent race condition)
$checkSql  = "SELECT id FROM bookings WHERE court_id = :court_id AND booking_date = :date AND time_slot = :time";
$checkStmt = $pdo->prepare($checkSql);
$checkStmt->execute(['court_id' => $court_id, 'date' => $date, 'time' => $time]);
if ($checkStmt->fetch()) {
    die(json_encode(['error' => 'Sorry, this court is already booked for the selected time.']));
}

// Generate unique booking reference  CB-YYYY-NNNN
$year     = date('Y');
$countSql = "SELECT COUNT(*) FROM bookings WHERE YEAR(booking_date) = :year";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute(['year' => $year]);
$seq      = (int) $countStmt->fetchColumn() + 1;
$reference = 'CB-' . $year . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);

// Insert booking
$insertSql = "INSERT INTO bookings (user_id, court_id, customer_name, customer_email, booking_date, time_slot, reference)
              VALUES (:user_id, :court_id, :name, :email, :date, :time, :reference)";
$insertStmt = $pdo->prepare($insertSql);
$success = $insertStmt->execute([
    'user_id'   => $user_id,
    'court_id'  => $court_id,
    'name'      => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
    'email'     => $email ?: null,
    'date'      => $date,
    'time'      => $time,
    'reference' => $reference,
]);

header('Content-Type: application/json');
if ($success) {
    $bookingId = $pdo->lastInsertId();
    echo json_encode(['success' => true, 'reference' => $reference, 'booking_id' => $bookingId]);
} else {
    echo json_encode(['error' => 'Booking failed. Please try again later.']);
}
?>
