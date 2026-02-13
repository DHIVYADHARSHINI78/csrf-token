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
    if (session_status() === PHP_SESSION_NONE) session_start();

    $data = $GLOBALS['request_data'];
    $userModel = new User(); 
    $user = $userModel->findByEmail($data['email']);

    if (!$user || !password_verify($data['password'], $user['password'])) {
        Response::json(['error' => 'Invalid email or password'], 401);
        return;
    }

    $csrfToken = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $csrfToken; 

$_SESSION['user_id'] = $user['id']; 
    $refreshToken = JWT::generateRefreshToken(['user_id' => $user['id'], 'email' => $user['email']]);
    $rtSignature = explode('.', $refreshToken)[2]; 

    $accessToken = JWT::generateAccessToken([
        'user_id' => $user['id'],
        'email'   => $user['email'],
        'rt_sig'  => hash('sha256', $rtSignature)
    ]);

    $userModel->storeRefreshToken($user['id'], $refreshToken);
    
    setcookie("refreshToken", $refreshToken, [
        'expires' => time() + REFRESH_TOKEN_EXP,
        'path' => "/",
        'httponly' => true,
        'secure' => false, 
        'samesite' => 'Lax'
    ]);

    Response::json([
        "access_token" => $accessToken,
        "csrf_token"   => $csrfToken, 
        "message"      => "Login successful"
    ]);
}
public function refresh() {

    $headers = getallheaders();
    $auth = $headers['Authorization'] ?? $headers['authorization'] ?? null;

    if (!$auth || !preg_match('/Bearer\s(\S+)/', $auth, $m)) {
        Response::json(["error" => "Access token required"], 401);
        return;
    }

    $providedAccessToken = $m[1];

   
    $isStillValid = JWT::validate($providedAccessToken, "access");
    if ($isStillValid) {
        Response::json(["error" => "Access token is still valid. No need to refresh."], 403);
        return;
    }

    $oldRefreshToken = $_COOKIE['refreshToken'] ?? null;
    if (!$oldRefreshToken) {
        Response::json(["error" => "No session found. Please login."], 401);
        return;
    }

    $userData = JWT::validate($oldRefreshToken, "refresh");
    if (!$userData) {
        Response::json(["error" => "Session expired. Please login again."], 401);
        return;
    }

    
    $userModel = new User();
    $user = $userModel->findByRefreshToken($oldRefreshToken);

    if (!$user) {
        Response::json(["error" => "Invalid session or logged out from elsewhere"], 401);
        return;
    }

    $newRefreshToken = JWT::generateRefreshToken([
        'user_id' => $user['user_id'], 
        'email'   => $user['email']
    ]);

    $rtParts = explode('.', $newRefreshToken);
    $rtSignature = $rtParts[2];

    $newAccessToken = JWT::generateAccessToken([
        'user_id' => $user['user_id'],
        'email'   => $user['email'],
        'rt_sig'  => hash('sha256', $rtSignature)
    ]);

 
    $userModel->storeRefreshToken($user['user_id'], $newRefreshToken);
    
    setcookie("refreshToken", $newRefreshToken, [
        'expires' => time() + REFRESH_TOKEN_EXP,
        'path' => "/",
        'httponly' => true,
        'secure' => false,
        'samesite' => 'Lax'
    ]);

    Response::json([
        "access_token" => $newAccessToken,
        "message" => "Token refreshed successfully"
    ]);
}
}