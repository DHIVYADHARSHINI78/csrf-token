<?php
class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function storeRefreshToken($userId, $refreshToken) {
     
        $stmt1 = $this->db->prepare("DELETE FROM refresh_tokens WHERE user_id = ?");
        $stmt1->execute([$userId]);

        if ($refreshToken) {
           
            $tokenHash = password_hash($refreshToken, PASSWORD_BCRYPT);
            
       $expiresAt = date('Y-m-d H:i:s', time() + REFRESH_TOKEN_EXP);
          
            $stmt3 = $this->db->prepare("INSERT INTO refresh_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)");
            return $stmt3->execute([$userId, $tokenHash, $expiresAt]);
        }
        return true;
    }

    public function findByRefreshToken($plainToken) {
    
        $sql = "SELECT rt.*, u.* FROM users u 
                JOIN refresh_tokens rt ON u.id = rt.user_id 
                WHERE rt.expires_at > NOW()";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        
        foreach ($rows as $row) {
            if (password_verify($plainToken, $row['token_hash'])) {
                return $row; 
            }
        }
        return null;
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