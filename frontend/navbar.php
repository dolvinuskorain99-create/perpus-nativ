<?php
// Pastikan session sudah aktif jika dipanggil dari file lain
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <!-- Sesuaikan path logo jika diperlukan -->
            <span class="fw-bold fs-5 text-dark">Perpus USTJ</span>
            <small class="text-muted ms-2 fs-6 d-none d-sm-inline">| Universitas Sains & Teknologi Jayapura</small>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item">
                    <a class="nav-link" href="index.php"><i class="bi bi-house-door me-1"></i> Beranda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="katalog.php"><i class="bi bi-book me-1"></i> Katalog Buku</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="kontak.php"><i class="bi bi-headset me-1"></i> Kontak</a>
                </li>

                <?php if (isset($_SESSION['user_login']) && $_SESSION['user_login'] === true): ?>
                    <!-- Dropdown User jika sudah login -->
                    <li class="nav-item dropdown ms-3">
                        <a class="nav-link dropdown-toggle btn btn-outline-primary px-3 rounded-pill text-primary" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($_SESSION['username'] ?? 'Akun'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item py-2" href="profil_user.php"><i class="bi bi-person me-2 text-primary"></i> Profil Saya</a></li>
                            <li><a class="dropdown-item py-2" href="riwayat_pinjam.php"><i class="bi bi-clock-history me-2 text-primary"></i> Riwayat Peminjaman</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Keluar</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- Tombol Login jika belum login -->
                    <li class="nav-item ms-3">
                        <a href="login.php" class="btn btn-primary px-4 rounded-pill">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>