<?php
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/utils/Session.php';
require_once __DIR__ . '/../src/utils/helpers.php';

Session::start();

// Zaten giriş yapmışsa yönlendir
if (Session::isLoggedIn()) {
    $role = Session::getUserRole();
    if ($role === 'admin') {
        redirect('/admin/index.php');
    } elseif ($role === 'firma_admin') {
        redirect('/firma-admin/index.php');
    } else {
        redirect('/index.php');
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Lütfen tüm alanları doldurun.';
    } else {
        $userModel = new User();
        $user = $userModel->login($email, $password);
        
        if ($user) {
            Session::login($user);
            
            // Role göre yönlendir
            if ($user['role'] === 'admin') {
                redirect('/admin/index.php');
            } elseif ($user['role'] === 'firma_admin') {
                redirect('/firma-admin/index.php');
            } else {
                redirect('/index.php');
            }
        } else {
            $error = 'E-posta veya şifre hatalı.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - BiletAl</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="auth-container">
            <h2>🔐 Giriş Yap</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= h($error) ?></div>
            <?php endif; ?>
            
            <?php displayFlash(); ?>
            
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">E-posta</label>
                    <input type="email" id="email" name="email" required 
                           value="<?= h($_POST['email'] ?? '') ?>" 
                           placeholder="ornek@email.com">
                </div>
                
                <div class="form-group">
                    <label for="password">Şifre</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="********">
                </div>
                
                <button type="submit" class="btn btn-primary">Giriş Yap</button>
            </form>
            
            <div class="auth-links">
                <p>Hesabınız yok mu? <a href="/register.php">Kayıt Ol</a></p>
            </div>
            
            <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 5px;">
                <h4 style="margin-bottom: 10px;">Test Hesapları:</h4>
                <p><strong>Admin:</strong> admin@bilet.com / admin123</p>
                <p><strong>Firma Admin:</strong> metro@bilet.com / metro123</p>
                <p><strong>Kullanıcı:</strong> ahmet@email.com / ahmet123</p>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>