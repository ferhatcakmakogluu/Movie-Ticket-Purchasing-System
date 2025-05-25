<?php
function renderMovieSlider() {
    ?>
    <div class="movie-slider-container">
        <div class="movie-slider">
            <div class="slide fade">
                <img src="assets/img/movie-horizontal-1.jpg" alt="Movie 1">
                <div class="slide-content">
                    <h2>Yeni Filmler</h2>
                    <p>En yeni filmler şimdi sinemalarda!</p>
                </div>
            </div>
            <div class="slide fade">
                <img src="assets/img/movie-horizontal-2.jpg" alt="Movie 2">
                <div class="slide-content">
                    <h2>Özel Gösterimler</h2>
                    <p>Kaçırılmayacak film gösterimleri!</p>
                </div>
            </div>
            <button class="slider-btn prev" onclick="moveSlide(-1)">&#10094;</button>
            <button class="slider-btn next" onclick="moveSlide(1)">&#10095;</button>
        </div>
        <div class="slider-dots">
            <span class="dot active" onclick="currentSlide(1)"></span>
            <span class="dot" onclick="currentSlide(2)"></span>
        </div>
    </div>
    <?php
}
?> 