<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login/login.php');
    exit;
}

require_once __DIR__ . '/../../../config/db_connect.php';

// ID kontrolü
if (!isset($_GET['id'])) {
    die("Geçersiz seans ID.");
}
$session_id = $_GET['id'];

// Seans bilgilerini al
$stmt = $db->prepare("
    SELECT s.*, m.title, m.description, m.poster_url
    FROM sessions s
    JOIN movies m ON s.movie_id = m.movie_id
    WHERE s.session_id = ?
");
$stmt->execute([$session_id]);
$session = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$session) {
    die("Seans bulunamadı.");
}

// Güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $session_date = $_POST['session_date'];
    $session_time = $_POST['session_time'];
    $price = $_POST['price'];
    $status = $_POST['status'];

    $update = $db->prepare("UPDATE sessions SET session_date = ?, session_time = ?, price = ?, status = ? WHERE session_id = ?");
    $update->execute([$session_date, $session_time, $price, $status, $session_id]);

    header("Location: ../sessions/sessions.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Seans Güncelle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .poster {
            max-height: 250px;
            border-radius: 10px;
        }
        .form-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.08);
        }
    </style>
</head>
<body>
<?php include_once '../../components/shared/admin-navbar.php'; ?>

<div class="container my-5">
    <h2 class="mb-4">🎬 Seans Güncelle</h2>
    <div class="row g-4">
        <!-- Film Detayları -->
        <div class="col-md-5">
            <div class="form-section text-center">
                <img src="../../../<?= htmlspecialchars($session['poster_url']) ?>" class="poster mb-3" alt="Poster">
                <h4><?= htmlspecialchars($session['title']) ?></h4>
                <p class="text-muted"><?= nl2br(htmlspecialchars($session['description'])) ?></p>
            </div>
        </div>

        <!-- Güncelleme Formu -->
        <div class="col-md-7">
            <div class="form-section">
                <form method="POST">
                    <div class="mb-3">
                        <label for="session_date" class="form-label">Seans Tarihi</label>
                        <input type="date" name="session_date" id="session_date" class="form-control" required value="<?= htmlspecialchars($session['session_date']) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="session_time" class="form-label">Seans Saati</label>
                        <input type="time" name="session_time" id="session_time" class="form-control" required value="<?= htmlspecialchars(substr($session['session_time'], 0, 5)) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label">Fiyat (₺)</label>
                        <input type="number" step="0.01" name="price" id="price" class="form-control" required value="<?= htmlspecialchars($session['price']) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Durum</label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="available" <?= $session['status'] === 'available' ? 'selected' : '' ?>>Uygun</option>
                            <option value="full" <?= $session['status'] === 'full' ? 'selected' : '' ?>>Dolu</option>
                            <option value="cancelled" <?= $session['status'] === 'cancelled' ? 'selected' : '' ?>>İptal Edildi</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Güncelle</button>
                    <a href="sessions.php" class="btn btn-secondary">İptal</a>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
