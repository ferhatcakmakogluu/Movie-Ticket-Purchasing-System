<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login/login.php');
    exit;
}

require_once __DIR__ . '/../../../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['theater_name'];
    $capacity = $_POST['capacity'];
    $type = $_POST['theater_type'];
    $status = $_POST['status'];

    try {
        $stmt = $db->prepare("INSERT INTO theaters (theater_name, capacity, theater_type, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $capacity, $type, $status]);

        header("Location: ../theaters/theaters.php");
        exit;
    } catch (PDOException $e) {
        die("Salon eklenemedi: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yeni Salon Ekle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include_once '../../components/shared/admin-navbar.php'; ?>

<div class="container my-5">
    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Yeni Salon Ekle</h3>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="theater_name" class="form-label">Salon Adı</label>
                    <input type="text" class="form-control" name="theater_name" id="theater_name" required>
                </div>

                <div class="mb-3">
                    <label for="capacity" class="form-label">Kapasite</label>
                    <input type="number" class="form-control" name="capacity" id="capacity" required min="1">
                </div>

                <div class="mb-3">
                    <label for="theater_type" class="form-label">Salon Türü</label>
                    <select class="form-select" name="theater_type" id="theater_type" required>
                        <option value="" disabled selected>Seçiniz</option>
                        <option value="2D">2D</option>
                        <option value="3D">3D</option>
                        <option value="IMAX">IMAX</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Durum</label>
                    <select class="form-select" name="status" id="status" required>
                        <option value="active">Aktif</option>
                        <option value="maintenance">Bakımda</option>
                        <option value="inactive">Pasif</option>
                    </select>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-success">Ekle</button>
                    <a href="theaters.php" class="btn btn-secondary">Geri Dön</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
