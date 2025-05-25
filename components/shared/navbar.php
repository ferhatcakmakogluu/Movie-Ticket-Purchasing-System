<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isActive($page) {
    $current_page = basename($_SERVER['PHP_SELF']);
    return $current_page == $page ? 'active' : '';
}

// Get the current directory depth
$current_path = $_SERVER['PHP_SELF'];
$depth = substr_count($current_path, '/') - 1;
$base_path = str_repeat('../', $depth);
if ($depth == 0) $base_path = '.';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="/web/cinema-ticket/index.php">
            <i class="fas fa-film me-2"></i>Sinema
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo isActive('index.php'); ?>" href="/web/cinema-ticket/index.php">Ana Sayfa</a>
                </li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive('../pages/tickets/index.php'); ?>" href="/web/cinema-ticket/pages/tickets/index.php">Biletlerim</a>
                    </li>
                <?php endif; ?>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/web/cinema-ticket/pages/profile/profile.php">Profilim</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/web/cinema-ticket/pages/login/logout.php">Çıkış Yap</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive('login.php'); ?>" href="/web/cinema-ticket/pages/login/index.php">Giriş Yap</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive('register.php'); ?>" href="/web/cinema-ticket/pages/register/index.php">Kayıt Ol</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav> 