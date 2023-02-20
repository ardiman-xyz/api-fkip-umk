<?php

namespace App\Models;

use CodeIgniter\Model;

class MasterModel extends Model
{
   public function get_data($username)
   {
       $data = $this->db->table('pengguna')
                ->select("pengguna.*, prodi.nama_prodi")
                ->join("prodi", "prodi.id_prodi = pengguna.id_prodi", "left")
                ->where("nim", $username)
                ->get()->getRowArray();
        return $data;
   }

   public function getData($table, $where = null, $pk = null)
   {
        if ($where === null) {
            $data = $this->db->table($table)->get()->getResultArray();
        }else{
            $data = $this->db->table($table)->where($pk, $where)->get()->getRowArray();
        }

    return $data;
   }

   public function getDataSekolah()
   {
       return $this->db->table("plp_magang_sekolah")->select('plp_magang_sekolah.nama_sekolah, plp_magang_sekolah.id')->get()->getResultArray();
   }

   public function getInfoSekolah($q)
   {
       return $this->db->table("plp_magang_sekolah")->where('id', $q)->get()->getRowArray();
   }

   public function getDataPendaftar($nim)
   {
       $data = $this->db->table("unit_pendaftar up")
                ->select("up.*, p.nama_lengkap as nama, prodi.nama_prodi")
                ->join("pengguna p", "p.nim = up.nim", "left")
                ->join("prodi", "prodi.id_prodi = up.id_prodi", "left")
                ->where("up.nim", $nim)
                ->get()->getRowArray();

        return $data;
   }

   public function getMahasiswaProdi($nim)
   {
       return $this->db->table("pengguna")->where("nim", $nim)->get()->getRowArray()['id_prodi'];
   }
}