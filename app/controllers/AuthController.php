<?php
class AuthController {
    public function register() {
        $data = $GLOBALS['request_data'];
        if (empty($data['email']) || empty($data['password'])) {
            Response::json(["error" => "Email & password required"], 400);
            return;
        }
        $userModel = new User();
        $hashed = password_hash($data['password'], PASSWORD_DEFAULT);
        try {
            $userModel->create($data['name'], $data['email'], $hashed);
            Response::json(["message" => "User created"], 201);
        } catch (Exception $e) {
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
            'expires' => time() + (86400 * 30),
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
    // Extract the access token from the Authorization header
    $headers = getallheaders();
    $auth = $headers['Authorization'] ?? $headers['authorization'] ?? null;
    if (!$auth || !preg_match('/Bearer\s(\S+)/', $auth, $m)) {
        Response::json(["error" => "Access token required in Authorization header"], 401);
        return;
    }
    $accessToken = $m[1];

    // Validate signature and type (but NOT expiry)
    $userData = JWT::validateSignatureAndType($accessToken, "access");
    if (!$userData) {
        // Signature or type is invalid – do not refresh
        Response::json(["error" => "Invalid access token. Cannot refresh."], 401);
        return;
    }

    // Signature is valid, now check if it's expired
    if ($userData['exp'] > time()) {
        // Token is still valid (not expired) – no need to refresh
        Response::json(["error" => "Access token is still valid. No need to refresh."], 400);
        return;
    }

    // Token is valid (signature matches) and expired – proceed with refresh
    $refreshToken = $_COOKIE['refreshToken'] ?? null;
    if (!$refreshToken) {
        Response::json(['error' => 'Session expired. Please login again.'], 401);
        return;
    }

    $userModel = new User();
    $user = $userModel->findByRefreshToken($refreshToken);
    if (!$user) {
        Response::json(['error' => 'Invalid refresh token'], 403);
        return;
    }

    // Generate new tokens
    $newRefreshToken = JWT::generateRefreshToken(['user_id' => $user['id'], 'email' => $user['email']]);
    $rtSignature = explode('.', $newRefreshToken)[2];
    $newAccessToken = JWT::generateAccessToken([
        'user_id' => $user['id'],
        'email'   => $user['email'],
        'rt_sig'  => hash('sha256', $rtSignature)
    ]);

    $userModel->storeRefreshToken($user['id'], $newRefreshToken);

    setcookie("refreshToken", $newRefreshToken, [
        'expires' => time() + (86400 * 30),
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
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $userId = $GLOBALS['user']['user_id'] ?? $_SESSION['user_id'] ?? null;

        if ($userId) {
            $userModel = new User();
            $userModel->storeRefreshToken($userId, null);
        }

        setcookie("refreshToken", "", [
            'expires' => time() - 3600,
            'path' => "/",
            'httponly' => true,
            'secure' => false,
            'samesite' => 'Lax'
        ]);

        session_unset();
        session_destroy();

        Response::json(["message" => "Logged out successfully and DB cleared"]);
    }
}