<?php
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/utils/Session.php';
require_once __DIR__ . '/../src/utils/helpers.php';

Session::start();
requireRole('user');

$biletModel = new Bilet();
$biletler = $biletModel->getByUser(Session::getUserId());
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biletlerim - BiletAl</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <h2>🎫 Biletlerim</h2>
        
        <?php displayFlash(); ?>
        
        <?php if (empty($biletler)): ?>
            <div class="alert alert-info">
                Henüz biletiniz yok. <a href="/index.php">Bilet satın almak için tıklayın.</a>
            </div>
        <?php else: ?>
            <div class="bilet-list">
                <?php foreach ($biletler as $bilet): ?>
                    <?php 
                    $odenenTutar = $bilet['fiyat'];
                    $isCancellable = $bilet['durum'] === 'active' && isSeferCancellable($bilet['tarih'], $bilet['saat']);
                    ?>
                    <div class="bilet-card">
                        <div class="bilet-header">
                            <div>
                                <h3>Bilet #<?= str_pad($bilet['id'], 6, '0', STR_PAD_LEFT) ?></h3>
                                <p style="color: #666; margin: 5px 0;"><?= h($bilet['firma_name']) ?></p>
                            </div>
                            <span class="bilet-status status-<?= $bilet['durum'] ?>">
                                <?= $bilet['durum'] === 'active' ? 'AKTİF' : 'İPTAL EDİLDİ' ?>
                            </span>
                        </div>
                        
                        <div class="bilet-details">
                            <div class="detail-item">
                                <span class="detail-label">Güzergah</span>
                                <span class="detail-value">
                                    <?= h($bilet['kalkis']) ?> ➜ <?= h($bilet['varis']) ?>
                                </span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-label">Tarih & Saat</span>
                                <span class="detail-value">
                                    <?= formatDate($bilet['tarih']) ?> - <?= formatTime($bilet['saat']) ?>
                                </span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-label">Koltuk No</span>
                                <span class="detail-value"><?= $bilet['koltuk_no'] ?></span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-label">Ödenen Tutar</span>
                                <span class="detail-value"><?= formatMoney($odenenTutar) ?></span>
                            </div>
                            
                            <?php if ($bilet['indirim_tutari'] > 0): ?>
                                <div class="detail-item">
                                    <span class="detail-label">Kupon</span>
                                    <span class="detail-value" style="color: green;">
                                        <?= h($bilet['kupon_kodu']) ?> 
                                        (-<?= formatMoney($bilet['indirim_tutari']) ?>)
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="detail-item">
                                <span class="detail-label">Satın Alma</span>
                                <span class="detail-value"><?= formatDateTime($bilet['satin_alma_tarihi']) ?></span>
                            </div>
                        </div>
                        
                        <div class="bilet-actions">
                            <?php if ($bilet['durum'] === 'active'): ?>
                                <a href="/bilet-pdf.php?id=<?= $bilet['id'] ?>" 
                                   class="btn btn-secondary" target="_blank">
                                    📄 PDF İndir
                                </a>
                                
                                <?php if ($isCancellable): ?>
                                    <a href="/bilet-iptal.php?id=<?= $bilet['id'] ?>" 
                                       class="btn btn-danger"
                                       onclick="return confirm('Bu bileti iptal etmek istediğinizden emin misiniz? Ödediğiniz tutar hesabınıza iade edilecektir.')">
                                        ❌ İptal Et
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-disabled" disabled 
                                            title="Kalkışa 1 saatten az kaldı, iptal edilemez">
                                        ⏰ İptal Edilemez
                                    </button>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color: #dc3545; font-weight: bold;">
                                    İptal Tarihi: <?= formatDateTime($bilet['iptal_tarihi']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>