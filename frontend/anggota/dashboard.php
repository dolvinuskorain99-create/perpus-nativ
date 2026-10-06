<?php
// =====================================================
// SISTEM INFORMASI PERPUSTAKAAN USTJ
// HALAMAN: DASHBOARD ANGGOTA
// FILE: frontend/anggota/dashboard.php
// =====================================================

// Memulai session dan menyertakan koneksi database (naik 2 level ke folder root)
session_start();
require_once '../../koneksi.php';

// Cek apakah user sudah login dan benar-benar berstatus sebagai anggota
if (!isset($_SESSION['user_login']) || $_SESSION['user_login'] !== true) {
    header("Location: ../login.php");
    exit;
}

// Ambil data user dari session
$id_user = $_SESSION['user_id'];
$username = $_SESSION['username'];
$nama_lengkap = $_SESSION['nama_lengkap'] ?? 'Mahasiswa';

// Ambil data detail user terbaru dari database untuk memastikan status (pending/aktif) dan NIM
$query_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = '$id_user'");
$data_user = mysqli_fetch_assoc($query_user);

$nim = $data_user['nim'] ?? 'Belum diatur';
$status_anggota = $data_user['status'] ?? 'pending';

// Hitung statistik peminjaman (contoh query opsional jika tabel peminjaman sudah ada)
// $buku_dipinjam = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM peminjaman WHERE id_user = '$id_user' AND status = 'dipinjam'"));
$buku_dipinjam = 0;
$buku_selesai = 0;
$reservasi = 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Anggota - Perpustakaan USTJ</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        /* Mengatur area konten utama agar berada di sebelah kanan sidebar */
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
        }
    </style>
</head>
<body>

    <!-- 1. PANGGIL SIDEBAR -->
    <?php include 'sidebar.php'; ?>

    <!-- 2. KONTEN UTAMA DASHBOARD -->
    <div class="main-content">
        <div class="container-fluid">
            
            <!-- Header Sambutan -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold text-dark mb-1">Dashboard Mahasiswa</h3>
                    <p class="text-muted mb-0">Selamat datang kembali, <strong><?= htmlspecialchars($nama_lengkap); ?></strong> 👋</p>
                </div>
                <div class="d-flex align-items-center bg-white p-2 px-3 rounded-pill shadow-sm border">
                    <i class="bi bi-person-circle fs-4 text-primary me-2"></i>
                    <div>
                        <span class="d-block fw-bold small text-dark"><?= htmlspecialchars($nama_lengkap); ?></span>
                        <span class="text-muted" style="font-size: 11px;">NIM: <?= htmlspecialchars($nim); ?></span>
                    </div>
                </div>
            </div>

            <!-- Banner Utama -->
            <div class="card border-0 shadow-sm text-white mb-4" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); border-radius: 15px;">
                <div class="card-body p-4 p-md-5">
                    <h1 class="fw-bold display-6 mb-2">Selamat Datang di Perpustakaan USTJ 📚</h1>
                    <p class="lead fs-6 mb-4 opacity-90">Temukan koleksi buku, lakukan peminjaman secara mandiri, dan pantau riwayat peminjaman Anda dengan mudah.</p>
                    <a href="../katalog.php" class="btn btn-light text-primary fw-semibold px-4 py-2 rounded-pill shadow-sm">
                        <i class="bi bi-search me-1"></i> Jelajahi Koleksi Buku
                    </a>
                </div>
            </div>

            <!-- Kartu Statistik -->
            <div class="row g-3 mb-4">
                <!-- Buku Dipinjam -->
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm h-100 rounded-4">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small d-block mb-1">Buku Dipinjam</span>
                                <h3 class="fw-bold mb-0 text-dark"><?= $buku_dipinjam; ?></h3>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded-4 text-primary">
                                <i class="bi bi-book-fill fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Buku Selesai -->
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm h-100 rounded-4">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small d-block mb-1">Buku Selesai</span>
                                <h3 class="fw-bold mb-0 text-dark"><?= $buku_selesai; ?></h3>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded-4 text-success">
                                <i class="bi bi-check-circle-fill fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reservasi -->
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm h-100 rounded-4">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small d-block mb-1">Reservasi</span>
                                <h3 class="fw-bold mb-0 text-dark"><?= $reservasi; ?></h3>
                            </div>
                            <div class="bg-warning bg-opacity-10 p-3 rounded-4 text-warning">
                                <i class="bi bi-bookmark-fill fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Anggota -->
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm h-100 rounded-4">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small d-block mb-1">Status Anggota</span>
                                <?php if ($status_anggota === 'aktif'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-bold">
                                        <i class="bi bi-check-circle me-1"></i> Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill fw-bold">
                                        <i class="bi bi-clock me-1"></i> Pending
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="bg-info bg-opacity-10 p-3 rounded-4 text-info">
                                <i class="bi bi-person-badge-fill fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bagian Profil & Akses Cepat -->
            <div class="row g-4">
                <!-- Profil Anggota Ringkas -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <h5 class="fw-bold text-dark mb-3">Profil Anggota</h5>
                            <p class="text-muted small mb-4">Informasi akun perpustakaan Anda yang terdaftar.</p>
                            
                            <div class="text-center py-3">
                                <div class="bg-light rounded-circle d-inline-flex p-3 mb-3">
                                    <i class="bi bi-person-fill display-5 text-primary"></i>
                                </div>
                                <h4 class="fw-bold mb-1"><?= htmlspecialchars($nama_lengkap); ?></h4>
                                <p class="text-muted mb-0">NIM: <?= htmlspecialchars($nim); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Akses Cepat -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <h5 class="fw-bold text-dark mb-3">Akses Cepat</h5>
                            <p class="text-muted small mb-4">Gunakan fitur perpustakaan yang tersedia dengan cepat.</p>
                            
                            <div class="d-grid gap-3">
                                <a href="peminjaman_mandiri.php" class="btn btn-light border text-start p-3 rounded-3 d-flex align-items-center shadow-sm">
                                    <i class="bi bi-search fs-4 text-primary me-3"></i>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark">Cari & Pinjam Buku</h6>
                                        <small class="text-muted">Temukan buku yang tersedia di perpustakaan.</small>
                                    </div>
                                </a>
                                <a href="riwayat_pinjam.php" class="btn btn-light border text-start p-3 rounded-3 d-flex align-items-center shadow-sm">
                                    <i class="bi bi-clock-history fs-4 text-primary me-3"></i>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark">Riwayat Peminjaman</h6>
                                        <small class="text-muted">Lihat daftar buku yang pernah dipinjam.</small>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>