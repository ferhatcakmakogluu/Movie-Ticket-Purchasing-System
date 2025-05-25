<?php
require_once __DIR__ . '/../../config/db_connect.php';
header('Content-Type: application/json');

$session_id = isset($_GET['session_id']) ? intval($_GET['session_id']) : 0;
if (!$session_id) {
    echo json_encode(['success' => false, 'msg' => 'Session id yok']);
    exit;
}

// Seans ile salonu bul
$stmt = $db->prepare('SELECT theater_id FROM sessions WHERE session_id = ?');
$stmt->execute([$session_id]);
$session = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$session) {
    echo json_encode(['success' => false, 'msg' => 'Seans bulunamadı']);
    exit;
}
$theater_id = $session['theater_id'];

// Salon bilgisi
$stmt2 = $db->prepare('SELECT theater_name, capacity FROM theaters WHERE theater_id = ? AND status = "active"');
$stmt2->execute([$theater_id]);
$theater = $stmt2->fetch(PDO::FETCH_ASSOC);
if (!$theater) {
    echo json_encode(['success' => false, 'msg' => 'Salon bulunamadı']);
    exit;
}
// Dolu koltuklar
$stmt3 = $db->prepare('SELECT seat_code FROM reservations WHERE session_id = ?');
$stmt3->execute([$session_id]);
$fullSeats = $stmt3->fetchAll(PDO::FETCH_COLUMN);
echo json_encode([
    'success' => true,
    'theater_name' => $theater['theater_name'],
    'capacity' => $theater['capacity'],
    'full_seats' => $fullSeats
]); 