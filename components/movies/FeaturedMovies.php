<?php
require_once __DIR__ . '/../../config/db_connect.php';

function renderFeaturedMovies() {
    global $db;
    $stmt = $db->prepare("SELECT movie_id, title, description, duration, category_id, poster_url, status FROM movies WHERE status = 'active'");
    $stmt->execute();
    $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <section class="featured-movies py-5">
        <div class="container">
            <h2 class="text-center mb-5">Vizyondaki Filmler</h2>
            <div class="row">
                <?php foreach ($movies as $movie): ?>
                    <div class="col-md-3 mb-4">
                        <div class="movie-card">
                            <div class="movie-poster">
                                <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($movie['title']); ?>"
                                     class="img-fluid">
                                <div class="movie-overlay">
                                    <div class="movie-rating">
                                        <i class="fas fa-star text-warning"></i>
                                        <span>4.5/5</span>
                                    </div>
                                    <a href="pages/movie-detail?id=<?php echo $movie['movie_id']; ?>" 
                                       class="btn btn-primary">Detayları Gör</a>
                                </div>
                            </div>
                            <div class="movie-info p-3">
                                <h3 class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></h3>
                                <div class="movie-meta mb-2">
                                    <span class="movie-duration">
                                        <i class="fas fa-clock me-1"></i>
                                        <?php echo htmlspecialchars($movie['duration']); ?>
                                    </span>
                                    <span class="movie-genre ms-3">
                                        <i class="fas fa-film me-1"></i>
                                        <!-- Kategori ismi için ayrı bir sorgu gerekebilir -->
                                        <?php echo htmlspecialchars($movie['category_id']); ?>
                                    </span>
                                </div>
                                <p class="movie-description">
                                    <?php echo substr(htmlspecialchars($movie['description']), 0, 100) . '...'; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}
?> 