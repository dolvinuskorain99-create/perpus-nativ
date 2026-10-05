<?php
// =====================================================
// SISTEM INFORMASI PERPUSTAKAAN USTJ
// FILE: admin/hapus_buku.php
// =====================================================

include '../koneksi.php';

if (!$koneksi) {
    die('Koneksi database gagal: ' . mysqli_connect_error());
}

if (isset($_GET['id'])) {
    $id_buku = (int)$_GET['id'];

    // Jalankan perintah delete
    $query_delete = "DELETE FROM buku WHERE id_buku = $id_buku";
    
    if (mysqli_query($koneksi, $query_delete)) {
        header("Location: buku.php?pesan=sukses_hapus");
        exit();
    } else {
        header("Location: buku.php?pesan=gagal_hapus");
        exit();
    }
} else {
    header("Location: buku.php");
    exit();
}
?>