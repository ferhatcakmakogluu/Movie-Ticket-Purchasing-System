<?php
require_once __DIR__ . '/../../config/db_connect.php';

function renderPastMovies() {
    global $db;
    $stmt = $db->prepare("SELECT movie_id, title, description, duration, category_id, poster_url, status FROM movies WHERE status = 'archived'");
    $stmt->execute();
    $pastMovies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <section class="past-movies py-5">
        <div class="container">
            <h2 class="mb-4">Geçmiş Filmler</h2>
            <div class="past-movies-container">
                <button class="scroll-arrow scroll-left" onclick="scrollMovies('left')">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="past-movies-scroll">
                    <?php foreach ($pastMovies as $movie): ?>
                        <div class="past-movie-card">
                            <div class="past-movie-poster">
                                <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($movie['title']); ?>">
                                <div class="past-movie-overlay">
                                    <div class="past-movie-rating">
                                        <div class="imdb-rating">
                                            <i class="fas fa-star text-warning"></i>
                                            <span>IMDb: 8.5</span>
                                        </div>
                                        <!--
                                        <div class="movie-price">
                                            <i class="fas fa-ticket-alt text-success"></i>
                                            <span>-- ₺</span>
                                        </div>
                                        -->
                                    </div>
                                    <a href="pages/movie-detail?id=<?php echo $movie['movie_id']; ?>" 
                                       class="btn btn-primary btn-sm">Detayları Gör</a>
                                </div>
                            </div>
                            <div class="past-movie-info">
                                <h3 class="past-movie-title"><?php echo htmlspecialchars($movie['title']); ?></h3>
                                <div class="past-movie-meta">
                                    <span class="movie-duration">
                                        <i class="fas fa-clock me-1"></i>
                                        <?php echo htmlspecialchars($movie['duration']); ?>
                                    </span>
                                    <span class="movie-genre ms-2">
                                        <i class="fas fa-film me-1"></i>
                                        <?php echo htmlspecialchars($movie['category_id']); ?>
                                    </span>
                                </div>
                                <p class="past-movie-description">
                                    <?php echo substr(htmlspecialchars($movie['description']), 0, 80) . '...'; ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="scroll-arrow scroll-right" onclick="scrollMovies('right')">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
        <script>
            function scrollMovies(direction) {
                const container = document.querySelector('.past-movies-scroll');
                const scrollAmount = 300;
                if (direction === 'left') {
                    container.scrollBy({
                        left: -scrollAmount,
                        behavior: 'smooth'
                    });
                } else {
                    container.scrollBy({
                        left: scrollAmount,
                        behavior: 'smooth'
                    });
                }
            }
        </script>
    </section>
    <?php
}
?> 