<?php

namespace App\Models;

use CodeIgniter\Model;

class PendaftarModel extends Model
{
	protected $table      = 'unit_pendaftar';

	public function paginatePendaftar($smt, $tahunAkademik, int $perpage,  int $page)
	{
		$data = $this->select("unit_pendaftar.*, p.nama_lengkap, prodi.nama_prodi")
				->join("pengguna p", "p.nim = unit_pendaftar.nim", "left")
				->join("prodi", "prodi.id_prodi = unit_pendaftar.id_prodi", "left")
				->where(['tahun_akademik' => $tahunAkademik, "semester" => $smt])->paginate($perpage, '', $page);
		return $data;
	}

	public function getTotalData($smt, $tahunAkademik)
	{
		$data = $this->select("unit_pendaftar.*, p.nama_lengkap, prodi.nama_prodi")
				->join("pengguna p", "p.nim = unit_pendaftar.nim", "left")
				->join("prodi", "prodi.id_prodi = unit_pendaftar.id_prodi", "left")
				->where(['tahun_akademik' => $tahunAkademik, "semester" => $smt])->countAllResults();
		return $data;
	}


}