<?php
    session_start();
    $user_id = $_SESSION['user_id'];

    require_once __DIR__ . '/../../config/db_connect.php';


    $stmt = $db->prepare("
    SELECT 
        t.ticket_id,
        t.session_id,
        t.user_id,
        t.payment_id,
        t.seat_number,
        t.purchase_date,
        t.status,
        p.card_number,
        p.card_full_name,
        p.card_expiration_date,
        p.total_amount,
        u.username,
        m.title AS movie_title,
        th.theater_name AS theater_name,
        s.session_date,
        s.session_time,
        s.price
    FROM tickets t
    JOIN payments p ON t.payment_id = p.payment_id
    JOIN users u ON t.user_id = u.user_id
    JOIN sessions s ON t.session_id = s.session_id
    JOIN movies m ON s.movie_id = m.movie_id
    JOIN theaters th ON s.theater_id = th.theater_id
    WHERE t.status = 'active' AND t.user_id = :user_id
    ORDER BY t.purchase_date DESC
");
$stmt->execute(['user_id' => $user_id]);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ödemeleri ayırmak istersen (aynı ödeme_id varsa tekrarları ayıklayabiliriz)
$payments = [];
foreach ($tickets as $ticket) {
    $pid = $ticket['payment_id'];
    if (!isset($payments[$pid])) {
        $payments[$pid] = [
            'payment_id' => $pid,
            'card_full_name' => $ticket['card_full_name'],
            'card_expiration_date' => $ticket['card_expiration_date'],
            'card_number' => '**** **** **** ' . substr($ticket['card_number'], -4),
            'total_amount' => $ticket['total_amount'],
            'username' => $ticket['username'],
            'tickets' => []
        ];
    }
    $payments[$pid]['tickets'][] = $ticket;
}
    
    // Kart numarasını maskele
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biletlerim</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../components/login/login.css">
    <style>
        .card {
            border-radius: 1rem;
        }

        .card-body {
            font-size: 0.95rem;
        }

        .card-title {
            font-weight: bold;
            font-size: 1.1rem;
        }
    </style>

</head>
<body>
    <!-- Navbar -->
    <?php include_once '../../components/shared/navbar.php'; ?>

    
    <div class="container my-5">
    <h3 class="mb-4">Biletlerim</h3>
    <?php if (empty($tickets)): ?>
        <div class="alert alert-info text-center" role="alert">
            Henüz satın alınmış bir biletiniz bulunmamaktadır.
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($tickets as $ticket): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($ticket['movie_title']); ?></h5>
                            <p class="card-text mb-2"><strong>Salon:</strong> <?php echo htmlspecialchars($ticket['theater_name']); ?></p>
                            <p class="card-text mb-2"><strong>Tarih:</strong> <?php echo date('d.m.Y', strtotime($ticket['session_date'])); ?></p>
                            <p class="card-text mb-2"><strong>Saat:</strong> <?php echo htmlspecialchars($ticket['session_time']); ?></p>
                            <p class="card-text mb-2"><strong>Koltuk:</strong> <?php echo htmlspecialchars($ticket['seat_number']); ?></p>
                            <p class="card-text mb-2"><strong>Fiyat:</strong> <?php echo number_format($ticket['price'], 2, ',', '.'); ?> ₺</p>
                            <p class="card-text mb-2"><strong>Durum:</strong> 
                                <span class="badge bg-<?php echo $ticket['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo $ticket['status'] === 'active' ? 'Aktif' : 'İptal'; ?>
                                </span>
                            </p>
                            <hr>
                            <h6 class="text-muted">Ödeme Bilgisi</h6>
                            <p class="card-text mb-2"><strong>Kart Sahibi:</strong> <?php echo htmlspecialchars($ticket['card_full_name']); ?></p>
                            <p class="card-text mb-2"><strong>Kart:</strong> **** **** **** <?php echo substr($ticket['card_number'], -4); ?></p>
                            <p class="card-text"><strong>Toplam:</strong> <?php echo number_format($ticket['total_amount'], 2, ',', '.'); ?> ₺</p>
                        </div>
                        <div class="card-footer text-muted text-end small">
                            Satın Alma: <?php echo date('d.m.Y H:i', strtotime($ticket['purchase_date'])); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>



    <!-- Footer -->
    <?php include_once '../../components/shared/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/main.js"></script>
</body>
</html> 