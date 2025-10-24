<?php
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/utils/Session.php';
require_once __DIR__ . '/../src/utils/helpers.php';

Session::start();
requireRole('user');

$biletId = $_GET['id'] ?? null;

if (!$biletId) {
    Session::setFlash('error', 'Geçersiz bilet.');
    redirect('/biletlerim.php');
}

$biletModel = new Bilet();
$seferModel = new Sefer();
$userModel = new User();

$bilet = $biletModel->findById($biletId);

if (!$bilet) {
    Session::setFlash('error', 'Bilet bulunamadı.');
    redirect('/biletlerim.php');
}

// Kullanıcının kendi bileti mi?
if ($bilet['user_id'] != Session::getUserId()) {
    Session::setFlash('error', 'Bu bilete erişim yetkiniz yok.');
    redirect('/biletlerim.php');
}

// Bilet iptal edilebilir mi?
if ($bilet['durum'] !== 'active') {
    Session::setFlash('error', 'Bu bilet zaten iptal edilmiş.');
    redirect('/biletlerim.php');
}

if (!isSeferCancellable($bilet['tarih'], $bilet['saat'])) {
    Session::setFlash('error', 'Kalkış saatine 1 saatten az kaldığı için bilet iptal edilemez.');
    redirect('/biletlerim.php');
}

try {
    $db = Database::getInstance();
    $db->beginTransaction();
    
    // Bileti iptal et
    $biletModel->cancel($biletId);
    
    // Dolu koltuk sayısını azalt
    $seferModel->decreaseDoluKoltuk($bilet['sefer_id']);
    
    // Parayı iade et
    $userModel->increaseBalance($bilet['user_id'], $bilet['fiyat']);
    
    $db->commit();
    
    // Session bakiyeyi güncelle
    $user = $userModel->findById($bilet['user_id']);
    Session::updateBalance($user['balance']);
    
    Session::setFlash('success', 'Biletiniz başarıyla iptal edildi. ' . formatMoney($bilet['fiyat']) . ' hesabınıza iade edildi.');
    redirect('/biletlerim.php');
    
} catch (Exception $e) {
    $db->rollback();
    Session::setFlash('error', 'Bilet iptal edilirken bir hata oluştu: ' . $e->getMessage());
    redirect('/biletlerim.php');
}