<?php

class Bilet {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Bilet oluşturma
    public function create($userId, $seferId, $koltukNo, $fiyat, $kuponKodu = null, $indirimTutari = 0) {
        $sql = "INSERT INTO biletler (user_id, sefer_id, koltuk_no, fiyat, kupon_kodu, indirim_tutari, durum) 
                VALUES (:user_id, :sefer_id, :koltuk_no, :fiyat, :kupon_kodu, :indirim_tutari, 'active')";
        
        $this->db->execute($sql, [
            ':user_id' => $userId,
            ':sefer_id' => $seferId,
            ':koltuk_no' => $koltukNo,
            ':fiyat' => $fiyat,
            ':kupon_kodu' => $kuponKodu,
            ':indirim_tutari' => $indirimTutari
        ]);
        
        return $this->db->lastInsertId();
    }
    
    // Kullanıcının biletlerini listele
    public function getByUser($userId) {
        $sql = "SELECT b.*, s.kalkis, s.varis, s.tarih, s.saat, f.name as firma_name
                FROM biletler b
                INNER JOIN seferler s ON b.sefer_id = s.id
                WHERE s.firma_id = :firma_id";
        
        $params = [':firma_id' => $firmaId];
        
        if ($startDate) {
            $sql .= " AND s.tarih >= :start_date";
            $params[':start_date'] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND s.tarih <= :end_date";
            $params[':end_date'] = $endDate;
        }
        
        return $this->db->fetchOne($sql, $params);
    }
    
    // Son biletler
    public function getRecent($limit = 10) {
        $sql = "SELECT b.*, s.kalkis, s.varis, s.tarih, s.saat, 
                       f.name as firma_name, u.name as yolcu_name
                FROM biletler b
                INNER JOIN seferler s ON b.sefer_id = s.id
                INNER JOIN firmalar f ON s.firma_id = f.id
                INNER JOIN users u ON b.user_id = u.id
                ORDER BY b.satin_alma_tarihi DESC
                LIMIT :limit";
        
        return $this->db->fetchAll($sql, [':limit' => $limit]);
    }
}eferler s ON b.sefer_id = s.id
                INNER JOIN firmalar f ON s.firma_id = f.id
                WHERE b.user_id = :user_id
                ORDER BY b.satin_alma_tarihi DESC";
        
        return $this->db->fetchAll($sql, [':user_id' => $userId]);
    }
    
    // ID ile bilet bulma
    public function findById($id) {
        $sql = "SELECT b.*, s.kalkis, s.varis, s.tarih, s.saat, s.firma_id, 
                       f.name as firma_name, u.name as yolcu_name, u.email as yolcu_email
                FROM biletler b
                INNER JOIN seferler s ON b.sefer_id = s.id
                INNER JOIN firmalar f ON s.firma_id = f.id
                INNER JOIN users u ON b.user_id = u.id
                WHERE b.id = :id";
        
        return $this->db->fetchOne($sql, [':id' => $id]);
    }
    
    // Bilet iptali
    public function cancel($biletId) {
        $sql = "UPDATE biletler 
                SET durum = 'cancelled', iptal_tarihi = CURRENT_TIMESTAMP 
                WHERE id = :id AND durum = 'active'";
        
        return $this->db->execute($sql, [':id' => $biletId]);
    }
    
    // Bilet iptal edilebilir mi?
    public function isCancellable($biletId) {
        $bilet = $this->findById($biletId);
        
        if (!$bilet || $bilet['durum'] !== 'active') {
            return false;
        }
        
        return isSeferCancellable($bilet['tarih'], $bilet['saat']);
    }
    
    // Sefer için satılan bilet sayısı
    public function countBySefer($seferId) {
        $sql = "SELECT COUNT(*) as count FROM biletler 
                WHERE sefer_id = :sefer_id AND durum = 'active'";
        
        $result = $this->db->fetchOne($sql, [':sefer_id' => $seferId]);
        return $result['count'];
    }
    
    // Kullanıcının aktif bilet sayısı
    public function countActiveByUser($userId) {
        $sql = "SELECT COUNT(*) as count FROM biletler 
                WHERE user_id = :user_id AND durum = 'active'";
        
        $result = $this->db->fetchOne($sql, [':user_id' => $userId]);
        return $result['count'];
    }
    
    // Firma için bilet istatistikleri
    public function getStatsByFirma($firmaId, $startDate = null, $endDate = null) {
        $sql = "SELECT 
                    COUNT(b.id) as toplam_bilet,
                    SUM(CASE WHEN b.durum = 'active' THEN 1 ELSE 0 END) as aktif_bilet,
                    SUM(CASE WHEN b.durum = 'cancelled' THEN 1 ELSE 0 END) as iptal_bilet,
                    SUM(CASE WHEN b.durum = 'active' THEN b.fiyat ELSE 0 END) as toplam_gelir
                FROM biletler b
                INNER JOIN s