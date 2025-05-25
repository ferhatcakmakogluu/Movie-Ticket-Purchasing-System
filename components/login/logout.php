<?php
session_start();

// Oturumu sonlandır
session_destroy();

// Cookie'yi sil
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Ana sayfaya yönlendir
header('Location: /index.php');
exit; 