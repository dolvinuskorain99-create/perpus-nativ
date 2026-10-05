<?php
// Mulai session jika belum ada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$halaman = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perpustakaan USTJ</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        *{
            font-family: 'Segoe UI', sans-serif;
        }

        body{
            background:#f8fafc;
            margin:0;
            padding:0;
        }

        /* ================= NAVBAR ================= */

        .navbar-custom{
            background:rgba(255,255,255,.95);
            backdrop-filter:blur(12px);
            border-bottom:1px solid #e5e7eb;
        }

        .navbar-brand img{
            width:50px;
            height:50px;
            object-fit:contain;
        }

        .judul{
            font-weight:700;
            font-size:16px;
            color:#0f172a;
        }

        .subjudul{
            font-size:11px;
            color:#64748b;
            margin-top:-2px;
        }

        .nav-link{
            color:#334155 !important;
            font-weight:600;
            padding:10px 16px !important;
            border-radius:50px;
            transition:.25s;
        }

        .nav-link:hover{
            background:#eff6ff;
            color:#2563eb !important;
        }

        .nav-link.active{
            background:#2563eb;
            color:#fff !important;
        }

        .btn-login{
            border-radius:30px;
            padding:10px 22px;
            font-weight:600;
        }

        .dropdown-menu{
            border:none;
            border-radius:16px;
            box-shadow:0 10px 30px rgba(0,0,0,.08);
            padding:10px;
        }

        .dropdown-item{
            border-radius:10px;
            padding:10px 14px;
        }

        .dropdown-item:hover{
            background:#eff6ff;
        }

        .user-avatar{
            width:34px;
            height:34px;
            border-radius:50%;
            background:#2563eb;
            color:#fff;
            display:flex;
            align-items:center;
            justify-content:center;
            font-weight:bold;
        }

        @media(max-width:991px){

            .navbar-collapse{
                background:#fff;
                padding:18px;
                margin-top:15px;
                border-radius:18px;
                box-shadow:0 15px 35px rgba(0,0,0,.08);
            }

            .subjudul{
                display:none;
            }

            .nav-item{
                margin-bottom:6px;
            }
        }
    </style>
</head>
<body>

<!-- ================= NAVBAR ================= -->

<nav class="navbar navbar-expand-lg navbar-custom sticky-top shadow-sm">
    <div class="container">

        <!-- LOGO -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">

            <img src="../assets/img/logo.png" alt="Logo USTJ">

            <div>
                <div class="judul">Perpustakaan USTJ</div>
                <div class="subjudul">Universitas Sains dan Teknologi Jayapura</div>
            </div>

        </a>

        <!-- TOGGLE -->
        <button class="navbar-toggler border-0 shadow-none" type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav">

            <i class="bi bi-list fs-2 text-primary"></i>

        </button>

        <!-- MENU -->
        <div class="collapse navbar-collapse" id="navbarNav">

            <ul class="navbar-nav ms-auto align-items-lg-center">

                <li class="nav-item">
                    <a class="nav-link <?= $halaman=='index.php' ? 'active' : '' ?>" href="index.php">
                        <i class="bi bi-house-door me-1"></i> Beranda
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $halaman=='katalog.php' ? 'active' : '' ?>" href="katalog.php">
                        <i class="bi bi-book me-1"></i> Katalog Buku
                    </a>
                </li>

                <li class="nav-item">
    <a class="nav-link <?= $halaman=='katalog_skripsi.php' ? 'active' : '' ?>" href="katalog_skripsi.php">
        <i class="bi bi-mortarboard me-1"></i>
        Skripsi
    </a>
</li>

                <li class="nav-item">
                    <a class="nav-link <?= $halaman=='tentang.php' ? 'active' : '' ?>" href="tentang.php">
                        <i class="bi bi-building me-1"></i> Tentang
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $halaman=='kontak.php' ? 'active' : '' ?>" href="kontak.php">
                        <i class="bi bi-envelope me-1"></i> Kontak
                    </a>
                </li>

                <?php if(isset($_SESSION['user_login']) && $_SESSION['user_login']==true): ?>

                    <!-- USER -->
                    <li class="nav-item dropdown ms-lg-2">

                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2"
                           href="#"
                           data-bs-toggle="dropdown">

                            <div class="user-avatar">
                                <?= strtoupper(substr($_SESSION['nama_lengkap'] ?? 'M',0,1)); ?>
                            </div>

                            <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Mahasiswa'); ?>

                        </a>

                        <ul class="dropdown-menu dropdown-menu-end">

                            <li>
                                <a class="dropdown-item" href="dashboard.php">
                                    <i class="bi bi-speedometer2 me-2 text-primary"></i>
                                    Dashboard
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item" href="profil_user.php">
                                    <i class="bi bi-person me-2 text-primary"></i>
                                    Profil Saya
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item" href="riwayat_pinjam.php">
                                    <i class="bi bi-clock-history me-2 text-primary"></i>
                                    Riwayat Peminjaman
                                </a>
                            </li>

                            <li><hr class="dropdown-divider"></li>

                            <li>
                                <a class="dropdown-item text-danger" href="logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i>
                                    Logout
                                </a>
                            </li>

                        </ul>

                    </li>

                <?php elseif(isset($_SESSION['admin_login']) && $_SESSION['admin_login']==true): ?>

                    <!-- ADMIN -->
                    <li class="nav-item ms-lg-3 mt-2 mt-lg-0">

                        <a href="../admin/dashboard.php" class="btn btn-dark rounded-pill px-3">

                            <i class="bi bi-speedometer2 me-1"></i>
                            Dashboard Admin

                        </a>

                    </li>

                <?php else: ?>

                    <!-- BELUM LOGIN -->
                    <li class="nav-item ms-lg-3 mt-2 mt-lg-0">

                        <a href="login.php" class="btn btn-primary btn-login w-100">

                            <i class="bi bi-box-arrow-in-right me-1"></i>
                            Login

                        </a>

                    </li>

                <?php endif; ?>

            </ul>

        </div>

    </div>
</nav>