<?php
// =====================================================
// SISTEM INFORMASI PERPUSTAKAAN USTJ
// HALAMAN: PEMINJAMAN MANDIRI (DENGAN FILTER KATEGORI & PRODI)
// FILE: frontend/anggota/peminjaman_mandiri.php
// =====================================================
session_start();
require_once '../../koneksi.php';

if (!isset($_SESSION['user_login']) || $_SESSION['user_login'] !== true) {
    header("Location: ../login.php");
    exit;
}

// Cari id_anggota yang benar: users.nim -> anggota.nim
$id_user = (int)($_SESSION['id_user'] ?? $_SESSION['user_id'] ?? $_SESSION['id'] ?? $_SESSION['id_anggota'] ?? 0);
$id_anggota = 0;
if ($id_user > 0) {
    $q_ang = mysqli_query($koneksi, "SELECT a.id_anggota
                                     FROM users u
                                     JOIN anggota a ON a.nim = u.nim
                                     WHERE u.id_user = $id_user LIMIT 1");
    $r_ang = $q_ang ? mysqli_fetch_assoc($q_ang) : null;
    $id_anggota = (int)($r_ang['id_anggota'] ?? 0);
}

$pesan_sukses = '';
$pesan_error  = '';

if ($id_anggota <= 0) {
    $pesan_error = 'NIM Anda belum terdaftar sebagai anggota perpustakaan, sehingga belum bisa meminjam. Hubungi petugas perpustakaan.';
}

if (isset($_GET['pesan']) && $_GET['pesan'] === 'sukses') {
    $pesan_sukses = 'Buku berhasil dipinjam! Silakan lapor buku di meja petugas perpustakaan.';
}

// =====================================================
// PROSES PINJAM BUKU
// =====================================================
if (isset($_POST['pinjam_buku'])) {
    $id_buku        = (int)$_POST['id_buku'];
    $tanggal_pinjam = date('Y-m-d');

    if ($id_anggota <= 0 || $id_buku <= 0) {
        $pesan_error = 'NIM Anda belum terdaftar sebagai anggota perpustakaan atau buku tidak ditemukan.';
    } else {
        // Batas maksimal 5 buku yang sedang dipinjam
        $q_aktif = mysqli_query($koneksi, "SELECT COUNT(*) AS aktif FROM peminjaman WHERE id_anggota = $id_anggota AND status = 'Dipinjam'");
        $aktif   = (int)mysqli_fetch_assoc($q_aktif)['aktif'];

        // Cegah meminjam buku yang sama dua kali
        $q_sama = mysqli_query($koneksi, "SELECT 1 FROM peminjaman WHERE id_anggota = $id_anggota AND id_buku = $id_buku AND status = 'Dipinjam'");

        if ($aktif >= 5) {
            $pesan_error = 'Batas peminjaman tercapai (maksimal 5 buku sekaligus).';
        } elseif (mysqli_num_rows($q_sama) > 0) {
            $pesan_error = 'Anda sedang meminjam buku ini dan belum mengembalikannya.';
        } else {
            // Kurangi stok menggunakan kolom jumlah_eksemplar
            mysqli_query($koneksi, "UPDATE buku SET jumlah_eksemplar = jumlah_eksemplar - 1 WHERE id_buku = $id_buku AND jumlah_eksemplar > 0");

            if (mysqli_affected_rows($koneksi) > 0) {
                $ok = mysqli_query($koneksi, "INSERT INTO peminjaman (id_buku, id_anggota, tanggal_pinjam, status)
                                             VALUES ($id_buku, $id_anggota, '$tanggal_pinjam', 'Dipinjam')");
                if ($ok) {
                    header("Location: peminjaman_mandiri.php?pesan=sukses");
                    exit;
                }
                // INSERT gagal -> kembalikan stok
                mysqli_query($koneksi, "UPDATE buku SET jumlah_eksemplar = jumlah_eksemplar + 1 WHERE id_buku = $id_buku");
                $pesan_error = 'Gagal memproses peminjaman: ' . mysqli_error($koneksi);
            } else {
                $pesan_error = 'Maaf, stok eksemplar buku ini sedang kosong!';
            }
        }
    }
}

// =====================================================
// PAGINATION, PENCARIAN & FILTER (KATEGORI & PRODI)
// =====================================================
$batas        = 10;
$halaman      = isset($_GET['halaman']) ? max(1, (int)$_GET['halaman']) : 1;
$halaman_awal = ($halaman - 1) * $batas;

$keyword      = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$filter_kat   = isset($_GET['id_kategori']) ? (int)$_GET['id_kategori'] : 0;
$filter_prodi = isset($_GET['id_prodi']) ? (int)$_GET['id_prodi'] : 0;

$where_arr = [];
if ($keyword !== '') {
    $search = mysqli_real_escape_string($koneksi, $keyword);
    $where_arr[] = "(buku.judul_buku LIKE '%$search%' OR buku.pengarang LIKE '%$search%' OR buku.penerbit LIKE '%$search%')";
}
if ($filter_kat > 0) {
    $where_arr[] = "(buku.id_kategori = $filter_kat)";
}
if ($filter_prodi > 0) {
    $where_arr[] = "(buku.id_prodi = $filter_prodi)";
}

$where = count($where_arr) > 0 ? "WHERE " . implode(' AND ', $where_arr) : '';

$row_jumlah    = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM buku $where"));
$total_data    = (int)$row_jumlah['total'];
$total_halaman = max(1, (int)ceil($total_data / $batas));

// Query utama dengan kolom eksplisit untuk menghindari tabrakan nama kolom antar tabel
$query_buku = mysqli_query($koneksi, "SELECT buku.id_buku, buku.judul_buku, buku.pengarang, buku.penerbit, buku.jumlah_eksemplar, buku.tahun_terbit,
                                             kategori.nama_kategori, program_studi.nama_prodi 
                                      FROM buku 
                                      LEFT JOIN kategori ON buku.id_kategori = kategori.id_kategori 
                                      LEFT JOIN program_studi ON buku.id_prodi = program_studi.id_prodi 
                                      $where ORDER BY buku.id_buku DESC LIMIT $halaman_awal, $batas");

// Ambil data untuk opsi dropdown filter
$q_kategori = mysqli_query($koneksi, "SELECT * FROM kategori");
$q_prodi    = mysqli_query($koneksi, "SELECT * FROM program_studi");

$nomor = $halaman_awal + 1;

$qs_params = [];
if ($keyword !== '') $qs_params['cari'] = $keyword;
if ($filter_kat > 0) $qs_params['id_kategori'] = $filter_kat;
if ($filter_prodi > 0) $qs_params['id_prodi'] = $filter_prodi;
$qs_cari = count($qs_params) > 0 ? '&' . http_build_query($qs_params) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Mandiri - Perpustakaan USTJ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .main-content { margin-left: 260px; padding: 35px; }
        .card-custom { border: none; border-radius: 16px; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.04); background: #ffffff; }
        .table-custom th { background-color: #f1f3f5; font-weight: 600; color: #495057; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #dee2e6; }
        .table-custom td { vertical-align: middle; font-size: 0.9rem; color: #333; padding: 14px 12px; }
        .book-title { font-weight: 700; color: #2b2d42; font-size: 0.95rem; }
        .search-box { border-radius: 50rem; padding-left: 15px; border: 1px solid #ced4da; }
        .search-box:focus { box-shadow: none; border-color: #0d6efd; }
        @media (max-width: 768px) { .main-content { margin-left: 0; padding: 15px; } }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div>
                    <h3 class="fw-bold text-dark mb-1"><i class="bi bi-journal-check text-primary me-2"></i> Peminjaman Mandiri</h3>
                    <p class="text-muted mb-0">Pilih buku dari katalog perpustakaan USTJ untuk melakukan peminjaman secara mandiri.</p>
                </div>
            </div>

            <!-- Form Filter Kategori, Prodi, & Pencarian -->
            <form action="" method="GET" class="row g-2 mb-4 bg-white p-3 rounded-4 shadow-sm align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <input type="text" name="cari" class="form-control search-box form-control-sm" placeholder="Cari judul, pengarang, penerbit..." value="<?= htmlspecialchars($keyword); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="id_kategori" class="form-select form-select-sm rounded-pill px-3" onchange="this.form.submit()">
                        <option value="">-- Semua Kategori --</option>
                        <?php while ($kat = mysqli_fetch_assoc($q_kategori)): ?>
                            <option value="<?= $kat['id_kategori']; ?>" <?= ($filter_kat == $kat['id_kategori']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($kat['nama_kategori']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="id_prodi" class="form-select form-select-sm rounded-pill px-3" onchange="this.form.submit()">
                        <option value="">-- Semua Program Studi --</option>
                        <?php while ($prodi = mysqli_fetch_assoc($q_prodi)): ?>
                            <option value="<?= $prodi['id_prodi']; ?>" <?= ($filter_prodi == $prodi['id_prodi']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($prodi['nama_prodi']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill w-100 fw-semibold"><i class="bi bi-search me-1"></i> Filter</button>
                    <?php if ($keyword !== '' || $filter_kat > 0 || $filter_prodi > 0): ?>
                        <a href="peminjaman_mandiri.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3" title="Reset Filter"><i class="bi bi-arrow-counterclockwise"></i></a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Notifikasi -->
            <?php if (!empty($pesan_sukses)): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 bg-success bg-opacity-10 text-success mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($pesan_sukses); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($pesan_error)): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0 bg-danger bg-opacity-10 text-danger mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($pesan_error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Tabel Katalog Buku -->
            <div class="card card-custom p-4">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h5 class="fw-bold text-dark m-0">Daftar Katalog Buku</h5>
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-semibold">
                        <i class="bi bi-book me-1"></i> Total Katalog: <?= $total_data; ?> Buku
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-custom align-middle">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 5%;">No</th>
                                <th style="width: 32%;">Detail Buku</th>
                                <th style="width: 20%;">Pengarang</th>
                                <th style="width: 18%;">Penerbit & Kategori</th>
                                <th class="text-center" style="width: 12%;">Stok</th>
                                <th class="text-center" style="width: 13%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($query_buku && mysqli_num_rows($query_buku) > 0): ?>
                                <?php while ($buku = mysqli_fetch_assoc($query_buku)): ?>
                                    <?php
                                        $stok = (int)($buku['jumlah_eksemplar'] ?? 0);
                                    ?>
                                    <tr>
                                        <td class="text-center text-muted fw-bold"><?= $nomor++; ?></td>
                                        <td>
                                            <div class="book-title"><?= htmlspecialchars($buku['judul_buku']); ?></div>
                                            <div class="text-muted small">
                                                <i class="bi bi-upc-scan text-secondary me-1"></i> ID Buku: #<?= $buku['id_buku']; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-dark"><i class="bi bi-person text-muted me-1"></i> <?= htmlspecialchars($buku['pengarang'] ?? '-'); ?></div>
                                        </td>
                                        <td>
                                            <div class="text-dark"><?= htmlspecialchars($buku['penerbit'] ?? '-'); ?></div>
                                            <div class="text-muted small mt-1">
                                                <span class="badge bg-light text-dark border me-1"><?= htmlspecialchars($buku['nama_kategori'] ?? 'Umum'); ?></span>
                                                <span class="badge bg-info bg-opacity-10 text-info"><?= htmlspecialchars($buku['nama_prodi'] ?? 'Semua Prodi'); ?></span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($stok > 0): ?>
                                                <span class="badge bg-success text-white px-3 py-2 rounded-pill fw-bold shadow-sm">
                                                    <i class="bi bi-check-circle me-1"></i> <?= $stok; ?> Tersedia
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger text-white px-3 py-2 rounded-pill fw-bold shadow-sm">
                                                    <i class="bi bi-x-circle me-1"></i> Habis (0)
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($stok > 0): ?>
                                                <form action="" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin meminjam buku &quot;<?= htmlspecialchars(addslashes($buku['judul_buku'])); ?>&quot;?')">
                                                    <input type="hidden" name="id_buku" value="<?= (int)$buku['id_buku']; ?>">
                                                    <button type="submit" name="pinjam_buku" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold shadow-sm">
                                                        <i class="bi bi-box-arrow-in-right me-1"></i> Pinjam
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-light text-muted rounded-pill px-3 border" disabled>Kosong</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-search display-5 d-block mb-2 text-secondary opacity-50"></i>
                                        Tidak ada data buku yang ditemukan sesuai pencarian/filter.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Navigasi Pagination Pintar -->
                <?php if ($total_halaman > 1): ?>
                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top flex-wrap gap-2">
                        <div class="text-muted small">
                            Halaman <strong><?= $halaman; ?></strong> dari <strong><?= $total_halaman; ?></strong> (Total <?= $total_data; ?> Buku)
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm m-0 shadow-sm">
                                <li class="page-item <?= ($halaman <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link rounded-start-pill px-3" href="?halaman=<?= $halaman - 1; ?><?= $qs_cari; ?>">
                                        <i class="bi bi-chevron-left"></i> Prev
                                    </a>
                                </li>

                                <?php
                                $link_rentang = 2;
                                $start_number = max(1, $halaman - $link_rentang);
                                $end_number   = min($total_halaman, $halaman + $link_rentang);

                                if ($start_number > 1) {
                                    echo '<li class="page-item"><a class="page-link" href="?halaman=1' . $qs_cari . '">1</a></li>';
                                    if ($start_number > 2) {
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                }

                                for ($i = $start_number; $i <= $end_number; $i++) {
                                    $active_class = ($halaman == $i) ? 'active' : '';
                                    echo '<li class="page-item ' . $active_class . '"><a class="page-link" href="?halaman=' . $i . $qs_cari . '">' . $i . '</a></li>';
                                }

                                if ($end_number < $total_halaman) {
                                    if ($end_number < $total_halaman - 1) {
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                    echo '<li class="page-item"><a class="page-link" href="?halaman=' . $total_halaman . $qs_cari . '">' . $total_halaman . '</a></li>';
                                }
                                ?>

                                <li class="page-item <?= ($halaman >= $total_halaman) ? 'disabled' : ''; ?>">
                                    <a class="page-link rounded-end-pill px-3" href="?halaman=<?= $halaman + 1; ?><?= $qs_cari; ?>">
                                        Next <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>