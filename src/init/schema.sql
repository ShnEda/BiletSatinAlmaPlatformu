-- Kullanıcılar Tablosu
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'user',
    firma_id INTEGER NULL,
    balance DECIMAL(10,2) DEFAULT 100.00,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (firma_id) REFERENCES firmalar(id) ON DELETE SET NULL,
    CHECK (role IN ('admin', 'firma_admin', 'user'))
);

-- Firmalar Tablosu
CREATE TABLE IF NOT EXISTS firmalar (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Seferler Tablosu
CREATE TABLE IF NOT EXISTS seferler (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    firma_id INTEGER NOT NULL,
    kalkis VARCHAR(100) NOT NULL,
    varis VARCHAR(100) NOT NULL,
    tarih DATE NOT NULL,
    saat TIME NOT NULL,
    fiyat DECIMAL(10,2) NOT NULL,
    toplam_koltuk INTEGER NOT NULL DEFAULT 40,
    dolu_koltuk INTEGER NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (firma_id) REFERENCES firmalar(id) ON DELETE CASCADE
);

-- Biletler Tablosu
CREATE TABLE IF NOT EXISTS biletler (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    sefer_id INTEGER NOT NULL,
    koltuk_no INTEGER NOT NULL,
    fiyat DECIMAL(10,2) NOT NULL,
    kupon_kodu VARCHAR(50) NULL,
    indirim_tutari DECIMAL(10,2) DEFAULT 0.00,
    durum VARCHAR(20) DEFAULT 'active',
    satin_alma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    iptal_tarihi DATETIME NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (sefer_id) REFERENCES seferler(id) ON DELETE CASCADE,
    CHECK (durum IN ('active', 'cancelled'))
);

-- Kuponlar Tablosu
CREATE TABLE IF NOT EXISTS kuponlar (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    kod VARCHAR(50) UNIQUE NOT NULL,
    firma_id INTEGER NULL,
    indirim_orani INTEGER NOT NULL,
    kullanim_limiti INTEGER NOT NULL DEFAULT 100,
    kullanim_sayisi INTEGER NOT NULL DEFAULT 0,
    son_kullanim_tarihi DATE NOT NULL,
    aktif INTEGER DEFAULT 1,
    created_by INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (firma_id) REFERENCES firmalar(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    CHECK (indirim_orani >= 0 AND indirim_orani <= 100)
);

-- Kupon Kullanım Takibi
CREATE TABLE IF NOT EXISTS kupon_kullanim (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    kupon_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    bilet_id INTEGER NOT NULL,
    kullanim_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kupon_id) REFERENCES kuponlar(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (bilet_id) REFERENCES biletler(id) ON DELETE CASCADE
);

-- İndeksler
CREATE INDEX idx_seferler_firma ON seferler(firma_id);
CREATE INDEX idx_seferler_tarih ON seferler(tarih, saat);
CREATE INDEX idx_seferler_rota ON seferler(kalkis, varis);
CREATE INDEX idx_biletler_user ON biletler(user_id);
CREATE INDEX idx_biletler_sefer ON biletler(sefer_id);
CREATE INDEX idx_kuponlar_kod ON kuponlar(kod);
CREATE INDEX idx_users_email ON users(email);