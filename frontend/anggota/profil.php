<?php
// =====================================================
// SISTEM INFORMASI PERPUSTAKAAN USTJ
// HALAMAN: PENGATURAN PROFIL
// FILE: frontend/anggota/profil.php
// =====================================================
session_start();
require_once '../../koneksi.php';

if (!isset($_SESSION['user_login']) || $_SESSION['user_login'] !== true) {
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['user_id'];
$nama_lengkap = $_SESSION['nama_lengkap'] ?? 'Mahasiswa';

$query_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = '$id_user'");
$data_user = mysqli_fetch_assoc($query_user);
$nim = $data_user['nim'] ?? '-';
$username = $data_user['username'] ?? '-';
$email = $data_user['email'] ?? '-';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Profil - Perpustakaan USTJ</title>
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
                    <h3 class="fw-bold text-dark mb-1">Pengaturan Profil</h3>
                    <p class="text-muted mb-0">Kelola informasi akun dan identitas mahasiswa Anda.</p>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4">
                        <h5 class="fw-bold text-dark mb-3">Informasi Akun</h5>
                        <hr class="text-muted">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-semibold">Nama Lengkap</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($nama_lengkap); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-semibold">NIM (Nomor Induk Mahasiswa)</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($nim); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-semibold">Username</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($username); ?>" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>