<?php
class AuthMiddleware {

    public static function handle() {
        $headers=getallheaders();
        $auth=$headers['Authorization']??$headers['authorization']??null;

        if(!$auth || !preg_match('/Bearer\s(\S+)/',$auth,$m)){
            Response::json(["error"=>"Token missing"],401);
            exit;
        }

        $token=$m[1];
        $user=JWT::validate($token,"access");

        if(!$user){
            Response::json(["error"=>"Access token expired or invalid"],401);
            exit;
        }

        return $user;
    }
}