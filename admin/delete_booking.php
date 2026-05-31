<?php
require 'auth.php';
$rootDir = dirname(__DIR__);
include $rootDir . '/db_connect.php';

header('Content-Type: application/json');

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) { echo json_encode(['error' => 'Invalid ID.']); exit; }

$del = $pdo->prepare("DELETE FROM bookings WHERE id = :id");
$del->execute(['id' => $id]);

if ($del->rowCount() > 0) {
    echo json_encode(['success' => true, 'message' => 'Booking deleted.']);
} else {
    echo json_encode(['error' => 'Booking not found.']);
}
