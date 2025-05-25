<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login/login.php');
    exit;
}

require_once __DIR__ . '/../../../config/db_connect.php';

// Filmleri getir
$moviesStmt = $db->query("SELECT movie_id, title, description, duration, poster_url FROM movies WHERE status = 'active'");
$movies = $moviesStmt->fetchAll(PDO::FETCH_ASSOC);

// Salonları getir
$theatersStmt = $db->query("SELECT theater_id, theater_name FROM theaters WHERE status = 'active'");
$theaters = $theatersStmt->fetchAll(PDO::FETCH_ASSOC);

// Form gönderildiyse
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movie_id = $_POST['movie_id'];
    $theater_id = $_POST['theater_id'];
    $session_date = $_POST['session_date'];
    $session_time = $_POST['session_time'];
    $price = $_POST['price'];
    $status = $_POST['status'];

    $stmt = $db->prepare("INSERT INTO sessions (movie_id, theater_id, session_date, session_time, price, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$movie_id, $theater_id, $session_date, $session_time, $price, $status]);

    header("Location: ../sessions/sessions.php");
    exit;
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $db->prepare("DELETE FROM sessions WHERE session_id = ?")->execute([$id]);
    header("Location: sessions.php");
    exit;
}

$stmt = $db->query("
    SELECT s.session_id, s.session_date, s.session_time, s.price, s.status,
           m.title AS movie_title, m.poster_url, t.theater_name
    FROM sessions s
    JOIN movies m ON s.movie_id = m.movie_id
    JOIN theaters t ON s.theater_id = t.theater_id
    ORDER BY s.session_date DESC, s.session_time ASC
");

$sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Seans Ekle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .movie-preview {
            display: none;
        }
        .movie-poster {
            max-height: 250px;
            object-fit: cover;
            border-radius: 8px;
        }
        .poster-thumb {
            height: 60px;
            width: auto;
            border-radius: 5px;
        }
        .status-available {
            color: green;
            font-weight: bold;
        }
        .status-full {
            color: red;
            font-weight: bold;
        }
        .status-cancelled {
            color: gray;
            font-weight: bold;
        }
    </style>
</head>
<body>
<?php include_once '../../components/shared/admin-navbar.php'; ?>

<div class="container my-5">
    <h2 class="mb-4">Yeni Seans Ekle</h2>
    <form method="POST" class="row g-4">
        <div class="col-md-6">
            <label for="movie_id" class="form-label">Film Seç</label>
            <select class="form-select" id="movie_id" name="movie_id" required onchange="showMoviePreview(this.value)">
                <option value="">Film Seçiniz</option>
                <?php foreach ($movies as $movie): ?>
                    <option value="<?= $movie['movie_id']; ?>"><?= htmlspecialchars($movie['title']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-6">
            <label for="theater_id" class="form-label">Salon Seç</label>
            <select class="form-select" name="theater_id" required>
                <option value="">Salon Seçiniz</option>
                <?php foreach ($theaters as $theater): ?>
                    <option value="<?= $theater['theater_id']; ?>"><?= htmlspecialchars($theater['theater_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="moviePreview" class="col-12 movie-preview p-4 bg-light rounded shadow-sm">
            <div class="row">
                <div class="col-md-3">
                    <img id="posterImage" src="" alt="Poster" class="img-fluid movie-poster">
                </div>
                <div class="col-md-9">
                    <h5 id="movieTitle"></h5>
                    <p id="movieDescription"></p>
                    <p><strong>Süre:</strong> <span id="movieDuration"></span> dk</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <label for="session_date" class="form-label">Tarih</label>
            <input type="date" class="form-control" name="session_date" required>
        </div>

        <div class="col-md-4">
            <label for="session_time" class="form-label">Saat</label>
            <input type="time" class="form-control" name="session_time" required>
        </div>

        <div class="col-md-2">
            <label for="price" class="form-label">Fiyat (₺)</label>
            <input type="number" step="0.01" class="form-control" name="price" required>
        </div>

        <div class="col-md-2">
            <label for="status" class="form-label">Durum</label>
            <select class="form-select" name="status" required>
                <option value="available">Müsait</option>
                <option value="full">Dolu</option>
                <option value="cancelled">İptal</option>
            </select>
        </div>

        <div class="col-12 text-end">
            <button type="submit" class="btn btn-success">Seansı Kaydet</button>
        </div>
    </form>
</div>

<div class="container my-5">
    <h2 class="mb-4">Seans Listesi</h2>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Poster</th>
                    <th>Film Adı</th>
                    <th>Salon</th>
                    <th>Tarih</th>
                    <th>Saat</th>
                    <th>Fiyat</th>
                    <th>Durum</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sessions as $session): ?>
                    <tr>
                        <td><img src="../../../<?= htmlspecialchars($session['poster_url']); ?>" class="poster-thumb" alt="Poster"></td>
                        <td><?= htmlspecialchars($session['movie_title']); ?></td>
                        <td><?= htmlspecialchars($session['theater_name']); ?></td>
                        <td><?= date('d.m.Y', strtotime($session['session_date'])); ?></td>
                        <td><?= substr($session['session_time'], 0, 5); ?></td>
                        <td><?= number_format($session['price'], 2, ',', '.'); ?> ₺</td>
                        <td>
                            <?php
                                $status = $session['status'];
                                $statusText = [
                                    'available' => 'Uygun',
                                    'full' => 'Dolu',
                                    'cancelled' => 'İptal Edildi'
                                ];

                                $statusLabel = isset($statusText[$status]) ? $statusText[$status] : ucfirst($status);
                                echo "<span class='status-{$status}'>$statusLabel</span>";
                            ?>
                        </td>
                        <td>
                            <a href="update_session.php?id=<?= $session['session_id']; ?>" class="btn btn-sm btn-primary">Güncelle</a>
                            <a href="sessions.php?delete=<?= $session['session_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu seansı silmek istediğinize emin misiniz?');">Sil</a>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($sessions)): ?>
                    <tr><td colspan="8" class="text-center text-muted">Kayıtlı seans bulunamadı.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    const movieData = <?php echo json_encode($movies); ?>;

    function showMoviePreview(movieId) {
        const preview = document.getElementById('moviePreview');
        const selected = movieData.find(m => m.movie_id == movieId);

        if (selected) {
            preview.style.display = 'block';
            document.getElementById('posterImage').src = "../../../" + selected.poster_url;
            document.getElementById('movieTitle').innerText = selected.title;
            document.getElementById('movieDescription').innerText = selected.description;
            document.getElementById('movieDuration').innerText = selected.duration;
        } else {
            preview.style.display = 'none';
        }
    }
</script>
</body>
</html>
