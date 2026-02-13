<?php
class AuthMiddleware {

   public static function handle() {
    $headers = getallheaders();
    $auth = $headers['Authorization'] ?? $headers['authorization'] ?? null;

    if (!$auth || !preg_match('/Bearer\s(\S+)/', $auth, $m)) {
        Response::json(["error" => "Token missing"], 401);
    }

    $token = $m[1];
    $userData = JWT::validate($token, "access");

    if (!$userData) {
        Response::json(["error" => "Access token expired or invalid"], 401);
    }


    $userModel = new User();
    $latestDBHash = $userModel->getLatestTokenHash($userData['user_id']);
    $cookieRT = $_COOKIE['refreshToken'] ?? null;

    
    if (!$cookieRT || !password_verify($cookieRT, $latestDBHash)) {
        Response::json(["error" => "Session Terminated: Another user logged in."], 401);
    }

   
    $currentCookieSig = explode('.', $cookieRT)[2];
    if ($userData['rt_sig'] !== hash('sha256', $currentCookieSig)) {
        Response::json(["error" => "Invalid Access Token Session."], 401);
    }

    return $userData;
}
}