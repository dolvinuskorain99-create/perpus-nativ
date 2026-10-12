<?php
// =====================================================
// SISTEM INFORMASI PERPUSTAKAAN USTJ
// KOMPONEN: SIDEBAR & NAVBAR MOBILE ANGGOTA
// FILE: frontend/anggota/sidebar.php
// =====================================================

$page_aktif = basename($_SERVER['PHP_SELF']);

// Ambil data user dari session dan database secara aman
$id_user_sb = (int)($_SESSION['id_user'] ?? $_SESSION['user_id'] ?? $_SESSION['id'] ?? 0);
$foto_sb = '';
$nama_sb = $_SESSION['nama_lengkap'] ?? 'Mahasiswa';
$nim_sb  = $_SESSION['nim'] ?? '';

if ($id_user_sb > 0 && isset($koneksi)) {
    $q_sb = mysqli_query($koneksi, "SELECT foto, nim, nama_lengkap FROM users WHERE id_user = $id_user_sb LIMIT 1");
    if ($q_sb && mysqli_num_rows($q_sb) > 0) {
        $r_sb = mysqli_fetch_assoc($q_sb);
        $foto_sb = $r_sb['foto'] ?? '';
        if (!empty($r_sb['nim'])) $nim_sb = $r_sb['nim'];
        if (!empty($r_sb['nama_lengkap'])) $nama_sb = $r_sb['nama_lengkap'];
    }
}
?>

