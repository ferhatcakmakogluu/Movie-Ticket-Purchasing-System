<?php
session_start();

// Session'daki user_id'yi ve diğer session verilerini sil
unset($_SESSION['user_id']);
session_destroy();

// Geri tuşu ile dönmeyi önlemek için header ile anasayfaya yönlendir
header("Location: /web/cinema-ticket/index.php");
exit();
?>