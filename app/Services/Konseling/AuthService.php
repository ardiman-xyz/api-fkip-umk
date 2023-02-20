<?php

namespace App\Services\Konseling;

use App\Models\UserModel;
use Exception;
use Firebase\JWT\JWT;

class AuthService
{
   private UserModel $userModel;

   public function __construct(UserModel $userModel)
   {
      $this->userModel = $userModel;
   }

   public function postLogin(string $username, string $password)
   {
      if($username === "" OR null && $password === "" OR null) throw new Exception("Silahkan isi input!");

      $user = $this->userModel->getDataByNIM($username);
      if($user === null) throw new Exception("Data tidak ditemukan");

      $passwordEncrypt = sha1($password);

      if($passwordEncrypt !== $user['password']) throw new Exception("Kombinasi Username dan password salah!");
      $key = getenv('TOKEN_SECRET');
      $payload = [
         "id"     => $user['id'],
         "nim"    => $user['nim'],
         "nama"   => $user['nama_lengkap']
     ];

     $token = JWT::encode($payload, $key, 'HS256');

      return [
         "status" => true,
         "token"  => $token
      ];
   }
}