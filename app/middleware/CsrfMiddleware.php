<?php
class CsrfMiddleware {
    public static function handle() {
    if (session_status() === PHP_SESSION_NONE) session_start();

   
    $currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (preg_match('/(login|register|refresh)/', $currentUri)) return;

    if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE', 'PATCH'])) {
        $headers = getallheaders();
        $receivedToken = $headers['X-CSRF-Token'] ?? null;
        
       
        $storedToken = $_SESSION['csrf_token'] ?? null;
        $sessionUserId = $_SESSION['user_id'] ?? null; 

        
        $tokenData = $GLOBALS['user'] ?? null; 
        
        if (!$receivedToken || !$storedToken || !hash_equals($storedToken, $receivedToken)) {
            Response::json(["error" => "CSRF Token mismatch"], 403);
            exit;
        }

        if ($tokenData && $sessionUserId !== $tokenData['user_id']) {
            Response::json(["error" => "CSRF Token does not belong to this user session"], 403);
            exit;
        }
    }
}
}