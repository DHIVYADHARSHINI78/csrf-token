<?php
class AuthController {

    public function register() {
        $data=$GLOBALS['request_data'];

        if(empty($data['email'])||empty($data['password'])){
            Response::json(["error"=>"Email & password required"],400);
            return;
        }

        $userModel=new User();
        $hashed=password_hash($data['password'],PASSWORD_DEFAULT);

        try{
            $userModel->create($data['name'],$data['email'],$hashed);
            Response::json(["message"=>"User created"],201);
        }catch(Exception $e){
            Response::json(["error"=>"Email exists"],409);
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

  
    setcookie(
        "refreshToken",                 
        $refreshToken,                   
        time() + REFRESH_TOKEN_EXP,     
        "/",                             
        "",                              
        false,  
        true    
    );

    Response::json([
        "access_token" => $accessToken,
        "access_expires_in" => ACCESS_TOKEN_EXP
    ]);
}


 public function refresh() {

    $refreshToken = $_COOKIE['refreshToken'] ?? null;

    if (!$refreshToken) {
        Response::json(["error"=>"No refresh token"],401);
        return;
    }

    $decoded = JWT::validate($refreshToken,"refresh");

    if(!$decoded){
        Response::json(["error"=>"Invalid refresh token"],401);
        return;
    }

    $payload=[
        "user_id"=>$decoded['user_id'],
        "email"=>$decoded['email']
    ];

    $newAccess = JWT::generateAccessToken($payload);

    Response::json([
        "access_token"=>$newAccess,
        "expires_in"=>ACCESS_TOKEN_EXP
    ]);
}

}
