<?php

namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\MasterModel;
use Config\Services;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Libraries\PdfGenerator;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;

use App\Models\MahasiswaModel;

class Mahasiswa extends BaseController
{
    use ResponseTrait;

    public function __construct()
    {
        $this->db               = \Config\Database::connect();
        $this->masterModel      = new MasterModel();
        $this->mahasiswaModel   = new MahasiswaModel;
    }

    private function _userDataToken() {
        $header = $this->request->getHeader("Authorization");
        $user   = validateJWTFromRequest($header);
        
        return $user;   
    }

    public function getInfo()
    {
        $header = $this->request->getHeader("Authorization");
        $user   = validateJWTFromRequest($header);

        $data = $this->db->table("pengguna")
                ->select("pengguna.nama_lengkap, prodi.nama_prodi")
                ->join("prodi", "prodi.id_prodi = pengguna.id_prodi", "left")
                ->where("id", $user->uid)->get()->getRowArray();

        return $this->response->setStatusCode(200)->setJSON(['status' => true, 'user' => $data]);

    }

    public function program()
    {   
        $header = $this->request->getHeader("Authorization");
        validateJWTFromRequest($header);

        $programs = $this->masterModel->getData("unit_kegiatan");

        return $this->respond(['status' => 200, "programs" => $programs]);
    }

    public function detailProgram()
    {
        $slug = $this->request->getJSON()->slug;

        $program = $this->masterModel->getData("unit_kegiatan", $slug, 'slug');
        return $this->respond(['status' => 200, "program" => $program]);
    }

    public function getDataSekolah()
    {
        $sekolah = $this->masterModel->getDataSekolah();
        return $this->respond(['status' => 200, "sekolah" => $sekolah]);
    }

    public function getInfoSekolah()
    {
        $query = (int) $this->request->getJSON()->search;
 
        $sekolah = $this->masterModel->getInfoSekolah($query);

        $pendaftar = $this->db->table("unit_pendaftar")->where("lokasi", $sekolah['nama_sekolah'])->countAllResults();

        $data = ['sekolah' => $sekolah, 'jml_pendaftar' => $pendaftar];


        return $this->respond(['status' => 200, "dataInfo" => $data]);
    }

    public function getStatusPendaftaran()
    {
        $data   = $this->_userDataToken();
        $nim    = $this->db->table("pengguna")->where("id", $data->uid)->get()->getRowArray()['nim'];

        $user = $this->masterModel->getDataPendaftar($nim);

        if (!empty($user)) {
            return $this->respond(['status' => 1, "dataInfo" => $user]);
        }else{
            return $this->respond(['status' => 0, "dataInfo" => $user]);
        }

    }


