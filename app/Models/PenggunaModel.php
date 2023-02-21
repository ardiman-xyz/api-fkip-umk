<?php

namespace App\Models;

use CodeIgniter\Model;

class PenggunaModel extends Model
{
   protected $table         = 'pengguna';
    protected $allowedFields = ['id_prodi', 'nim', 'password', 'token', 'nama_lengkap', 'no_wa'];
   //  protected $returnType    = \App\Entities\User::class;
    protected $useTimestamps = false;

   public function getDataByNIM(string $nim): ?array
   {
      return $this->where("nim", $nim)->first();
   }
   
}