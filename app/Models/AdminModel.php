<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminModel extends Model
{
	public function getDataTahunAkademik()
	{
		return $this->db->table("unit_pendaftar")
				->select("tahun_akademik")
				->groupBy("tahun_akademik")->get()->getResultArray();
	}

	public function getDataPendaftar($smt, $tahunAkademik)
	{
		$data = $this->db->table("unit_pendaftar up")
				->select("up.*, p.nama_lengkap, prodi.nama_prodi")
				->join("pengguna p", "p.nim = up.nim", "left")
				->join("prodi", "prodi.id_prodi = up.id_prodi", "left")
				->where(['tahun_akademik' => $tahunAkademik, "semester" => $smt])
				->get()->getResultArray();
		return $data;
	}

	public function getSearchByNameNim($params)
	{
		return $this->db->table("unit_pendaftar up")
				->select("up.*, p.nama_lengkap, prodi.nama_prodi")
				->join("pengguna p", "p.nim = up.nim", "left")
				->join("prodi", "prodi.id_prodi = up.id_prodi", "left")
				->like("up.nim", $params)->get()->getResultArray();
	}

	public function getDataMoreFilterPendaftar($prodi)
	{

		return $this->db->table("unit_pendaftar up")
				->select("up.*, p.nama_lengkap, prodi.nama_prodi")
				->join("pengguna p", "p.nim = up.nim", "left")
				->join("prodi", "prodi.id_prodi = up.id_prodi", "left")
				->where("up.id_prodi", $prodi)->get()->getResultArray();

	}

	public function ambilsemuaData()
	{
		return $this->db->table("unit_pendaftar");
	}

	public function getDataDosen()
	{
		return $this->db->table("dosen")
				->select("dosen.id_dosen, dosen.nama_dosen")
				->get()->getResultArray();
	}

	public function getDataIdDpl($nama)
	{
		return $this->db->table("dosen")->where("nama_dosen", $nama)->get()->getRowArray()['id_dosen'];
	}

	public function cekDplBimbingan($id_dosen, $thn_akademik, $semester)
	{
		return $this->db->table("unit_bimbingan")
				->where(["id_dosen" => $id_dosen, "tahun_akademik" => $thn_akademik, "semester" => $semester])
				->get()->getRowArray();
	}

	public function cekPendaftarBimbingan($nim)
	{
		$id_pendaftar = $this->db->table("unit_pendaftar")->where("nim", $nim)->get()->getRowArray()['id'];
		$data =  $this->db->table("unit_bimbingan")
				->select("pengguna.nama_lengkap, dosen.nama_dosen")
				->join("pengguna", "pengguna.nim = ".$nim, "left")
				->join("dosen", "dosen.id_dosen = unit_bimbingan.id_dosen", "left")
				->where("unit_bimbingan.id_pendaftar", $id_pendaftar)
				->get()->getRowArray();
		return ['data' => $data, "id_pendaftar" => $id_pendaftar];
	}

	public function getDataBimbingan($thn_akademik, $semester)
	{
		return $this->db->table("unit_bimbingan ub")
				->select("d.nama_dosen, ub.id_dosen")
				->join("dosen d", "d.id_dosen = ub.id_dosen", "left")
				->where(["tahun_akademik" => $thn_akademik, "semester" => $semester])
				->groupBy("ub.id_dosen")->get()->getResultArray();
	}

	public function getJumlahBimbingan($id_dosen, $thn_akademik, $semester)
	{
		$jmlBimbingan =  $this->db->table("unit_bimbingan")->where(["tahun_akademik" => $thn_akademik, "semester" => $semester, "id_dosen" => $id_dosen])->countAllResults();

		$sekolah =  $this->db->table("unit_bimbingan ub")
					->select("up.lokasi, up.jenis_kegiatan")
					->join("unit_pendaftar up", "up.id = ub.id_pendaftar", "left")
					->where(["ub.tahun_akademik" => $thn_akademik, "ub.semester" => $semester, "ub.id_dosen" => $id_dosen])
					->groupBy("up.lokasi")
					->get()->getResultArray();

		return ['jmlBimbingan' => $jmlBimbingan, "sekolah" => $sekolah];
	}

	public function getDosenName($id_dosen)
	{
		return $this->db->table("dosen")->where("id_dosen", $id_dosen)->get()->getRowArray()['nama_dosen'];
	}

	public function getDataMahasiswaByNim($nim)
	{
		return $this->db->table("pengguna")
				->select("pengguna.nim, pengguna.nama_lengkap, up.id as status_daftar")
				->join("unit_pendaftar up", "pengguna.nim = up.nim", "left")
				->where("pengguna.nim", $nim)
				->get()->getRowArray();
	}

	public function getDataBimbinganDetail($smt, $tahun_akademik, $id_dosen)
	{
		$data = $this->db->table("unit_bimbingan ub")
				->select("ub.id_pendaftar, ub.id as id_bimbingan, dosen.nama_dosen, pengguna.nama_lengkap as nama_mahasiswa, up.nim, up.lokasi, up.jenis_kegiatan")
				->join("unit_pendaftar up", "up.id = ub.id_pendaftar", "left")
				->join("pengguna", "pengguna.nim = up.nim", "left")
				->join("dosen", "dosen.id_dosen = ub.id_dosen", "left")
				->where(['ub.tahun_akademik' => $tahun_akademik, "ub.semester" => $smt, "ub.id_dosen" => $id_dosen])
				->get()->getResultArray();
		return $data;
	}

	function getDataPengaturanUnit() {
		return $this->db->table("unit_pengaturan")->get()->getRowArray();
	}

	function getDataSekolah() {
		return $this->db->table("plp_magang_sekolah")->orderBy('id', 'desc')->get()->getResultArray();
	}

	function cekStatusBimbinganById($id)
	{
		$response = false;
		$bimbingan = $this->db->table('unit_bimbingan')->where("id_pendaftar", $id)->get()->getRowArray();
		
		if($bimbingan) {
			$data = $this->db->table('unit_pendaftar up')
					->select("p.nim, p.nama_lengkap")
					->join('pengguna p', 'p.nim = up.nim', 'left')
					->where('up.id', $id)
					->get()->getRowArray();
			$response = $data;
		}

		return $response;

	}

	function simpanBimbinganUpdate($id, $id_dosen, $semester, $tahun_akademik) {

		$response = false;
		$data = [
				"tahun_akademik" 	=> $tahun_akademik,
				"semester"			=> $semester,
				"id_pendaftar"		=> $id,
				"id_dosen"			=> $id_dosen,
				"n_pembekalan"		=> 0,
				"n_pelaksanaan"		=> 0,
				"n_laporan"			=> 0,
				"n_akhir"			=> 0,
				"grade"				=> "E",
				"updated_at"		=> date("Y-m-d")
			];
		$insert = $this->db->table("unit_bimbingan")->insert($data);

		if($insert) {
			$dt = $this->db->table('unit_pendaftar up')
					->select('up.jenis_kegiatan, up.lokasi, p.nim, p.nama_lengkap as nama_mahasiswa')
					->join('pengguna p', 'p.nim = up.nim', "left")
					->where('up.id', $id)
					->get()->getRowArray();
			$response = $dt;
		}

		return $response;
	}

}