<!-- CSS KHUSUS RESPONSIF SIDEBAR MOBILE -->
<style>
    /* Styling Sidebar Desktop */
    .sidebar-wrapper {
        width: 260px;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        background-color: #212529;
        color: #ffffff;
        z-index: 1040;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 20px 15px;
        transition: all 0.3s ease;
    }

    .sidebar-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        padding-bottom: 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .sidebar-brand img {
        width: 40px;
        height: 40px;
        object-fit: contain;
    }

    .sidebar-user {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 12px;
        margin: 15px 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .sidebar-menu .nav-link {
        color: #adb5bd;
        font-weight: 500;
        padding: 12px 16px;
        border-radius: 10px;
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: all 0.2s;
    }

    .sidebar-menu .nav-link:hover,
    .sidebar-menu .nav-link.active {
        color: #ffffff;
        background-color: #0d6efd;
    }

    /* Top Navbar Mobile Trigger */
    .mobile-navbar {
        display: none;
        background: #212529;
        color: #ffffff;
        padding: 12px 20px;
        position: sticky;
        top: 0;
        z-index: 1030;
    }

    /* Sembunyikan sidebar biasa di layar HP (< 768px) dan gunakan Offcanvas */
    @media (max-width: 768px) {
        .sidebar-wrapper {
            display: none;
        }

        .mobile-navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .main-content {
            margin-left: 0 !important;
            padding: 15px !important;
        }
    }
</style>

<!-- TOP NAVBAR KHUSUS UNTUK LAYAR HP (MOBILE) -->
<div class="mobile-navbar shadow-sm align-items-center">
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-light btn-sm border-0 fs-4 px-2 py-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar" aria-controls="offcanvasSidebar">
            <i class="bi bi-list"></i>
        </button>
        <span class="fw-bold fs-6">Perpus USTJ</span>
    </div>
    <div class="d-flex align-items-center">
        <?php if (!empty($foto_sb) && file_exists('../../assets/img/profil/' . $foto_sb)): ?>
            <img src="../../assets/img/profil/<?= htmlspecialchars($foto_sb); ?>" alt="User" class="rounded-circle border" style="width: 32px; height: 32px; object-fit: cover;">
        <?php else: ?>
            <i class="bi bi-person-circle fs-4 text-primary"></i>
        <?php endif; ?>
    </div>
</div>

<!-- SIDEBAR UNTUK DESKTOP / LAPTOP -->
<aside class="sidebar-wrapper">
    <div>
        <!-- Brand / Logo Gambar Tanpa Background Putih -->
        <div class="sidebar-brand">
            <img src="../../assets/img/logo.png" alt="Logo USTJ">
            <div>
                <h6 class="fw-bold mb-0 text-white">Perpus USTJ</h6>
                <small class="text-white-50" style="font-size: 11px;">Universitas Sains & Teknologi</small>
            </div>
        </div>

        <!-- Profil Ringkas -->
        <div class="sidebar-user">
            <?php if (!empty($foto_sb) && file_exists('../../assets/img/profil/' . $foto_sb)): ?>
                <img src="../../assets/img/profil/<?= htmlspecialchars($foto_sb); ?>" alt="User" class="rounded-circle border border-2 border-primary" style="width: 40px; height: 40px; object-fit: cover;">
            <?php else: ?>
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px; font-size: 1rem;">
                    <i class="bi bi-person-fill"></i>
                </div>
            <?php endif; ?>
            <div class="overflow-hidden">
                <span class="d-block fw-bold text-white text-truncate small"><?= htmlspecialchars($nama_sb); ?></span>
                <span class="text-white-50 d-block" style="font-size: 11px;">NIM: <?= htmlspecialchars($nim_sb); ?></span>
            </div>
        </div>

        <!-- Menu Navigasi Utama -->
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="nav-link <?= ($page_aktif == 'dashboard.php') ? 'active' : ''; ?>">
                <i class="bi bi-grid-fill"></i>
                <span>Dashboard</span>
            </a>
            <a href="peminjaman_mandiri.php" class="nav-link <?= ($page_aktif == 'peminjaman_mandiri.php') ? 'active' : ''; ?>">
                <i class="bi bi-journal-bookmark-fill"></i>
                <span>Peminjaman Mandiri</span>
            </a>
            <a href="riwayat_pinjam.php" class="nav-link <?= ($page_aktif == 'riwayat_pinjam.php') ? 'active' : ''; ?>">
                <i class="bi bi-clock-history"></i>
                <span>Riwayat Pinjam</span>
            </a>
            <a href="profil.php" class="nav-link <?= ($page_aktif == 'profil.php') ? 'active' : ''; ?>">
                <i class="bi bi-person-gear"></i>
                <span>Pengaturan Profil</span>
            </a>
        </nav>
    </div>

    <!-- Tombol Keluar / Logout -->
    <div>
        <a href="../logout.php" class="btn btn-outline-danger w-100 rounded-3 d-flex align-items-center justify-content-center gap-2" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
            <i class="bi bi-box-arrow-right"></i>
            <span>Keluar Akun</span>
        </a>
    </div>
</aside>

<!-- SIDEBAR OFFCANVAS KHUSUS MOBILE -->
<div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="offcanvasSidebar" aria-labelledby="offcanvasSidebarLabel" style="width: 280px;">
    <div class="offcanvas-header border-bottom border-secondary">
        <h5 class="offcanvas-title fw-bold d-flex align-items-center gap-2" id="offcanvasSidebarLabel">
            <img src="../../assets/img/logo.png" alt="Logo" style="width: 32px; height: 32px; object-fit: contain;">
            <span>Perpus USTJ</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column justify-content-between p-3">
        <div>
            <!-- Profil Ringkas Offcanvas -->
            <div class="sidebar-user my-2">
                <?php if (!empty($foto_sb) && file_exists('../../assets/img/profil/' . $foto_sb)): ?>
                    <img src="../../assets/img/profil/<?= htmlspecialchars($foto_sb); ?>" alt="User" class="rounded-circle border border-2 border-primary" style="width: 40px; height: 40px; object-fit: cover;">
                <?php else: ?>
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px; font-size: 1rem;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                <?php endif; ?>
                <div class="overflow-hidden">
                    <span class="d-block fw-bold text-white text-truncate small"><?= htmlspecialchars($nama_sb); ?></span>
                    <span class="text-white-50 d-block" style="font-size: 11px;">NIM: <?= htmlspecialchars($nim_sb); ?></span>
                </div>
            </div>

            <hr class="border-secondary my-3">

            <!-- Navigasi Menu Offcanvas -->
            <nav class="sidebar-menu">
                <a href="dashboard.php" class="nav-link <?= ($page_aktif == 'dashboard.php') ? 'active' : ''; ?>">
                    <i class="bi bi-grid-fill"></i>
                    <span>Dashboard</span>
                </a>
                <a href="peminjaman_mandiri.php" class="nav-link <?= ($page_aktif == 'peminjaman_mandiri.php') ? 'active' : ''; ?>">
                    <i class="bi bi-journal-bookmark-fill"></i>
                    <span>Peminjaman Mandiri</span>
                </a>
                <a href="riwayat_pinjam.php" class="nav-link <?= ($page_aktif == 'riwayat_pinjam.php') ? 'active' : ''; ?>">
                    <i class="bi bi-clock-history"></i>
                    <span>Riwayat Pinjam</span>
                </a>
                <a href="profil.php" class="nav-link <?= ($page_aktif == 'profil.php') ? 'active' : ''; ?>">
                    <i class="bi bi-person-gear"></i>
                    <span>Pengaturan Profil</span>
                </a>
            </nav>
        </div>

        <div>
            <a href="../logout.php" class="btn btn-outline-danger w-100 rounded-3 d-flex align-items-center justify-content-center gap-2 mt-4" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                <i class="bi bi-box-arrow-right"></i>
                <span>Keluar Akun</span>
            </a>
        </div>
    </div>
</div>