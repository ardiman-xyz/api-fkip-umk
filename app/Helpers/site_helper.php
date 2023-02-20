<?php

use App\Models\UserModel;
use Config\Services;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use CodeIgniter\HTTP\ResponseInterface;

use App\Libraries\PdfGenerator;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;


function validateJWTFromRequest($authenticationHeader)
{
    $key = getenv('TOKEN_SECRET');

    if(is_null($authenticationHeader)) {
    	throw new Exception('Missing or invalid JWT in request');
    }

    $token = explode(' ', $authenticationHeader)[2];

    try {
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        return $decoded;
    } catch (\Throwable $th) {
        return Services::response()
                        ->setJSON(['msg' => 'Invalid Token'])
                        ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
    }
}

if ( ! function_exists('shortdate_indo'))
{
    function shortdate_indo($tgl)
    {
        if ($tgl || $tgl !== null || $tgl === '') {
            $ubah = gmdate($tgl, time()+60*60*8);
            $pecah = explode("-",$ubah);
            $tanggal = $pecah[2];
            $bulan = short_bulan($pecah[1]);
            $tahun = $pecah[0];
            return $tanggal.'/'.$bulan.'/'.$tahun;
        }else{
            return "not valid date";
        }
    }
}



 if ( ! function_exists('short_bulan'))
{   
    function short_bulan($bln)
    {
        switch ($bln)
        {
            case 1:
                return "01";
                break;
            case 2:
                return "02";
                break;
            case 3:
                return "03";
                break;
            case 4:
                return "04";
                break;
            case 5:
                return "05";
                break;
            case 6:
                return "06";
                break;
            case 7:
                return "07";
                break;
            case 8:
                return "08";
                break;
            case 9:
                return "09";
                break;
            case 10:
                return "10";
                break;
            case 11:
                return "11";
                break;
            case 12:
                return "12";
                break;
        }
    }
}


/**
 * Get Youtube video ID from URL
 *
 * @param string $url
 * @return mixed Youtube video ID or FALSE if not found
 */

function getYoutubeIdFromUrl($url) {
    $parts = parse_url($url);
    if(isset($parts['query'])){
        parse_str($parts['query'], $qs);
        if(isset($qs['v'])){
            return $qs['v'];
        }else if(isset($qs['vi'])){
            return $qs['vi'];
        }
    }
    if(isset($parts['path'])){
        $path = explode('/', trim($parts['path'], '/'));
        return $path[count($path)-1];
    }
    return false;
}

function genarateImageDomPDF($path) {
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    $base64 = 'data:image/'.$type.';base64,'.base64_encode($data);

    return $base64;
}

function QrCodeGenerator($nim, $id_pendaftar)
{
    $writer = new PngWriter();

    $url = base_url("mahasiswa/cekQrCode/".$id_pendaftar);

    $qrCode = QrCode::create($url)
        ->setEncoding(new Encoding('UTF-8'))
        ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
        ->setSize(200)
        ->setMargin(10)
        ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
        ->setForegroundColor(new Color(0, 0, 0))
        ->setBackgroundColor(new Color(255, 255, 255));

        $logo = Logo::create(FCPATH.'assets/tut.png')
                    ->setResizeToWidth(80);

        $result = $writer->write($qrCode, $logo);

        $result->saveToFile(FCPATH.'assets/qrcode/'.$nim.'.png');
}

function _getGrade($n1, $n2, $n3, $n4) {
    $result = ( (int)$n1 + (int)$n2 + (int)$n3 + (int)$n4 ) / 4;

    if ($result >= 80) {
        $nilai = 'A';
    }elseif($result >= 70){
        $nilai = 'B';
    }elseif($result >= 60) {
        $nilai = 'C';
    }else{
        $nilai = 'D';
    }

    return $nilai;
}

if ( ! function_exists('medium_bulan'))
{
    function medium_bulan($bln)
    {
        switch ($bln)
        {
            case 1:
                return "Januari";
                break;
            case 2:
                return "Februari";
                break;
            case 3:
                return "Maret";
                break;
            case 4:
                return "April";
                break;
            case 5:
                return "Mei";
                break;
            case 6:
                return "Juni";
                break;
            case 7:
                return "Juli";
                break;
            case 8:
                return "Agustus";
                break;
            case 9:
                return "September";
                break;
            case 10:
                return "Oktober";
                break;
            case 11:
                return "November";
                break;
            case 12:
                return "Desember";
                break;
        }
    }
}

//Format Medium date
    if ( ! function_exists('mediumdate_indo'))
    {
        function mediumdate_indo($tgl)
        {
            $ubah = gmdate($tgl, time()+60*60*8);
            $pecah = explode("-",$ubah);
            $tanggal = $pecah[2];
            $bulan = medium_bulan($pecah[1]);
            $tahun = $pecah[0];
            return $tanggal.' '.$bulan.' '.$tahun;
        }
    }

function slugify($text, string $divider = '-')
{
  // replace non letter or digits by divider
  $text = preg_replace('~[^\pL\d]+~u', $divider, $text);

  // transliterate
  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

  // remove unwanted characters
  $text = preg_replace('~[^-\w]+~', '', $text);

  // trim
  $text = trim($text, $divider);

  // remove duplicate divider
  $text = preg_replace('~-+~', $divider, $text);

  // lowercase
  $text = strtolower($text);

  if (empty($text)) {
    return 'n-a';
  }

  return $text;
}