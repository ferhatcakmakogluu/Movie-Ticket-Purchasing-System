<?php
session_start();
require_once __DIR__ . '/../../config/db_connect.php';

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php?error=please_login');
    exit();
}

// URL'den payment_id ve mesajı al
$message = isset($_GET['message']) ? $_GET['message'] : '';
$payment_id = isset($_GET['payment_id']) ? intval($_GET['payment_id']) : 0;

if ($payment_id <= 0 || $message !== 'payment_saved') {
    header('Location: ../../movies.php?error=invalid_request');
    exit();
}

try {
    // Ödeme bilgilerini al
    $stmt = $db->prepare("
        SELECT p.*, u.username
        FROM payments p
        JOIN users u ON p.user_id = u.user_id
        WHERE p.payment_id = ?
    ");
    $stmt->execute([$payment_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        header('Location: ../../movies.php?error=invalid_payment');
        exit();
    }

    // Bilet bilgilerini al
    $stmt = $db->prepare("
        SELECT t.ticket_id, t.seat_number, t.purchase_date, t.status,
               s.session_date, s.session_time, s.price,
               m.title AS movie_title,
               th.theater_name
        FROM tickets t
        JOIN sessions s ON t.session_id = s.session_id
        JOIN movies m ON s.movie_id = m.movie_id
        JOIN theaters th ON s.theater_id = th.theater_id
        WHERE t.payment_id = ?
    ");
    $stmt->execute([$payment_id]);
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    header('Location: ../../movies.php?error=database_error&details=' . urlencode($e->getMessage()));
    exit();
}

// Kart numarasını maskele
$masked_card_number = '**** **** **** ' . substr($payment['card_number'], -4);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ödeme Başarılı - Sinema Bilet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 800px;
            margin-top: 50px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            background: linear-gradient(135deg, #ffffff, #f1f3f5);
        }
        .card-header {
            background-color: #007bff;
            color: white;
            border-radius: 15px 15px 0 0;
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
            padding: 15px;
        }
        .card-body {
            padding: 25px;
        }
        .card-body p {
            margin: 0.5rem 0;
            font-size: 1.1rem;
        }
        .card-body strong {
            color: #343a40;
        }
        .ticket-list {
            margin-top: 20px;
        }
        .ticket-item {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
        }
        .btn-home {
            background-color: #28a745;
            border: none;
            padding: 10px 20px;
            font-size: 1.1rem;
            border-radius: 25px;
            transition: background-color 0.3s;
        }
        .btn-home:hover {
            background-color: #218838;
        }
        .success-icon {
            font-size: 3rem;
            color: #28a745;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon">✔</div>
        <div class="card">
            <div class="card-header">
                Ödeme Başarıyla Tamamlandı!
            </div>
            <div class="card-body">
                <h5>Ödeme Bilgileri</h5>
                <p><strong>Ödeme ID:</strong> <?php echo htmlspecialchars($payment['payment_id']); ?></p>
                <p><strong>Kullanıcı:</strong> <?php echo htmlspecialchars($payment['username']); ?></p>
                <p><strong>Kart Sahibi:</strong> <?php echo htmlspecialchars($payment['card_full_name']); ?></p>
                <p><strong>Kart Numarası:</strong> <?php echo htmlspecialchars($masked_card_number); ?></p>
                <p><strong>Son Kullanma Tarihi:</strong> <?php echo htmlspecialchars($payment['card_expiration_date']); ?></p>
                <p><strong>Toplam Tutar:</strong> <?php echo number_format($payment['total_amount'], 2, ',', '.'); ?> ₺</p>

                <div class="ticket-list">
                    <h5>Bilet Bilgileri</h5>
                    <?php if (!empty($tickets)): ?>
                        <?php foreach ($tickets as $ticket): ?>
                            <div class="ticket-item">
                                <p><strong>Bilet ID:</strong> <?php echo htmlspecialchars($ticket['ticket_id']); ?></p>
                                <p><strong>Film:</strong> <?php echo htmlspecialchars($ticket['movie_title']); ?></p>
                                <p><strong>Salon:</strong> <?php echo htmlspecialchars($ticket['theater_name']); ?></p>
                                <p><strong>Tarih:</strong> <?php echo date('d.m.Y', strtotime($ticket['session_date'])); ?></p>
                                <p><strong>Saat:</strong> <?php echo htmlspecialchars($ticket['session_time']); ?></p>
                                <p><strong>Koltuk:</strong> <?php echo htmlspecialchars($ticket['seat_number']); ?></p>
                                <p><strong>Fiyat:</strong> <?php echo number_format($ticket['price'], 2, ',', '.'); ?> ₺</p>
                                <p><strong>Durum:</strong> <?php echo $ticket['status'] === 'active' ? 'Aktif' : 'İptal'; ?></p>
                                <p><strong>Satın Alma Tarihi:</strong> <?php echo date('d.m.Y H:i', strtotime($ticket['purchase_date'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Bu ödeme için bilet bulunamadı.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="text-center">
            <a href="../../index.php" class="btn btn-home">Ana Sayfaya Dön</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>