    public function daftar()
    {


        $nim        = $this->request->getPOST("nim");
        $lokasi     = $this->request->getPOST("lokasi");

        $cek        = $this->db->table("unit_pendaftar")->where("nim", $nim)->get()->getRowArray();
        $config     = $this->db->table("unit_pengaturan")->get()->getRowArray();
        $dateNow    = date("Y-m-d");


        if ($config['tgl_berakhir'] < $dateNow) {
            return $this->response->setStatusCode(400)->setJSON(['status' => false, 'message' => "Pendaftaran sudah ditutup!"]);
        }

        if ($cek !== null) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => "Anda sudah melakukan pendaftaran!"]);   
        }

        $cekMatakuliah = $this->mahasiswaModel->cekMatakuliahMhs($nim);
        if (!$cekMatakuliah) {
           return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => "Anda belum melakukan pengisian matakuliah, silahkan mengisi terlebih dahulu!"]);
        }

        $kuotaSekolahPilihan = $this->db->table("plp_magang_sekolah")->where("nama_sekolah", $lokasi)->get()->getRowArray();
        $cekJumlahPendaftarDiLokasi = $this->db->table("unit_pendaftar")
                                       ->where([
                                                "lokasi"          => $lokasi,
                                                'tahun_akademik'  => $config['tahun_akademik'],
                                                'semester'        => $config['semester']
                                             ])
                                       ->countAllResults();



        if ((int)$kuotaSekolahPilihan['kuota'] < $cekJumlahPendaftarDiLokasi) {
            return $this->response->setStatusCode(400)->setJSON(
                ['status' => false, 
                    'message' => "Kuota ".$kuotaSekolahPilihan['nama_sekolah'] ." sudah penuh!, silahkan pilih lokasi lain! ".$kuotaSekolahPilihan['kuota'].'-'.$cekJumlahPendaftarDiLokasi
                ]); 
        }

       $suratIzinPenelitian = $this->request->getFile("suratIzin");
       $buktiBayar          = $this->request->getFile("buktiBayar");
       $btq                 = $this->request->getFile("btq");
       $path = FCPATH."assets/upload/gambar";

       // surat izin penelitian
        $newSuratPenelitianName = $suratIzinPenelitian->getRandomName();
        $suratIzinPenelitian->move($path, $newSuratPenelitianName);

        // bukti bayar
        $newBuktiBayarName = $buktiBayar->getRandomName();
        $buktiBayar->move($path, $newBuktiBayarName);

        //btq
        $newNameBtq = $btq->getRandomName();
        $btq->move($path, $newNameBtq);

        $idProdi    = $this->masterModel->getMahasiswaProdi($nim);

        $slug_jenis_kegiatan    = $this->request->getPOST("kegiatan");
        $jenisKegitan           = $this->db->table('unit_kegiatan')->where('slug', $slug_jenis_kegiatan)->get()->getRowArray()['nama_kegiatan'];


        $data = [
            "id_prodi"          => $idProdi,
            "nim"               => $nim,
            "tahun_akademik"    => $config['tahun_akademik'],
            "semester"          => $config['semester'],
            "jenis_kegiatan"    => $jenisKegitan,
            "jenis_kepesertaan" => $this->request->getPOST("kepesertaan"),
            "lokasi"            => $lokasi,    
            "no_hp"             => $this->request->getPOST("noWA"),
            "ukuran_baju"       => $this->request->getPOST("ukuranBaju"),
            "bukti_bayar"       => $newSuratPenelitianName,
            "bukti_bayar2"      => $newBuktiBayarName,
            "btq"               => $newNameBtq,
            "tgl_bayar"         => $this->request->getPOST("tglBayar"),  
            "created_at"        => date("Y-m-d")  
        ];

        $this->db->table("unit_pendaftar")->insert($data);
        return $this->response->setStatusCode(201)->setJSON(['status' => true, 'message' => "Anda berhail melakukan pendaftaran!"]);   

    }

    public function getInfoMahasiswa()
    {
        $header = $this->request->getHeader("Authorization");
        $user   = validateJWTFromRequest($header);

        $mahasiswa = $this->db->table("pengguna")
                    ->select("pengguna.nim, pengguna.nama_lengkap as nama_mahasiswa, pengguna.id as id_mhs, p.nama_prodi")
                    ->join("prodi p", "p.id_prodi = pengguna.id_prodi", "left")
                    ->where("id", $user->uid)
                    ->get()->getRowArray();

        return $this->response->setStatusCode(200)->setJSON(['infoMhs' => $mahasiswa]);     

    }

    // logbook


    public function getLogbook()
    {
        $dataToken      = $this->_userDataToken();
        $nim            = $this->db->table("pengguna")->where("id", $dataToken->uid)->get()->getRowArray()['nim'];
        $id_pendaftar   = $this->db->table("unit_pendaftar")->where("nim", $nim)->get()->getRowArray()['id'];

        $logbook = $this->db->table("unit_logbook_mhs")
                    ->where("id_pendaftar", $id_pendaftar)
                    ->orderBy("id", 'desc')
                    ->get()->getResultArray();

        foreach ($logbook as $key => $row) {
            $dateShow = shortdate_indo($row['date_created']);
            $logbook[$key] += ['tanggalPost' => $dateShow];
        }

        return $this->response->setStatusCode(200)->setJSON(['status' => true, 'logbook' => $logbook]);

    }

    

    public function detailLogbook($id)
    {   
        $this->_userDataToken();

        $logbook = $this->db->table("unit_logbook_mhs ulm")->where("ulm.id", $id)->get()->getRowArray();
        $gambar = $this->db->table("unit_logbook_doc uld")
                ->select('uld.id, uld.gambar')->where("id_logbook", $logbook['id'])
                ->get()->getResultArray();
        $logbook += ['gambar' => $gambar];

        return $this->response->setStatusCode(200)->setJSON(['status' => true, 'logbook' => $logbook]);

    }

    public function deleteLogbook($id)
    {   
        $this->_userDataToken();

       $logbook = $this->db->table("unit_logbook_mhs")->where("id", $id)->get()->getRowArray();
       $gambar  = $this->db->table("unit_logbook_doc")->where("id_logbook", $id)->get()->getResultArray();


       if ($gambar !== null | $gambar !== '') {
            $path = FCPATH."assets/upload/gambar/logbook_kegiatan";
            foreach ($gambar as $key => $row) {
                unlink($path.'/'.$row['gambar']);
            }
       }

        $this->db->table("unit_logbook_doc")->where("id_logbook", $id)->delete();
        $this->db->table("unit_logbook_mhs")->where("id", $id)->delete();

        return $this->response->setStatusCode(200)->setJSON(['status' => true, 'message' => "Logbook berhasil di hapus!"]);

    }

    public function simpanLogbook()
    {
        
        $dataToken      = $this->_userDataToken();
        $nim            = $this->db->table("pengguna")->where("id", $dataToken->uid)->get()->getRowArray()['nim'];
        $id_pendaftar   = $this->db->table("unit_pendaftar")->where("nim", $nim)->get()->getRowArray()['id'];

        $fileLampiran       = $this->request->getFiles();
        $countFileLampiran  = count($fileLampiran);
        $mingguKe           = (int)$this->request->getPost('mingguKe');

        if ($countFileLampiran < 1) {
            return $this->response->setStatusCode(400)->setJSON(['status' => false, 'message' => "Silahkan uplad file lampiran kegiatan, min 1 foto!"]); 
        }

        $cekLogbookUser    = $this->db->table("unit_logbook_mhs")
                                ->where(['id_pendaftar' => $id_pendaftar, 'mingguKe' => $mingguKe])
                                ->get()->getRowArray();

        if ($cekLogbookUser) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => false, 
                'message' => "Logbook minggu ke ".$mingguKe." sudah di upload tanggal ".$cekLogbookUser['date_created']
            ]);
        }

        $path = FCPATH."assets/upload/gambar/logbook_kegiatan";

        $tanggal    = $this->request->getPost('tanggal');
        $bulan      = $this->request->getPost('bulan');
        $tahun      = $this->request->getPost('tahun');

        $data90['id_pendaftar']       = $id_pendaftar;
        $data90['tgl_kegiatan']       = $tanggal.' '.$bulan.' '.date("Y");
        $data90['mingguKe']           = $mingguKe;
        $data90['nama_kegiatan']      = $this->request->getPost('namaKegiatan');
        $data90['tujuan_kegiatan']    = $this->request->getPost('tujuanKegiatan');
        $data90['catatan']            = $this->request->getPost('catatan');
        $data90['pemecahan_masalah']  = $this->request->getPost('pemecahanMasalah');
        $data90['kesimpulan']         = $this->request->getPost('kesimpulan');
        $data90['rencana']            = $this->request->getPost('kegiatanSelanjutnya');
        $data90['date_created']       = date("Y-m-d");

        $this->db->table("unit_logbook_mhs")->insert($data90);
        $IDInsert = $this->db->insertID();

        foreach($fileLampiran as $index => $file) {

            $newName = $file->getRandomName();

            $datalampiran['id_logbook'] = $IDInsert;
            $datalampiran['gambar']     = $newName;

            $this->db->table("unit_logbook_doc")->insert($datalampiran);

            $file->move($path, $newName);
        }

        return $this->response->setStatusCode(201)->setJSON(['status' => true, 'message' => "Logbook anda berhasil di upload!"]); 

        
    }

    public function updateLogbook()
    {
        $dataToken      = $this->_userDataToken(); 

        $idLogbook = $this->request->getPost("idLogbook");
        $logbook = $this->db->table("unit_logbook_mhs")->where("id", $idLogbook)->get()->getRowArray();

        if ($logbook['status'] === 'verified') {
            return $this->response->setStatusCode(401)->setJSON(['status' => true, 'message' => "Logbook sudah diverifikasi, tidak dapat diubah lagi!"]);
        }


        // jika ada gambar
        $fileLampiran       = $this->request->getFiles();

        if ($fileLampiran) {

            $gambar = $this->db->table("unit_logbook_doc")->where("id_logbook", $idLogbook)->get()->getResultArray();
            $path = FCPATH."assets/upload/gambar/logbook_kegiatan";

            foreach ($gambar as $key => $row) {
                unlink($path.'/'.$row['gambar']);
            }


            $this->db->table("unit_logbook_doc")->where("id_logbook", $idLogbook)->delete();

            foreach($fileLampiran as $index => $file) {

                $newName = $file->getRandomName();

                $datalampiran['id_logbook'] = $idLogbook;
                $datalampiran['gambar']     = $newName;

                $this->db->table("unit_logbook_doc")->insert($datalampiran);

                $file->move($path, $newName);
            }
        }

        $tanggal    = $this->request->getPost('tanggal');
        $bulan      = $this->request->getPost('bulan');
        $tahun      = $this->request->getPost('tahun');

        $mingguKe           = (int)$this->request->getPost('mingguKe');

        $data90['tgl_kegiatan']       = $tanggal.' '.$bulan.' '.date("Y");
        $data90['mingguKe']           = $mingguKe;
        $data90['nama_kegiatan']      = $this->request->getPost('namaKegiatan');
        $data90['tujuan_kegiatan']    = $this->request->getPost('tujuanKegiatan');
        $data90['catatan']            = $this->request->getPost('catatan');
        $data90['pemecahan_masalah']  = $this->request->getPost('pemecahanMasalah');
        $data90['kesimpulan']         = $this->request->getPost('kesimpulan');
        $data90['rencana']            = $this->request->getPost('kegiatanSelanjutnya');

        $this->db->table('unit_logbook_mhs')->where("id", $idLogbook)->update($data90);

        return $this->response->setStatusCode(200)->setJSON(['status' => true, 'message' => "Logbook berhasil di update!"]);
    }

    public function getLaporanAkhir()
    {
        $dataToken      = $this->_userDataToken();
        $nim            = $this->db->table("pengguna")->where("id", $dataToken->uid)->get()->getRowArray()['nim'];

        $laporan = $this->db->table("unit_pendaftar up")
                    ->select("up.laporan, up.link_kegiatan_magang, up.updated_at")
                    ->where("nim", $nim)->get()->getRowArray();

        return $this->response->setStatusCode(200)->setJSON(['status' => true, 'laporan' => $laporan]);


    }

    public function uploadLaporan()
    {

        $dataToken      = $this->_userDataToken();
        $nim            = $this->db->table("pengguna")->where("id", $dataToken->uid)->get()->getRowArray()['nim'];

        $dataPendaftar  = $this->db->table("unit_pendaftar")->where("nim", $nim)->get()->getRowArray();
        $where = ['tahun_akademik' => $dataPendaftar['tahun_akademik'], 'semester' => $dataPendaftar['semester']];
        $cekConfigUnit  = $this->db->table("unit_pengaturan")->where($where)->get()->getRowArray();
        $tanggal_sekarang = date("Y-m-d");

        // if (!$cekConfigUnit || $cekConfigUnit === NULL) {
        //     return $this->response->setStatusCode(400)->setJSON(['status' => false, 'message' => "Tahun akademik tidak valid!"]);
        // }

        // if ($cekConfigUnit['tgl_penarikan']  < $tanggal_sekarang) {
        //     return $this->response->setStatusCode(400)->setJSON(['status' => false, 'message' => "Maaf, anda terlambat mengunggah laporan!"]);
        // }

        $laporan = $this->request->getFile('laporan');
        $link = $this->request->getPost("link");

        if (!$laporan) {
             return $this->response->setStatusCode(400)->setJSON(['status' => false, 'message' => "File laporan harus di isi!"]);
        }

         $path = FCPATH."assets/upload/laporan";

         $newFileName = $laporan->getRandomName();
         $laporan->move($path, $newFileName);
           
        $idYoutube = getYoutubeIdFromUrl($link);

        if ($idYoutube) {
            $idLink = $idYoutube;
        }else{
            $idLink = $link;
        }


        $update = $this->db->table("unit_pendaftar")
                    ->set([
                        'laporan'               => $newFileName, 
                        'link_kegiatan_magang'  => $idLink,
                        'updated_at'            => time()
                    ])
                    ->where('nim', $nim)->update();

        if ($update) {
            return $this->response->setStatusCode(201)->setJSON(['status' => true, 'message' => "Laporan berhasil di unggah!"]);
        }

        return $this->response->setStatusCode(400)->setJSON(['status' => false, 'message' => "Something wrong, please cek your connection!"]);

    }

    public function getInfoDaftar()
    {
        $dataToken      = $this->_userDataToken();
        $nim            = $this->db->table("pengguna")->where("id", $dataToken->uid)->get()->getRowArray()['nim'];

        $dataPendaftar  = $this->db->table("unit_pendaftar")->where("nim", $nim)->get()->getRowArray();

        if (!$dataPendaftar) {
            return $this->response->setStatusCode(201)->setJSON(['status' => true, 'info' => null]);
        }
        $nilai = $this->db->table("unit_bimbingan")
                ->select("unit_bimbingan.*, dosen.nama_dosen")
                ->join("dosen", 'dosen.id_dosen = unit_bimbingan.id_dosen', "left")
                ->where("id_pendaftar", $dataPendaftar['id'])
                ->get()->getRowArray();

        $date = shortdate_indo($dataPendaftar['created_at']);       
        $dataPendaftar += ['nilaiAkhir' => $nilai ? $nilai : null];
        $dataPendaftar += ['date_join' => $date];

        return $this->response->setStatusCode(201)->setJSON(['status' => true, 'info' => $dataPendaftar]);


    }

    public function cetakNilai($token)
    {
        $key = getenv('TOKEN_SECRET');
        try {
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
        } catch (\Throwable $th) {
            return Services::response()
                            ->setJSON(['msg' => 'Invalid Token'])
                            ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }



        $nim   = $this->db->table("pengguna")->where("id", $decoded->uid)->get()->getRowArray()['nim'];

        $dataPendaftar      = $this->db->table("unit_pendaftar")->where("nim", $nim)->get()->getRowArray();
        QrCodeGenerator($nim, $dataPendaftar['id']);

        $data['matakuliah'] = $this->mahasiswaModel->getMatakuliah($nim);
        $data['nilai']      = $this->mahasiswaModel->getNilai($dataPendaftar['id']);
        $data['mahasiswa']  = $this->mahasiswaModel->getDataDetailMhs($decoded->uid);

        $html = view('cetak_nilai_mhs', $data);

        PdfGenerator::generate($html, 'nilai-akhir-mbkm-'.$nim, 'A4', 'portrait');



    }

    public function getMatakuliah()
    {
        $dataToken      = $this->_userDataToken();
        $nim            = $this->db->table("pengguna")->where("id", $dataToken->uid)->get()->getRowArray()['nim'];

        // $id_pendaftar  = $this->db->table("unit_pendaftar")->where("nim", $nim)->get()->getRowArray()['id'];
        // var_dump($nim);
        $data           = $this->db->table("unit_matakuliah_mhs")->where("nim", $nim)->get()->getResultArray();
        $status_daftar  = $this->mahasiswaModel->cekStatusPendaftaran($nim);

        return $this->response->setStatusCode(201)->setJSON(['status' => $status_daftar, 'data' => $data]);

    }


    public function simpanMatakuliah()
    {
        $dataToken      = $this->_userDataToken();
        $nim            = $this->db->table("pengguna")->where("id", $dataToken->uid)->get()->getRowArray()['nim'];

        // post
        
        $matakuliah = $this->request->getJSON();

        $this->mahasiswaModel->cekMatakuliah($nim);

        $data = [];
        $countSks = 0;

        foreach ($matakuliah as $key => $row) {
            array_push($data, [
                'nim'           => $nim,
                'matakuliah'    => $row->matakuliah, 
                'sks'           => (int)$row->sks
            ]);

            $countSks += (int)$row->sks;
        }

        if ($countSks > 20) {
           return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => "Gagal disimpan, tidak boleh lebih dari 20 sks!"]);
        }


        $insert = $this->db->table("unit_matakuliah_mhs")->insertBatch($data);

        if ($insert) {
           return $this->response->setStatusCode(201)->setJSON(['status' => true, 'message' => "Matakuliah berhasil disimpan!"]);
        }

        return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => "Gagal disimpan!"]);
    }


    public function cetakSuratRekomendasi($token)
    {
        $key = getenv('TOKEN_SECRET');
        try {
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
        } catch (\Throwable $th) {
            return Services::response()
                            ->setJSON(['msg' => 'Invalid Token'])
                            ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }


        $data['mahasiswa']   = $this->mahasiswaModel->getDataDetailMhs($decoded->uid); 


        $data['matakuliah'] = $this->mahasiswaModel->getMatakuliah($data['mahasiswa']['nim']);

        $html = view('cetak_surat_rekomendasi', $data);

        PdfGenerator::generate($html, 'surat-rekomendasi-'.$data['mahasiswa']['nama_lengkap'].'-'.date('Y'), 'A4', 'portrait');


    }

    public function cekQrCode($id_pendaftar)
    {
        $dataPendaftar      = $this->db->table("unit_pendaftar")->where("id", $id_pendaftar)->get()->getRowArray();
        $mahasiswa          = $this->db->table("pengguna")->where("nim", $dataPendaftar['nim'])->get()->getRowArray();

        if (!$dataPendaftar) {
            $data['status'] = false;
        }else{
            $data['status'] = true;
        }
        $data['matakuliah'] = $this->mahasiswaModel->getMatakuliah($mahasiswa['nim']);
        $data['nilai']      = $this->mahasiswaModel->getNilai($id_pendaftar);
        $data['mahasiswa']  = $this->mahasiswaModel->getDataDetailMhs($mahasiswa['id']);

        return view('cek_qrcode', $data);


    }

   
}
