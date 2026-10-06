<?php

session_start();

include '../koneksi.php';

// =====================================================
// VALIDASI LOGIN
// =====================================================

if (!isset($_SESSION['user_login']) || $_SESSION['user_login'] !== true) {

    header("Location: login.php");
    exit;

}

// =====================================================
// AMBIL DATA USER
// =====================================================

$username_user = $_SESSION['username'];

$username_esc = mysqli_real_escape_string($koneksi, $username_user);

$query_user = "
    SELECT *
    FROM users
    WHERE username = '$username_esc'
       OR nim = '$username_esc'
    LIMIT 1
";

$result_user = mysqli_query($koneksi, $query_user);

$user = mysqli_fetch_assoc($result_user);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Dashboard Mahasiswa - Perpustakaan USTJ</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet">

    <!-- Google Font -->
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">


    <style>

        :root {

            --primary: #0d6efd;
            --primary-dark: #0b5ed7;
            --background: #f5f7fb;
            --sidebar-width: 260px;
            --text-dark: #172033;
            --text-muted: #718096;
            --border: #e8edf4;

        }


        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            background: var(--background);

            color: var(--text-dark);

            font-family:
                'Plus Jakarta Sans',
                system-ui,
                sans-serif;

        }


        /* =================================================
           SIDEBAR
        ================================================= */

        .sidebar {

            width: var(--sidebar-width);

            min-height: 100vh;

            background: #ffffff;

            box-shadow:
                2px 0 15px rgba(0, 0, 0, 0.04);

            position: fixed;

            top: 0;
            left: 0;

            z-index: 1040;

            transition: all .3s ease;

        }


        .sidebar-brand {

            padding: 8px 5px 20px;

            text-align: center;

        }


        .sidebar-brand img {

            width: 55px;

            height: 55px;

            object-fit: contain;

            margin-bottom: 8px;

        }


        .sidebar-brand h5 {

            margin: 0;

            font-weight: 800;

            color: var(--primary);

        }


        .sidebar-brand small {

            color: #8a94a6;

            font-size: 10px;

        }


        .sidebar .nav-link {

            color: #596579;

            padding: 12px 16px;

            border-radius: 10px;

            margin-bottom: 6px;

            font-size: 14px;

            font-weight: 600;

            transition: all .2s ease;

        }


        .sidebar .nav-link i {

            font-size: 17px;

            width: 25px;

        }


        .sidebar .nav-link:hover {

            background: #eef5ff;

            color: var(--primary);

            transform: translateX(3px);

        }


        .sidebar .nav-link.active {

            background: linear-gradient(
                135deg,
                #0d6efd,
                #3d8bfd
            );

            color: white;

            box-shadow:
                0 5px 15px rgba(13, 110, 253, .20);

        }


        .sidebar .nav-link.active i {

            color: white !important;

        }


        .sidebar .logout-link {

            color: #dc3545 !important;

        }


        .sidebar .logout-link:hover {

            background: #fff1f2;

            color: #dc3545 !important;

        }


        /* =================================================
           MAIN CONTENT
        ================================================= */

        .main-content {

            margin-left: var(--sidebar-width);

            padding: 28px;

            min-height: 100vh;

            transition: all .3s ease;

        }


        /* =================================================
           TOP HEADER
        ================================================= */

        .top-header {

            background: #ffffff;

            border: 1px solid var(--border);

            border-radius: 16px;

            padding: 20px 24px;

            margin-bottom: 24px;

            box-shadow:
                0 5px 20px rgba(31, 45, 61, .04);

        }


        .welcome-text h4 {

            font-size: 21px;

            font-weight: 800;

            margin-bottom: 4px;

        }


        .welcome-text p {

            margin: 0;

            color: var(--text-muted);

            font-size: 13px;

        }


        .user-avatar {

            width: 48px;

            height: 48px;

            border-radius: 14px;

            background: linear-gradient(
                135deg,
                #e8f1ff,
                #d8e8ff
            );

            color: var(--primary);

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 22px;

        }


        /* =================================================
           HERO
        ================================================= */

        .hero-card {

            position: relative;

            overflow: hidden;

            border-radius: 18px;

            padding: 28px;

            color: white;

            background:
                linear-gradient(
                    135deg,
                    #0d6efd,
                    #084298
                );

            box-shadow:
                0 12px 30px rgba(13, 110, 253, .20);

            margin-bottom: 24px;

        }


        .hero-card::before {

            content: "";

            position: absolute;

            width: 180px;

            height: 180px;

            border-radius: 50%;

            background: rgba(255,255,255,.08);

            right: -50px;

            top: -60px;

        }


        .hero-card::after {

            content: "";

            position: absolute;

            width: 100px;

            height: 100px;

            border-radius: 50%;

            background: rgba(255,255,255,.06);

            right: 100px;

            bottom: -50px;

        }


        .hero-content {

            position: relative;

            z-index: 2;

        }


        .hero-card h3 {

            font-weight: 800;

            margin-bottom: 7px;

        }


        .hero-card p {

            opacity: .88;

            font-size: 13px;

            max-width: 600px;

            margin-bottom: 18px;

        }


        .hero-btn {

            background: white;

            color: var(--primary);

            border: none;

            border-radius: 9px;

            padding: 10px 18px;

            font-size: 13px;

            font-weight: 700;

        }


        .hero-btn:hover {

            background: #f1f5ff;

            color: var(--primary-dark);

        }


        /* =================================================
           STATISTIC
        ================================================= */

        .stat-card {

            background: white;

            border: 1px solid var(--border);

            border-radius: 15px;

            padding: 20px;

            height: 100%;

            box-shadow:
                0 5px 18px rgba(31, 45, 61, .035);

            transition: all .25s ease;

        }


        .stat-card:hover {

            transform: translateY(-4px);

            box-shadow:
                0 10px 25px rgba(31, 45, 61, .08);

        }


        .stat-icon {

            width: 48px;

            height: 48px;

            border-radius: 12px;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 21px;

        }


        .stat-blue {

            background: #eaf2ff;

            color: #0d6efd;

        }


        .stat-green {

            background: #eafaf0;

            color: #198754;

        }


        .stat-orange {

            background: #fff4e5;

            color: #fd7e14;

        }


        .stat-purple {

            background: #f1edff;

            color: #6f42c1;

        }


        .stat-label {

            color: var(--text-muted);

            font-size: 12px;

            margin-bottom: 4px;

        }


        .stat-number {

            font-size: 25px;

            font-weight: 800;

            margin: 0;

        }


        /* =================================================
           CONTENT CARD
        ================================================= */

        .content-card {

            background: white;

            border: 1px solid var(--border);

            border-radius: 16px;

            padding: 22px;

            height: 100%;

            box-shadow:
                0 5px 18px rgba(31, 45, 61, .035);

        }


        .section-title {

            font-size: 16px;

            font-weight: 800;

            margin-bottom: 5px;

        }


        .section-subtitle {

            font-size: 12px;

            color: var(--text-muted);

        }


        /* =================================================
           PROFILE
        ================================================= */

        .profile-avatar {

            width: 82px;

            height: 82px;

            margin: 0 auto 15px;

            border-radius: 22px;

            background: linear-gradient(
                135deg,
                #e8f1ff,
                #d7e7ff
            );

            color: var(--primary);

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 38px;

        }


        .profile-name {

            font-weight: 800;

            font-size: 17px;

            margin-bottom: 4px;

        }


        .profile-nim {

            color: var(--text-muted);

            font-size: 12px;

        }


        .profile-info {

            padding-top: 15px;

            margin-top: 15px;

            border-top: 1px solid var(--border);

        }


        .profile-info-item {

            display: flex;

            align-items: center;

            gap: 10px;

            margin-bottom: 13px;

            font-size: 12px;

        }


        .profile-info-item:last-child {

            margin-bottom: 0;

        }


        .profile-info-icon {

            width: 32px;

            height: 32px;

            border-radius: 9px;

            background: #eef5ff;

            color: var(--primary);

            display: flex;

            align-items: center;

            justify-content: center;

        }


        /* =================================================
           QUICK MENU
        ================================================= */

        .quick-menu {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 12px;

        }


        .quick-item {

            text-decoration: none;

            color: var(--text-dark);

            border: 1px solid var(--border);

            border-radius: 12px;

            padding: 16px;

            transition: all .2s ease;

        }


        .quick-item:hover {

            border-color: #b9d3ff;

            background: #f8fbff;

            transform: translateY(-2px);

        }


        .quick-icon {

            width: 38px;

            height: 38px;

            border-radius: 10px;

            background: #eef5ff;

            color: var(--primary);

            display: flex;

            align-items: center;

            justify-content: center;

            margin-bottom: 10px;

        }


        .quick-item h6 {

            font-weight: 700;

            font-size: 13px;

            margin-bottom: 4px;

        }


        .quick-item small {

            color: var(--text-muted);

            font-size: 10px;

        }


        /* =================================================
           STATUS
        ================================================= */

        .status-badge {

            display: inline-flex;

            align-items: center;

            gap: 5px;

            padding: 6px 10px;

            border-radius: 20px;

            background: #eafaf0;

            color: #198754;

            font-size: 11px;

            font-weight: 700;

        }


        /* =================================================
           MOBILE
        ================================================= */

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


            .top-header {

                padding: 16px;

            }


            .hero-card {

                padding: 22px;

            }

        }


        @media (max-width: 575.98px) {

            .quick-menu {

                grid-template-columns: 1fr;

            }


            .hero-card h3 {

                font-size: 20px;

            }


            .welcome-text h4 {

                font-size: 17px;

            }

        }


    </style>

