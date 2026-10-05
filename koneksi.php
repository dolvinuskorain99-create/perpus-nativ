<?php
//UNTUK KONEK KE DATA BASE -PERPUSTAKAAN USTJ

// SEMBUNYIKAN PESAN ERROR MENTAH DARI PUBLIK UNTUK KEMAMANAAN NANTI DI HOSTING

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = "localhost";
$user = "root";
$pass ="";
$db = "db_perpustakaan";

// INI UNTUK KONEK KE DATA BASE
$koneksi = mysqli_connect($host, $user, $pass, $db);
if (!$koneksi) {

    //INI UNTUK PESAN AMAN USER JIKA GAGAL ,TANPA MEMBOCORKAN DETAIL SERVER/PASSWORD


    die("Sistem Sedang Mengalami gangguan koneksi database. Silahkan coba beberapa saa lagiT Terimakasih.");
}



?>