<?php
$host = 'localhost';
$dbname = 'cinema_db';
$username = 'root';
$password = ''; # MySql parolanız bulunmaktaysa doldurunuz / If you have a MySql password, fill it in.

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Bağlantı hatası: " . $e->getMessage();
    die();
}
?> 