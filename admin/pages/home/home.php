<?php
// Start the session
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login/login.php');
    exit;
}

require_once __DIR__ . '/../../../config/db_connect.php';

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $movie_id = (int)$_POST['delete_id'];
    try {
        $stmt = $db->prepare('DELETE FROM movies WHERE movie_id = ?');
        $stmt->execute([$movie_id]);
        $success = 'Film başarıyla silindi!';
    } catch (PDOException $e) {
        $error = 'Silme hatası: ' . $e->getMessage();
    }
}

// Fetch all movies
try {
    $stmt = $db->query('SELECT movie_id, title, description, duration, release_date, category_id, poster_url, trailer_url, status FROM movies');
    $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Filmler alınamadı: ' . $e->getMessage();
    $movies = [];
}

// Fetch categories for mapping (optional)
$categories = [];
try {
    $stmt = $db->query('SELECT category_id, category_name FROM categories');
    $categories = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // Maps category_id => category_name
} catch (PDOException $e) {
    error_log("Kategoriler alınamadı: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Film Listesi - Admin Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .movie-container {
            max-width: 1200px;
            margin: 50px auto;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-img-top {
            border-radius: 10px 10px 0 0;
            object-fit: cover;
            height: 200px;
        }
        .card-body {
            padding: 1.5rem;
        }
        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
        }
        .description {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .badge {
            font-size: 0.9rem;
        }
        .btn-update {
            background: linear-gradient(90deg, #28a745, #1d7c35);
            border: none;
        }
        .btn-update:hover {
            background: linear-gradient(90deg, #1d7c35, #145524);
        }
        .btn-delete {
            background: linear-gradient(90deg, #dc3545, #a72834);
            border: none;
        }
        .btn-delete:hover {
            background: linear-gradient(90deg, #a72834, #7a1d26);
        }
    </style>
</head>
<body>
    <?php include_once '../../components/shared/admin-navbar.php'; ?>

    <div class="movie-container">
        <h2 class="text-center mb-5"><i class="fas fa-film me-2"></i>Film Listesi</h2>
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
        <?php endif; ?>
        <?php if (empty($movies)): ?>
            <div class="alert alert-info text-center" role="alert">
                Henüz film bulunmamaktadır.
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($movies as $movie): ?>
                    <div class="col">
                        <div class="card h-100">
                            <?php if (!empty($movie['poster_url'])): ?>
                                <img src="../../../<?php echo htmlspecialchars($movie['poster_url']); ?>" 
                                     class="card-img-top" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                            <?php else: ?>
                                <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-image fa-3x text-white"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($movie['title']); ?></h5>
                                <p class="card-text description"><?php echo htmlspecialchars($movie['description']); ?></p>
                                <p class="card-text"><strong>Süre:</strong> <?php echo $movie['duration']; ?> dakika</p>
                                <p class="card-text"><strong>Vizyon:</strong> <?php echo date('d.m.Y', strtotime($movie['release_date'])); ?></p>
                                <p class="card-text"><strong>Kategori:</strong> 
                                    <?php echo isset($categories[$movie['category_id']]) ? htmlspecialchars($categories[$movie['category_id']]) : $movie['category_id']; ?>
                                </p>
                                <p class="card-text"><strong>Fragman:</strong> 
                                    <?php if (!empty($movie['trailer_url'])): ?>
                                        <a href="<?php echo htmlspecialchars($movie['trailer_url']); ?>" target="_blank">İzle</a>
                                    <?php else: ?>
                                        Yok
                                    <?php endif; ?>
                                </p>
                                <p class="card-text"><strong>Durum:</strong> 
                                    <span class="badge bg-<?php 
                                        echo $movie['status'] === 'active' ? 'success' : 
                                             ($movie['status'] === 'coming_soon' ? 'warning' : 'secondary'); ?>">
                                        <?php echo $movie['status'] === 'active' ? 'Aktif' : 
                                                  ($movie['status'] === 'coming_soon' ? 'Yakında' : 'Arşivlenmiş'); ?>
                                    </span>
                                </p>
                            </div>
                            <div class="card-footer d-flex justify-content-between">
                                <a href="../update-movie/update-movie.php?id=<?php echo $movie['movie_id']; ?>" 
                                   class="btn btn-update btn-sm text-white">
                                    <i class="fas fa-edit me-1"></i> Güncelle
                                </a>
                                <form method="POST" action="" onsubmit="return confirm('Bu filmi silmek istediğinizden emin misiniz?');">
                                    <input type="hidden" name="delete_id" value="<?php echo $movie['movie_id']; ?>">
                                    <button type="submit" class="btn btn-delete btn-sm text-white">
                                        <i class="fas fa-trash me-1"></i> Sil
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>