</head>


<body>


<!-- =====================================================
     MOBILE TOP BAR
===================================================== -->

<div class="d-lg-none bg-white p-3 shadow-sm
            d-flex justify-content-between align-items-center
            sticky-top">

    <div class="d-flex align-items-center">

        <button
            class="btn btn-outline-primary me-2"
            type="button"
            data-bs-toggle="offcanvas"
            data-bs-target="#sidebarMobile">

            <i class="bi bi-list fs-5"></i>

        </button>

        <span class="fw-bold text-primary">
            Perpus USTJ
        </span>

    </div>

    <span class="small text-muted">

        <?= htmlspecialchars($_SESSION['username']); ?>

    </span>

</div>


<div class="container-fluid p-0">


<!-- =====================================================
     SIDEBAR DESKTOP
===================================================== -->

<div class="sidebar p-3 d-none d-lg-flex
            flex-column justify-content-between">

    <div>

        <div class="sidebar-brand">

            <img
                src="../assets/img/logo.png"
                alt="Logo USTJ">

            <h5>
                Perpus USTJ
            </h5>

            <small>
                Universitas Sains & Teknologi Jayapura
            </small>

        </div>


        <hr class="text-muted">


        <ul class="nav flex-column">


            <li class="nav-item">

                <a
                    href="profil_user.php"
                    class="nav-link active">

                    <i class="bi bi-grid-1x2-fill me-2"></i>

                    Dashboard

                </a>

            </li>


            <li class="nav-item">

                <a
                    href="katalog_pinjam.php"
                    class="nav-link">

                    <i class="bi bi-book-half me-2"></i>

                    Peminjaman Mandiri

                </a>

            </li>


            <li class="nav-item">

                <a
                    href="riwayat_pinjam.php"
                    class="nav-link">

                    <i class="bi bi-clock-history me-2"></i>

                    Riwayat Pinjam

                </a>

            </li>


            <li class="nav-item">

                <a
                    href="profil_edit.php"
                    class="nav-link">

                    <i class="bi bi-person-gear me-2"></i>

                    Pengaturan Profil

                </a>

            </li>

        </ul>

    </div>


    <div>

        <hr class="text-muted">


        <a
            href="logout.php"
            class="nav-link logout-link">

            <i class="bi bi-box-arrow-right me-2"></i>

            Keluar Akun

        </a>

    </div>

