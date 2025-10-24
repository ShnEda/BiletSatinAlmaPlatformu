<?php
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/utils/Session.php';
require_once __DIR__ . '/../src/utils/helpers.php';

Session::start();

$seferId = $_GET['id'] ?? null;

if (!$seferId) {
    redirect('/index.php');
}

$seferModel = new Sefer();
$sefer = $seferModel->findById($seferId);

if (!$sefer) {
    Session::setFlash('error', 'Sefer bulunamadı.');
    redirect('/index.php');
}

$doluKoltuklar = $seferModel->getDoluKoltuklar($seferId);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sefer Detayları - BiletAl</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <h2>🚌 Sefer Detayları</h2>
        
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
                
                <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 5px;">
                    <h3 style="margin-bottom: 15px;">📊 Sefer Bilgileri</h3>
                    
                    <div style="display: grid; gap: 15px;">
                        <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #ddd;">
                            <strong>Firma:</strong>
                            <span><?= h($sefer['firma_name']) ?></span>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #ddd;">
                            <strong>Tarih:</strong>
                            <span><?= formatDate($sefer['tarih']) ?></span>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #ddd;">
                            <strong>Kalkış Saati:</strong>
                            <span><?= formatTime($sefer['saat']) ?></span>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #ddd;">
                            <strong>Fiyat:</strong>
                            <span><?= formatMoney($sefer['fiyat']) ?></span>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #ddd;">
                            <strong>Toplam Koltuk:</strong>
                            <span><?= $sefer['toplam_koltuk'] ?></span>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #ddd;">
                            <strong>Dolu Koltuk:</strong>
                            <span><?= $sefer['dolu_koltuk'] ?></span>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; padding: 10px 0;">
                            <strong>Boş Koltuk:</strong>
                            <span style="color: green; font-weight: bold;"><?= $sefer['bos_koltuk'] ?></span>
                        </div>
                    </div>
                </div>
                
                <div style="margin-top: 30px;">
                    <h3 style="margin-bottom: 15px;">🪑 Koltuk Durumu</h3>
                    <div class="koltuk-container" style="pointer-events: none;">
                        <?php for ($i = 1; $i <= $sefer['toplam_koltuk']; $i++): ?>
                            <?php $isDolu = in_array($i, $doluKoltuklar); ?>
                            <div class="koltuk <?= $isDolu ? 'dolu' : '' ?>">
                                <div style="font-size: 24px; margin-bottom: 5px;">🪑</div>
                                <div><?= $i ?></div>
                                <?= $isDolu ? '<div style="font-size: 12px; color: #dc3545;">Dolu</div>' : '<div style="font-size: 12px; color: #28a745;">Boş</div>' ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
            
            <div class="sefer-footer">
                <a href="/index.php" class="btn btn-secondary">◀ Geri</a>
                
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
                        Bilet Almak İçin Giriş Yapın
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>