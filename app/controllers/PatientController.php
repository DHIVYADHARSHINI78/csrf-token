<?php

class PatientController {

    public function index() {
        $patientModel = new Patient();
        $patients = $patientModel->getAll();
        Response::json([
            "requested_by" => $GLOBALS['user']['email'] ?? 'Unknown User', 
            "data" => $patients
        ]);
    }

    public function show() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            Response::json(['error' => 'Patient ID is required'], 400);
            return;
        }
        $patientModel = new Patient();
        $patient = $patientModel->findById($id);
        if ($patient) {
            Response::json($patient);
        } else {
            Response::json(['error' => 'Patient not found'], 404);
        }
    }

    public function create() {
        $data = $GLOBALS['request_data'];
        $required_fields = ['name', 'age', 'gender', 'contact', 'disease', 'address'];
        
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                Response::json(['error' => ucfirst($field) . " is required"], 400);
                return;
            }
        }

        if (!is_numeric($data['age']) || $data['age'] <= 0) {
            Response::json(['error' => 'Age must be greater than 0'], 400);
            return;
        }

        if (!preg_match('/^[0-9]{10}$/', $data['contact'])) {
            Response::json(['error' => 'Contact must be 10 digits'], 400);
            return;
        }

        $patient = new Patient();
        $success = $patient->create($data['name'], $data['age'], $data['gender'], $data['contact'], $data['disease'], $data['address']);
        
        if ($success) {
            Response::json(['message' => 'Patient added'], 201);
        } else {
            Response::json(['error' => 'Failed to add'], 500);
        }
    }

    public function update() {
        $id = $_GET['id'] ?? null;
        $data = $GLOBALS['request_data'];

        if (!$id) {
            Response::json(['error' => 'ID missing'], 400);
            return;
        }

        
        $required = ['name', 'age', 'gender', 'contact', 'disease'];
        foreach ($required as $f) {
            if (empty($data[$f])) {
                Response::json(['error' => ucfirst($f) . " is required"], 400);
                return;
            }
        }

        $patientModel = new Patient();
      
$success = $patientModel->update(
    $id, 
    $data['name'], 
    $data['age'], 
    $data['gender'], 
    $data['disease'], 
    $data['contact'],
    $data['address'] 
);

        if ($success) {
            Response::json(['message' => 'Update successful']);
        } else {
            Response::json(['error' => 'Update failed'], 500);
        }
    }

    public function patch() {
        $id = $_GET['id'] ?? null;
        $data = $GLOBALS['request_data'];

        if (!$id || empty($data)) {
            Response::json(['error' => 'ID or data missing'], 400);
            return;
        }

     
        if (isset($data['contact']) && !preg_match('/^[0-9]{10}$/', $data['contact'])) {
            Response::json(['error' => 'Contact must be 10 digits'], 400);
            return;
        }

        if (isset($data['age']) && ($data['age'] <= 0)) {
            Response::json(['error' => 'Age must be greater than 0'], 400);
            return;
        }

        $patientModel = new Patient();
        $success = $patientModel->patchUpdate($id, $data);

        if ($success) {
            Response::json(['message' => 'Partial update successful']);
        } else {
            Response::json(['error' => 'Patch failed'], 500);
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        $patientModel = new Patient();
        if ($patientModel->delete($id)) {
            Response::json(['message' => 'Deleted']);
        } else {
            Response::json(['error' => 'Delete failed'], 500);
        }
    }
}