<?php
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/utils/Session.php';
require_once __DIR__ . '/../src/utils/helpers.php';

Session::start();

$seferModel = new Sefer();
$sehirler = getSehirler();

$seferler = [];
$searchPerformed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kalkis = $_POST['kalkis'] ?? '';
    $varis = $_POST['varis'] ?? '';
    $tarih = $_POST['tarih'] ?? '';
    
    if ($kalkis && $varis) {
        $seferler = $seferModel->search($kalkis, $varis, $tarih);
        $searchPerformed = true;
    }
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilet Satın Alma Platformu</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <?php displayFlash(); ?>
        
        <div class="hero">
            <h1>🚌 Otobüs Bileti Al</h1>
            <p>Türkiye'nin en güvenilir bilet satış platformu</p>
        </div>
        
        <div class="search-box">
            <form method="POST" class="search-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="kalkis">Kalkış</label>
                        <select name="kalkis" id="kalkis" required>
                            <option value="">Kalkış Noktası Seçin</option>
                            <?php foreach ($sehirler as $sehir): ?>
                                <option value="<?= h($sehir) ?>" <?= (isset($_POST['kalkis']) && $_POST['kalkis'] === $sehir) ? 'selected' : '' ?>>
                                    <?= h($sehir) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="varis">Varış</label>
                        <select name="varis" id="varis" required>
                            <option value="">Varış Noktası Seçin</option>
                            <?php foreach ($sehirler as $sehir): ?>
                                <option value="<?= h($sehir) ?>" <?= (isset($_POST['varis']) && $_POST['varis'] === $sehir) ? 'selected' : '' ?>>
                                    <?= h($sehir) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="tarih">Tarih</label>
                        <input type="date" name="tarih" id="tarih" 
                               min="<?= date('Y-m-d') ?>" 
                               value="<?= h($_POST['tarih'] ?? date('Y-m-d')) ?>">
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Sefer Ara</button>
                    </div>
                </div>
            </form>
        </div>
        
        <?php if ($searchPerformed): ?>
            <div class="results-section">
                <h2>Sefer Sonuçları</h2>
                
                <?php if (empty($seferler)): ?>
                    <div class="alert alert-info">
                        Seçtiğiniz güzergah için sefer bulunamadı. Lütfen farklı bir tarih veya güzergah deneyin.
                    </div>
                <?php else: ?>
                    <div class="sefer-list">
                        <?php foreach ($seferler as $sefer): ?>
                            <div class="sefer-card">
                                <div class="sefer-header">
                                    <h3><?= h($sefer['firma_name']) ?></h3>
                                    <span class="sefer-fiyat"><?= formatMoney($sefer['fiyat']) ?></span>
                                </div>
                                
                                <div class="sefer-body">
                                    <div class="sefer-route">
                                        <div class="route-point">
                                            <strong><?= h($sefer['kalkis']) ?></strong>
                                            <span><?= formatTime($sefer['saat']) ?></span>
                                        </div>
                                        <div class="route-line">➜</div>
                                        <div class="route-point">
                                            <strong><?= h($sefer['varis']) ?></strong>
                                            <span><?= formatDate($sefer['tarih']) ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="sefer-info">
                                        <span class="info-item">
                                            🪑 <?= $sefer['bos_koltuk'] ?> / <?= $sefer['toplam_koltuk'] ?> Boş Koltuk
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="sefer-footer">
                                    <a href="/sefer-detay.php?id=<?= $sefer['id'] ?>" class="btn btn-secondary">
                                        Detaylar
                                    </a>
                                    
                                    <?php if (Session::isLoggedIn()): ?>
                                        <?php if ($sefer['bos_koltuk'] > 0): ?>
                                            <a href="/bilet-satin-al.php?sefer_id=<?= $sefer['id'] ?>" class="btn btn-primary">
                                                Bilet Al
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-disabled" disabled>Dolu</button>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <a href="/login.php" class="btn btn-primary">
                                            Bilet Al
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>