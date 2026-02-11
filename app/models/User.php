<?php
class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    
    public function updateRefreshToken($userId, $token) {
        $stmt = $this->db->prepare("UPDATE users SET refresh_token = ? WHERE id = ?");
        return $stmt->execute([$token, $userId]);
    }
public function storeRefreshToken($userId, $refreshToken) {
    $stmt = $this->db->prepare("UPDATE users SET refresh_token=? WHERE id=?");
    return $stmt->execute([$refreshToken, $userId]);
}

    
    public function findByRefreshToken($token) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE refresh_token = ? LIMIT 1");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

   
    public function create($name, $email, $password) {
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        return $stmt->execute([$name, $email, $password]);
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}