<?php
    session_start();
    require_once __DIR__ . '/../../../config/db_connect.php';
    
    // Check if form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        // Validate input
        if (empty($username) || empty($password)) {
            $error = 'Kullanıcı adı ve şifre gereklidir.';
        } else {
            // Query to find user by username
            $stmt = $db->prepare('SELECT user_id, username, password, role FROM users WHERE username = ?');
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify user and password
            if ($user && password_verify($password, $user['password'])) {
                // Check if role is admin
                if ($user['role'] === 'admin') {
                    // Set session with user_id (overwrites if exists)
                    $_SESSION['admin_id'] = $user['user_id'];
                    // Redirect to home page
                    header('Location: ../home/home.php');
                    exit;
                } else {
                    $error = 'Bu sayfaya yalnızca admin kullanıcılar erişebilir.';
                }
            } else {
                $error = 'Geçersiz kullanıcı adı veya şifre.';
            }
        }
    }

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Giriş</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../components/login/login.css">
    <style>
        .login-form-container {
            max-width: 400px;
            margin: auto;
            padding-top: 50px;
        }
        .input-group-text, .btn-outline-secondary {
            border-color: #ced4da;
        }
        .btn-outline-secondary:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>

    <!-- Login Section -->
    <div class="login-form-container">
        <h1 class="text-center mb-4">Admin Yönetim Paneli</h1>
        <div class="card shadow">
            <div class="card-body p-5">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                <form method="POST" action="" class="needs-validation" novalidate>
                    <div class="mb-4">
                        <label for="username" class="form-label">Admin Kullanıcı Adı</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Admin Şifre</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Beni Hatırla</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fas fa-sign-in-alt me-2"></i>Giriş Yap
                    </button>

                    <div class="text-center">
                        <a href="#" class="text-muted">Şifremi Unuttum</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Password toggle functionality
        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Bootstrap form validation
        (function () {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>


    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/main.js"></script>
</body>
</html> 