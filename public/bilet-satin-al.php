<?php
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/utils/Session.php';
require_once __DIR__ . '/../src/utils/helpers.php';

Session::start();
requireRole('user');

$seferId = $_GET['sefer_id'] ?? null;

if (!$seferId) {
    Session::setFlash('error', 'Geçersiz sefer.');
    redirect('/index.php');
}

$seferModel = new Sefer();
$biletModel = new Bilet();
$kuponModel = new Kupon();
$userModel = new User();

$sefer = $seferModel->findById($seferId);

if (!$sefer) {
    Session::setFlash('error', 'Sefer bulunamadı.');
    redirect('/index.php');
}

if ($sefer['bos_koltuk'] <= 0) {
    Session::setFlash('error', 'Bu seferde boş koltuk kalmamıştır.');
    redirect('/index.php');
}

$doluKoltuklar = $seferModel->getDoluKoltuklar($seferId);
$user = Session::getUser();
$kuponIndirim = 0;
$kuponKodu = '';
$kuponMesaj = '';

// Kupon doğrulama
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kupon_kodu'])) {
    $kuponKodu = strtoupper(trim($_POST['kupon_kodu']));
    $validation = $kuponModel->validate($kuponKodu, $sefer['firma_id']);
    
    if ($validation['valid']) {
        $kuponIndirim = $validation['kupon']['indirim_orani'];
        $kuponMesaj = $validation['message'];
    } else {
        $kuponMesaj = $validation['message'];
    }
}

// Bilet satın alma
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['koltuk_no'])) {
    $koltukNo = (int)$_POST['koltuk_no'];
    $kuponKodu = strtoupper(trim($_POST['kupon_kodu_hidden'] ?? ''));
    $kuponIndirim = (float)($_POST['kupon_indirim_hidden'] ?? 0);
    
    // Koltuk müsait mi?
    if (!$seferModel->isKoltukAvailable($seferId, $koltukNo)) {
        Session::setFlash('success', 'Biletiniz başarıyla satın alındı!');
        redirect('/biletlerim.php');
        
    } catch (Exception $e) {
        $db->rollback();
        Session::setFlash('error', 'Bilet satın alınırken bir hata oluştu: ' . $e->getMessage());
        redirect('/bilet-satin-al.php?sefer_id=' . $seferId);
    }
}

