<?php

namespace App\Services\Konseling\Admin;

use App\Models\UserModel;
use Exception;
use Firebase\JWT\JWT;

class AuthService
{
   private UserModel $userModel;

   public function __construct(userModel $userModel)
   {
      $this->userModel = $userModel;
   }

   public function postLogin(string $username, string $password)
   {
      if($username === "" OR null && $password === "" OR null) throw new Exception("Silahkan isi input!");

      $user = $this->userModel->getDataByUsername($username);
      if($user === null) throw new Exception("User tidak ditemukan!");

      $passwordEncrypt = sha1($password);
      if($passwordEncrypt !== $user['password']) throw new Exception("Kombinasi Username dan password salah!");
      
      $key = getenv('TOKEN_SECRET');
      $payload = [
         "id"     => $user['id_user'],
         "nama"   => $user['nama_user']
     ];
     $token = JWT::encode($payload, $key, 'HS256');

     return [
        "success" => true,
        "error"  => null,
        "token"  => $token
     ];

   }
}