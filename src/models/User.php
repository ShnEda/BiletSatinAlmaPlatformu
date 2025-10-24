<?php

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Kullanıcı kaydı
    public function register($email, $password, $name) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        $sql = "INSERT INTO users (email, password, name, role, balance) 
                VALUES (:email, :password, :name, 'user', 100.00)";
        
        try {
            $this->db->execute($sql, [
                ':email' => $email,
                ':password' => $hashedPassword,
                ':name' => $name
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
                throw new Exception('Bu e-posta adresi zaten kayıtlı.');
            }
            throw $e;
        }
    }
    
    // E-posta ile giriş
    public function login($email, $password) {
        $sql = "SELECT * FROM users WHERE email = :email";
        $user = $this->db->fetchOne($sql, [':email' => $email]);
        
        if (!$user) {
            return false;
        }
        
        if (!password_verify($password, $user['password'])) {
            return false;
        }
        
        return $user;
    }
    
    // ID ile kullanıcı bulma
    public function findById($id) {
        $sql = "SELECT u.*, f.name as firma_name 
                FROM users u 
                LEFT JOIN firmalar f ON u.firma_id = f.id 
                WHERE u.id = :id";
        return $this->db->fetchOne($sql, [':id' => $id]);
    }
    
    // E-posta ile kullanıcı bulma
    public function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = :email";
        return $this->db->fetchOne($sql, [':email' => $email]);
    }
    
    // Bakiye güncelleme
    public function updateBalance($userId, $amount) {
        $sql = "UPDATE users SET balance = :balance WHERE id = :id";
        return $this->db->execute($sql, [
            ':balance' => $amount,
            ':id' => $userId
        ]);
    }
    
    // Bakiye artırma
    public function increaseBalance($userId, $amount) {
        $sql = "UPDATE users SET balance = balance + :amount WHERE id = :id";
        return $this->db->execute($sql, [
            ':amount' => $amount,
            ':id' => $userId
        ]);
    }
    
    // Bakiye azaltma
    public function decreaseBalance($userId, $amount) {
        $sql = "UPDATE users SET balance = balance - :amount WHERE id = :id";
        return $this->db->execute($sql, [
            ':amount' => $amount,
            ':id' => $userId
        ]);
    }
    
    // Firma Admin oluşturma
    public function createFirmaAdmin($email, $password, $name, $firmaId) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        $sql = "INSERT INTO users (email, password, name, role, firma_id, balance) 
                VALUES (:email, :password, :name, 'firma_admin', :firma_id, 0)";
        
        try {
            $this->db->execute($sql, [
                ':email' => $email,
                ':password' => $hashedPassword,
                ':name' => $name,
                ':firma_id' => $firmaId
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
                throw new Exception('Bu e-posta adresi zaten kayıtlı.');
            }
            throw $e;
        }
    }
    
    // Tüm Firma Adminlerini listele
    public function getAllFirmaAdmins() {
        $sql = "SELECT u.*, f.name as firma_name 
                FROM users u 
                LEFT JOIN firmalar f ON u.firma_id = f.id 
                WHERE u.role = 'firma_admin' 
                ORDER BY u.created_at DESC";
        return $this->db->fetchAll($sql);
    }
    
    // Firma Admin güncelleme
    public function updateFirmaAdmin($id, $email, $name, $firmaId, $password = null) {
        if ($password) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $sql = "UPDATE users 
                    SET email = :email, name = :name, firma_id = :firma_id, password = :password 
                    WHERE id = :id AND role = 'firma_admin'";
            return $this->db->execute($sql, [
                ':email' => $email,
                ':name' => $name,
                ':firma_id' => $firmaId,
                ':password' => $hashedPassword,
                ':id' => $id
            ]);
        } else {
            $sql = "UPDATE users 
                    SET email = :email, name = :name, firma_id = :firma_id 
                    WHERE id = :id AND role = 'firma_admin'";
            return $this->db->execute($sql, [
                ':email' => $email,
                ':name' => $name,
                ':firma_id' => $firmaId,
                ':id' => $id
            ]);
        }
    }
    
    // Kullanıcı silme
    public function delete($id) {
        $sql = "DELETE FROM users WHERE id = :id";
        return $this->db->execute($sql, [':id' => $id]);
    }
    
    // Profil güncelleme
    public function updateProfile($id, $name, $email) {
        $sql = "UPDATE users SET name = :name, email = :email WHERE id = :id";
        return $this->db->execute($sql, [
            ':name' => $name,
            ':email' => $email,
            ':id' => $id
        ]);
    }
    
    // Şifre değiştirme
    public function changePassword($id, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $sql = "UPDATE users SET password = :password WHERE id = :id";
        return $this->db->execute($sql, [
            ':password' => $hashedPassword,
            ':id' => $id
        ]);
    }
}