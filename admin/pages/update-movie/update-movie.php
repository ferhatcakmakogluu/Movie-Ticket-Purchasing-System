<?php
// Start the session
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login/login.php');
    exit;
}

require_once __DIR__ . '/../../../config/db_connect.php';

// Get movie ID from query parameter
$movie_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($movie_id <= 0) {
    $error = 'Geçersiz film ID.';
}

// Fetch movie data
$movie = null;
if ($movie_id > 0) {
    try {
        $stmt = $db->prepare('SELECT movie_id, title, description, duration, release_date, category_id, poster_url, trailer_url, status FROM movies WHERE movie_id = ?');
        $stmt->execute([$movie_id]);
        $movie = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$movie) {
            $error = 'Film bulunamadı.';
        }
    } catch (PDOException $e) {
        $error = 'Film alınamadı: ' . $e->getMessage();
    }
}

// Fetch categories for dropdown (optional)
$categories = [];
try {
    $stmt = $db->query('SELECT category_id, category_name FROM categories'); // Adjust 'category_name' if different
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Kategoriler alınamadı: " . $e->getMessage());
}

// Define uploads directory (absolute path to project root/uploads)
$upload_dir = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'uploads';
$upload_dir = str_replace('\\', '/', $upload_dir); // Normalize to forward slashes
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        $error = 'Uploads klasörü oluşturulamadı.';
    }
}
if (!is_writable($upload_dir)) {
    $error = 'Uploads klasörü yazılabilir değil. Lütfen yazma izinlerini kontrol edin.';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $movie) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $duration = (int)($_POST['duration'] ?? 0);
    $release_date = $_POST['release_date'] ?? '';
    $category_id = (int)($_POST['category_id'] ?? 0);
    $poster_url = trim($_POST['poster_url'] ?? $movie['poster_url']); // Retain existing if no upload
    $trailer_url = trim($_POST['trailer_url'] ?? '');
    $status = $_POST['status'] ?? '';

    // Validate input
    $errors = [];
    if (empty($title)) $errors[] = 'Film adı gereklidir.';
    if (empty($description)) $errors[] = 'Açıklama gereklidir.';
    if ($duration <= 0) $errors[] = 'Süre pozitif bir sayı olmalıdır.';
    if (empty($release_date)) $errors[] = 'Vizyon tarihi gereklidir.';
    if ($category_id <= 0) $errors[] = 'Kategori ID geçerli olmalıdır.';
    if (!in_array($status, ['active', 'archived', 'coming_soon'])) $errors[] = 'Geçersiz durum seçimi.';

    // Handle file upload
    if (isset($_FILES['poster_file']) && $_FILES['poster_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['poster_file'];
        $allowed_types = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB

        if ($file['error'] === UPLOAD_ERR_OK) {
            if (!in_array($file['type'], $allowed_types)) {
                $errors[] = 'Yalnızca PNG, JPEG, JPG veya GIF dosyaları yüklenebilir.';
            } elseif ($file['size'] > $max_size) {
                $errors[] = 'Dosya boyutu 2MB\'dan küçük olmalıdır.';
            } else {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid('poster_') . '.' . $ext;
                $upload_path = $upload_dir . '/' . $filename;

                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $poster_url = 'uploads/' . $filename; // Relative path for database
                } else {
                    $errors[] = 'Dosya yüklenemedi. Uploads klasörünün var olduğundan ve yazılabilir olduğundan emin olun.';
                }
            }
        } else {
            $errors[] = 'Dosya yükleme hatası: Kod ' . $file['error'];
        }
    }

    // If no errors, update database
    if (empty($errors)) {
        try {
            $stmt = $db->prepare('
                UPDATE movies 
                SET title = ?, description = ?, duration = ?, release_date = ?, category_id = ?, poster_url = ?, trailer_url = ?, status = ?
                WHERE movie_id = ?
            ');
            $stmt->execute([$title, $description, $duration, $release_date, $category_id, $poster_url, $trailer_url, $status, $movie_id]);
            $success = 'Film başarıyla güncellendi!';
            // Refresh movie data after update
            $stmt = $db->prepare('SELECT movie_id, title, description, duration, release_date, category_id, poster_url, trailer_url, status FROM movies WHERE movie_id = ?');
            $stmt->execute([$movie_id]);
            $movie = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $errors[] = 'Güncelleme hatası: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Film Güncelle - Admin Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .form-container {
            max-width: 600px;
            margin: 50px auto;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background: linear-gradient(90deg, #28a745, #1d7c35);
            color: white;
            border-radius: 10px 10px 0 0;
        }
        .card-img-top {
            border-radius: 10px 10px 0 0;
            object-fit: cover;
            height: 200px;
            width: 100%;
        }
        .form-label {
            font-weight: 500;
        }
        .input-group-text {
            background-color: #f8f9fa;
        }
        .btn-primary {
            background: linear-gradient(90deg, #28a745, #1d7c35);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(90deg, #1d7c35, #145524);
        }
    </style>
</head>
<body>

    <?php include_once '../../components/shared/admin-navbar.php'; ?>

    <div class="form-container">
        <div class="card">
            <div class="card-header text-center">
                <h3 class="mb-0"><i class="fas fa-edit me-2"></i>Film Güncelle</h3>
            </div>
            <?php if ($movie && !empty($movie['poster_url'])): ?>
                <img src="../../../<?php echo htmlspecialchars($movie['poster_url']); ?>" 
                     class="card-img-top" alt="<?php echo htmlspecialchars($movie['title']); ?>">
            <?php else: ?>
                <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 200px;">
                    <i class="fas fa-image fa-3x text-white"></i>
                </div>
            <?php endif; ?>
            <div class="card-body p-5">
                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php elseif (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if ($movie): ?>
                    <form method="POST" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="mb-4">
                            <label for="title" class="form-label">Film Adı</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-heading"></i></span>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?php echo htmlspecialchars($_POST['title'] ?? $movie['title']); ?>" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label">Açıklama</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($_POST['description'] ?? $movie['description']); ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="duration" class="form-label">Süre (dakika)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                <input type="number" class="form-control" id="duration" name="duration" 
                                       value="<?php echo htmlspecialchars($_POST['duration'] ?? $movie['duration']); ?>" min="1" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="release_date" class="form-label">Vizyon Tarihi</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                <input type="date" class="form-control" id="release_date" name="release_date" 
                                       value="<?php echo htmlspecialchars($_POST['release_date'] ?? $movie['release_date']); ?>" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="category_id" class="form-label">Kategori ID</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-list"></i></span>
                                <?php if (!empty($categories)): ?>
                                    <select class="form-select" id="category_id" name="category_id" required>
                                        <option value="">Kategori seçin</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['category_id']; ?>" 
                                                    <?php echo ($_POST['category_id'] ?? $movie['category_id']) == $category['category_id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['category_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else: ?>
                                    <input type="number" class="form-control" id="category_id" name="category_id" 
                                           value="<?php echo htmlspecialchars($_POST['category_id'] ?? $movie['category_id']); ?>" min="1" required 
                                           placeholder="Kategori ID'sini girin">
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="poster_file" class="form-label">Poster Yükle</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-upload"></i></span>
                                <input type="file" class="form-control" id="poster_file" name="poster_file" accept="image/png,image/jpeg,image/jpg,image/gif">
                            </div>
                            <small class="form-text text-muted">PNG, JPEG, JPG veya GIF, maksimum 2MB.</small>
                        </div>

                        <div class="mb-4">
                            <label for="poster_url" class="form-label">Mevcut Poster URL</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-image"></i></span>
                                <input type="url" class="form-control" id="poster_url" name="poster_url" 
                                       value="<?php echo htmlspecialchars($_POST['poster_url'] ?? $movie['poster_url']); ?>" readonly>
                            </div>
                            <small class="form-text text-muted">Yeni poster yüklenmezse mevcut URL kullanılır.</small>
                        </div>

                        <div class="mb-4">
                            <label for="trailer_url" class="form-label">Fragman URL</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-video"></i></span>
                                <input type="url" class="form-control" id="trailer_url" name="trailer_url" 
                                       value="<?php echo htmlspecialchars($_POST['trailer_url'] ?? $movie['trailer_url']); ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="status" class="form-label">Durum</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-toggle-on"></i></span>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="">Durum seçin</option>
                                    <option value="active" <?php echo ($_POST['status'] ?? $movie['status']) === 'active' ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="archived" <?php echo ($_POST['status'] ?? $movie['status']) === 'archived' ? 'selected' : ''; ?>>Arşivlenmiş</option>
                                    <option value="coming_soon" <?php echo ($_POST['status'] ?? $movie['status']) === 'coming_soon' ? 'selected' : ''; ?>>Yakında</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-2"></i>Güncelle
                        </button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning text-center" role="alert">
                        Film bulunamadı veya geçersiz ID.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Bootstrap form validation
        (function () {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html>