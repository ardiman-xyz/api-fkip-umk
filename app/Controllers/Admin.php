<?php

namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\AdminModel;

class Admin extends BaseController
{
	use ResponseTrait;

	public function __construct()
    {
        $this->db           = \Config\Database::connect();
        $this->adminModel  = new AdminModel();
    }

    private function _userDataToken() {
        $header = $this->request->getHeader("Authorization");
        $user   = validateJWTFromRequest($header);
        
        return $user;   
    }

	public function getTahunAkademik()
	{
		$data = $this->adminModel->getDataTahunAkademik();

		return $this->response->setStatusCode(200)->setJSON($data);
	}

	public function getDataPendaftar($perpage, $page)
	{

		$semester = $this->request->getJSON()->semester;
		$tahunAkademik = $this->request->getJSON()->tahun_akademik;

		$newTahunAkad = str_replace("+","/",$tahunAkademik);

		$pendaftarModel = new \App\Models\PendaftarModel();

		 $data = [
            'users' => $pendaftarModel->paginatePendaftar($semester, $newTahunAkad, $perpage, $page),
            "total" => $pendaftarModel->getTotalData($semester, $newTahunAkad)
        ];

        return $this->response->setStatusCode(200)->setJSON($data);

	}

	public function getDataSearchPendaftar()
	{
		$searchParams = $this->request->getJSON()->search;

		$data = $this->adminModel->getSearchByNameNim($searchParams);

		return $this->response->setStatusCode(200)->setJSON($data);
	}

	public function getDatacenter()
	{
		$data['prodi'] = $this->db->table('prodi')->get()->getResultArray();
		$data['kegiatan'] = $this->db->table('unit_kegiatan')->get()->getResultArray();
		$data['lokasi'] = $this->db->table('plp_magang_sekolah')->get()->getResultArray();

		return $this->response->setStatusCode(200)->setJSON($data);
	}

	public function getDataFilterpendaftar()
	{
		$prodi 		= $this->request->getJSON()->filter->prodi;
		$kegiatan 	= $this->request->getJSON()->filter->kegiatan;

		$data = $this->adminModel->getDataMoreFilterPendaftar($prodi);
		return $this->response->setStatusCode(200)->setJSON($data);
	}

	public function dataPeserta($perpage, $page)
	{
		$pendaftarModel = new \App\Models\PendaftarModel();
		$pager = \Config\Services::pager();

		$semester = "Ganjil";
		$tahunAkademik = "2020/2021";

		 $data = [
            'users' => $pendaftarModel->paginatePendaftar($semester, $tahunAkademik, $perpage, $page),
            "total" => $pendaftarModel->getTotalData($semester, $newTahunAkad)
        ];

        return $this->response->setStatusCode(200)->setJSON($data);

	}

	public function getDataDosen()
	{
		$dosen = $this->adminModel->getDataDosen();

		return $this->response->setStatusCode(200)->setJSON($dosen);
	}

	public function getDataMahasiswa()
	{	
		$semester = $this->request->getJSON()->semester;
		$tahunAkademik = $this->request->getJSON()->tahun_akademik;

		$newTahunAkad = str_replace("+","/",$tahunAkademik);

		$mahasiswa = $this->db->table("unit_pendaftar up")
				  ->select("up.nim, up.id as id_pendaftar, p.nama_lengkap")
				  ->join("pengguna p", "p.nim = up.nim", "left")
				  ->where(["tahun_akademik" => $newTahunAkad, "semester" => $semester])
				  ->get()->getResultArray();

		return $this->response->setStatusCode(200)->setJSON($mahasiswa);
	}

