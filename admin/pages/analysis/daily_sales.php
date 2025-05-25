<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login/login.php');
    exit;
}

require_once __DIR__ . '/../../../config/db_connect.php';

// Tarih seçilmişse işle
$selectedDate = $_GET['date'] ?? date('Y-m-d');

// SQL: Seçilen günün biletlerini ve ilgili film bilgilerini getir
$query = $db->prepare("
                SELECT 
                m.title,
                m.poster_url,
                COUNT(t.ticket_id) AS ticket_count,
                COUNT(DISTINCT t.payment_id) AS payment_count,
                SUM(DISTINCT p.total_amount) AS total_revenue
            FROM tickets t
            INNER JOIN payments p ON t.payment_id = p.payment_id
            INNER JOIN sessions s ON t.session_id = s.session_id
            INNER JOIN movies m ON s.movie_id = m.movie_id
            WHERE DATE(t.purchase_date) = :selectedDate
            AND t.status = 'active'
            GROUP BY m.movie_id
");
$query->execute(['selectedDate' => $selectedDate]);
$sales = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Günlük Bilet Satışları</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
    <style>
        .movie-card {
            border: 1px solid #dee2e6;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            background: #f9f9f9;
            transition: 0.3s;
        }
        .movie-card:hover {
            transform: scale(1.01);
        }
        .movie-poster {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
            border-radius: 8px;
        }
        .calendar-container {
            max-width: 300px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body class="bg-light">

    <?php include_once '../../components/shared/admin-navbar.php'; ?>

    <div class="container py-5">
        <h2 class="text-center mb-4">🎟️ Günlük Bilet Satışları</h2>

        <div class="mb-4 d-flex justify-content-center align-items-center" style="flex-direction: column;">
            <label for="datePicker" class="form-label fs-5">Tarih Seçin:</label>
            <input type="text" id="datePicker" class="form-control w-25" value="<?php echo date('Y-m-d'); ?>">
        </div>

        <!-- Günlük Toplam Kazanç Kutusu -->
        <div class="summary-box mb-4 text-center">
            <h5>Günlük Toplam Gelir</h5>
            <?php
                $selectedDate = $_GET['date'] ?? date('Y-m-d');

                $stmt = $db->prepare("
                    SELECT SUM(DISTINCT p.total_amount) AS total_revenue
                    FROM tickets t
                    INNER JOIN payments p ON t.payment_id = p.payment_id
                    WHERE DATE(t.purchase_date) = :date AND t.status = 'active'
                ");
                $stmt->execute(['date' => $selectedDate]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $total = $result['total_revenue'] ?? 0;
            ?>
            <div class="amount"><?php echo number_format($total, 2, ',', '.'); ?> ₺</div>
        </div>

        <?php if (count($sales) > 0): ?>
            <div class="row">
                <?php foreach ($sales as $sale): ?>
                    <div class="col-md-4">
                        <div class="movie-card">
                            <img src="../../../<?php echo htmlspecialchars($sale['poster_url']); ?>" class="movie-poster mb-3" alt="Poster">
                            <h5 class="mb-2"><?php echo htmlspecialchars($sale['title']); ?></h5>
                            <p><strong>Bilet Sayısı:</strong> <?php echo $sale['ticket_count']; ?></p>
                            <p><strong>Toplam Gelir:</strong> <?php echo number_format($sale['total_revenue'], 2, ',', '.'); ?> ₺</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">Seçilen tarihte bilet satışı bulunamadı.</div>
        <?php endif; ?>
    </div>

    <!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/tr.js"></script>
    <script>
        flatpickr("#datePicker", {
            locale: "tr",
            dateFormat: "Y-m-d",
            defaultDate: "<?php echo $selectedDate; ?>",
            onChange: function(selectedDates, dateStr) {
                // Seçilen tarihe yönlendir
                window.location.href = "?date=" + dateStr;
            }
        });
</script>
</body>
</html>
