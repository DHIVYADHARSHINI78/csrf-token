<?php
class AuthController {

    public function register() {
        $data = $GLOBALS['request_data'];
        if(empty($data['email']) || empty($data['password'])){
            Response::json(["error" => "Email & password required"], 400);
            return;
        }
        $userModel = new User();
        $hashed = password_hash($data['password'], PASSWORD_DEFAULT);
        try {
            $userModel->create($data['name'], $data['email'], $hashed);
            Response::json(["message" => "User created"], 201);
        } catch(Exception $e) {
            Response::json(["error" => "Email exists"], 409);
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

        $accessToken = JWT::generateAccessToken([
            'user_id' => $user['id'],
            'email' => $user['email']
        ]);

        $refreshToken = JWT::generateRefreshToken([
            'user_id' => $user['id'],
            'email' => $user['email']
        ]);

        
        $userModel->storeRefreshToken($user['id'], $refreshToken);

        setcookie("refreshToken", $refreshToken, time() + REFRESH_TOKEN_EXP, "/", "", false, true);

        Response::json([
            "access_token" => $accessToken,
            "access_expires_in" => ACCESS_TOKEN_EXP
        ]);
    }

    public function refresh() {
        $oldToken = $_COOKIE['refreshToken'] ?? null;
        if (!$oldToken) {
            Response::json(["error" => "No refresh token"], 401);
            return;
        }

        $userModel = new User(); 
        $user = $userModel->findByRefreshToken($oldToken);

        if (!$user) {
            Response::json(["error" => "Invalid refresh token"], 401);
            return;
        }

        $newAccessToken = JWT::generateAccessToken([
            'user_id' => $user['id'], 
            'email' => $user['email']
        ]);

       
        $newRefreshToken = JWT::generateRefreshToken([
            'user_id' => $user['id'],
            'email' => $user['email'] 
        ]);

  
        $userModel->storeRefreshToken($user['id'], $newRefreshToken);

       setcookie("refreshToken", $newRefreshToken, time() + REFRESH_TOKEN_EXP, "/", "", false, true);

        Response::json([
            "access_token" => $newAccessToken,
            "message" => "Token refreshed successfully"
        ]);
    }
}