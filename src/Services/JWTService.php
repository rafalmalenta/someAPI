<?php


namespace App\Services;


use Ahc\Jwt\JWT;

class JWTService
{
    private JWT $JWT;

    public function __construct()
    {
       $this->JWT = new JWT("secret",'HS256', 6000);
    }
    public function generateToken($username):string
    {
        return $this->JWT->encode([
            'username'=>$username
        ]);
    }
    public function getToken($token):string
    {
        return $this->JWT->decode($token,true)["username"];
    }
    public function verifyToken($token):bool
    {
        try{
            $this->JWT->decode($token,true);
            return true;
        }
        catch (\Exception $e){
            return false;
        }
    }
    public function addTime($seconds)
    {
        $this->JWT->setTestTimestamp(time()+$seconds);
    }
}