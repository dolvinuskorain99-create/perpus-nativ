<?php
// =====================================================
// SISTEM INFORMASI PERPUSTAKAAN USTJ
// SIDEBAR AREA ANGGOTA
// FILE: frontend/anggota/sidebar.php
// =====================================================

$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar d-flex flex-column flex-shrink-0 p-3 bg-dark text-white border-end border-secondary shadow-sm" style="width: 260px; min-height: 100vh; position: fixed; top: 0; left: 0; z-index: 1040;">
    
    <!-- Logo & Identitas Kampus USTJ -->
    <a href="dashboard.php" class="d-flex align-items-center mb-3 text-decoration-none px-2 py-2 border-bottom border-secondary pb-3">
        <img src="../../assets/img/logo.png" alt="Logo USTJ" width="38" height="38" class="rounded-3 me-2 object-fit-contain" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        <!-- Fallback jika gambar gagal dimuat -->
        <div class="rounded-3 align-items-center justify-content-center text-white fw-bold me-2" style="width: 38px; height: 38px; background: #212529; font-size: 13px; display: none;">
            USTJ
        </div>
        <div>
            <span class="fw-bold text-white d-block lh-sm" style="font-size: 14px;">Perpus USTJ</span>
            <small class="text-white-50 d-block" style="font-size: 10px;">Universitas Sains & Teknologi Jayapura</small>
        </div>
    </a>

    <!-- Informasi Anggota Ringkas -->
    <div class="px-2 py-2 mb-3 rounded-3 bg-black bg-opacity-25 border border-secondary d-flex align-items-center gap-2">
        <div class="rounded-circle bg-secondary bg-opacity-25 d-flex align-items-center justify-content-center text-white fw-bold" style="width: 30px; height: 30px; font-size: 11px;">
            <?= isset($nama_lengkap) ? strtoupper(substr($nama_lengkap, 0, 1)) : 'M'; ?>
        </div>
        <div class="overflow-hidden">
            <span class="d-block fw-bold text-white text-truncate" style="font-size: 11px;"><?= htmlspecialchars($nama_lengkap ?? 'Mahasiswa'); ?></span>
            <span class="text-white-50 font-mono d-block" style="font-size: 10px;">NIM: <?= htmlspecialchars($nim ?? '-'); ?></span>
        </div>
    </div>

    <!-- Navigasi Utama -->
    <ul class="nav nav-pills flex-column mb-auto gap-1">
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link <?= ($current_page === 'dashboard.php') ? 'active text-white' : 'text-white-50'; ?> rounded-3 py-2 px-3 small fw-semibold" style="<?= ($current_page === 'dashboard.php') ? 'background-color: #0d6efd;' : ''; ?>">
                <i class="bi bi-grid-fill me-2 <?= ($current_page === 'dashboard.php') ? 'text-warning' : 'text-white-50'; ?>"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="peminjaman_mandiri.php" class="nav-link <?= ($current_page === 'peminjaman_mandiri.php') ? 'active text-white' : 'text-white-50'; ?> rounded-3 py-2 px-3 small fw-semibold" style="<?= ($current_page === 'peminjaman_mandiri.php') ? 'background-color: #0d6efd;' : ''; ?>">
                <i class="bi bi-book-fill me-2 <?= ($current_page === 'peminjaman_mandiri.php') ? 'text-warning' : 'text-white-50'; ?>"></i> Peminjaman Mandiri
            </a>
        </li>
        <li>
            <a href="riwayat_pinjam.php" class="nav-link <?= ($current_page === 'riwayat_pinjam.php') ? 'active text-white' : 'text-white-50'; ?> rounded-3 py-2 px-3 small fw-semibold" style="<?= ($current_page === 'riwayat_pinjam.php') ? 'background-color: #0d6efd;' : ''; ?>">
                <i class="bi bi-clock-history me-2 <?= ($current_page === 'riwayat_pinjam.php') ? 'text-warning' : 'text-white-50'; ?>"></i> Riwayat Pinjam
            </a>
        </li>
        <li>
            <a href="profil.php" class="nav-link <?= ($current_page === 'profil.php') ? 'active text-white' : 'text-white-50'; ?> rounded-3 py-2 px-3 small fw-semibold" style="<?= ($current_page === 'profil.php') ? 'background-color: #0d6efd;' : ''; ?>">
                <i class="bi bi-person-badge-fill me-2 <?= ($current_page === 'profil.php') ? 'text-warning' : 'text-white-50'; ?>"></i> Pengaturan Profil
            </a>
        </li>
    </ul>

    <!-- Logout -->
    <div class="pt-3 mt-3 border-top border-secondary">
        <a href="../logout.php" class="nav-link text-danger rounded-3 py-2 px-3 small fw-semibold d-flex align-items-center" onclick="return confirm('Apakah Anda yakin ingin keluar dari akun?')">
            <i class="bi bi-box-arrow-right me-2 fs-6"></i> Keluar Akun
        </a>
    </div>

</aside>

<style>
    /* Efek hover lembut pada menu sidebar gelap */
    .nav-link.text-white-50:hover {
        color: #ffffff !important;
        background-color: rgba(255, 255, 255, 0.05);
    }
</style>