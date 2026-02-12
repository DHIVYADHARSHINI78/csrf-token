<?php
class Patient {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

   
    public function findById($id, $userId) {
        $sql = "SELECT * FROM patients WHERE id = :id AND user_id = :userId LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id, 'userId' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($name, $age, $gender, $contact, $address, $userId) {
        $query = "INSERT INTO patients (name, age, gender, contact, address, user_id) 
                  VALUES (:name, :age, :gender, :contact, :address, :userId)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':name' => $name,
            ':age' => $age,
            ':gender' => $gender,
            ':contact' => $contact,
            ':address' => $address,
            ':userId' => $userId
        ]);
    }

  
    public function update($id, $name, $age, $gender, $contact, $address, $userId) {
        $query = "UPDATE patients 
                  SET name = :name, age = :age, gender = :gender, contact = :contact, address = :address 
                  WHERE id = :id AND user_id = :userId";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':name' => $name,
            ':age' => $age,
            ':gender' => $gender,
            ':contact' => $contact,
            ':address' => $address,
            ':id' => $id,
            ':userId' => $userId
        ]);
    }

   
public function patchUpdate($id, $data, $userId) {

    unset($data['user_id']); 

    $fields = [];
    $params = [':id' => $id, ':userId' => $userId];
    
    foreach ($data as $key => $value) {
        $fields[] = "$key = :$key";
        $params[":$key"] = $value;
    }
    
    
    $sql = "UPDATE patients SET " . implode(', ', $fields) . " WHERE id = :id AND user_id = :userId";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute($params);
}
    
    public function delete($id, $userId) {
        $query = "DELETE FROM patients WHERE id = :id AND user_id = :userId";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id, ':userId' => $userId]);
    }

    public function getAll($userId) {
        $stmt = $this->db->prepare("SELECT * FROM patients WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}