</div>


<!-- =====================================================
     SIDEBAR MOBILE
===================================================== -->

<div
    class="offcanvas offcanvas-start"
    tabindex="-1"
    id="sidebarMobile">

    <div class="offcanvas-header">

        <h5 class="offcanvas-title fw-bold text-primary">

            <i class="bi bi-book-half me-2"></i>

            Perpus USTJ

        </h5>

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="offcanvas">
        </button>

    </div>


    <div class="offcanvas-body d-flex
                flex-column justify-content-between">

        <ul class="nav flex-column">


            <li class="nav-item">

                <a
                    href="profil_user.php"
                    class="nav-link active">

                    <i class="bi bi-grid-1x2-fill me-2"></i>

                    Dashboard

                </a>

            </li>


            <li class="nav-item">

                <a
                    href="katalog_pinjam.php"
                    class="nav-link">

                    <i class="bi bi-book-half me-2"></i>

                    Peminjaman Mandiri

                </a>

            </li>


            <li class="nav-item">

                <a
                    href="riwayat_pinjam.php"
                    class="nav-link">

                    <i class="bi bi-clock-history me-2"></i>

                    Riwayat Pinjam

                </a>

            </li>


            <li class="nav-item">

                <a
                    href="profil_edit.php"
                    class="nav-link">

                    <i class="bi bi-person-gear me-2"></i>

                    Pengaturan Profil

                </a>

            </li>

        </ul>


        <a
            href="logout.php"
            class="nav-link text-danger">

            <i class="bi bi-box-arrow-right me-2"></i>

            Keluar Akun

        </a>

    </div>

