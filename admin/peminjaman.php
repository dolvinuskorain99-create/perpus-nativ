<?php
// =====================================================
// SISTEM INFORMASI PERPUSTAKAAN USTJ
// HALAMAN: MANAJEMEN PEMINJAMAN BUKU
// FILE: admin/peminjaman.php
// =====================================================

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../koneksi.php';

if (!$koneksi) {
    die('Koneksi database gagal: ' . mysqli_connect_error());
}

$pesan_gagal = '';

// =====================================================
// PROSES TAMBAH PEMINJAMAN BARU (oleh admin)
// =====================================================
if (isset($_POST['tambah_peminjaman'])) {
    $id_buku        = (int)$_POST['id_buku'];
    $id_anggota     = (int)$_POST['id_anggota'];
    $tanggal_pinjam = date('Y-m-d');

    if ($id_buku > 0 && $id_anggota > 0) {
        // Kurangi stok hanya jika masih tersedia
        mysqli_query($koneksi, "UPDATE buku SET jumlah_eksemplar = jumlah_eksemplar - 1 WHERE id_buku = $id_buku AND jumlah_eksemplar > 0");

        if (mysqli_affected_rows($koneksi) > 0) {
            $query_insert = "INSERT INTO peminjaman (id_buku, id_anggota, tanggal_pinjam, status)
                             VALUES ($id_buku, $id_anggota, '$tanggal_pinjam', 'Dipinjam')";

            if (mysqli_query($koneksi, $query_insert)) {
                header("Location: peminjaman.php?pesan=sukses_tambah");
                exit();
            } else {
                // Kembalikan stok jika insert gagal
                mysqli_query($koneksi, "UPDATE buku SET jumlah_eksemplar = jumlah_eksemplar + 1 WHERE id_buku = $id_buku");
                $pesan_gagal = "Gagal menyimpan data peminjaman: " . mysqli_error($koneksi);
            }
        } else {
            $pesan_gagal = "Stok buku tersebut sedang kosong!";
        }
    } else {
        $pesan_gagal = "Silakan pilih buku dan anggota terlebih dahulu!";
    }
}

// =====================================================
// PROSES PENGEMBALIAN BUKU
// =====================================================
if (isset($_GET['aksi']) && $_GET['aksi'] === 'kembali' && isset($_GET['id'])) {
    $id_pinjam = (int)$_GET['id'];

    $q_pinjam  = mysqli_query($koneksi, "SELECT * FROM peminjaman WHERE id_peminjaman = $id_pinjam AND status = 'Dipinjam'");
    $dt_pinjam = mysqli_fetch_assoc($q_pinjam);

    if ($dt_pinjam) {
        $id_buku_kembali  = (int)$dt_pinjam['id_buku'];
        $tanggal_sekarang = date('Y-m-d');

        $update_peminjaman = mysqli_query($koneksi, "UPDATE peminjaman SET status = 'Kembali', tanggal_kembali = '$tanggal_sekarang' WHERE id_peminjaman = $id_pinjam");

        if ($update_peminjaman) {
            // Pulihkan stok (+1)
            mysqli_query($koneksi, "UPDATE buku SET jumlah_eksemplar = jumlah_eksemplar + 1 WHERE id_buku = $id_buku_kembali");
            header("Location: peminjaman.php?pesan=sukses_kembali");
            exit();
        } else {
            $pesan_gagal = "Gagal memproses pengembalian buku!";
        }
    }
}

// =====================================================
// PAGINATION & PENCARIAN
// =====================================================
$batas   = 10;
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
if ($halaman < 1) { $halaman = 1; }

$keyword = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$where   = [];

if ($keyword !== '') {
    $kw_esc  = mysqli_real_escape_string($koneksi, $keyword);
    $where[] = "(buku.judul_buku LIKE '%$kw_esc%' OR anggota.nama LIKE '%$kw_esc%' OR anggota.nim LIKE '%$kw_esc%')";
}

$sql_where = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$sql_count = "SELECT COUNT(*) AS total FROM peminjaman
              JOIN buku ON peminjaman.id_buku = buku.id_buku
              JOIN anggota ON peminjaman.id_anggota = anggota.id_anggota $sql_where";
