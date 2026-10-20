<?php
// =====================================================
// SISTEM INFORMASI PERPUSTAKAAN USTJ
// HALAMAN: MANAJEMEN DATA BUKU (ADMIN)
// FILE: admin/buku.php
// =====================================================

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../koneksi.php';

if (!$koneksi) {
    die('Koneksi database gagal: ' . mysqli_connect_error());
}

// =====================================================
// KONFIGURASI PAGINATION
// =====================================================
$batas = 10;
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
if ($halaman < 1) { $halaman = 1; }

// =====================================================
// FILTER & SEARCH INPUT
// =====================================================
$keyword = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$filter_fakultas = isset($_GET['fakultas']) ? trim($_GET['fakultas']) : '';
$filter_prodi = isset($_GET['prodi']) ? trim($_GET['prodi']) : '';

// =====================================================
// SUSUN KLAUSA WHERE
// =====================================================
$where = [];

if ($keyword !== '') {
    $kw_esc = mysqli_real_escape_string($koneksi, $keyword);
    $where[] = "(
        buku.judul_buku LIKE '%$kw_esc%'
        OR buku.pengarang LIKE '%$kw_esc%'
        OR buku.penerbit LIKE '%$kw_esc%'
        OR buku.tahun_terbit LIKE '%$kw_esc%'
    )";
}

if ($filter_fakultas !== '') {
    $fak_esc = mysqli_real_escape_string($koneksi, $filter_fakultas);
    $where[] = "program_studi.nama_fakultas = '$fak_esc'";
}

if ($filter_prodi !== '') {
    $prodi_esc = mysqli_real_escape_string($koneksi, $filter_prodi);
    $where[] = "program_studi.nama_prodi = '$prodi_esc'";
}

$sql_where = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// =====================================================
// HITUNG TOTAL DATA
// =====================================================
$sql_count = "
    SELECT COUNT(*) AS total
    FROM buku
    LEFT JOIN program_studi ON buku.id_prodi = program_studi.id_prodi
    $sql_where
";
$query_count = mysqli_query($koneksi, $sql_count);
$data_count = mysqli_fetch_assoc($query_count);
$total_data = (int)($data_count['total'] ?? 0);

// =====================================================
// PAGINATION
// =====================================================
$total_halaman = max(1, (int)ceil($total_data / $batas));
if ($halaman > $total_halaman) { $halaman = $total_halaman; }
$offset = ($halaman - 1) * $batas;

// =====================================================
// QUERY DATA BUKU
// =====================================================
$sql_data = "
    SELECT buku.*, program_studi.nama_prodi, program_studi.nama_fakultas
    FROM buku
    LEFT JOIN program_studi ON buku.id_prodi = program_studi.id_prodi
    $sql_where
    ORDER BY buku.id_buku DESC
    LIMIT $offset, $batas
";
$query_data = mysqli_query($koneksi, $sql_data);
$data_buku = $query_data ? mysqli_fetch_all($query_data, MYSQLI_ASSOC) : [];

// =====================================================
// AMBIL DAFTAR FAKULTAS & PRODI UNTUK DROPDOWN
// =====================================================
$sql_fakultas = "SELECT DISTINCT nama_fakultas FROM program_studi WHERE nama_fakultas IS NOT NULL AND nama_fakultas != '' ORDER BY nama_fakultas ASC";
$query_fakultas = mysqli_query($koneksi, $sql_fakultas);

$sql_prodi = "SELECT DISTINCT nama_prodi, nama_fakultas FROM program_studi WHERE nama_prodi IS NOT NULL AND nama_prodi != ''";
if ($filter_fakultas !== '') {
    $fak_esc = mysqli_real_escape_string($koneksi, $filter_fakultas);
    $sql_prodi .= " AND nama_fakultas = '$fak_esc'";
}
$sql_prodi .= " ORDER BY nama_prodi ASC";
$query_prodi = mysqli_query($koneksi, $sql_prodi);

// Query string untuk pagination
$params = [];
if ($keyword !== '') { $params['cari'] = $keyword; }
if ($filter_fakultas !== '') { $params['fakultas'] = $filter_fakultas; }
if ($filter_prodi !== '') { $params['prodi'] = $filter_prodi; }
$query_string = !empty($params) ? '&' . http_build_query($params) : '';

