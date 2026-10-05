<?php
session_start();
include '../koneksi.php';

// Validasi: Pastikan yang login adalah pengguna/mahasiswa
if (!isset($_SESSION['user_login']) || $_SESSION['user_login'] !== true) {
    header("Location: login.php");
    exit;
}

// Ambil data terbaru pengguna dari database
$username_user = $_SESSION['username'];
$query_user = "SELECT * FROM users WHERE username = '$username_user' OR nim = '$username_user' LIMIT 1";
$result_user = mysqli_query($koneksi, $query_user);
$user = mysqli_fetch_assoc($result_user);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa - Perpustakaan USTJ</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f7f6;
        }
        /* Styling Sidebar Desktop & Mobile Offcanvas/Toggle */
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: #ffffff;
            box-shadow: 2px 0 10px rgba(0,0,0,0.03);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1040;
            transition: all 0.3s ease-in-out;
        }
        .sidebar .nav-link {
            color: #495057;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 6px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: #0d6efd;
            color: #fff;
        }
        .sidebar .nav-link:hover i, .sidebar .nav-link.active i {
            color: #fff !important;
        }
        /* Konten Utama */
        .main-content {
            margin-left: 260px;
            padding: 30px;
            transition: all 0.3s ease-in-out;
        }
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
        }
        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 0.25rem 1rem rgba(0, 0, 0, 0.04);
        }
    </style>
</head>
<body>

    <!-- Tombol Toggle Sidebar untuk Mobile -->
    <div class="d-lg-none bg-white p-3 shadow-sm d-flex justify-content-between align-items-center sticky-top">
        <div class="d-flex align-items-center">
            <button class="btn btn-outline-primary me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMobile">
                <i class="bi bi-list fs-5"></i>
            </button>
            <span class="fw-bold text-primary">Perpus USTJ</span>
        </div>
        <span class="small text-muted"><?= htmlspecialchars($_SESSION['username']); ?></span>
    </div>

    <div class="container-fluid p-0">
        
        <!-- SIDEBAR UTAMA (Desktop) / Offcanvas (Mobile) -->
        <div class="sidebar p-3 d-none d-lg-flex flex-column justify-content-between">
            <div>
                <div class="text-center my-3">
                    <!-- Logo Kampus (Sesuaikan path jika logo ada di folder assets/img/) -->
                    <img src="../assets/img/logo.png" alt="Logo USTJ" style="width: 50px; height: auto;" class="mb-2 onerror-hide">
                    <h5 class="fw-bold text-primary mb-0">Perpus USTJ</h5>
                    <small class="text-muted" style="font-size: 11px;">Universitas Sains & Teknologi Jayapura</small>
                </div>
                <hr class="text-muted">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a href="profil_user.php" class="nav-link active">
                            <i class="bi bi-speedometer2 me-2 text-primary"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="katalog_pinjam.php" class="nav-link">
                            <i class="bi bi-book-half me-2 text-primary"></i> Peminjaman Mandiri
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="riwayat_pinjam.php" class="nav-link">
                            <i class="bi bi-clock-history me-2 text-primary"></i> Riwayat Pinjam
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="profil_edit.php" class="nav-link">
                            <i class="bi bi-person-gear me-2 text-primary"></i> Pengaturan Profil
                        </a>
                    </li>
                </ul>
            </div>
            <div>
                <hr class="text-muted">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a href="logout.php" class="nav-link text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i> Keluar Akun
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- SIDEBAR KHUSUS MOBILE (Drawer) -->
        <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarMobile">
            <div class="offcanvas-header">
                <div class="d-flex align-items-center">
                    <h5 class="offcanvas-title fw-bold text-primary">Perpus USTJ</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body d-flex flex-column justify-content-between">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a href="profil_user.php" class="nav-link active">
                            <i class="bi bi-speedometer2 me-2 text-primary"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="katalog_pinjam.php" class="nav-link">
                            <i class="bi bi-book-half me-2 text-primary"></i> Peminjaman Mandiri
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="riwayat_pinjam.php" class="nav-link">
                            <i class="bi bi-clock-history me-2 text-primary"></i> Riwayat Pinjam
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="profil_edit.php" class="nav-link">
                            <i class="bi bi-person-gear me-2 text-primary"></i> Pengaturan Profil
                        </a>
                    </li>
                </ul>
                <ul class="nav flex-column mb-3">
                    <li class="nav-item">
                        <a href="logout.php" class="nav-link text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i> Keluar Akun
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- KONTEN UTAMA KANAN -->
        <div class="main-content">
            <!-- Top Header / Bar Atas -->
            <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded-3 shadow-sm mb-4">
                <div>
                    <h4 class="fw-bold text-dark mb-0">Dashboard Mahasiswa</h4>
                    <small class="text-muted">Selamat datang kembali, <strong><?= htmlspecialchars($user['nama_lengkap'] ?? $_SESSION['username']); ?></strong></small>
                </div>
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                        <i class="bi bi-person-fill fs-5"></i>
                    </div>
                    <div class="d-none d-sm-block text-start">
                        <h6 class="mb-0 fw-semibold fs-6"><?= htmlspecialchars($user['nama_lengkap'] ?? $_SESSION['username']); ?></h6>
                        <small class="text-muted" style="font-size: 12px;">NIM: <?= htmlspecialchars($user['nim'] ?? '-'); ?></small>
                    </div>
                </div>
            </div>

            <!-- Kartu Statistik Atas -->
            <div class="row g-4 mb-4">
                <div class="col-sm-6 col-xl-6">
                    <div class="card card-custom p-3 bg-white border-start border-primary border-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 p-3 rounded text-primary fs-3">
                                <i class="bi bi-journal-check"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="text-muted mb-1 small">Buku Sedang Dipinjam</h6>
                                <h3 class="fw-bold mb-0">0</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-6">
                    <div class="card card-custom p-3 bg-white border-start border-success border-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-success bg-opacity-10 p-3 rounded text-success fs-3">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="text-muted mb-1 small">Total Selesai Dibaca</h6>
                                <h3 class="fw-bold mb-0">0</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bagian Informasi & Akses Fitur -->
            <div class="row g-4">
                <div class="col-md-5">
                    <div class="card card-custom p-4 bg-white h-100 text-center">
                        <div class="mb-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                <i class="bi bi-person-fill fs-2"></i>
                            </div>
                        </div>
                        <h5 class="fw-bold mb-1"><?= htmlspecialchars($user['nama_lengkap'] ?? 'Mahasiswa USTJ'); ?></h5>
                        <p class="text-muted small mb-3">NIM: <?= htmlspecialchars($user['nim'] ?? '-'); ?></p>
                        <hr class="text-muted">
                        <div class="text-start small text-secondary">
                            <p class="mb-2"><i class="bi bi-envelope me-2 text-primary"></i> <?= htmlspecialchars($user['email'] ?? '-'); ?></p>
                            <p class="mb-0"><i class="bi bi-book me-2 text-primary"></i> Status Anggota: <span class="badge bg-success">Aktif</span></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="card card-custom p-4 bg-white h-100 d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="fw-bold mb-3">Sistem Peminjaman Mandiri</h5>
                            <p class="text-muted small">Kelola peminjaman buku perpustakaan digital Universitas Sains & Teknologi Jayapura secara mandiri, cepat, dan transparan.</p>
                        </div>
                        <div>
                            <a href="katalog_pinjam.php" class="btn btn-primary w-100 py-2">
                                <i class="bi bi-search me-2"></i> Buka Katalog & Pinjam Buku
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>