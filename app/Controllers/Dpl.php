<?php

namespace App\Controllers;

use App\Models\AdminModel;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\DplModel;

class Dpl extends BaseController
{
    use ResponseTrait;
    private $adminModel;

    public function __construct()
    {
        $this->db           = \Config\Database::connect();
        $this->dplModel     = new DplModel;
        $this->adminModel   = new AdminModel();
    }

    private function _userDataToken() {
        $header = $this->request->getHeader("Authorization");
        $user   = validateJWTFromRequest($header);
        
        return $user;   
    }

    public function getDataBimbingan()
    {
        $data = $this->_userDataToken();

        $unitPengaturan = $this->db->table("unit_pengaturan")->get()->getRowArray();
        $bimbingan = $this->dplModel->getDataBimbingan($data->uid, $unitPengaturan['tahun_akademik'], $unitPengaturan['semester']);
        $thn_akademik = 'TAHUN AKADEMIK '.$unitPengaturan['tahun_akademik'].'/'.$unitPengaturan['semester'].' - '.date("Y");

        return $this->response->setStatusCode(200)->setJSON([
            'status'            => true, 
            'bimbingan'         => $bimbingan, 
            'tahun_akademik'    => $thn_akademik,
            't_akademik'        => $this->adminModel->getDataTahunAkademik(),
        ]);

    }

    public function getMahasiswaDetail($id_pendaftar)
    {
        $this->_userDataToken();

        $data = $this->dplModel->getMahasiswaBimbinganDetail($id_pendaftar);

        return $this->response->setStatusCode(200)->setJSON(['status' => true, 'mahasiswa' => $data]);
    }

    public function getLogbookMhs($id_pendaftar)
    {
         $logbook = $this->dplModel->getDataLogbookMhs($id_pendaftar);
         return $this->response->setStatusCode(200)->setJSON(['status' => true, 'logbook' => $logbook]);
    }



    public function getLogbookMhsDetail($id_pendaftar)
    {
        $logbook  = $this->db->table("unit_logbook_mhs")->where("id_pendaftar", $id_pendaftar)->get()->getRowArray();
        $lampiran = $this->dplModel->getLampiranLogbook($logbook['id']);

        return $this->response->setStatusCode(200)->setJSON(['status' => true, 'logbookDetail' => $logbook, "lampiran" => $lampiran]);
    }

    public function readLogbook($id_logbook)
    {
        $updateStatus = $this->db->table("unit_logbook_mhs")->where("id", $id_logbook)->set("status", "verified")->update();

        if ($updateStatus) {
           return $this->response->setStatusCode(200)->setJSON(['status' => true, 'message' => "Logbook berhasil dikonfirmasi!"]);
        }

        return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => "Something wrong, please try again!"]);
    }


    public function getLaporanMhs($id_pendaftar)
    {
        $laporan = $this->dplModel->getLaporan($id_pendaftar);
        return $this->response->setStatusCode(200)->setJSON(['status' => true, 'laporan' => $laporan]);
    }

    public function getDataBimbinganNilai()
    {   
         $user = $this->_userDataToken();
         $unitPengaturan = $this->db->table("unit_pengaturan")->get()->getRowArray();

         $id_dosen = $user->uid;
 
        $bimbingan = $this->db->table("unit_bimbingan ub")
                        ->select("ub.*, p.nama_lengkap")
                        ->join("unit_pendaftar up", "up.id = ub.id_pendaftar", "left")
                        ->join("pengguna p", "p.nim = up.nim", "left")
                        ->where([
                            'ub.id_dosen'          => $id_dosen, 
                            'ub.tahun_akademik'    => $unitPengaturan['tahun_akademik'], 
                            'ub.semester'          => $unitPengaturan['semester']
                        ])->get()->getResultArray();

            return $this->response->setStatusCode(200)->setJSON(['status' => true, 'bimbingan' => $bimbingan]);


    }

    public function simpanNilaiResult()
    {
        $user = $this->_userDataToken();
        $unitPengaturan = $this->db->table("unit_pengaturan")->get()->getRowArray();

        $id_dosen = $user->uid;
        $where = ['tahun_akademik' => $unitPengaturan['tahun_akademik'], 'semester' => $unitPengaturan['semester'], 'id_dosen' => $id_dosen];
        $cekIssetDpl = $this->db->table("unit_bimbingan")->where($where)->get()->getRowArray();

        if (!$cekIssetDpl) {
             return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => "Anda tidak dapat mengakses menu ini!"]);
        }

        $tgl_sekarang = date("Y-m-d");

        // if ($unitPengaturan['tgl_penarikan'] < $tgl_sekarang) {
        //     return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => "Tidak dapat mengupdate nilai, Tahun akademik sudah berakhir!"]);
        // }
        
        $nilai = $this->request->getJSON()->nilai;

        foreach ($nilai as $key => $item) {

            $grade = _getGrade($item->n_pembekalan, $item->n_pelaksanaan, $item->n_laporan, $item->n_akhir);

            $this->db->table("unit_bimbingan")->where('id', $item->id)->set([
                'n_pembekalan'  => (int)$item->n_pembekalan,
                'n_pelaksanaan' => (int)$item->n_pelaksanaan,
                'n_laporan'     => (int)$item->n_laporan,
                'n_akhir'       => (int)$item->n_akhir,
                'grade'         => $grade,
                'updated_at'     => date('Y-m-d')
            ])->update();
        }

        return $this->response->setStatusCode(200)->setJSON(['status' => true, 'message' => "Nilai berhasil di simpan!"]);

    }

    public function getBimbingan($tahun_akademik, $semester)
    {
        
		$newTahunAkad 	    = str_replace("_","/", $tahun_akademik);
        $user               = $this->_userDataToken();
        $id_dosen           = $user->uid;

        $bimbingan          = $this->dplModel->getDataBimbingan($id_dosen, $newTahunAkad, $semester);
        $thn_akademik_text  = 'TAHUN AKADEMIK '.$newTahunAkad.'/'.$semester;

        return $this->response->setStatusCode(200)->setJSON([
            'status'                => true, 
            'bimbingan'             => $bimbingan, 
            'tahun_akademik_text'    => $thn_akademik_text,
        ]);

    }


}
