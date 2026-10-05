<!-- Sidebar -->
<nav id="sidebar" class="sidebar bg-dark text-white shadow-sm">

    <!-- Brand / Logo Kampus -->
    <div class="sidebar-brand text-center">

        <img src="../assets/img/logo.png"
             alt="Logo USTJ"
             class="sidebar-logo">

        <h5 class="fw-bold mb-0">
            PerpusAdmin
        </h5>

        <small class="text-white-50">
            Perpustakaan USTJ
        </small>

    </div>

    <!-- Menu Navigasi -->
    <div class="sidebar-content">

        <ul class="nav flex-column">

            <!-- Dashboard -->
            <li class="nav-item">

                <a href="index.php"
                   class="nav-link
                   <?= basename($_SERVER['PHP_SELF']) == 'index.php'
                       ? 'active'
                       : ''; ?>">

                    <i class="bi bi-speedometer2"></i>

                    <span>Dashboard</span>

                </a>

            </li>

            <!-- Data Buku -->
            <li class="nav-item">

                <a href="buku.php"
                   class="nav-link
                   <?= in_array(
                       basename($_SERVER['PHP_SELF']),
                       [
                           'buku.php',
                           'tambah_buku.php',
                           'edit_buku.php'
                       ]
                   )
                       ? 'active'
                       : ''; ?>">

                    <i class="bi bi-journal-bookmark-fill"></i>

                    <span>Data Buku</span>

                </a>

            </li>

            <!-- Data Skripsi -->
            <li class="nav-item">

                <a href="skripsi.php"
                   class="nav-link
                   <?= in_array(
                       basename($_SERVER['PHP_SELF']),
                       [
                           'skripsi.php',
                           'tambah_skripsi.php',
                           'edit_skripsi.php',
                           'import_excel.php'
                       ]
                   )
                       ? 'active'
                       : ''; ?>">

                    <i class="bi bi-mortarboard-fill"></i>

                    <span>Data Skripsi</span>

                </a>

            </li>

            <!-- Kategori Buku -->
            <li class="nav-item">

                <a href="kategori.php"
                   class="nav-link
                   <?= basename($_SERVER['PHP_SELF']) == 'kategori.php'
                       ? 'active'
                       : ''; ?>">

                    <i class="bi bi-tags-fill"></i>

                    <span>Kategori Buku</span>

                </a>

            </li>

            <!-- Data Anggota -->
            <li class="nav-item">

                <a href="anggota.php"
                   class="nav-link
                   <?= basename($_SERVER['PHP_SELF']) == 'anggota.php'
                       ? 'active'
                       : ''; ?>">

                    <i class="bi bi-people-fill"></i>

                    <span>Data Anggota</span>

                </a>

            </li>

            <!-- =========================
                TRANSAKSI
            ========================== -->

            <li class="sidebar-divider">
                <span>TRANSAKSI</span>
            </li>

            <!-- Peminjaman -->
            <li class="nav-item">

                <a href="peminjaman.php"
                   class="nav-link
                   <?= basename($_SERVER['PHP_SELF']) == 'peminjaman.php'
                       ? 'active'
                       : ''; ?>">

                    <i class="bi bi-arrow-left-right"></i>

                    <span>Peminjaman Buku</span>

                </a>

            </li>

            <!-- Pengembalian -->
            <li class="nav-item">

                <a href="pengembalian.php"
                   class="nav-link
                   <?= basename($_SERVER['PHP_SELF']) == 'pengembalian.php'
                       ? 'active'
                       : ''; ?>">

                    <i class="bi bi-arrow-counterclockwise"></i>

                    <span>Pengembalian</span>

                </a>

            </li>

            <!-- Laporan -->
            <li class="nav-item">

                <a href="laporan.php"
                   class="nav-link
                   <?= basename($_SERVER['PHP_SELF']) == 'laporan.php'
                       ? 'active'
                       : ''; ?>">

                    <i class="bi bi-file-earmark-bar-graph-fill"></i>

                    <span>Laporan</span>

                </a>

            </li>

            <!-- =========================
                ADMINISTRASI
            ========================== -->

            <li class="sidebar-divider">
                <span>ADMINISTRASI</span>
            </li>

            <!-- Data Petugas -->
            <li class="nav-item">

                <a href="petugas.php"
                   class="nav-link
                   <?= basename($_SERVER['PHP_SELF']) == 'petugas.php'
                       ? 'active'
                       : ''; ?>">

                    <i class="bi bi-shield-lock-fill"></i>

                    <span>Data Petugas</span>

                </a>

            </li>

            <!-- Lihat Website -->
            <li class="nav-item website-item">
                <a href="../frontend/index.php" target="_blank" class="btn btn-outline-info w-100 d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-box-arrow-up-right"></i> Lihat Website
                </a>
            </li>

        </ul>

    </div>

    <!-- Logout -->
    <div class="sidebar-footer">

        <a href="../frontend/logout.php"
           class="logout-link">

            <i class="bi bi-box-arrow-right"></i>

            <span>Keluar Sistem</span>

        </a>

    </div>

