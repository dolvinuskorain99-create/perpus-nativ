<?php
// =====================================================
// SISTEM INFORMASI PERPUSTAKAAN USTJ
// HALAMAN: PEMINJAMAN MANDIRI
// FILE: frontend/anggota/peminjaman_mandiri.php
// =====================================================
session_start();
require_once '../../koneksi.php';

if (!isset($_SESSION['user_login']) || $_SESSION['user_login'] !== true) {
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['user_id'];
$nama_lengkap = $_SESSION['nama_lengkap'] ?? 'Mahasiswa';

// Ambil data user untuk NIM
$query_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = '$id_user'");
$data_user = mysqli_fetch_assoc($query_user);
$nim = $data_user['nim'] ?? '-';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Mandiri - Perpustakaan USTJ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .main-content { margin-left: 260px; padding: 30px; }
        @media (max-width: 768px) { .main-content { margin-left: 0; padding: 15px; } }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold text-dark mb-1">Peminjaman Mandiri</h3>
                    <p class="text-muted mb-0">Cari buku dan ajukan peminjaman secara mandiri.</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4">
                <div class="text-center py-5">
                    <i class="bi bi-book display-1 text-muted opacity-50 mb-3"></i>
                    <h5 class="fw-bold text-dark">Katalog Buku Tersedia</h5>
                    <p class="text-muted small">Fitur pencarian dan pengajuan peminjaman buku mandiri sedang dipersiapkan.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>