$query_count = mysqli_query($koneksi, $sql_count);
if (!$query_count) {
    die('Query count gagal: ' . mysqli_error($koneksi));
}
$data_count = mysqli_fetch_assoc($query_count);
$total_data = (int)($data_count['total'] ?? 0);

$total_halaman = max(1, (int)ceil($total_data / $batas));
if ($halaman > $total_halaman) { $halaman = $total_halaman; }
$offset = ($halaman - 1) * $batas;

$sql_data = "SELECT peminjaman.*, buku.judul_buku AS judul, anggota.nama AS nama_anggota, anggota.nim
             FROM peminjaman
             JOIN buku ON peminjaman.id_buku = buku.id_buku
             JOIN anggota ON peminjaman.id_anggota = anggota.id_anggota
             $sql_where ORDER BY peminjaman.id_peminjaman DESC LIMIT $offset, $batas";
$query_data = mysqli_query($koneksi, $sql_data);
if (!$query_data) {
    die('Query data gagal: ' . mysqli_error($koneksi));
}

$params = [];
if ($keyword !== '') { $params['cari'] = $keyword; }
$query_string = !empty($params) ? '&' . http_build_query($params) : '';

$nomor_awal  = $total_data > 0 ? $offset + 1 : 0;
$nomor_akhir = min($offset + $batas, $total_data);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Peminjaman Buku - Perpustakaan USTJ</title>

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --sidebar-width: 250px;
            --main-bg: #f8fafc;
            --border-color: #e2e8f0;
            --text-dark: #0f172a;
            --text-muted: #64748b;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0; padding: 0;
            background: var(--main-bg);
            color: #1e293b;
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
        }

        #main-content-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            width: calc(100% - var(--sidebar-width));
            background: var(--main-bg);
        }

        .top-navbar {
            min-height: 70px; padding: 0 28px;
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            display: flex; align-items: center;
        }

        .breadcrumb-admin { font-size: 13px; color: #64748b; }
        .breadcrumb-admin .current { color: #0d6efd; font-weight: 600; }

        .date-badge {
            padding: 7px 11px; font-size: 12px; font-weight: 500;
            background: #f8fafc; border: 1px solid #e2e8f0; color: #475569; border-radius: 7px;
        }

        .admin-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: #0d6efd; color: white;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 700;
        }

        .content-body { padding: 28px; max-width: 1400px; margin: 0 auto; }
        .page-header { margin-bottom: 24px; }
        .page-title { margin: 0; font-size: 24px; font-weight: 700; color: var(--text-dark); }
        .page-description { margin-top: 6px; font-size: 13px; color: var(--text-muted); }

        .custom-card {
            border: 1px solid var(--border-color);
            border-radius: 12px; background: #ffffff;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        }

        .filter-card { margin-bottom: 22px; }
        .filter-card .card-body { padding: 20px; }
        .filter-label { display: block; margin-bottom: 7px; font-size: 11px; font-weight: 700; color: #64748b; }

        .form-control, .form-select { min-height: 42px; border-color: #dbe2ea; border-radius: 8px; font-size: 13px; }
        .search-button { min-width: 82px; border-radius: 8px; font-size: 13px; font-weight: 600; }

        .data-info { margin-bottom: 12px; font-size: 13px; color: #64748b; }
        .table-card { overflow: hidden; }
        .table-wrapper { overflow-x: auto; width: 100%; }
        .table { margin: 0; min-width: 800px; }

        .table thead th {
            padding: 13px 16px; background: #0f172a; color: #ffffff;
            border: none; font-size: 11px; font-weight: 700; text-transform: uppercase;
        }

        .table tbody td {
            padding: 14px 16px; font-size: 13px; color: #334155;
            vertical-align: middle; border-bottom: 1px solid #edf1f5;
        }
        .table tbody tr:hover { background-color: #f8fafc; }

        .badge-status { padding: 6px 12px; font-size: 11px; font-weight: 600; border-radius: 6px; }
        .badge-dipinjam { background: #fef3c7; color: #d97706; }
        .badge-kembali { background: #dcfce7; color: #16a34a; }

        @media (max-width: 991.98px) {
            #main-content-wrapper { margin-left: 250px; width: calc(100% - 250px); }
        }
        @media (max-width: 767.98px) {
            #main-content-wrapper { margin-left: 0; width: 100%; }
        }
    </style>
</head>

<body>

<!-- SIDEBAR -->
<?php include 'sidebar.php'; ?>

<!-- MAIN CONTENT -->
<div id="main-content-wrapper">

    <!-- TOP NAVBAR -->
    <nav class="top-navbar justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-light d-md-none" id="sidebarToggle">
                <i class="bi bi-list fs-5"></i>
            </button>
            <div class="breadcrumb-admin">
                <span class="text-dark fw-semibold">PerpusAdmin USTJ</span>
                <span class="mx-1">/</span>
                <span class="current">Peminjaman Buku</span>
            </div>
        </div>

        <div class="d-flex align-items-center gap-3">
            <span class="date-badge">
                <i class="bi bi-calendar3 text-primary me-1"></i>
                <?= date('d M Y'); ?>
            </span>
            <div class="d-flex align-items-center gap-2">
                <div class="admin-avatar">AP</div>
                <div class="d-none d-sm-block">
                    <div class="fw-semibold small" style="line-height: 1.2;">Admin Perpus</div>
                    <small class="text-muted" style="font-size: 10px;">USTJ Jayapura</small>
                </div>
            </div>
        </div>
    </nav>

    <!-- CONTENT BODY -->
    <main class="content-body">

        <!-- PAGE HEADER -->
        <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h1 class="page-title d-flex align-items-center gap-2">
                    <i class="bi bi-journal-arrow-up text-primary"></i>
                    Manajemen Peminjaman Buku
                </h1>
                <p class="page-description">
                    Kelola transaksi peminjaman dan pengembalian buku perpustakaan Universitas Sains dan Teknologi Jayapura.
                </p>
            </div>

            <div>
                <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTambahPinjam" style="border-radius: 8px;">
                    <i class="bi bi-plus-lg"></i>
                    Transaksi Pinjam Baru
                </button>
            </div>
        </div>

        <!-- ALERTS -->
        <?php if (isset($_GET['pesan'])): ?>
            <?php if ($_GET['pesan'] === 'sukses_tambah'): ?>
                <div class="alert alert-success border-0 shadow-sm mb-4">
                    <i class="bi bi-check-circle-fill me-2"></i> Peminjaman buku berhasil dicatat dan stok buku dikurangi!
                </div>
            <?php elseif ($_GET['pesan'] === 'sukses_kembali'): ?>
                <div class="alert alert-success border-0 shadow-sm mb-4">
                    <i class="bi bi-check-circle-fill me-2"></i> Buku berhasil dikembalikan dan stok buku dipulihkan!
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($pesan_gagal !== ''): ?>
            <div class="alert alert-danger border-0 shadow-sm mb-4">
                <i class="bi bi-x-circle-fill me-2"></i> <?= htmlspecialchars($pesan_gagal); ?>
            </div>
        <?php endif; ?>

        <!-- FILTER & PENCARIAN -->
        <div class="custom-card filter-card">
            <div class="card-body">
                <form method="GET" action="peminjaman.php">
                    <div class="row g-3 align-items-end">
                        <div class="col-xl-8 col-md-12">
                            <label class="filter-label">CARI PEMINJAMAN</label>
                            <div class="input-group">
                                <input type="text" name="cari" class="form-control" placeholder="Cari berdasarkan judul buku, nama, atau NIM peminjam..." value="<?= htmlspecialchars($keyword); ?>">
                                <button type="submit" class="btn btn-primary search-button">
                                    <i class="bi bi-search me-1"></i> Cari
                                </button>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-12 text-xl-end">
                            <?php if ($keyword !== ''): ?>
                                <a href="peminjaman.php" class="btn btn-outline-secondary btn-sm px-3 py-2">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- INFO DATA -->
        <div class="data-info d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>Total riwayat: <strong><?= number_format($total_data, 0, ',', '.'); ?></strong> transaksi</div>
            <div>Menampilkan <?= $nomor_awal; ?> - <?= $nomor_akhir; ?> dari <?= number_format($total_data, 0, ',', '.'); ?> data</div>
        </div>

        <!-- TABEL PEMINJAMAN -->
        <div class="custom-card table-card">
            <div class="table-wrapper">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th class="text-center" width="60">No</th>
                            <th>Nama Peminjam (NIM)</th>
                            <th>Judul Buku</th>
                            <th class="text-center">Tgl Pinjam</th>
                            <th class="text-center">Tgl Kembali</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" width="130">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($query_data) > 0): ?>
                            <?php $no = $offset + 1; while ($row = mysqli_fetch_assoc($query_data)): ?>
                                <tr>
                                    <td class="text-center text-muted fw-semibold"><?= $no++; ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_anggota']); ?></div>
                                        <small class="text-muted">NIM: <?= htmlspecialchars($row['nim'] ?? '-'); ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-primary"><?= htmlspecialchars($row['judul']); ?></div>
                                    </td>
                                    <td class="text-center"><?= date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?></td>
                                    <td class="text-center">
                                        <?= !empty($row['tanggal_kembali']) ? date('d/m/Y', strtotime($row['tanggal_kembali'])) : '-'; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($row['status'] === 'Dipinjam'): ?>
                                            <span class="badge-status badge-dipinjam">Dipinjam</span>
                                        <?php else: ?>
                                            <span class="badge-status badge-kembali">Kembali</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($row['status'] === 'Dipinjam'): ?>
                                            <a href="peminjaman.php?aksi=kembali&id=<?= (int)$row['id_peminjaman']; ?>" class="btn btn-success btn-sm px-2 py-1 text-white fw-semibold" style="font-size: 11px; border-radius: 6px;" onclick="return confirm('Konfirmasi buku ini sudah dikembalikan?')">
                                                <i class="bi bi-check-circle me-1"></i> Kembalikan
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small fst-italic">Selesai</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-journal-x fs-1 text-secondary opacity-50 d-block mb-2"></i>
                                    <h5>Tidak ada data peminjaman ditemukan</h5>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PAGINASI -->
        <?php if ($total_halaman > 1): ?>
        <div class="pagination-wrapper d-flex justify-content-center mt-4">
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <li class="page-item <?= ($halaman <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?halaman=<?= $halaman - 1; ?><?= $query_string; ?>"><i class="bi bi-chevron-left"></i></a>
                    </li>
                    <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                        <li class="page-item <?= ($i == $halaman) ? 'active' : ''; ?>">
                            <a class="page-link" href="?halaman=<?= $i; ?><?= $query_string; ?>"><?= $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($halaman >= $total_halaman) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?halaman=<?= $halaman + 1; ?><?= $query_string; ?>"><i class="bi bi-chevron-right"></i></a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>

    </main>
</div>

<!-- MODAL TAMBAH PEMINJAMAN -->
<div class="modal fade" id="modalTambahPinjam" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-journal-plus me-2"></i> Form Peminjaman Buku</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="peminjaman.php" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Pilih Anggota / Peminjam <span class="text-danger">*</span></label>
                        <select name="id_anggota" class="form-select" required>
                            <option value="">-- Pilih Anggota USTJ --</option>
                            <?php
                            $q_anggota = mysqli_query($koneksi, "SELECT * FROM anggota ORDER BY nama ASC");
                            while ($ang = mysqli_fetch_assoc($q_anggota)) {
                                echo "<option value='" . (int)$ang['id_anggota'] . "'>" . htmlspecialchars($ang['nama']) . " (NIM: " . htmlspecialchars($ang['nim'] ?? '-') . ")</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Pilih Buku <span class="text-danger">*</span></label>
                        <select name="id_buku" class="form-select" required>
                            <option value="">-- Pilih Buku (Stok Tersedia) --</option>
                            <?php
                            $q_buku_list = mysqli_query($koneksi, "SELECT * FROM buku WHERE jumlah_eksemplar > 0 ORDER BY judul_buku ASC");
                            while ($bk = mysqli_fetch_assoc($q_buku_list)) {
                                echo "<option value='" . (int)$bk['id_buku'] . "'>" . htmlspecialchars($bk['judul_buku']) . " (Stok: " . (int)$bk['jumlah_eksemplar'] . ")</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light py-3 px-4 rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_peminjaman" class="btn btn-primary px-4">Proses Pinjam</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>