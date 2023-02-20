<?php

namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\MasterModel;

class Home extends BaseController
{
    use ResponseTrait;

    public function __construct()
    {
        $this->db           = \Config\Database::connect();
        $this->masterModel  = new MasterModel();
    }

    public function index()
    {
        return view('welcome_message');
    }

    public function login()
    {
        $username = $this->request->getJSON()->username;
        $role = $this->request->getJSON()->role;
        $password = sha1($this->request->getJSON()->password);

        if ($role === 'mahasiswa') {
            $data = $this->masterModel->get_data($username);

            if ($data) {
                
                if ($password === $data['password']) {
                

                    $key = getenv('TOKEN_SECRET');
                    $payload = array(
                        "uid" => $data['id'],
                        "role" => "mahasiswa"
                    );

                    $token = JWT::encode($payload, $key, 'HS256');


                    return $this->respond(['token' => $token, 'user' => $data, "hak_akses" => "mahasiswa"]);

                }else{
                    $response = ['code' => 404, "message" => "username and password combination is not match!"];
                }

            }else{
                $response = ['code' => 404, "message" => "user not found"];
            }
            
        }elseif($role === 'md2b') {
            $data = $this->masterModel->getData("unit_user", $username, "username");

            if ($data) {
                
                if ($password === $data['password']) {
                

                    $key = getenv('TOKEN_SECRET');

                    $payload = array(
                        "uid" => $data['id'],
                        "role" => "admin"
                    );

                    $token = JWT::encode($payload, $key, 'HS256');


                    return $this->respond(['token' => $token, 'user' => $data, "hak_akses" => "admin"]);

                }else{
                    $response = ['code' => 404, "message" => "username and password combination is not match!"];
                }

            }else{
                $response = ['code' => 404, "message" => "user not found"];
            }
        }else if($role === "dpl") {
            $passwordDpl = $this->request->getJSON()->password;
            $data = $this->masterModel->getData("dosen", $username, "NIDN");

            if (!$data || $data === null || $data === "") {
                $response = ['code' => 404, "message" => "user not found"];
            }else{

                if (!$passwordDpl === $data['password'] ) {
                    $response = ['code' => 404, "message" => "username and password combination is not match!"];
                }else{

                    $key = getenv('TOKEN_SECRET');

                    $payload = array(
                        "uid" => $data['id_dosen'],
                        "role" => "dpl"
                    );

                    $token = JWT::encode($payload, $key, 'HS256');


                    return $this->respond(['token' => $token, "hak_akses" => "dpl"]);
                }

            }
        }

        else{
            $response = ['code' => 404, "message" => "user tidak ditemukan, masukkan data yang benar!"];
        }


        return $this->response->setStatusCode(404)->setJSON($response);
    }

    public function programs()
    {   
        if($this->request->getMethod() !== 'get') return $this->response->setStatusCode(405)->setJSON(['status' => false, 'message' => 'Something wrong with our request!']);

        $programs = $this->masterModel->getData("unit_kegiatan");

        return $this->respond(['status' => 200, "programs" => $programs]);
    }
   
}