	public function simpanDataBimbingan()
	{
		$tahun_akademik = $this->request->getJSON()->tahun_akademik;

		$newTahunAkad 	= str_replace("+","/", $tahun_akademik);
		$semester 		= $this->request->getJSON()->semester;
		$dpl 			= $this->request->getJSON()->dpl;
		$mhsBimbingan 	= $this->request->getJSON()->mhsBimbingan;

		$idDpl = $this->adminModel->getDataIdDpl($dpl);

		$cekDpl = $this->adminModel->cekDplBimbingan($idDpl, $newTahunAkad, $semester);


		if (!empty($cekDpl)) {
			return $this->response->setStatusCode(409)->setBody("data sudah ada!");
		}else{

			$pesan = [];

			foreach($mhsBimbingan as $key => $nim) {
				$cekPendaftar = $this->adminModel->cekPendaftarBimbingan($nim);
				if ($cekPendaftar['data']) {
					
					array_push($pesan, "".$cekPendaftar['data']['nama_lengkap']. " sudah melakukan bimbingan ke DPL ".$cekPendaftar['data']['nama_dosen']); 
					continue;
				}

				$data = [
					"tahun_akademik" 	=> $newTahunAkad,
					"semester"			=> $semester,
					"id_pendaftar"		=> $cekPendaftar['id_pendaftar'],
					"id_dosen"			=> $idDpl,
					"n_pembekalan"		=> 0,
					"n_pelaksanaan"		=> 0,
					"n_laporan"			=> 0,
					"n_akhir"			=> 0,
					"grade"				=> "E",
					"date_created"		=> date("Y-m-d")
				];

				$this->db->table("unit_bimbingan")->insert($data);
			}

			$data = [
				"message" => "Data bimbingan berhasil di simpan!",
				"errors" => $pesan,
			];
			return $this->response->setStatusCode(201)->setJSON($data);

		}

	}

	public function getDataBimbingan()
	{
		$tahun_akademik = $this->request->getJSON()->tahun_akademik;

		$newTahunAkad 	= str_replace("+","/", $tahun_akademik);
		$semester 		= $this->request->getJSON()->semester;

		$data = $this->adminModel->getDataBimbingan($newTahunAkad, $semester);

		foreach ($data as $key => $dosen) {
			$dataInfo = $this->adminModel->getJumlahBimbingan($dosen['id_dosen'], $newTahunAkad, $semester);
			$data[$key] += ['info2' => $dataInfo];
		}

		return $this->response->setStatusCode(200)->setJSON($data);

	}

	public function deleteBimbingan()
	{
		$id_dosen = $this->request->getJSON()->id_dosen;

		$nama_dosen = $this->adminModel->getDosenName($id_dosen);

		$tahun_akademik = $this->request->getJSON()->tahunAkademik;
		$newTahunAkad 	= str_replace("+","/", $tahun_akademik);
		$semester 		= $this->request->getJSON()->semester;

		$where = ["id_dosen" => $id_dosen, "tahun_akademik" => $newTahunAkad, "semester" => $semester];

		$this->db->table("unit_bimbingan")->where($where)->delete();

		return $this->response->setStatusCode(200)->setJSON(["message" => "Data bimbingan ". $nama_dosen ."  berhasil di hapus!"]);

	}

	public function getMahsiswaByNim()
	{
		$nim = $this->request->getJSON()->nim;

		$data = $this->adminModel->getDataMahasiswaByNim($nim);

		return $this->response->setStatusCode(200)->setJSON($data);
	}

	private function _strTahunAkademik($tahun_akademik)
	{
		$newTahunAkad 	= str_replace("+","/", $tahun_akademik);
		return $newTahunAkad;
	}

	public function getDataBimbinganDetail()
	{
		$semester 		= $this->request->getJSON()->semester;
		$tahun_akademik = $this->_strTahunAkademik($this->request->getJSON()->tahun_akademik);
		$id_dosen		= $this->request->getJSON()->id_dosen;


		$dataBimbingan = $this->adminModel->getDataBimbinganDetail($semester, $tahun_akademik, $id_dosen);

		return $this->response->setStatusCode(200)->setJSON($dataBimbingan);
	}

	public function getDataPengaturan()
	{
		$user = $this->_userDataToken();
		
		if ($user->role !== 'admin') return $this->response->setStatusCode(405)->setJSON(['status' => false, 'message' => "Anda tidak punya akses!"]); 

		$dataPengaturan = $this->adminModel->getDataPengaturanUnit();

		return $this->response->setStatusCode(201)->setJSON(['status' => true, 'pengaturan' => $dataPengaturan]);
	}

	public function simpanPengaturan()
	{
		$req = $this->request->getJson()->pengaturan;
		$data = [
			'tahun' 			=> date('Y'),
			'tahun_akademik' 	=> $req->tahun_akademik,
			'semester'			=> $req->semester,
			'tgl_mulai'			=> $req->tgl_mulai,
			'tgl_berakhir'		=> $req->tgl_berakhir,
			'tgl_pembekalan'	=> $req->tgl_pembekalan,
			'tgl_penarikan'		=> $req->tgl_penarikan,
			'ket'				=> $req->ket,
			'updated_at'		=> date('Y-m-d')
		];

		$update = $this->db->table('unit_pengaturan')->where('id', $req->id)->update($data);

		if(!$update) return $this->response->setStatusCode(401)->setJSON('message', 'Gagal!, data gagal di update!');

		return $this->response->setStatusCode(201)->setJSON(['status' => true, 'message' => "Data berhasil di update!"]);
	}

