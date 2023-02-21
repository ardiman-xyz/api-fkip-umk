<?php

namespace App\Services\Konseling\Mahasiswa;

use App\Models\PenggunaModel;
use Exception;
use Firebase\JWT\JWT;

class AuthService
{
   private PenggunaModel $penggunaModel;

   public function __construct(PenggunaModel $penggunaModel)
   {
      $this->penggunaModel = $penggunaModel;
   }

   public function postLogin(string $username, string $password)
   {
      if($username === "" OR null && $password === "" OR null) throw new Exception("Silahkan isi input!");

      $user = $this->penggunaModel->getDataByNIM($username);
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
         "success" => true,
         "error"  => null,
         "token"  => $token
      ];
   }
}