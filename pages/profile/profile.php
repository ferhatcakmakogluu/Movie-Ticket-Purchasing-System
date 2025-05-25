<?php
session_start();

// Kullanıcı giriş yapmamışsa login sayfasına yönlendir
if (!isset($_SESSION['user_id'])) {
    header("Location: /web/cinema-ticket/pages/login/index.php");
    exit();
}

require_once __DIR__ . '/../../config/db_connect.php';// Veritabanı bağlantısı

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Kullanıcı bilgilerini çek
$stmt = $db->prepare("SELECT username, email, phone, full_name FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt->closeCursor();

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $errors = [];

    // Doğrulama
    if (empty($full_name)) {
        $errors[] = "Tam ad boş olamaz.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Geçerli bir e-posta adresi giriniz.";
    }
    if (!empty($phone) && !preg_match("/^[0-9]{10,15}$/", $phone)) {
        $errors[] = "Geçerli bir telefon numarası giriniz.";
    }
    if (!empty($password) && strlen($password) < 6) {
        $errors[] = "Şifre en az 6 karakter olmalıdır.";
    }

    // Hata yoksa güncelle
    if (empty($errors)) {
        $update_query = "UPDATE users SET full_name = ?, email = ?, phone = ?";
        $params = [$full_name, $email, $phone];

        // Şifre güncelleniyorsa
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_query .= ", password = ?";
            $params[] = $hashed_password;
        }

        $update_query .= " WHERE user_id = ?";
        $params[] = $user_id;

        $stmt = $db->prepare($update_query);
        if ($stmt->execute($params)) {
            $success_message = "Profil bilgileriniz başarıyla güncellendi.";
        } else {
            $error_message = "Bir hata oluştu, lütfen tekrar deneyin.";
        }
        $stmt->closeCursor();
    } else {
        $error_message = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Ayarları</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../components/login/login.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .profile-card {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }
        .form-label {
            font-weight: 500;
        }
        .btn-save {
            background-color: #0d6efd;
            border: none;
        }
        .btn-save:hover {
            background-color: #0b5ed7;
        }
        .alert {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include_once '../../components/shared/navbar.php'; ?>

    <div class="container">
        <div class="profile-card">
            <h2 class="text-center mb-4">Profil Ayarları</h2>

            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">Kullanıcı Adı (Değiştirilemez)</label>
                    <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                </div>
                <div class="mb-3">
                    <label for="full_name" class="form-label">Tam Ad</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">E-posta</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label—optional" class="form-label">Telefon Numarası</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Yeni Şifre (Boş bırakırsanız değişmez)</label>
                    <input type="password" class="form-control" id="password" name="password">
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-save">Kaydet</button>
                </div>
            </form>
        </div>
    </div>

    <?php include_once '../../components/shared/footer.php'; ?>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/main.js"></script>
</body>
</html>