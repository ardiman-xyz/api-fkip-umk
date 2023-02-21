<?php

namespace Config;

$routes = Services::routes();

if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

$routes->get('/', 'Home::index');

$routes->post('/login', 'Home::login');
$routes->get('/programs', 'Home::programs');

$routes->group('mahasiswa', function ($routes) {
    $routes->get('/', 'Mahasiswa::getInfo');
    $routes->get('program', 'Mahasiswa::program');
    $routes->post('detailProgram', 'Mahasiswa::detailProgram');
    $routes->post('getDataSekolah', 'Mahasiswa::getDataSekolah');
    $routes->post('getInfoSekolah', 'Mahasiswa::getInfoSekolah');
    $routes->get('getStatusPendaftaran', 'Mahasiswa::getStatusPendaftaran');
    
    $routes->get('getInfoMahasiswa', 'Mahasiswa::getInfoMahasiswa');
    $routes->post('daftar', 'Mahasiswa::daftar');

    $routes->group("logbook", function($routes) {
        $routes->post("/", "Mahasiswa::simpanLogbook");
        $routes->get("/", "Mahasiswa::getLogbook");
        $routes->post("update", "Mahasiswa::updateLogbook");
        $routes->get("detail/(:any)", "Mahasiswa::detailLogbook/$1");
        $routes->delete("(:any)", "Mahasiswa::deleteLogbook/$1");
    });

    $routes->post("uploadLaporan", "Mahasiswa::uploadLaporan");
    $routes->get("getLaporanAkhir", "Mahasiswa::getLaporanAkhir");
    $routes->get("getInfoDaftar", "Mahasiswa::getInfoDaftar");
    $routes->get("cetak_nilai/(:any)", "Mahasiswa::cetakNilai/$1");
    $routes->get("getMatakuliah", "Mahasiswa::getMatakuliah");
    $routes->post("simpanMatakuliah", "Mahasiswa::simpanMatakuliah");
    $routes->get("cetak_surat_rekomendasi/(:any)", "Mahasiswa::cetakSuratRekomendasi/$1");
    $routes->get("cekQrcode/(:any)", "Mahasiswa::cekQrcode/$1");

});

$routes->group('admin', function ($routes) {
    $routes->get('getTahunAkademik', 'Admin::getTahunAkademik');
    $routes->post('getDataPendaftar/(:any)/(:any)', 'Admin::getDataPendaftar/$1/$2');
    $routes->post('getDataSearchPendaftar', 'Admin::getDataSearchPendaftar');
    $routes->get('getDatacenter', 'Admin::getDatacenter');
    $routes->post('getDataFilterpendaftar', 'Admin::getDataFilterpendaftar');

    $routes->get('dataPeserta/(:any)/(:any)', 'Admin::dataPeserta/$1/$2');
    $routes->get('getDataDosen', 'Admin::getDataDosen');
    $routes->post('getDataMahasiswa', 'Admin::getDataMahasiswa');
    $routes->post('simpanDataBimbingan', 'Admin::simpanDataBimbingan');
    $routes->post('getDataBimbingan', 'Admin::getDataBimbingan');

    $routes->delete('deleteBimbingan', 'Admin::deleteBimbingan');
    $routes->post('updateBimbingan', 'Admin::updateBimbingan');

    $routes->post('getMahsiswaByNim', 'Admin::getMahsiswaByNim');
    $routes->post('getDataBimbinganDetail', 'Admin::getDataBimbinganDetail');
    $routes->get('getDataPengaturan', 'Admin::getDataPengaturan');
    $routes->post('simpanPengaturan', 'Admin::simpanPengaturan');
    $routes->post('getDataSekolah', 'Admin::getDataSekolah');
    $routes->post('simpanSekolah', 'Admin::simpanSekolah');
    $routes->delete('deleteSekolah/(:any)', 'Admin::deleteSekolah/$1');
    $routes->post('addProgram', 'Admin::addProgram/');
    $routes->get("getDataPrograms", 'Admin::getDataPrograms');
    $routes->delete("deleteProgram/(:id)", 'Admin::deleteProgram/$1');
    $routes->get("getInfoPendaftar/(:id)", 'Admin::getInfoPendaftar/$1');
    $routes->post("updatePendaftar", 'Admin::updatePendaftar');
    $routes->delete("deletePeserta/(:id)", 'Admin::deletePeserta/$1');
    $routes->delete("deleteBimbinganDpl/(:id)", 'Admin::deleteBimbinganDpl/$1');

});

$routes->group('dpl', function ($routes) {
    $routes->get('getDataBimbingan', 'Dpl::getDataBimbingan');
    $routes->get('getBimbingan/(:any)/(:any)', 'Dpl::getBimbingan/$1/$2');
    $routes->get('getMahasiswaDetail/(:any)', 'Dpl::getMahasiswaDetail/$1');
    $routes->get('getLogbookMhs/(:any)', 'Dpl::getLogbookMhs/$1');
    $routes->get('getLogbookMhsDetail/(:any)', 'Dpl::getLogbookMhsDetail/$1');
    $routes->get('readLogbook/(:any)', 'Dpl::readLogbook/$1');
    $routes->get('getLaporanMhs/(:any)', 'Dpl::getLaporanMhs/$1');
    $routes->get('getDataBimbinganNilai', 'Dpl::getDataBimbinganNilai');
    $routes->post('simpanNilaiResult', 'Dpl::simpanNilaiResult');
});

/*
 * --------------------------------------------------------------------
 * Router Api
 * --------------------------------------------------------------------
 */

 // for website e-konseling fkip

 $routes->group('konseling', function ($routes) {

    $routes->group('admin', function ($routes) {
        $routes->post('login', 'Konseling\Admin\Auth::login');
    });
    
    $routes->group('mahasiswa', function ($routes) {
        $routes->post('login', 'Konseling\Mahasiswa\Auth::login');
    });

});





if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
