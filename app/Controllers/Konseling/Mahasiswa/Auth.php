<?php

namespace App\Controllers\Konseling\Mahasiswa;

use App\Models\PenggunaModel;
use App\Services\Konseling\Mahasiswa\AuthService;
use CodeIgniter\RESTful\ResourceController;
use Exception;

class Auth extends ResourceController
{
   private AuthService $authService;

   public function __construct()
   {
      $this->authService = new AuthService(new PenggunaModel());
   }

   public function login()
   {
      $username = $this->request->getVar("username");
      $password = $this->request->getVar("password");

      try {
         $data = $this->authService->postLogin($username, $password);
         return $this->respond($data, 200, "ok");

      } catch (Exception $e) {
         return $this->fail($e->getMessage(), 400);
      }

   }
}