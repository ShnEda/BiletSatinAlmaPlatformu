<header class="site-header">
    <div class="container">
        <nav class="navbar">
            <a href="/index.php" class="logo">
                <span class="logo-icon">🚌</span>
                <span class="logo-text">BiletAl</span>
            </a>
            
            <ul class="nav-menu">
                <li><a href="/index.php" class="<?= isActivePage('index.php') ? 'active' : '' ?>">Ana Sayfa</a></li>
                
                <?php if (Session::isLoggedIn()): ?>
                    <?php if (Session::isUser()): ?>
                        <li><a href="/biletlerim.php" class="<?= isActivePage('biletlerim.php') ? 'active' : '' ?>">Biletlerim</a></li>
                    <?php endif; ?>
                    
                    <?php if (Session::isFirmaAdmin()): ?>
                        <li><a href="/firma-admin/index.php" class="<?= isActivePage('firma-admin') ? 'active' : '' ?>">Firma Paneli</a></li>
                    <?php endif; ?>
                    
                    <?php if (Session::isAdmin()): ?>
                        <li><a href="/admin/index.php" class="<?= isActivePage('admin') ? 'active' : '' ?>">Admin Paneli</a></li>
                    <?php endif; ?>
                    
                    <li class="user-menu">
                        <span class="user-name">
                            👤 <?= h(Session::get('user_name')) ?>
                            <?php if (Session::isUser()): ?>
                                <span class="user-balance"><?= formatMoney(Session::get('user_balance')) ?></span>
                            <?php endif; ?>
                        </span>
                    </li>
                    <li><a href="/logout.php" class="btn-logout">Çıkış</a></li>
                <?php else: ?>
                    <li><a href="/login.php" class="btn-login">Giriş Yap</a></li>
                    <li><a href="/register.php" class="btn-register">Kayıt Ol</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>