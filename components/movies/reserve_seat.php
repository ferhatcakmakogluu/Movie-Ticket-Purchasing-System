<?php
require_once __DIR__ . '/../../config/db_connect.php';
session_start();
header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? 0;
$session_id = $_POST['session_id'] ?? '';
$seat_code = $_POST['seat_code'] ?? '';
if (!$user_id || !$session_id || !$seat_code) {
    echo json_encode(['success' => false, 'msg' => 'Eksik bilgi']);
    exit;
}
// Koltuk zaten rezerve edilmiş mi?
$stmt = $db->prepare('SELECT COUNT(*) FROM reservations WHERE session_id = ? AND seat_code = ?');
$stmt->execute([$session_id, $seat_code]);
if ($stmt->fetchColumn() > 0) {
    echo json_encode(['success' => false, 'msg' => 'Bu koltuk zaten dolu!']);
    exit;
}
// Rezervasyon ekle
$stmt2 = $db->prepare('INSERT INTO reservations (session_id, seat_code, user_id, created_at) VALUES (?, ?, ?, NOW())');
$ok = $stmt2->execute([$session_id, $seat_code, $user_id]);
if ($ok) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'msg' => 'Rezervasyon kaydedilemedi!']);
} 