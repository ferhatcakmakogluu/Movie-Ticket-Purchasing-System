<?php
require_once __DIR__ . '/../../config/db_connect.php';

if (isset($_GET['theater_id'])) {
    $theaterId = intval($_GET['theater_id']);
    $stmt = $db->prepare("SELECT capacity FROM theaters WHERE theater_id = ?");
    $stmt->execute([$theaterId]);
    $capacity = $stmt->fetchColumn();
    if ($capacity) {
        echo json_encode(['success' => true, 'kapasite' => (int)$capacity]);
    } else {
        echo json_encode(['success' => false, 'msg' => 'Kapasite bulunamadı.']);
    }
} else {
    echo json_encode(['success' => false, 'msg' => 'Geçersiz istek.']);
}
?>
