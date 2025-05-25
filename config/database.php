<?php
$host = 'localhost';
$dbname = 'cinema_db';
$username = 'root';
$password = ''; # MySql parolanız bulunmaktaysa doldurunuz / If you have a MySql password, fill it in.

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Veritabanı bağlantı hatası: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Veritabanı bağlantısı kurulamadı: " . $e->getMessage());
}
?> 