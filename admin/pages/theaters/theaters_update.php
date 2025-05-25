<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login/login.php');
    exit;
}

require_once __DIR__ . '/../../../config/db_connect.php';

if (!isset($_GET['id'])) {
    die("Salon ID'si belirtilmedi.");
}

$theaterId = $_GET['id'];

// Güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['theater_name'];
    $capacity = $_POST['capacity'];
    $type = $_POST['theater_type'];
    $status = $_POST['status'];

    try {
        $stmt = $db->prepare("UPDATE theaters SET theater_name = ?, capacity = ?, theater_type = ?, status = ? WHERE theater_id = ?");
        $stmt->execute([$name, $capacity, $type, $status, $theaterId]);
        header("Location: ../theaters/theaters.php");
        exit;
    } catch (PDOException $e) {
        die("Güncelleme hatası: " . $e->getMessage());
    }
}

// Mevcut salon bilgilerini çek
try {
    $stmt = $db->prepare("SELECT * FROM theaters WHERE theater_id = ?");
    $stmt->execute([$theaterId]);
    $theater = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$theater) {
        die("Salon bulunamadı.");
    }
} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Salon Güncelle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include_once '../../components/shared/admin-navbar.php'; ?>

<div class="container my-5">
    <h2>Salon Güncelle</h2>
    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label for="theater_name" class="form-label">Salon Adı</label>
            <input type="text" class="form-control" name="theater_name" id="theater_name" required value="<?php echo htmlspecialchars($theater['theater_name']); ?>">
        </div>

        <div class="mb-3">
            <label for="capacity" class="form-label">Kapasite</label>
            <input type="number" class="form-control" name="capacity" id="capacity" required value="<?php echo htmlspecialchars($theater['capacity']); ?>">
        </div>

        <div class="mb-3">
            <label for="theater_type" class="form-label">Salon Türü</label>
            <select class="form-select" name="theater_type" id="theater_type" required>
                <option value="2D" <?php if ($theater['theater_type'] === '2D') echo 'selected'; ?>>2D</option>
                <option value="3D" <?php if ($theater['theater_type'] === '3D') echo 'selected'; ?>>3D</option>
                <option value="IMAX" <?php if ($theater['theater_type'] === 'IMAX') echo 'selected'; ?>>IMAX</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Durum</label>
            <select class="form-select" name="status" id="status" required>
                <option value="active" <?php if ($theater['status'] === 'active') echo 'selected'; ?>>Aktif</option>
                <option value="maintenance" <?php if ($theater['status'] === 'maintenance') echo 'selected'; ?>>Bakımda</option>
                <option value="inactive" <?php if ($theater['status'] === 'inactive') echo 'selected'; ?>>Pasif</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Güncelle</button>
        <a href="theaters.php" class="btn btn-secondary">İptal</a>
    </form>
</div>
</body>
</html>