$indirimTutari = hesaplaIndirim($sefer['fiyat'], $kuponIndirim);
$odenenTutar = $sefer['fiyat'] - $indirimTutari;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilet Satın Al - BiletAl</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <h2>🎫 Bilet Satın Al</h2>
        
        <?php displayFlash(); ?>
        
        <div class="sefer-card" style="margin-bottom: 30px;">
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
            </div>
        </div>
        
        <!-- Kupon Uygulama -->
        <div class="table-container" style="margin-bottom: 30px;">
            <h3>💳 İndirim Kuponu</h3>
            <form method="POST" style="display: flex; gap: 10px; align-items: end; margin-top: 20px;">
                <div class="form-group" style="flex: 1; margin-bottom: 0;">
                    <input type="text" name="kupon_kodu" placeholder="Kupon kodunu girin" 
                           value="<?= h($kuponKodu) ?>" style="text-transform: uppercase;">
                </div>
                <button type="submit" class="btn btn-secondary">Uygula</button>
            </form>
            
            <?php if ($kuponMesaj): ?>
                <div class="alert <?= $kuponIndirim > 0 ? 'alert-success' : 'alert-error' ?>" style="margin-top: 15px;">
                    <?= h($kuponMesaj) ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Fiyat Özeti -->
        <?php if ($kuponIndirim > 0): ?>
            <div class="table-container" style="margin-bottom: 30px; background: #fff3cd;">
                <h3>💰 Fiyat Özeti</h3>
                <div style="margin-top: 15px;">
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #ddd;">
                        <span>Bilet Fiyatı:</span>
                        <strong><?= formatMoney($sefer['fiyat']) ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #ddd; color: green;">
                        <span>İndirim (%<?= $kuponIndirim ?>):</span>
                        <strong>-<?= formatMoney($indirimTutari) ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; font-size: 20px;">
                        <span>Ödenecek Tutar:</span>
                        <strong><?= formatMoney($odenenTutar) ?></strong>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Koltuk Seçimi -->
        <div class="table-container">
            <h3>🪑 Koltuk Seçimi</h3>
            <p style="margin: 15px 0;">
                Bakiyeniz: <strong><?= formatMoney($user['balance']) ?></strong>
            </p>
            
            <div style="display: flex; gap: 20px; margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 30px; height: 30px; background: white; border: 2px solid #ddd; border-radius: 5px;"></div>
                    <span>Boş</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 30px; height: 30px; background: #f8d7da; border: 2px solid #dc3545; border-radius: 5px;"></div>
                    <span>Dolu</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 30px; height: 30px; background: #d4edda; border: 2px solid #28a745; border-radius: 5px;"></div>
                    <span>Seçili</span>
                </div>
            </div>
            
            <form method="POST" id="biletForm">
                <input type="hidden" name="kupon_kodu_hidden" value="<?= h($kuponKodu) ?>">
                <input type="hidden" name="kupon_indirim_hidden" value="<?= $kuponIndirim ?>">
                <input type="hidden" name="koltuk_no" id="koltuk_no_input">
                
                <div class="koltuk-container">
                    <?php for ($i = 1; $i <= $sefer['toplam_koltuk']; $i++): ?>
                        <?php $isDolu = in_array($i, $doluKoltuklar); ?>
                        <div class="koltuk <?= $isDolu ? 'dolu' : '' ?>" 
                             data-koltuk="<?= $i ?>"
                             onclick="<?= $isDolu ? '' : 'selectKoltuk(' . $i . ')' ?>">
                            <div style="font-size: 24px; margin-bottom: 5px;">🪑</div>
                            <div><?= $i ?></div>
                            <?= $isDolu ? '<div style="font-size: 12px; color: #dc3545;">Dolu</div>' : '' ?>
                        </div>
                    <?php endfor; ?>
                </div>
                
                <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 5px;">
                    <p id="secimMesaj" style="font-size: 18px; margin-bottom: 15px;">
                        Lütfen bir koltuk seçin
                    </p>
                    <button type="submit" id="satinAlBtn" class="btn btn-primary" disabled>
                        Satın Al - <?= formatMoney($odenenTutar) ?>
                    </button>
                </div>
            </form>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        let selectedKoltuk = null;
        
        function selectKoltuk(koltukNo) {
            // Önceki seçimi temizle
            if (selectedKoltuk) {
                const prevKoltuk = document.querySelector(`[data-koltuk="${selectedKoltuk}"]`);
                if (prevKoltuk) {
                    prevKoltuk.classList.remove('secili');
                }
            }
            
            // Yeni seçimi işaretle
            selectedKoltuk = koltukNo;
            const koltuk = document.querySelector(`[data-koltuk="${koltukNo}"]`);
            koltuk.classList.add('secili');
            
            // Hidden input'u güncelle
            document.getElementById('koltuk_no_input').value = koltukNo;
            
            // Mesajı güncelle
            document.getElementById('secimMesaj').innerHTML = 
                `<strong>Seçilen Koltuk: ${koltukNo}</strong>`;
            
            // Butonu aktif et
            document.getElementById('satinAlBtn').disabled = false;
        }
        
        // Form submit kontrolü
        document.getElementById('biletForm').addEventListener('submit', function(e) {
            if (!selectedKoltuk) {
                e.preventDefault();
                alert('Lütfen bir koltuk seçin!');
            }
        });
    </script>
</body>
</html>('error', 'Seçtiğiniz koltuk artık müsait değil.');
        redirect('/bilet-satin-al.php?sefer_id=' . $seferId);
    }
    
    $indirimTutari = hesaplaIndirim($sefer['fiyat'], $kuponIndirim);
    $odenenTutar = $sefer['fiyat'] - $indirimTutari;
    
    // Bakiye kontrolü
    if ($user['balance'] < $odenenTutar) {
        Session::setFlash('error', 'Yetersiz bakiye. Bakiyeniz: ' . formatMoney($user['balance']));
        redirect('/bilet-satin-al.php?sefer_id=' . $seferId);
    }
    
    try {
        $db = Database::getInstance();
        $db->beginTransaction();
        
        // Bilet oluştur
        $biletId = $biletModel->create(
            $user['id'],
            $seferId,
            $koltukNo,
            $odenenTutar,
            $kuponKodu ?: null,
            $indirimTutari
        );
        
        // Bakiyeyi azalt
        $userModel->decreaseBalance($user['id'], $odenenTutar);
        
        // Dolu koltuk sayısını artır
        $seferModel->increaseDoluKoltuk($seferId);
        
        // Kupon kullanımını kaydet
        if ($kuponKodu) {
            $kupon = $kuponModel->findByKod($kuponKodu);
            if ($kupon) {
                $kuponModel->increaseUsage($kupon['id']);
                $kuponModel->recordUsage($kupon['id'], $user['id'], $biletId);
            }
        }
        
        $db->commit();
        
        // Session'daki bakiyeyi güncelle
        $newBalance = $user['balance'] - $odenenTutar;
        Session::updateBalance($newBalance);
        
        Session::setFlash