</nav>

<!-- Backdrop / Overlay untuk Mobile saat Sidebar Terbuka -->
<div id="sidebar-backdrop" class="sidebar-backdrop"></div>


<style>
/* =====================================================
   SIDEBAR UTAMA
===================================================== */
#sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 250px;
    height: 100vh;
    z-index: 1050;
    display: flex;
    flex-direction: column;
    background-color: #212529;
    transition: left 0.3s ease-in-out;
}

/* =====================================================
   BRAND / LOGO
===================================================== */
.sidebar-brand {
    padding: 22px 15px 18px;
    border-bottom: 1px solid rgba(255,255,255,0.12);
}

.sidebar-logo {
    width: 52px;
    height: 52px;
    object-fit: contain;
    margin-bottom: 8px;
}

.sidebar-brand h5 {
    font-size: 17px;
}

.sidebar-brand small {
    font-size: 12px;
}

/* =====================================================
   AREA MENU
===================================================== */
.sidebar-content {
    flex: 1;
    overflow-y: auto;
    padding: 15px 12px;
}

/* Scrollbar */
.sidebar-content::-webkit-scrollbar {
    width: 5px;
}

.sidebar-content::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar-content::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,.2);
    border-radius: 10px;
}

/* =====================================================
   MENU LINK
===================================================== */
.sidebar .nav-link {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    padding: 10px 13px;
    margin-bottom: 4px;
    border-radius: 7px;
    color: rgba(255,255,255,.82);
    font-size: 14px;
    text-decoration: none;
    transition: all .2s ease;
}

/* Icon */
.sidebar .nav-link i {
    width: 22px;
    min-width: 22px;
    text-align: center;
    font-size: 17px;
}

/* Hover */
.sidebar .nav-link:hover {
    background-color: rgba(255,255,255,.08);
    color: #fff;
}

/* =====================================================
   MENU AKTIF
===================================================== */
.sidebar .nav-link.active {
    background-color: #0d6efd;
    color: #fff;
    box-shadow: 0 3px 7px rgba(0,0,0,.18);
}

/* =====================================================
   SECTION / DIVIDER
===================================================== */
.sidebar-divider {
    margin: 13px 4px 6px;
    padding: 8px 10px 4px;
    border-top: 1px solid rgba(255,255,255,.12);
}

.sidebar-divider span {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .8px;
    color: rgba(255,255,255,.45);
}

/* =====================================================
   LIHAT WEBSITE
===================================================== */
.website-item {
    margin-top: 10px;
}

.website-link {
    justify-content: center;
    color: #0dcaf0 !important;
    border: 1px solid #0dcaf0;
    background: transparent;
}

.website-link:hover {
    background-color: #0dcaf0 !important;
    color: #212529 !important;
}

/* =====================================================
   FOOTER / LOGOUT
===================================================== */
.sidebar-footer {
    padding: 14px;
    border-top: 1px solid rgba(255,255,255,.12);
}

.logout-link {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 10px;
    border-radius: 7px;
    background-color: #dc3545;
    color: #fff;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    transition: all .2s ease;
}

.logout-link:hover {
    background-color: #bb2d3b;
    color: #fff;
}

/* =====================================================
   BACKDROP MOBILE OVERLAY
===================================================== */
.sidebar-backdrop {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1040;
    transition: opacity 0.3s ease;
}

.sidebar-backdrop.show {
    display: block;
}

/* =====================================================
   RESPONSIVE MOBILE
===================================================== */
@media (max-width: 991.98px) {
    #sidebar {
        left: -250px; /* Sembunyikan sidebar di luar layar pada layar HP/Tablet */
    }

    /* Ketika class sidebar-show ditambahkan via JS, geser ke posisi 0 */
    #sidebar.sidebar-show {
        left: 0 !important;
    }
}
</style>

<!-- Script Pendukung Toggle Sidebar (Opsional jika belum dipasang di template utama) -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const sidebar = document.getElementById("sidebar");
    const backdrop = document.getElementById("sidebar-backdrop");
    
    // Cari tombol toggle jika ada di layout Anda (biasanya ber-ID 'sidebarToggle' atau sejenisnya)
    const toggleBtn = document.getElementById("sidebarToggle");

    if (toggleBtn) {
        toggleBtn.addEventListener("click", function() {
            sidebar.classList.toggle("sidebar-show");
            backdrop.classList.toggle("show");
        });
    }

    if (backdrop) {
        backdrop.addEventListener("click", function() {
            sidebar.classList.remove("sidebar-show");
            backdrop.classList.remove("show");
        });
    }
});
</script>