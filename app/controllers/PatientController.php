<?php
class PatientController {
    

    public function index() {
        $userId = $GLOBALS['user']['user_id'];
        $patientModel = new Patient();
        $patients = $patientModel->getAll($userId);
        Response::json(["data" => $patients]);
    }

    public function show() {
        $id = $_GET['id'] ?? null;
        $userId = $GLOBALS['user']['user_id'];

        if (!$id || !is_numeric($id)) {
            Response::json(['error' => 'Valid ID required'], 400);
            return;
        }

        $patientModel = new Patient();
        $patient = $patientModel->findById($id, $userId);

        if ($patient) {
            Response::json($patient);
        } else {
         
            Response::json(['error' => '403 Forbidden: Access Denied'], 403);
        }
    }

    public function create() {
        $data = $GLOBALS['request_data'];
        $userId = $GLOBALS['user']['user_id'];
        $required_fields = ['name', 'age', 'gender', 'contact', 'address'];

        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                Response::json(['error' => ucfirst($field) . " is required"], 400);
                return;
            }
        }

        if (!is_numeric($data['age']) || $data['age'] <= 0) {
            Response::json(['error' => 'Age must be a number greater than 0'], 400);
            return;
        }

        if (!preg_match('/^[0-9]{10}$/', $data['contact'])) {
            Response::json(['error' => 'Phone must be exactly 10 digits'], 400);
            return;
        }

        $patientModel = new Patient();
        $success = $patientModel->create($data['name'], $data['age'], $data['gender'], $data['contact'], $data['address'], $userId);

        if ($success) {
            Response::json(['message' => 'Patient added successfully'], 201);
        } else {
            Response::json(['error' => 'Failed to add patient'], 500);
        }
    }

    public function update() {
        $id = $_GET['id'] ?? null;
        $userId = $GLOBALS['user']['user_id'];
        $data = $GLOBALS['request_data'];

        if (!$id || !is_numeric($id)) {
            Response::json(['error' => 'Valid ID required'], 400);
            return;
        }

        $patientModel = new Patient();
        
        if (!$patientModel->findById($id, $userId)) {
            Response::json(['error' => '403 Forbidden: You cannot update this patient'], 403);
            return;
        }

        $success = $patientModel->update($id, $data['name'], $data['age'], $data['gender'], $data['contact'], $data['address'], $userId);

        if ($success) {
            Response::json(['message' => 'Patient updated successfully']);
        } else {
            Response::json(['error' => 'Update failed'], 500);
        }
    }

   
    public function patch() {
        $id = $_GET['id'] ?? null;
        $userId = $GLOBALS['user']['user_id'];
        $data = $GLOBALS['request_data'];

        if (!$id || !is_numeric($id) || empty($data)) {
            Response::json(['error' => 'Valid ID and data required'], 400);
            return;
        }

        $patientModel = new Patient();
        if (!$patientModel->findById($id, $userId)) {
            Response::json(['error' => '403 Forbidden: You cannot update this patient'], 403);
            return;
        }

        $success = $patientModel->patchUpdate($id, $data, $userId);

        if ($success) {
            Response::json(['message' => 'Patient partially updated successfully']);
        } else {
            Response::json(['error' => 'Patch update failed'], 500);
        }
    }


    public function delete() {
        $id = $_GET['id'] ?? null;
        $userId = $GLOBALS['user']['user_id'];

        if (!$id || !is_numeric($id)) {
            Response::json(['error' => 'Valid ID required'], 400);
            return;
        }

        $patientModel = new Patient();
   
        if ($patientModel->delete($id, $userId)) {
            Response::json(['message' => 'Patient deleted successfully']);
        } else {
            Response::json(['error' => '403 Forbidden or Patient not found'], 403);
        }
    }
}