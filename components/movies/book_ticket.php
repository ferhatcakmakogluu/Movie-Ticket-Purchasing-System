<?php
session_start();
require_once __DIR__ . '/../../config/db_connect.php'; // Veritabanı bağlantısı

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit();
}

// Formdan gelen verileri al
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movie_id = isset($_POST['movie_id']) ? intval($_POST['movie_id']) : 0;
    $session_id = isset($_POST['session_id']) ? intval($_POST['session_id']) : 0;
    $card_full_name = isset($_POST['card_name']) ? trim($_POST['card_name']) : '';
    $card_number = isset($_POST['card_number']) ? trim($_POST['card_number']) : '';
    $card_expiration_date = isset($_POST['expiry_date']) ? trim($_POST['expiry_date']) : '';
    $card_CVV = isset($_POST['cvv']) ? trim($_POST['cvv']) : '';
    $selected_seats = isset($_POST['selected_seats']) ? json_decode($_POST['selected_seats'], true) : [];
    $total_amount = isset($_POST['total_amount']) ? floatval($_POST['total_amount']) : 0.0;

    /*echo "<h2>Formdan Gelen Veriler</h2>" .
         "<strong>movie_id:</strong> " . htmlspecialchars($movie_id) . " (Film ID'si)<br>" .
         "<strong>session_id:</strong> " . htmlspecialchars($session_id) . " (Seans ID'si)<br>" .
         "<strong>card_name:</strong> " . htmlspecialchars($card_full_name) . " (Kart Üzerindeki İsim)<br>" .
         "<strong>card_number:</strong> " . htmlspecialchars($card_number) . " (Kart Numarası)<br>" .
         "<strong>expiry_date:</strong> " . htmlspecialchars($card_expiration_date) . " (Son Kullanma Tarihi)<br>" .
         "<strong>cvv:</strong> " . htmlspecialchars($card_CVV) . " (CVV Kodu)<br>" .
         "<strong>selected_seats:</strong> " . htmlspecialchars(implode(', ', $selected_seats)) . " (Seçilen Koltuklar)<br>" .
         "<strong>total_amount:</strong> " . htmlspecialchars($total_amount) . " (Toplam Ödenecek Tutar)";

    */

    try {
        // Transaction başlat
        $db->beginTransaction();

        // Ödeme bilgilerini payments tablosuna ekle
        $stmt = $db->prepare("
            INSERT INTO payments (user_id, card_number, card_full_name, card_expiration_date, card_CVV, total_amount)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $card_number,
            $card_full_name,
            $card_expiration_date,
            $card_CVV,
            $total_amount
        ]);

        // Ödeme ID'sini al
        $payment_id = $db->lastInsertId();

        // Her bir koltuk için tickets tablosuna kayıt ekle
        $stmt = $db->prepare("
            INSERT INTO tickets (session_id, user_id, payment_id, seat_number, purchase_date, status)
            VALUES (?, ?, ?, ?, NOW(), 'active')
        ");

        foreach ($selected_seats as $seat) {
            $stmt->execute([
                $session_id,
                $_SESSION['user_id'],
                $payment_id,
                $seat
            ]);
        }

        // Transaction'ı tamamla
        $db->commit();

        // Başarılı işlem sonrası yönlendirme
        header('Location: ../../components/movies/success.php?message=payment_saved&payment_id=' . $payment_id);
        exit();

    } catch (Exception $e) {
        // Hata durumunda transaction'ı geri al
        $db->rollBack();
        // Hata mesajını logla (üretimde dosyaya yaz)
        error_log("Payment error: " . $e->getMessage());
        // Hata sayfasına yönlendir
        header('Location: ../../movies.php?error=payment_failed&details=' . urlencode($e->getMessage()));
        exit();
    }



}
    // Verileri doğrula
    /*
    if ($movie_id <= 0 || $session_id <= 0 || empty($card_full_name) || empty($card_number) || 
        empty($card_expiration_date) || empty($card_CVV) || empty($selected_seats)) {
        header('Location: ../../movies.php?error=missing_data');
        exit();
    }

    try {
        // Seans ve fiyat bilgilerini al
        $stmt = $pdo->prepare("
            SELECT s.price, s.theater_id, t.capacity 
            FROM sessions s 
            JOIN theaters t ON s.theater_id = t.theater_id 
            WHERE s.session_id = ? AND s.status = 'active'
        ");
        $stmt->execute([$session_id]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$session) {
            header('Location: ../../movies.php?error=invalid_session');
            exit();
        }

        $ticket_price = $session['price'];
        $theater_capacity = $session['capacity'];
        $total_amount = $ticket_price * count($selected_seats);

        // Koltukların geçerli olduğunu ve kapasiteyi aşmadığını kontrol et
        if (count($selected_seats) > $theater_capacity) {
            header('Location: ../../movies.php?error=exceeds_capacity');
            exit();
        }

        // Koltukların daha önce alınıp alınmadığını kontrol et
        $stmt = $pdo->prepare("
            SELECT seat_number 
            FROM tickets 
            WHERE session_id = ? AND seat_number IN (" . implode(',', array_fill(0, count($selected_seats), '?')) . ") AND status = 'active'
        ");
        $stmt->execute(array_merge([$session_id], $selected_seats));
        $taken_seats = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($taken_seats)) {
            header('Location: ../../movies.php?error=seats_taken&seats=' . implode(',', $taken_seats));
            exit();
        }

        // Veritabanı işlemleri için transaction başlat
        $pdo->beginTransaction();

        // 1. Ödeme bilgilerini payments tablosuna ekle
        $stmt = $pdo->prepare("
            INSERT INTO payments (user_id, card_number, card_full_name, card_expiration_date, card_CVV)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $card_number, // Güvenlik için maskeleme/şifreleme önerilir
            $card_full_name,
            $card_expiration_date,
            $card_CVV
        ]);

        // Ödeme ID'sini al
        $payment_id = $pdo->lastInsertId();

        // 2. Her bir koltuk için tickets tablosuna kayıt ekle
        $stmt = $pdo->prepare("
            INSERT INTO tickets (session_id, user_id, payment_id, seat_number, purchase_date, status)
            VALUES (?, ?, ?, ?, NOW(), 'active')
        ");

        foreach ($selected_seats as $seat) {
            $stmt->execute([
                $session_id,
                $_SESSION['user_id'],
                $payment_id,
                $seat
            ]);
        }

        // Transaction'ı tamamla
        $pdo->commit();

        // Başarılı işlem sonrası yönlendirme
        header('Location: ../../success.php?message=ticket_booked');
        exit();

    } catch (Exception $e) {
        // Hata durumunda transaction'ı geri al
        $pdo->rollBack();
        header('Location: ../../movies.php?error=booking_failed&details=' . urlencode($e->getMessage()));
        exit();
    }
} else {
    // POST isteği değilse
    header('Location: ../../movies.php?error=invalid_request');
    exit();
}*/
?>