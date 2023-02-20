<?php

namespace App\Models;

use CodeIgniter\Model;

class DplModel extends Model
{
	function getDataBimbingan($id, $tahunAkademik, $semester) : array {
		return $this->db->table("unit_bimbingan ub")
				->select("up.id, up.nim, up.jenis_kegiatan, up.lokasi, p.nama_lengkap as nama_mahasiswa")
				->join("unit_pendaftar up", "up.id = ub.id_pendaftar", "left")
				->join("pengguna p", "p.nim = up.nim", "left")
				->where(["ub.id_dosen" => $id, 'ub.tahun_akademik' => $tahunAkademik, 'ub.semester' => $semester])
				->get()->getResultArray();
	}

	function getMahasiswaBimbinganDetail($id_pendaftar) {
		return $this->db->table("unit_pendaftar up")
				->select("up.id as id_pendaftar, p.nama_lengkap, p.nim, prodi.nama_prodi, up.lokasi, up.jenis_kepesertaan, up.no_hp, ub.grade, prodi.nama_prodi")
				->join("pengguna p", "p.nim = up.nim", "left")
				->join("prodi", "prodi.id_prodi = p.id_prodi", "left")
				->join("unit_bimbingan ub", "ub.id_pendaftar = up.id", "left")
				->where("up.id", $id_pendaftar)
				->get()->getRowArray();
	}

	function getDataLogbookMhs($id_pendaftar)
	{
		return $this->db->table("unit_logbook_mhs ulm")
					->select("ulm.mingguKe, ulm.nama_kegiatan, ulm.status, ulm.tgl_kegiatan")
					->where("id_pendaftar", $id_pendaftar)->get()->getResultArray();
	}

	function getLampiranLogbook($id_logbook) {
		return $this->db->table("unit_logbook_doc")->where("id_logbook", $id_logbook)->get()->getResultArray();
	}

	function getLaporan($id_pendaftar) {
		return $this->db->table("unit_pendaftar")
				->select("unit_pendaftar.laporan, unit_pendaftar.link_kegiatan_magang")
				->where("id", $id_pendaftar)->get()->getRowArray();
	}

}