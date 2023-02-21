<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
   protected $table         = 'user';
   protected $allowedFields = ['username', 'password', 'level', 'nama_user', 'jenis_kelamin'];
  //  protected $returnType    = \App\Entities\User::class;
   protected $useTimestamps = false;

   public function getDataByUsername(string $username): ?array
   {
      return $this->where("username", $username)->first();
   }

}