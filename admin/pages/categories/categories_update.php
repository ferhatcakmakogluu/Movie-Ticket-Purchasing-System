<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login/login.php');
    exit;
}

require_once __DIR__ . '/../../../config/db_connect.php';

// ID kontrolü
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Geçerli bir kategori ID'si gerekli.");
}

$categoryId = $_GET['id'];

// Kategori bilgilerini getir
$stmt = $db->prepare("SELECT * FROM categories WHERE category_id = ?");
$stmt->execute([$categoryId]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    die("Kategori bulunamadı.");
}

// Güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['category_name'];
    $description = $_POST['description'];

    $updateStmt = $db->prepare("UPDATE categories SET category_name = ?, description = ? WHERE category_id = ?");
    $updateStmt->execute([$name, $description, $categoryId]);

    header("Location: categories.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kategori Güncelle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include_once '../../components/shared/admin-navbar.php'; ?>

<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <strong>Kategori Güncelle</strong>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="category_name" class="form-label">Kategori Adı</label>
                    <input type="text" class="form-control" id="category_name" name="category_name"
                           value="<?php echo htmlspecialchars($category['category_name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Açıklama</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($category['description']); ?></textarea>
                </div>
                <button type="submit" class="btn btn-success">Kaydet</button>
                <a href="categories.php" class="btn btn-secondary">İptal</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
