<?php

class PDFGenerator {
    
    public static function generateBilet($bilet) {
        // Basit HTML tabanlı PDF oluşturma
        // Not: Production'da FPDF veya TCPDF kullanılabilir
        
        $html = self::getBiletHTML($bilet);
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="bilet_' . $bilet['id'] . '.pdf"');
        
        // DomPDF veya benzeri kütüphane olmadan basit çözüm
        // Burada HTML to PDF için tarayıcı print fonksiyonu kullanılacak
        echo $html;
    }
    
    private static function getBiletHTML($bilet) {
        $odenenTutar = $bilet['fiyat'] - $bilet['indirim_tutari'];
        
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Bilet #' . $bilet['id'] . '</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            padding: 40px;
            background: white;
        }
        .bilet-container {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #333;
            padding: 30px;
            background: white;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #333;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 15px 0;
            padding: 10px;
            background: #f5f5f5;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        .info-value {
            color: #333;
        }
        .route {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin: 30px 0;
            padding: 20px;
            background: #e8f4f8;
            border-radius: 10px;
        }
        .barcode {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: #f9f9f9;
            font-family: monospace;
            font-size: 18px;
            letter-spacing: 3px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #333;
            color: #666;
            font-size: 12px;
        }
        .durum-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 5px;
            font-weight: bold;
        }
        .durum-active { background: #d4edda; color: #155724; }
        .durum-cancelled { background: #f8d7da; color: #721c24; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="bilet-container">
        <div class="header">
            <h1>🚌 OTOBÜS BİLETİ</h1>
            <p style="margin: 10px 0; font-size: 18px;">' . h($bilet['firma_name']) . '</p>
        </div>
        
        <div class="route">
            ' . h($bilet['kalkis']) . ' ➜ ' . h($bilet['varis']) . '
        </div>
        
        <div class="info-row">
            <span class="info-label">Bilet No:</span>
            <span class="info-value">#' . str_pad($bilet['id'], 6, '0', STR_PAD_LEFT) . '</span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Yolcu Adı:</span>
            <span class="info-value">' . h($bilet['yolcu_name']) . '</span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Tarih:</span>
            <span class="info-value">' . formatDate($bilet['tarih']) . '</span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Kalkış Saati:</span>
            <span class="info-value">' . formatTime($bilet['saat']) . '</span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Koltuk No:</span>
            <span class="info-value">' . $bilet['koltuk_no'] . '</span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Bilet Fiyatı:</span>
            <span class="info-value">' . formatMoney($bilet['fiyat']) . '</span>
        </div>';
        
        if ($bilet['indirim_tutari'] > 0) {
            $html .= '
        <div class="info-row">
            <span class="info-label">İndirim (' . h($bilet['kupon_kodu']) . '):</span>
            <span class="info-value">-' . formatMoney($bilet['indirim_tutari']) . '</span>
        </div>';
        }
        
        $html .= '
        <div class="info-row" style="background: #fff3cd; font-size: 18px;">
            <span class="info-label">Ödenen Tutar:</span>
            <span class="info-value" style="font-weight: bold;">' . formatMoney($odenenTutar) . '</span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Satın Alma:</span>
            <span class="info-value">' . formatDateTime($bilet['satin_alma_tarihi']) . '</span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Durum:</span>
            <span class="info-value">
                <span class="durum-badge durum-' . $bilet['durum'] . '">
                    ' . ($bilet['durum'] === 'active' ? 'AKTİF' : 'İPTAL EDİLDİ') . '
                </span>
            </span>
        </div>
        
        <div class="barcode">
            ' . strtoupper(substr(md5($bilet['id'] . $bilet['user_id']), 0, 16)) . '
        </div>
        
        <div class="footer">
            <p><strong>ÖNEMLİ BİLGİLER:</strong></p>
            <p>• Lütfen seyahatten 30 dakika önce terminalde bulunun.</p>
            <p>• Biletinizi ve kimlik belgenizi yanınızda bulundurun.</p>
            <p>• İptal işlemleri kalkış saatinden en az 1 saat önce yapılmalıdır.</p>
            <p style="margin-top: 20px;">İyi yolculuklar dileriz!</p>
        </div>
    </div>
    
    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 30px; font-size: 16px; cursor: pointer;">
            Yazdır / PDF Kaydet
        </button>
        <button onclick="window.close()" style="padding: 10px 30px; font-size: 16px; cursor: pointer; margin-left: 10px;">
            Kapat
        </button>
    </div>
</body>
</html>';
        
        return $html;
    }
}