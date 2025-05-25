<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sinema Yönetim Sistemi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="components/slider/slider.css">
    <link rel="stylesheet" href="components/movies/movies.css">
    <link rel="stylesheet" href="components/movies/past-movies.css">
</head>
<body>
    <!-- Navbar -->
    <?php include_once 'components/shared/navbar.php'; ?>

    <!-- Hero Section with Slider -->
    <?php 
    require_once 'components/slider/slider.php';
    renderMovieSlider();
    ?>

    <!-- Featured Movies Section -->
    <?php
    require_once 'components/movies/FeaturedMovies.php';
    renderFeaturedMovies();
    ?>

    <!-- Past Movies Section -->
    <?php
    require_once 'components/movies/PastMovies.php';
    renderPastMovies();
    ?>

    <!-- Footer -->
    <?php include_once 'components/shared/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="components/slider/slider.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html> 