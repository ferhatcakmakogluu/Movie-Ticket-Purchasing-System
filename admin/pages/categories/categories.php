<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login/login.php');
    exit;
}

require_once __DIR__ . '/../../../config/db_connect.php';

// Kategori ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = $_POST['category_name'];
    $description = $_POST['description'];

    $stmt = $db->prepare("INSERT INTO categories (category_name, description) VALUES (?, ?)");
    $stmt->execute([$name, $description]);

    header("Location: ../categories/categories.php");
    exit;
}

// Kategori silme işlemi
if (isset($_GET['delete'])) {
    $deleteId = $_GET['delete'];
    $stmt = $db->prepare("DELETE FROM categories WHERE category_id = ?");
    $stmt->execute([$deleteId]);
    header("Location: categories.php");
    exit;
}

// Kategorileri çek
$stmt = $db->query("SELECT * FROM categories ORDER BY category_id DESC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Film Kategorileri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include_once '../../components/shared/admin-navbar.php'; ?>

<div class="container my-5">
    <h2 class="mb-4 text-primary">🎬 Film Kategorileri</h2>

    <!-- Kategori Ekleme Formu -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white">
            <strong>Yeni Kategori Ekle</strong>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="add_category" value="1">
                <div class="mb-3">
                    <label for="category_name" class="form-label">Kategori Adı</label>
                    <input type="text" class="form-control" id="category_name" name="category_name" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Açıklama</label>
                    <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                </div>
                <button type="submit" class="btn btn-success">Ekle</button>
            </form>
        </div>
    </div>

    <!-- Kategori Tablosu -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <strong>Mevcut Kategoriler</strong>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Kategori Adı</th>
                        <th>Açıklama</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td><?php echo $cat['category_id']; ?></td>
                                <td><?php echo htmlspecialchars($cat['category_name']); ?></td>
                                <td><?php echo htmlspecialchars($cat['description']); ?></td>
                                <td>
                                    <a href="categories_update.php?id=<?php echo $cat['category_id']; ?>" class="btn btn-sm btn-warning">Güncelle</a>
                                    <a href="?delete=<?php echo $cat['category_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu kategoriyi silmek istediğinize emin misiniz?')">Sil</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4">Hiç kategori bulunamadı.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
