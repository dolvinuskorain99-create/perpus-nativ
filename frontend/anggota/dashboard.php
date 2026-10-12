<?php
// =====================================================
// SISTEM INFORMASI PERPUSTAKAAN USTJ
// HALAMAN: DASHBOARD ANGGOTA (DENGAN SLIDER BANNER & STATISTIK REALTIME)
// FILE: frontend/anggota/dashboard.php
// =====================================================

session_start();
require_once '../../koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_login']) || $_SESSION['user_login'] !== true) {
    header("Location: ../login.php");
    exit;
}

// Ambil ID User dari Sesi
$id_user = (int)($_SESSION['id_user'] ?? $_SESSION['user_id'] ?? $_SESSION['id'] ?? 0);

// Ambil detail user dasar
$query_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = $id_user LIMIT 1");
$data_user = mysqli_fetch_assoc($query_user);

$nim = $data_user['nim'] ?? ($_SESSION['nim'] ?? '');
$status_anggota = strtolower($data_user['status'] ?? 'aktif');

// Cari id_anggota dan nama dari tabel anggota berdasarkan NIM atau relasi
$id_anggota = 0;
$nama_anggota_db = '';
if (!empty($nim)) {
    $q_ang = mysqli_query($koneksi, "SELECT * FROM anggota WHERE nim = '$nim' LIMIT 1");
    if ($q_ang && mysqli_num_rows($q_ang) > 0) {
        $r_ang = mysqli_fetch_assoc($q_ang);
        $id_anggota = (int)($r_ang['id_anggota'] ?? 0);
        // Cek nama kolom apa saja yang mungkin ada untuk nama di tabel anggota
        $nama_anggota_db = $r_ang['nama_lengkap'] ?? ($r_ang['nama'] ?? ($r_ang['nama_anggota'] ?? ''));
        $foto = $data_user['foto'] ?? '';
    }
}

$nama_lengkap = !empty($nama_anggota_db) ? $nama_anggota_db : ($_SESSION['nama_lengkap'] ?? 'Mahasiswa');

// =====================================================
// SINKRONISASI HITUNG STATISTIK DARI DATABASE
// =====================================================

// 1. Hitung Buku Sedang Dipinjam
$buku_dipinjam = 0;
if ($id_anggota > 0) {
    $q_pinjam = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM peminjaman WHERE id_anggota = $id_anggota AND LOWER(status) = 'dipinjam'");
    if ($q_pinjam) {
        $buku_dipinjam = (int)mysqli_fetch_assoc($q_pinjam)['total'];
    }
}

// 2. Hitung Buku Selesai Dikembalikan
$buku_selesai = 0;
if ($id_anggota > 0) {
    $q_selesai = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM peminjaman WHERE id_anggota = $id_anggota AND (LOWER(status) = 'dikembalikan' OR LOWER(status) = 'kembali')");
    if ($q_selesai) {
        $buku_selesai = (int)mysqli_fetch_assoc($q_selesai)['total'];
    }
}

// 3. Reservasi
$reservasi = 0;

// 4. Hitung Total Koleksi Buku di Perpustakaan
$total_buku = 0;
$q_buku = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM buku");
if ($q_buku) {
    $total_buku = (int)mysqli_fetch_assoc($q_buku)['total'];
}