	public function getDataSekolah() 
	{
		$this->_userDataToken();

		$data = $this->adminModel->getDataSekolah();

		return $this->response->setStatusCode(200)->setJSON(['sekolah' => $data]);
	}

	public function simpanSekolah()
	{
		$data = $this->request->getJson()->form;

		$cekIsset = $this->db->table('plp_magang_sekolah')->where('nama_sekolah', $data->nama_sekolah)->get()->getRowArray();

		if($cekIsset) return $this->response->setStatusCode(401)->setJSON(['message' => $data->nama_sekolah." sudah ada!"]);

		$form = [
			'nama_sekolah' 	=> $data->nama_sekolah,
			'alamat' 		=> $data->alamat,
			'kuota'			=> (int)$data->kuota,
			'status'		=> $data->status,
		];

		$insert = $this->db->table('plp_magang_sekolah')->insert($form);

		if(!$insert) return $this->response->setStatusCode(401)->setJSON(['message' => "Terjadi kesalahan, silahkan coba lagi!"]);

		return $this->response->setStatusCode(200)->setJSON(['data' => $form, 'message' => 'Data berhasil di simpan!']);
	}

	public function deleteSekolah($id)
	{
		if($this->request->getMethod() !== 'delete') return $this->response->setStatusCode(405)->setJSON(['message' => "Request anda tidak didukung!"]);

		$delete = $this->db->table('plp_magang_sekolah')->where('id', $id)->delete();

		if(!$delete) return $this->response->setStatusCode(401)->setJSON(['message' => "Something wrong, please try again!"]);

		return $this->response->setStatusCode(200)->setJSON(['message' => 'Data berhasil di hapus!']);
	}

	public function addProgram()
	{
		$this->_userDataToken();


		if($this->request->getMethod() !== 'post') return $this->response->setStatusCode(405)->setJSON(['message' => "Request anda tidak didukung!"]);

		$gambar = $this->request->getFile('file');

		if($gambar === null || $gambar === '') return $this->response->setStatusCode(405)->setJSON(['message' => "Gambar harus di isi!"]);

		$path = FCPATH."assets/upload/gambar";

       // surat izin penelitian
        $newRandomName = $gambar->getRandomName();
        $gambar->move($path, $newRandomName);

        $data = [
        	'nama_kegiatan' => $this->request->getPost('nama'),
        	'slug'			=> slugify($this->request->getPost('nama')),
        	'background'	=> $newRandomName,
        	'deskripsi'		=> $this->request->getPost('deskripsi'),
        	'aktif'			=> 'Y',
        ];

        $insert = $this->db->table("unit_kegiatan")->insert($data);
        if(!$insert) return $this->response->setStatusCode(401)->setJSON(['message' => "Something wrong, please try again!"]);


        return $this->response->setStatusCode(201)->setJSON(['message' => 'Data program berhasil di simpan!']);
		
	}

	public function getDataPrograms()
	{
		$this->_userDataToken();

		if($this->request->getMethod() !== 'get') return $this->response->setStatusCode(405)->setJSON(['message' => 'Method request tidak di dukung']);

		$data = $this->db->table("unit_kegiatan")->get()->getResultArray();

		return $this->response->setStatusCode(200)->setJSON(['status' => true, 'programs' => $data]);
	}

	public function deleteProgram($id)
	{
		$this->_userDataToken();
		if($this->request->getMethod() !== 'delete') return $this->response->setStatusCode(405)->setJSON(['message' => 'Method request tidak di dukung']);

		$dataProgram = $this->db->table('unit_kegiatan')->where('id', $id)->get()->getRowArray();

		if($dataProgram['background'] !== '' || $dataProgram['background'] !== null) {
			$path = FCPATH."assets/upload/gambar";
			unlink($path.'/'.$dataProgram['background']);
		}

		$delete = $this->db->table('unit_kegiatan')->where('id', $id)->delete();

		if(!$delete) return $this->response->setStatusCode(401)->setJSON(['message' => 'Something wrong, please try again']);

		return $this->response->setStatusCode(200)->setJSON(['status' => true, 'message' => "Program berhasil di hapus!"]);

	}

