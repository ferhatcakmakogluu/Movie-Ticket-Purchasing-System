<?php
require_once __DIR__ . '/../../config/database.php';

function handleLogin() {
    global $conn;
    $errors = [];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        // Validate inputs
        if (empty($username)) {
            $errors[] = "Kullanıcı adı gereklidir.";
        }
        if (empty($password)) {
            $errors[] = "Şifre gereklidir.";
        }

        if (empty($errors)) {
            // Check user in database
            $stmt = $conn->prepare("SELECT user_id, username, password, full_name FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                if (password_verify($password, $user['password'])) {
                    // Login successful
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];

                    // Handle remember me
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/'); // 30 days
                        
                        // Store token in database
                        $stmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE user_id = ?");
                        $stmt->bind_param("si", $token, $user['user_id']);
                        $stmt->execute();
                    }

                    // Redirect to home page
                    header("Location: /web/cinema-ticket/index.php");
                    exit();
                } else {
                    $errors[] = "Geçersiz kullanıcı adı veya şifre.";
                }
            } else {
                $errors[] = "Geçersiz kullanıcı adı veya şifre.";
            }
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode("<br>", $errors);
        }
    }
}

function renderLoginForm() {
    handleLogin();
    ?>
    <div class="login-form-container">
        <div class="card shadow">
            <div class="card-body p-5">
                <form method="POST" action="" class="needs-validation" novalidate>
                    <div class="mb-4">
                        <label for="username" class="form-label">Kullanıcı Adı</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Şifre</label>
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
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
    <?php
}
?> 