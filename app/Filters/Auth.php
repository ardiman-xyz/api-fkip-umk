<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;
use Config\Services;


class Auth implements FilterInterface
{

    public function before(RequestInterface $request, $arguments = null)
    {

        $key = getenv('TOKEN_SECRET');
        $header = $request->getServer("HTTP_AUTHORIZATION");

        if(!$header) return Services::response()
                            ->setJSON(['msg' => 'Token Required'])
                            ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        $token = explode(' ', $header)[2];


        try {
            JWT::decode($token, $key, ['HS256']);
        } catch (\Throwable $th) {
            return Services::response()
                            ->setJSON(['msg' => 'Invalid Token'])
                            ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}