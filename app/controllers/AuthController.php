<?php

class AuthController {

    public function register() {
        $data = $GLOBALS['request_data'];

       
        if (empty($data['email']) || empty($data['password'])) {
            Response::json(["error" => "Email and password required"], 400);
            return;
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Response::json(["error" => "Invalid email format"], 400);
            return;
        }

      
        if (strlen($data['password']) < 8) {
            Response::json(["error" => "Password must be at least 8 characters long"], 400);
            return;
        }

        $userModel = new User();
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        try {
            $userModel->create($data['name'], $data['email'], $hashedPassword);
            Response::json(["message" => "User created successfully"], 201);
        } catch (Exception $e) {
            Response::json(["error" => "Email already exists"], 409);
        }
    }

    public function login() {
        $data = $GLOBALS['request_data'];
        
     
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Response::json(["error" => "Invalid email format"], 400);
            return;
        }

        $userModel = new User();
        $user = $userModel->findByEmail($data['email']);

        if (!$user || !password_verify($data['password'], $user['password'])) {
            Response::json(['error' => 'Invalid email or password'], 401);
            return;
        }

        $token = JWT::generate(['user_id' => $user['id'], 'email' => $user['email']]);
        Response::json(['token' => $token]);
    }
}