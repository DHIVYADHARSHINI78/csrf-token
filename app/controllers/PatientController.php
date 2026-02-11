<?php
class PatientController {
    public function index() {
        $patientModel = new Patient();
        $patients = $patientModel->getAll();
        Response::json(["data" => $patients]);
    }

    public function show() {
        $id = $_GET['id'] ?? null;

        if (!$id || !is_numeric($id)) {
            Response::json(['error' => 'Valid ID required'], 400);
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

        $patient = new Patient();
        $success = $patient->create($data['name'], $data['age'], $data['gender'], $data['contact'], $data['address']);

        if ($success) {
            Response::json(['message' => 'Patient added successfully'], 201);
        } else {
            Response::json(['error' => 'Failed to add patient'], 500);
        }
    }

    public function update() {
        $id = $_GET['id'] ?? null;
        $data = $GLOBALS['request_data'];

        if (!$id || !is_numeric($id)) {
            Response::json(['error' => 'Valid ID required'], 400);
            return;
        }

        $required = ['name', 'age', 'gender', 'contact', 'address'];
        foreach ($required as $f) {
            if (empty($data[$f])) {
                Response::json(['error' => ucfirst($f) . " is required"], 400);
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
        $success = $patientModel->update($id, $data['name'], $data['age'], $data['gender'], $data['contact'], $data['address']);

        if ($success) {
            Response::json(['message' => 'Patient updated successfully']);
        } else {
            Response::json(['error' => 'Update failed'], 500);
        }
    }

    public function patch() {
        $id = $_GET['id'] ?? null;
        $data = $GLOBALS['request_data'];

        if (!$id || !is_numeric($id) || empty($data)) {
            Response::json(['error' => 'Valid ID and data required'], 400);
            return;
        }

        if (isset($data['contact']) && !preg_match('/^[0-9]{10}$/', $data['contact'])) {
            Response::json(['error' => 'Phone must be exactly 10 digits'], 400);
            return;
        }

        if (isset($data['age']) && (!is_numeric($data['age']) || $data['age'] <= 0)) {
            Response::json(['error' => 'Age must be a number greater than 0'], 400);
            return;
        }

        $patientModel = new Patient();
        $success = $patientModel->patchUpdate($id, $data);

        if ($success) {
            Response::json(['message' => 'Patient partially updated successfully']);
        } else {
            Response::json(['error' => 'Patch update failed'], 500);
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;

        if (!$id || !is_numeric($id)) {
            Response::json(['error' => 'Valid ID required'], 400);
            return;
        }

        $patientModel = new Patient();
        if ($patientModel->delete($id)) {
            Response::json(['message' => 'Patient deleted successfully']);
        } else {
            Response::json(['error' => 'Delete failed'], 500);
        }
    }
}