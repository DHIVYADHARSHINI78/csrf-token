<?php
class Patient {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findById($id) {
        $sql = "SELECT * FROM patients WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($name, $age, $gender, $contact, $address) {
        $query = "INSERT INTO patients (name, age, gender, contact, address) VALUES (:name, :age, :gender, :contact, :address)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':name' => $name,
            ':age' => $age,
            ':gender' => $gender,
            ':contact' => $contact,
            ':address' => $address
        ]);
    }

    public function update($id, $name, $age, $gender, $contact, $address) {
        $query = "UPDATE patients SET name = :name, age = :age, gender = :gender, contact = :contact, address = :address WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':name' => $name,
            ':age' => $age,
            ':gender' => $gender,
            ':contact' => $contact,
            ':address' => $address,
            ':id' => $id
        ]);
    }

    public function patchUpdate($id, $data) {
        $fields = [];
        $params = [':id' => $id];
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[":$key"] = $value;
        }
        $sql = "UPDATE patients SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id) {
        $query = "DELETE FROM patients WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM patients");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}