// 5. Hitung Total Skripsi / Tugas Akhir
$total_skripsi = 0;
$check_skripsi_tbl = mysqli_query($koneksi, "SHOW TABLES LIKE 'skripsi'");
if ($check_skripsi_tbl && mysqli_num_rows($check_skripsi_tbl) > 0) {
    $q_skripsi = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM skripsi");
    $total_skripsi = (int)mysqli_fetch_assoc($q_skripsi)['total'];
} else {
    $q_skripsi = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM buku WHERE judul_buku LIKE '%Skripsi%' OR judul_buku LIKE '%Tugas Akhir%'");
    if ($q_skripsi) {
        $total_skripsi = (int)mysqli_fetch_assoc($q_skripsi)['total'];
    }
}
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
        .main-content {
            margin-left: 260px;
            padding: 35px;
        }
        .card-custom {
            border: none;
            border-radius: 16px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.04);
            background: #ffffff;
        }
        .banner-card {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            border-radius: 20px;
            overflow: hidden;
            position: relative;
        }
        .carousel-item img {
            height: 220px;
            object-fit: cover;
            border-radius: 15px;
            opacity: 0.85;
        }
        .stat-badge {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            padding: 10px 18px;
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
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div>
                    <h3 class="fw-bold text-dark mb-1">Dashboard Mahasiswa</h3>
                    <p class="text-muted mb-0">Selamat datang kembali, <strong><?= htmlspecialchars($nama_lengkap); ?></strong> 👋</p>
                </div>
                <div class="d-flex align-items-center bg-white p-2 px-3 rounded-pill shadow-sm border">
    <?php if (!empty($foto) && file_exists('../../assets/img/profil/' . $foto)): ?>
        <img src="../../assets/img/profil/<?= htmlspecialchars($foto); ?>" alt="Foto Profil" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
    <?php else: ?>
        <i class="bi bi-person-circle fs-4 text-primary me-2"></i>
    <?php endif; ?>
    <div>
        <span class="d-block fw-bold small text-dark"><?= htmlspecialchars($nama_lengkap); ?></span>
        <span class="text-muted" style="font-size: 11px;">NIM: <?= htmlspecialchars($nim); ?></span>
    </div>
</div>
            </div>

            <!-- Banner Utama dengan Carousel Animasi Foto & Statistik Total Buku -->
            <div class="card banner-card border-0 shadow-sm text-white mb-4 p-3 p-md-4">
                <div class="row align-items-center g-4">
                    <div class="col-lg-7">
                        <span class="badge bg-white text-primary px-3 py-2 rounded-pill fw-bold mb-3 shadow-sm">
                            <i class="bi bi-stars me-1"></i> Perpustakaan Digital USTJ
                        </span>
                        <h2 class="fw-bold display-6 mb-2">Selamat Datang di Perpustakaan USTJ 📚</h2>
                        <p class="lead fs-6 mb-4 text-white-50">Temukan koleksi buku ilmiah, cetak, hingga skripsi. Lakukan peminjaman secara mandiri dan pantau status transaksi Anda.</p>
                        
                        <!-- Badge Total Buku & Skripsi di Dalam Card Biru -->
                        <div class="d-flex flex-wrap gap-3 mb-4">
                            <div class="stat-badge d-flex align-items-center">
                                <i class="bi bi-journal-bookmark-fill fs-3 text-warning me-3"></i>
                                <div>
                                    <div class="fw-bold fs-5 text-white"><?= number_format($total_buku); ?> Buku</div>
                                    <div class="small text-white-50">Total Koleksi Perpustakaan</div>
                                </div>
                            </div>
                            <div class="stat-badge d-flex align-items-center">
                                <i class="bi bi-file-earmark-text-fill fs-3 text-info me-3"></i>
                                <div>
                                    <div class="fw-bold fs-5 text-white"><?= number_format($total_skripsi); ?> Skripsi</div>
                                    <div class="small text-white-50">Karya Ilmiah / Tugas Akhir</div>
                                </div>
                            </div>
                        </div>

                        <a href="peminjaman_mandiri.php" class="btn btn-light text-primary fw-bold px-4 py-2 rounded-pill shadow-sm">
                            <i class="bi bi-search me-1"></i> Jelajahi & Pinjam Buku
                        </a>
                    </div>

                    <!-- Slide Animasi Foto Gambar Perpustakaan -->
                    <div class="col-lg-5 d-none d-lg-block">
                        <div id="dashboardCarousel" class="carousel slide carousel-fade shadow-lg rounded-4 overflow-hidden" data-bs-ride="carousel" data-bs-interval="3500">
                            <div class="carousel-inner">
                                <div class="carousel-item active">
                                    <img src="https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=800&q=80" class="d-block w-100" alt="Perpustakaan 1">
                                </div>
                                <div class="carousel-item">
                                    <img src="https://images.unsplash.com/photo-1481627834876-b7833e8f5570?auto=format&fit=crop&w=800&q=80" class="d-block w-100" alt="Perpustakaan 2">
                                </div>
                                <div class="carousel-item">
                                    <img src="https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&fit=crop&w=800&q=80" class="d-block w-100" alt="Perpustakaan 3">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kartu Statistik Aktivitas Peminjaman -->
            <div class="row g-3 mb-4">
                <!-- Buku Dipinjam -->
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm h-100 rounded-4">
                        <div class="card-body d-flex justify-content-between align-items-center p-4">
                            <div>
                                <span class="text-muted small d-block mb-1 fw-semibold">Buku Dipinjam</span>
                                <h3 class="fw-bold mb-0 text-dark"><?= $buku_dipinjam; ?></h3>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded-4 text-primary">
                                <i class="bi bi-book-fill fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Buku Selesai -->
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm h-100 rounded-4">
                        <div class="card-body d-flex justify-content-between align-items-center p-4">
                            <div>
                                <span class="text-muted small d-block mb-1 fw-semibold">Buku Selesai</span>
                                <h3 class="fw-bold mb-0 text-dark"><?= $buku_selesai; ?></h3>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded-4 text-success">
                                <i class="bi bi-check-circle-fill fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reservasi -->
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm h-100 rounded-4">
                        <div class="card-body d-flex justify-content-between align-items-center p-4">
                            <div>
                                <span class="text-muted small d-block mb-1 fw-semibold">Reservasi</span>
                                <h3 class="fw-bold mb-0 text-dark"><?= $reservasi; ?></h3>
                            </div>
                            <div class="bg-warning bg-opacity-10 p-3 rounded-4 text-warning">
                                <i class="bi bi-bookmark-fill fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Anggota -->
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm h-100 rounded-4">
                        <div class="card-body d-flex justify-content-between align-items-center p-4">
                            <div>
                                <span class="text-muted small d-block mb-1 fw-semibold">Status Anggota</span>
                                <?php if ($status_anggota === 'aktif'): ?>
                                    <span class="badge bg-success text-white px-3 py-2 rounded-pill fw-bold shadow-sm">
                                        <i class="bi bi-check-circle me-1"></i> Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold shadow-sm">
                                        <i class="bi bi-clock me-1"></i> Pending
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="bg-info bg-opacity-10 p-3 rounded-4 text-info">
                                <i class="bi bi-person-badge-fill fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bagian Profil & Akses Cepat -->
            <div class="row g-4">
                <!-- Profil Anggota Ringkas -->
               <!-- Profil Anggota Ringkas -->
<div class="col-lg-6">
    <div class="card card-custom p-4 h-100">
        <h5 class="fw-bold text-dark mb-1">Profil Anggota</h5>
        <p class="text-muted small mb-4">Informasi akun perpustakaan Anda yang terdaftar.</p>
        
        <div class="text-center py-3">
            <div class="mb-3 d-flex justify-content-center">
                <?php if (!empty($foto) && file_exists('../../assets/img/profil/' . $foto)): ?>
                    <img src="../../assets/img/profil/<?= htmlspecialchars($foto); ?>" alt="Foto Profil" class="rounded-circle shadow-sm border" style="width: 90px; height: 90px; object-fit: cover;">
                <?php else: ?>
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm fw-bold" style="width: 90px; height: 90px; font-size: 2rem;">
                        <?= $inisial; ?>
                    </div>
                <?php endif; ?>
            </div>
            <h4 class="fw-bold text-dark mb-1"><?= htmlspecialchars($nama_lengkap); ?></h4>
            <p class="text-muted mb-0 fw-semibold">NIM: <?= htmlspecialchars($nim); ?></p>
        </div>
    </div>
</div>

                <!-- Akses Cepat -->
                <div class="col-lg-6">
                    <div class="card card-custom p-4 h-100">
                        <h5 class="fw-bold text-dark mb-1">Akses Cepat</h5>
                        <p class="text-muted small mb-4">Gunakan fitur perpustakaan yang tersedia dengan cepat.</p>
                        
                        <div class="d-grid gap-3">
                            <a href="peminjaman_mandiri.php" class="btn btn-light border text-start p-3 rounded-4 d-flex align-items-center shadow-sm">
                                <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary me-3">
                                    <i class="bi bi-search fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">Cari & Pinjam Buku</h6>
                                    <small class="text-muted">Temukan buku yang tersedia di perpustakaan.</small>
                                </div>
                            </a>
                            <a href="riwayat_pinjam.php" class="btn btn-light border text-start p-3 rounded-4 d-flex align-items-center shadow-sm">
                                <div class="bg-info bg-opacity-10 p-3 rounded-circle text-info me-3">
                                    <i class="bi bi-clock-history fs-4"></i>
                                </div>
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

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>