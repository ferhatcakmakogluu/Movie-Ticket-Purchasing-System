<?php
session_start();
require_once '../../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;

    try {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Başarılı giriş
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            
            if ($remember) {
                // 30 günlük cookie oluştur
                setcookie('remember_token', 
                         generateRememberToken($user['id']), 
                         time() + (86400 * 30), 
                         '/');
            }
            
            header('Location: /index.php');
            exit;
        } else {
            $_SESSION['error'] = 'Geçersiz email veya şifre';
            header('Location: /pages/login.php');
            exit;
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = 'Bir hata oluştu. Lütfen tekrar deneyin.';
        header('Location: /pages/login.php');
        exit;
    }
}

function generateRememberToken($userId) {
    $token = bin2hex(random_bytes(32));
    // Token'ı veritabanına kaydet
    global $db;
    $stmt = $db->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
    $stmt->execute([$token, $userId]);
    return $token;
}
?> 