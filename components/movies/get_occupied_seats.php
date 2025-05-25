<?php
require_once __DIR__ . '/../../config/db_connect.php'; // Veritabanı bağlantısı

header('Content-Type: application/json');

if (!isset($db) || !($db instanceof PDO)) {
    echo json_encode(['error' => 'Veritabanı bağlantısı başarısız']);
    exit();
}

$session_id = isset($_GET['session_id']) ? intval($_GET['session_id']) : 0;

if ($session_id <= 0) {
    echo json_encode(['error' => 'Geçersiz seans ID']);
    exit();
}

try {
    $stmt = $db->prepare("
        SELECT seat_number
        FROM tickets
        WHERE session_id = ? AND status = 'active'
    ");
    $stmt->execute([$session_id]);
    $occupied_seats = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode(['occupied_seats' => $occupied_seats]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Veritabanı hatası: ' . $e->getMessage()]);
}
?>
