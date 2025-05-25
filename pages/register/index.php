<?php
require_once '../../config/database.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = [];
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    // Validate username
    if (empty($username)) {
        $errors['username'] = "Kullanıcı adı gereklidir.";
    } elseif (strlen($username) < 3) {
        $errors['username'] = "Kullanıcı adı en az 3 karakter olmalıdır.";
    }

    // Validate email
    if (empty($email)) {
        $errors['email'] = "E-posta adresi gereklidir.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Geçerli bir e-posta adresi giriniz.";
    }

    // Validate password
    if (empty($password)) {
        $errors['password'] = "Şifre gereklidir.";
    } elseif (strlen($password) < 6) {
        $errors['password'] = "Şifre en az 6 karakter olmalıdır.";
    } elseif (!preg_match("/[A-Z]/", $password)) {
        $errors['password'] = "Şifre en az bir büyük harf içermelidir.";
    } elseif (!preg_match("/[a-z]/", $password)) {
        $errors['password'] = "Şifre en az bir küçük harf içermelidir.";
    } elseif (!preg_match("/[0-9]/", $password)) {
        $errors['password'] = "Şifre en az bir rakam içermelidir.";
    }

    // Validate confirm password
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = "Şifreler eşleşmiyor.";
    }

    // Validate full name
    if (empty($full_name)) {
        $errors['full_name'] = "Ad Soyad gereklidir.";
    }

    // Validate phone
    if (empty($phone)) {
        $errors['phone'] = "Telefon numarası gereklidir.";
    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
        $errors['phone'] = "Geçerli bir telefon numarası giriniz (10 haneli).";
    }

    // Check if username or email already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $errors['exists'] = "Bu kullanıcı adı veya e-posta adresi zaten kayıtlı.";
        }
    }

    // If no errors, insert user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'user'; // Default role for new registrations
        
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, phone, full_name, role) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $username, $hashed_password, $email, $phone, $full_name, $role);
        
        if ($stmt->execute()) {
            $success = true;
            $_SESSION['register_success'] = true;
            $_SESSION['countdown_start'] = true;
        } else {
            $errors['db'] = "Kayıt sırasında bir hata oluştu.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Üye Ol - Sinema</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include_once '../../components/shared/navbar.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <?php if ($success): ?>
                    <div class="card shadow">
                        <div class="card-body text-center p-5">
                            <h2 class="text-success mb-4">
                                <i class="fas fa-check-circle fa-2x mb-3"></i><br>
                                Kayıt Başarılı!
                            </h2>
                            <p class="lead mb-4">Giriş sayfasına yönlendiriliyorsunuz...</p>
                            <div class="countdown-text h3 mb-4">3</div>
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                    <script>
                        let count = 3;
                        const countdownElement = document.querySelector('.countdown-text');
                        
                        const countdown = setInterval(() => {
                            count--;
                            if (count <= 0) {
                                clearInterval(countdown);
                                window.location.href = '../login/index.php';
                            } else {
                                countdownElement.textContent = count;
                            }
                        }, 1000);
                    </script>
                <?php else: ?>
                    <div class="card shadow">
                        <div class="card-body p-5">
                            <h2 class="text-center mb-4">Üye Ol</h2>
                            
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?php echo htmlspecialchars($error); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="" id="registerForm" novalidate>
                                <div class="mb-3">
                                    <label for="username" class="form-label">Kullanıcı Adı</label>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">E-posta Adresi</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Şifre</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <div class="form-text">
                                        Şifreniz en az 6 karakter uzunluğunda olmalı ve en az bir büyük harf, bir küçük harf ve bir rakam içermelidir.
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Şifre Tekrar</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>

                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Ad Soyad</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" 
                                           value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">Telefon Numarası</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" 
                                           placeholder="5XX XXX XXXX" required>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">Üye Ol</button>
                            </form>

                            <div class="text-center mt-3">
                                <p>Zaten üye misiniz? <a href="../login/index.php">Giriş Yap</a></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include_once '../../components/shared/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html> 