</div>


<!-- =====================================================
     MAIN CONTENT
===================================================== -->

<div class="main-content">


    <!-- =================================================
         TOP HEADER
    ================================================= -->

    <div class="top-header
                d-flex justify-content-between
                align-items-center">

        <div class="welcome-text">

            <h4>
                Dashboard Mahasiswa
            </h4>

            <p>

                Selamat datang kembali,
                
                <strong>
                    <?= htmlspecialchars(
                        $user['nama_lengkap']
                        ?? $_SESSION['username']
                    ); ?>
                </strong>

                👋

            </p>

        </div>


        <div class="d-flex align-items-center">

            <div class="user-avatar me-2">

                <i class="bi bi-person-fill"></i>

            </div>


            <div class="d-none d-sm-block">

                <div class="fw-bold"
                     style="font-size:13px;">

                    <?= htmlspecialchars(
                        $user['nama_lengkap']
                        ?? $_SESSION['username']
                    ); ?>

                </div>

                <small class="text-muted">

                    NIM:
                    <?= htmlspecialchars(
                        $user['nim'] ?? '-'
                    ); ?>

                </small>

            </div>

        </div>

    </div>


    <!-- =================================================
         HERO
    ================================================= -->

    <div class="hero-card">

        <div class="hero-content">

            <h3>

                Selamat Datang di
                Perpustakaan USTJ 📚

            </h3>

            <p>

                Temukan koleksi buku, lakukan peminjaman
                secara mandiri, dan pantau riwayat
                peminjaman Anda dengan mudah.

            </p>


            <a
                href="katalog_pinjam.php"
                class="btn hero-btn">

                <i class="bi bi-search me-2"></i>

                Jelajahi Koleksi Buku

            </a>

        </div>

    </div>


    <!-- =================================================
         STATISTIK
    ================================================= -->

    <div class="row g-3 mb-4">


        <div class="col-6 col-xl-3">

            <div class="stat-card">

                <div class="d-flex
                            justify-content-between
                            align-items-start">

                    <div>

                        <div class="stat-label">

                            Buku Dipinjam

                        </div>

                        <h3 class="stat-number">

                            0

                        </h3>

                    </div>


                    <div class="stat-icon stat-blue">

                        <i class="bi bi-journal-bookmark-fill"></i>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-6 col-xl-3">

            <div class="stat-card">

                <div class="d-flex
                            justify-content-between
                            align-items-start">

                    <div>

                        <div class="stat-label">

                            Buku Selesai

                        </div>

                        <h3 class="stat-number">

                            0

                        </h3>

                    </div>


                    <div class="stat-icon stat-green">

                        <i class="bi bi-check-circle-fill"></i>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-6 col-xl-3">

            <div class="stat-card">

                <div class="d-flex
                            justify-content-between
                            align-items-start">

                    <div>

                        <div class="stat-label">

                            Reservasi

                        </div>

                        <h3 class="stat-number">

                            0

                        </h3>

                    </div>


                    <div class="stat-icon stat-orange">

                        <i class="bi bi-bookmark-star-fill"></i>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-6 col-xl-3">

            <div class="stat-card">

                <div class="d-flex
                            justify-content-between
                            align-items-start">

                    <div>

                        <div class="stat-label">

                            Status Anggota

                        </div>

                        <h6 class="mt-2 mb-0">

                            <span class="status-badge">

                                <i class="bi bi-check-circle-fill"></i>

                                Aktif

                            </span>

                        </h6>

                    </div>


                    <div class="stat-icon stat-purple">

                        <i class="bi bi-person-check-fill"></i>

                    </div>

                </div>

            </div>

        </div>


    </div>


    <!-- =================================================
         BOTTOM CONTENT
    ================================================= -->

    <div class="row g-4">


        <!-- PROFIL -->

        <div class="col-lg-5">

            <div class="content-card text-center">

                <div class="section-title text-start">

                    Profil Anggota

                </div>

                <div class="section-subtitle text-start mb-4">

                    Informasi akun perpustakaan Anda

                </div>


                <div class="profile-avatar">

                    <i class="bi bi-person-fill"></i>

                </div>


                <div class="profile-name">

                    <?= htmlspecialchars(
                        $user['nama_lengkap']
                        ?? 'Mahasiswa USTJ'
                    ); ?>

                </div>


                <div class="profile-nim">

                    NIM:
                    <?= htmlspecialchars(
                        $user['nim'] ?? '-'
                    ); ?>

                </div>


                <div class="profile-info text-start">


                    <div class="profile-info-item">

                        <div class="profile-info-icon">

                            <i class="bi bi-envelope"></i>

                        </div>

                        <div>

                            <small class="text-muted d-block">
                                Email
                            </small>

                            <?= htmlspecialchars(
                                $user['email'] ?? '-'
                            ); ?>

                        </div>

                    </div>


                    <div class="profile-info-item">

                        <div class="profile-info-icon">

                            <i class="bi bi-person-badge"></i>

                        </div>

                        <div>

                            <small class="text-muted d-block">
                                Username
                            </small>

                            <?= htmlspecialchars(
                                $user['username'] ?? '-'
                            ); ?>

                        </div>

                    </div>


                    <div class="profile-info-item">

                        <div class="profile-info-icon">

                            <i class="bi bi-shield-check"></i>

                        </div>

                        <div>

                            <small class="text-muted d-block">
                                Status Anggota
                            </small>

                            <span class="status-badge">

                                <i class="bi bi-check-circle-fill"></i>

                                Aktif

                            </span>

                        </div>

                    </div>


                </div>


                <a
                    href="profil_edit.php"
                    class="btn btn-outline-primary
                           w-100 mt-4">

                    <i class="bi bi-pencil-square me-2"></i>

                    Edit Profil

                </a>

            </div>

        </div>


        <!-- AKSES CEPAT -->

        <div class="col-lg-7">

            <div class="content-card">

                <div class="section-title">

                    Akses Cepat

                </div>

                <div class="section-subtitle mb-4">

                    Gunakan fitur perpustakaan yang tersedia

                </div>


                <div class="quick-menu">


                    <a
                        href="katalog_pinjam.php"
                        class="quick-item">

                        <div class="quick-icon">

                            <i class="bi bi-search"></i>

                        </div>

                        <h6>

                            Cari & Pinjam Buku

                        </h6>

                        <small>

                            Temukan buku yang tersedia
                            di perpustakaan.

                        </small>

                    </a>


                    <a
                        href="riwayat_pinjam.php"
                        class="quick-item">

                        <div class="quick-icon">

                            <i class="bi bi-clock-history"></i>

                        </div>

                        <h6>

                            Riwayat Peminjaman

                        </h6>

                        <small>

                            Lihat daftar buku yang
                            pernah dipinjam.

                        </small>

                    </a>


                    <a
                        href="profil_edit.php"
                        class="quick-item">

                        <div class="quick-icon">

                            <i class="bi bi-person-gear"></i>

                        </div>

                        <h6>

                            Pengaturan Profil

                        </h6>

                        <small>

                            Perbarui informasi akun Anda.

                        </small>

                    </a>


                    <a
                        href="katalog_pinjam.php"
                        class="quick-item">

                        <div class="quick-icon">

                            <i class="bi bi-book-half"></i>

                        </div>

                        <h6>

                            Katalog Buku

                        </h6>

                        <small>

                            Jelajahi koleksi buku USTJ.

                        </small>

                    </a>


                </div>


                <div class="mt-4 p-3 rounded-3"
                     style="background:#f8fbff;">

                    <div class="d-flex align-items-center">

                        <div class="quick-icon mb-0 me-3">

                            <i class="bi bi-info-circle"></i>

                        </div>

                        <div>

                            <div class="fw-bold"
                                 style="font-size:13px;">

                                Tips Peminjaman

                            </div>

                            <small class="text-muted">

                                Pastikan buku dikembalikan
                                sesuai dengan batas waktu
                                peminjaman.

                            </small>

                        </div>

                    </div>

                </div>

            </div>

        </div>


    </div>


</div>

</div>


<!-- =====================================================
     BOOTSTRAP JS
===================================================== -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>