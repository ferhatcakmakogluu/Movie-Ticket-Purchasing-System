<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login/login.php');
    exit;
}

require_once __DIR__ . '/../../../config/db_connect.php';

// Ekleme işlemi

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_theater_id'])) {
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

// Silme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = $_POST['delete_id'];

    try {
        $stmt = $db->prepare("DELETE FROM theaters WHERE theater_id = ?");
        $stmt->execute([$deleteId]);
        header("Location: theaters.php");
        exit;
    } catch (PDOException $e) {
        die("Silme hatası: " . $e->getMessage());
    }
}

// Tüm salonları çek
try {
    $stmt = $db->prepare("SELECT theater_id, theater_name, capacity, theater_type, status FROM theaters");
    $stmt->execute();
    $theaters = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Salonlar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .badge {
            font-size: 0.85rem;
        }
        .status-active {
            background-color: #28a745;
        }
        .status-maintenance {
            background-color: #ffc107;
            color: #000;
        }
        .status-inactive {
            background-color: #dc3545;
        }
    </style>
</head>
<body>
<?php include_once '../../components/shared/admin-navbar.php'; ?>

<!--Salon ekleme-->
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
                    <input type="hidden" name="add_theater_id" value="<?php echo $theater['theater_id']; ?>">
                    <button type="submit" class="btn btn-success">Ekle</button>
                    <!--<a href="theaters.php" class="btn btn-secondary">Geri Dön</a>-->
                </div>
            </form>
        </div>
    </div>
</div>

<div class="container my-5">
    <h2 class="mb-4">Sahne / Salonlar</h2>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach ($theaters as $theater): ?>
            <div class="col">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($theater['theater_name']); ?></h5>
                        <p class="card-text mb-2"><strong>Kapasite:</strong> <?php echo $theater['capacity']; ?> kişi</p>
                        <p class="card-text mb-2"><strong>Tür:</strong> <?php echo $theater['theater_type']; ?></p>
                        <p class="card-text">
                            <strong>Durum:</strong>
                            <span class="badge 
                                <?php
                                    echo $theater['status'] === 'active' ? 'status-active' : 
                                         ($theater['status'] === 'maintenance' ? 'status-maintenance' : 'status-inactive');
                                ?>">
                                <?php
                                    echo $theater['status'] === 'active' ? 'Aktif' : 
                                         ($theater['status'] === 'maintenance' ? 'Bakımda' : 'Pasif');
                                ?>
                            </span>
                        </p>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <a href="theaters_update.php?id=<?php echo $theater['theater_id']; ?>" class="btn btn-sm btn-primary">Güncelle</a>
                        <form method="POST" onsubmit="return confirm('Bu salonu silmek istediğinize emin misiniz?');">
                            <input type="hidden" name="delete_id" value="<?php echo $theater['theater_id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Sil</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