	public function getInfoPendaftar($id)
	{
		$this->_userDataToken();
		if($this->request->getMethod() !== 'get') return $this->response->setStatusCode(405)->setJSON(['message' => 'Method request tidak di dukung']);

		$pendaftar 	= $this->db->table('unit_pendaftar')->where('id', $id)->get()->getRowArray();
		$lokasi 	= $this->db->table('plp_magang_sekolah')->get()->getResultArray();
		$kegiatan 	= $this->db->table('unit_kegiatan')->get()->getResultArray();

		return $this->response->setStatusCode(200)->setJSON([
				'status' 	=> true, 
				'pendaftar' => $pendaftar,
				'lokasi'	=> $lokasi,
				'kegiatan'	=> $kegiatan		
			]);
	}

	public function updatePendaftar()
	{
		$this->_userDataToken();
		if($this->request->getMethod() !== 'post') return $this->response->setStatusCode(405)->setJSON(['message' => 'Method request tidak di dukung']);

		$req = $this->request->getJson();

		$cekSesi = $this->db->table('unit_pengaturan')->get()->getRowArray();

		$data = [
			'jenis_kegiatan' 	=> $req->jenis_kegiatan,
			'jenis_kepesertaan'	=> $req->jenis_kepesertaan,
			'lokasi'			=> $req->lokasi,
			'no_hp'				=> $req->no_hp,
			'ukuran_baju'		=> $req->ukuran_baju,
			'tgl_bayar'			=> $req->tgl_bayar,
			'updated_at'		=> date('Y-m-d')
		];

		$update = $this->db->table('unit_pendaftar')->where('id', $req->id)->update($data);

		if(!$update) return $this->response->setStatusCode(401)->setJSON(['message' => 'Something wrong, please try again']);

		return $this->response->setStatusCode(201)->setJSON(['status' => true, 'message' => "Data berhasil di ubah!"]);

	}

	public function deletePeserta($id)
	{
		$this->_userDataToken();
		if($this->request->getMethod() !== 'delete') return $this->response->setStatusCode(405)->setJSON(['message' => 'Method request tidak di dukung']);

		$cekStatusBimbingan = $this->db->table('unit_bimbingan')->where('id_pendaftar', $id)->get()->getRowArray();

		if($cekStatusBimbingan) return $this->response->setStatusCode(401)->setJSON(['message' => 'Pendaftar dalam bimbingan, silahkan menghapus pendaftar dalam bimbingan!']);

		$delete = $this->db->table('unit_pendaftar')->where('id', $id)->delete();

		if (!$delete) return $this->response->setStatusCode(401)->setJSON(['message' => 'Something wrong, please try again']);

		return $this->response->setStatusCode(201)->setJSON(['status' => true, 'message' => "Data berhasil di hapus!"]);

	}

	public function updateBimbingan()
	{

		$id_dosen 			= $this->request->getJson()->id_dosen;
		$tahun_akademik 	= $this->request->getJson()->tahun_akademik;
		$semester 			= $this->request->getJson()->semester;
		$ids 				= $this->request->getJson()->ids;

		$newTahunAkad 	= str_replace("+","/", $tahun_akademik);

		$errors 		= [];
		$successfully 	= [];

		foreach ($ids as $key => $id) {
			$cekStatusBimbingan = $this->adminModel->cekStatusBimbinganById($id);

			if($cekStatusBimbingan) {
				$errors[] = 'Mahasiswa '.$cekStatusBimbingan['nim'].'/'.$cekStatusBimbingan['nama_lengkap'].' sudah melakukan bimbingan';
				continue;
			}

			$inserted = $this->adminModel->simpanBimbinganUpdate($id, $id_dosen, $semester, $newTahunAkad);

			if(!$inserted) {
				$errors[] = 'ID '.$id.' gagal di simpan!';
				continue;
			}

			$successfully[] = $inserted;
		}

		return $this->response->setStatusCode(201)->setJSON([
				'status' 		=> true, 
				'message' 		=> "Data berhasil di simpan!", 
				'errors' 		=> $errors,
				'successfully'	=> $successfully
			]);

	}
	
	public function deleteBimbinganDpl($id)
	{
		$this->_userDataToken();
		if($this->request->getMethod() !== 'delete') return $this->response->setStatusCode(405)->setJSON(['message' => 'Method request tidak di dukung']);

		$deleted = $this->db->table("unit_bimbingan")->where('id', $id)->delete();

		if(!$deleted) return $this->response->setStatusCode(401)->setJSON(['message' => 'Something wrong, please try again']);

		return $this->response->setStatusCode(201)->setJSON([
				'status' 		=> true, 
				'message' 		=> "Data bimbingan berhasil dihapus!", 
			]);
	}

}