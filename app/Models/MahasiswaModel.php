<?php

namespace App\Models;

use CodeIgniter\Model;

class MahasiswaModel extends Model {

	function getDataDetailMhs($id)
	{
		return $this->db->table("pengguna")
				->select("pengguna.nama_lengkap,  pengguna.nim, prodi.nama_prodi")
				->join("prodi", "prodi.id_prodi = pengguna.id_prodi", "left")
				->where("pengguna.id", $id)->get()->getRowArray();
	}

	function getMatakuliah($nim) {
		return $this->db->table("unit_matakuliah_mhs")->where("nim", $nim)->get()->getResultArray();
	}

	function cekMatakuliah($nim) {
		$data = $this->db->table("unit_matakuliah_mhs")->where("nim", $nim)->get()->getRowArray();

		if ($data) {
			$this->db->table("unit_matakuliah_mhs")->where("nim", $nim)->delete();
		}
	}

	function cekStatusPendaftaran($nim) {
		$data = $this->db->table("unit_pendaftar")->where("nim", $nim)->get()->getRowArray();

		if ($data || $data !== null) {
			return true;
		}else{
			return false;
		}
	}

	public function cekMatakuliahMhs($nim)
	{
		$data = $this->db->table("unit_matakuliah_mhs")->where("nim", $nim)->get()->getRowArray();

		if ($data || $data !== null) {
			return true;
		}else{
			return false;
		}
	}

	function getNilai($id_mhs) {
		return $this->db->table('unit_bimbingan ub')
				->select("ub.*, dosen.nama_dosen")
				->join("dosen", 'dosen.id_dosen = ub.id_dosen', "left")
				->where('id_pendaftar', $id_mhs)
				->get()->getRowArray();
	}

}