$nomor_awal = $total_data > 0 ? $offset + 1 : 0;
$nomor_akhir = min($offset + $batas, $total_data);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Data Buku - Perpustakaan USTJ</title>

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
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            padding: 0;
            background: var(--main-bg);
            color: #1e293b;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            overflow-x: hidden;
        }

        #main-content-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            width: calc(100% - var(--sidebar-width));
            background: var(--main-bg);
        }

        .top-navbar {
            min-height: 70px;
            padding: 0 28px;
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
        }

        .breadcrumb-admin { font-size: 13px; color: #64748b; }
        .breadcrumb-admin .current { color: #0d6efd; font-weight: 600; }

        .date-badge {
            padding: 7px 11px;
            font-size: 12px;
            font-weight: 500;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #475569;
            border-radius: 7px;
        }

        .admin-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #0d6efd;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
        }

        .content-body {
            padding: 28px;
            max-width: 1700px;
            margin: 0 auto;
        }

        .page-header { margin-bottom: 24px; }
        .page-title { margin: 0; font-size: 24px; line-height: 1.3; font-weight: 700; color: var(--text-dark); }
        .page-description { margin-top: 6px; margin-bottom: 0; font-size: 13px; color: var(--text-muted); }

        .custom-card {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        }

        .filter-card { margin-bottom: 22px; }
        .filter-card .card-body { padding: 20px; }
        .filter-label { display: block; margin-bottom: 7px; font-size: 11px; font-weight: 700; letter-spacing: .04em; color: #64748b; }

        .form-select, .form-control {
            min-height: 42px;
            border-color: #dbe2ea;
            border-radius: 8px;
            font-size: 13px;
            color: #334155;
        }

        .search-button { min-width: 82px; border-radius: 8px; font-size: 13px; font-weight: 600; }

        .active-filter {
            margin-top: 18px;
            padding-top: 15px;
            border-top: 1px solid #edf0f3;
        }

        .filter-badge { padding: 6px 9px; border-radius: 6px; font-size: 11px; font-weight: 600; }

        .data-info { margin-bottom: 12px; }
        .data-total { font-size: 13px; color: #64748b; }
        .data-total strong { color: #0f172a; }
        .data-showing { font-size: 12px; color: #64748b; }

        .table-card { overflow: hidden; }
        .table-wrapper { overflow-x: auto; width: 100%; }
        .table { margin: 0; min-width: 1050px; }

        .book-list-mobile { display: grid; gap: 12px; }
        .book-mobile-card {
            padding: 16px;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 1px 3px rgba(15, 23, 42, .04);
        }
        .book-mobile-title { min-width: 0; overflow-wrap: anywhere; }
        .book-mobile-meta { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .book-mobile-label { display: block; margin-bottom: 3px; color: var(--text-muted); font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .book-mobile-value { color: #334155; font-size: 12px; overflow-wrap: anywhere; }

        .table thead th {
            padding: 13px 14px;
            background: #0f172a;
            color: #ffffff;
            border: none;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            vertical-align: middle;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 13px 14px;
            font-size: 12.5px;
            color: #334155;
            vertical-align: middle;
            border-bottom: 1px solid #edf1f5;
        }

        .table tbody tr:last-child td { border-bottom: none; }
        .table tbody tr:hover { background-color: #f8fafc; }

        .action-buttons { display: flex; align-items: center; justify-content: center; gap: 4px; }
        .action-buttons .btn { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; }
        .action-buttons i { font-size: 14px; }

        .pagination-wrapper { margin-top: 22px; }
        .pagination { margin-bottom: 0; }
        .page-link {
            min-width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 2px;
            border-radius: 7px !important;
            border: 1px solid #e2e8f0;
            color: #475569;
            font-size: 12px;
            font-weight: 500;
        }
        .page-link:hover { background-color: #f1f5f9; color: #0d6efd; }
        .page-item.active .page-link { background-color: #0d6efd; border-color: #0d6efd; color: #ffffff; }

        @media (max-width: 991.98px) {
            #main-content-wrapper { margin-left: 250px; width: calc(100% - 250px); }
            .content-body { padding: 22px; }
            .top-navbar { padding: 0 22px; }
        }

        @media (max-width: 767.98px) {
            #main-content-wrapper { margin-left: 0; width: 100%; }
            .top-navbar { min-height: 62px; padding: 0 16px; }
            .content-body { padding: 18px 15px; }
            .page-title { font-size: 20px; }
            .date-badge { display: none; }
            .page-actions { width: 100%; }
            .page-actions .btn { flex: 1; justify-content: center; padding: 10px 8px; font-size: 12px; }
            .book-mobile-card { padding: 14px; }
        }

        @media (max-width: 380px) {
            .book-mobile-meta { grid-template-columns: minmax(0, 1fr); gap: 9px; }
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
                <span class="current">Data Buku</span>
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
                    <i class="bi bi-journal-bookmark-fill text-primary"></i>
                    Manajemen Data Buku
                </h1>
                <p class="page-description">
                    Kelola data buku koleksi perpustakaan dan lakukan import massal dari file Excel.
                </p>
            </div>
            
            <div class="page-actions d-flex gap-2">
                <a href="tambah_buku.php" class="btn btn-primary d-inline-flex align-items-center gap-2">
                    <i class="bi bi-plus-circle"></i>
                    Tambah Buku
                </a>
                <button class="btn btn-success d-inline-flex align-items-center gap-2" data-bs-toggle="collapse" data-bs-target="#importExcel">
                    <i class="bi bi-file-earmark-excel"></i>
                    Import Excel
                </button>
            </div>
        </div>

        <!-- ALERT -->
        <?php if (isset($_GET['berhasil'])): ?>
            <div class="alert alert-success border-0 shadow-sm mb-4">
                <i class="bi bi-check-circle-fill me-2"></i> Data buku berhasil diimport.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['gagal'])): ?>
            <div class="alert alert-danger border-0 shadow-sm mb-4">
                <i class="bi bi-x-circle-fill me-2"></i> Import gagal, silakan cek kembali format file Excel Anda.
            </div>
        <?php endif; ?>

        <!-- IMPORT EXCEL COLLAPSE -->
        <div class="collapse mb-4" id="importExcel">
            <div class="custom-card">
                <div class="card-header bg-success text-white py-3 px-4 rounded-top-3 border-0">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-file-earmark-excel me-2"></i> Import Data Buku dari Excel
                    </h6>
                </div>
                <div class="card-body p-4">
                    <form action="proses_import_buku.php" method="POST" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Fakultas <span class="text-danger">*</span></label>
                                <select name="nama_fakultas" class="form-select" required>
                                    <option value="">-- Pilih Fakultas --</option>
                                    <?php
                                    mysqli_data_seek($query_fakultas, 0);
                                    while ($f = mysqli_fetch_assoc($query_fakultas)) {
                                    ?>
                                        <option value="<?= htmlspecialchars($f['nama_fakultas']); ?>">
                                            <?= htmlspecialchars($f['nama_fakultas']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <small class="text-muted mt-1 d-block">Pilih fakultas sesuai dengan file Excel koleksi buku.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">File Excel (.xlsx) <span class="text-danger">*</span></label>
                                <input type="file" name="file_excel" class="form-control" accept=".xlsx" required>
                                <small class="text-muted mt-1 d-block">Gunakan format file beresktensi .xlsx.</small>
                            </div>
                        </div>

                        <div class="alert alert-info bg-info-subtle border-0 rounded-3 text-dark small mt-3 mb-3">
                            <h6 class="fw-bold text-primary mb-1"><i class="bi bi-info-circle me-1"></i> Petunjuk Import:</h6>
                            <ul class="mb-0 ps-3">
                                <li>Pilih fakultas tujuan terlebih dahulu.</li>
                                <li>Upload file Excel induk buku sesuai fakultas.</li>
                                <li>Pastikan nama program studi pada file Excel sudah sinkron dengan data di database.</li>
                            </ul>
                        </div>

                        <button type="submit" name="import" class="btn btn-success px-4">
                            <i class="bi bi-upload me-1"></i> Import Sekarang
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- FILTER & PENCARIAN -->
        <div class="custom-card filter-card">
            <div class="card-body">
                <form method="GET" action="buku.php">
                    <div class="row g-3">
                        <!-- FAKULTAS -->
                        <div class="col-xl-4 col-md-6">
                            <label class="filter-label">FAKULTAS</label>
                            <select name="fakultas" class="form-select" onchange="this.form.submit()">
                                <option value="">Semua Fakultas</option>
                                <?php
                                mysqli_data_seek($query_fakultas, 0);
                                while ($fak = mysqli_fetch_assoc($query_fakultas)):
                                ?>
                                    <option value="<?= htmlspecialchars($fak['nama_fakultas']); ?>" <?= ($filter_fakultas === $fak['nama_fakultas']) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($fak['nama_fakultas']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- PROGRAM STUDI -->
                        <div class="col-xl-4 col-md-6">
                            <label class="filter-label">PROGRAM STUDI / JURUSAN</label>
                            <select name="prodi" class="form-select" onchange="this.form.submit()">
                                <option value="">Semua Program Studi</option>
                                <?php
                                mysqli_data_seek($query_prodi, 0);
                                while ($prodi = mysqli_fetch_assoc($query_prodi)):
                                ?>
                                    <option value="<?= htmlspecialchars($prodi['nama_prodi']); ?>" <?= ($filter_prodi === $prodi['nama_prodi']) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($prodi['nama_prodi']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- PENCARIAN -->
                        <div class="col-xl-4 col-md-12">
                            <label class="filter-label">CARI BUKU</label>
                            <div class="input-group">
                                <input type="text" name="cari" class="form-control" placeholder="Judul, pengarang, penerbit, tahun..." value="<?= htmlspecialchars($keyword); ?>">
                                <button type="submit" class="btn btn-primary search-button">
                                    <i class="bi bi-search me-1"></i> Cari
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- FILTER AKTIF -->
                    <?php if ($keyword !== '' || $filter_fakultas !== '' || $filter_prodi !== ''): ?>
                        <div class="active-filter d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <small class="text-muted">Filter aktif:</small>
                                <?php if ($filter_fakultas !== ''): ?>
                                    <span class="badge bg-primary filter-badge"><?= htmlspecialchars($filter_fakultas); ?></span>
                                <?php endif; ?>
                                <?php if ($filter_prodi !== ''): ?>
                                    <span class="badge bg-success filter-badge"><?= htmlspecialchars($filter_prodi); ?></span>
                                <?php endif; ?>
                                <?php if ($keyword !== ''): ?>
                                    <span class="badge bg-secondary filter-badge">Pencarian: <?= htmlspecialchars($keyword); ?></span>
                                <?php endif; ?>
                            </div>
                            <a href="buku.php" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter
                            </a>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- INFO DATA -->
        <div class="data-info d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="data-total">
                Total data: <strong><?= number_format($total_data, 0, ',', '.'); ?></strong> buku
            </div>
            <div class="data-showing">
                Menampilkan <?= $nomor_awal; ?> - <?= $nomor_akhir; ?> dari <?= number_format($total_data, 0, ',', '.'); ?> data
            </div>
        </div>

        <!-- TABEL BUKU -->
        <div class="custom-card table-card d-none d-lg-block">
            <div class="table-wrapper">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th class="text-center" width="50">No</th>
                            <th>Fakultas</th>
                            <th>Program Studi</th>
                            <th>Judul Buku</th>
                            <th>Pengarang</th>
                            <th>Penerbit</th>
                            <th class="text-center" width="80">Tahun</th>
                            <th class="text-center" width="70">Stok</th>
                            <th class="text-center" width="110">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($data_buku)): ?>
                            <?php $no = $offset + 1; foreach ($data_buku as $row): ?>
                                <tr>
                                    <td class="text-center text-muted fw-semibold"><?= $no++; ?></td>
                                    <td><small><?= htmlspecialchars($row['nama_fakultas'] ?? '-'); ?></small></td>
                                    <td><small><?= htmlspecialchars($row['nama_prodi'] ?? '-'); ?></small></td>
                                    <td>
                                        <div class="fw-semibold text-dark" style="max-width: 320px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($row['judul_buku']); ?>">
                                            <?= htmlspecialchars($row['judul_buku']); ?>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($row['pengarang']); ?></td>
                                    <td><small class="text-muted"><?= htmlspecialchars($row['penerbit']); ?></small></td>
                                    <td class="text-center"><span class="badge bg-light text-dark border"><?= htmlspecialchars($row['tahun_terbit']); ?></span></td>
                                    <td class="text-center"><span class="badge bg-primary-subtle text-primary fw-bold"><?= htmlspecialchars($row['jumlah_eksemplar']); ?></span></td>
                                    <td class="text-center">
                                        <div class="action-buttons">
                                           <!-- Tombol Edit -->
<a href="edit_buku.php?id=<?= $row['id_buku']; ?>" class="btn btn-warning btn-sm" title="Edit Buku">
    <i class="bi bi-pencil-square"></i>
</a>

<!-- Tombol Hapus -->
<a href="hapus_buku.php?id=<?= $row['id_buku']; ?>" class="btn btn-danger btn-sm" title="Hapus Buku" onclick="return confirm('Apakah Anda yakin ingin menghapus buku ini?')">
    <i class="bi bi-trash"></i>
</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-journal-x fs-1 text-secondary opacity-50 d-block mb-2"></i>
                                    <h5>Tidak ada data buku ditemukan</h5>
                                    <p class="small mb-0">Coba ubah kata kunci pencarian atau reset filter Anda.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="book-list-mobile d-lg-none">
            <?php if (!empty($data_buku)): ?>
                <?php $no = $offset + 1; foreach ($data_buku as $row): ?>
                    <article class="book-mobile-card">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div class="book-mobile-title">
                                <span class="small text-muted d-block mb-1">Buku #<?= $no++; ?></span>
                                <h2 class="h6 fw-bold mb-0 text-dark"><?= htmlspecialchars($row['judul_buku']); ?></h2>
                            </div>
                            <div class="action-buttons flex-shrink-0">
                                <a href="edit_buku.php?id=<?= $row['id_buku']; ?>" class="btn btn-warning btn-sm" title="Edit buku" aria-label="Edit <?= htmlspecialchars($row['judul_buku'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a href="hapus_buku.php?id=<?= $row['id_buku']; ?>" class="btn btn-danger btn-sm" title="Hapus buku" aria-label="Hapus <?= htmlspecialchars($row['judul_buku'], ENT_QUOTES, 'UTF-8'); ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus buku ini?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </div>
                        <div class="book-mobile-meta">
                            <div><span class="book-mobile-label">Fakultas</span><span class="book-mobile-value"><?= htmlspecialchars($row['nama_fakultas'] ?? '-'); ?></span></div>
                            <div><span class="book-mobile-label">Program Studi</span><span class="book-mobile-value"><?= htmlspecialchars($row['nama_prodi'] ?? '-'); ?></span></div>
                            <div><span class="book-mobile-label">Pengarang</span><span class="book-mobile-value"><?= htmlspecialchars($row['pengarang']); ?></span></div>
                            <div><span class="book-mobile-label">Penerbit</span><span class="book-mobile-value"><?= htmlspecialchars($row['penerbit']); ?></span></div>
                            <div><span class="book-mobile-label">Tahun Terbit</span><span class="book-mobile-value"><?= htmlspecialchars($row['tahun_terbit']); ?></span></div>
                            <div><span class="book-mobile-label">Stok</span><span class="badge bg-primary-subtle text-primary fw-bold"><?= htmlspecialchars($row['jumlah_eksemplar']); ?></span></div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="custom-card text-center p-4 text-muted">
                    <i class="bi bi-journal-x fs-1 d-block mb-2"></i>
                    <h2 class="h6 fw-bold">Tidak ada data buku ditemukan</h2>
                    <p class="small mb-0">Coba ubah kata kunci atau reset filter.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- PAGINASI RAPI -->
        <?php if ($total_halaman > 1): ?>
        <div class="pagination-wrapper d-flex justify-content-center">
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <!-- Tombol Sebelumnya -->
                    <li class="page-item <?= ($halaman <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?halaman=<?= $halaman - 1; ?><?= $query_string; ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>

                    <?php
                    $range = 2;
                    $start_page = max(1, $halaman - $range);
                    $end_page = min($total_halaman, $halaman + $range);

                    if ($start_page > 1) {
                        echo '<li class="page-item"><a class="page-link" href="?halaman=1' . $query_string . '">1</a></li>';
                        if ($start_page > 2) { echo '<li class="page-item disabled"><span class="page-link">...</span></li>'; }
                    }

                    for ($i = $start_page; $i <= $end_page; $i++):
                    ?>
                        <li class="page-item <?= ($i == $halaman) ? 'active' : ''; ?>">
                            <a class="page-link" href="?halaman=<?= $i; ?><?= $query_string; ?>"><?= $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php
                    if ($end_page < $total_halaman) {
                        if ($end_page < $total_halaman - 1) { echo '<li class="page-item disabled"><span class="page-link">...</span></li>'; }
                        echo '<li class="page-item"><a class="page-link" href="?halaman=' . $total_halaman . $query_string . '">' . $total_halaman . '</a></li>';
                    }
                    ?>

                    <!-- Tombol Selanjutnya -->
                    <li class="page-item <?= ($halaman >= $total_halaman) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?halaman=<?= $halaman + 1; ?><?= $query_string; ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>

    </main>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>