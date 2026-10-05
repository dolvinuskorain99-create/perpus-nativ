<?php


// INI ADALAH LOGIKA DAN PEMANGILAN TAMBAH DATA SKRIPSI MANUAL 

if (isset($_POST['tambah_skripsi'])) {
    $nim = mysqli_real_escape_string($koneksi, trim($_POST['nim']));
    $nama_penulis = mysqli_real_escape_string($koneksi, trim($_POST['nama_penulis']));
    $judul_skripsi = mysqli_real_escape_string($koneksi, trim($_POST['judul_skripsi']));
    $fakultas = mysqli_real_escape_string($koneksi, trim($_POST['fakultas']));
    $prodi = mysqli_real_escape_string($koneksi, trim($_POST['prodi']));
    $tahun_lulus = mysqli_real_escape_string($koneksi, trim($_POST['tahun_lulus']));

    // Ambil id_prodi berdasarkan nama_prodi yang dipilih
    $q_id = mysqli_query($koneksi, "SELECT id_prodi FROM program_studi WHERE nama_prodi = '$prodi' LIMIT 1");
    $d_id = mysqli_fetch_assoc($q_id);
    $id_prodi = $d_id['id_prodi'] ?? 1;

    if (!empty($judul_skripsi) && !empty($nama_penulis)) {
        $query_insert = "INSERT INTO skripsi (id_prodi, fakultas, prodi, nim_mahasiswa, nama_penulis, judul_skripsi, tahun_lulus) 
                         VALUES ('$id_prodi', '$fakultas', '$prodi', '$nim', '$nama_penulis', '$judul_skripsi', '$tahun_lulus')";
        
        if (mysqli_query($koneksi, $query_insert)) {
            header("Location: skripsi.php?pesan=sukses_tambah");
            exit();
        } else {
            $error_tambah = "Gagal menyimpan data: " . mysqli_error($koneksi);
        }
    } else {
        $error_tambah = "Judul dan Nama Penulis wajib diisi!";
    }
}
// =====================================================
// SISTEM INFORMASI PERPUSTAKAAN USTJ
// HALAMAN: KELOLA DATA SKRIPSI (ADMIN)
// FILE: admin/skripsi.php
// =====================================================

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// =====================================================
// KONEKSI DATABASE
// =====================================================

include '../koneksi.php';

if (!$koneksi) {
    die('Koneksi database gagal: ' . mysqli_connect_error());
}

// =====================================================
// KONFIGURASI PAGINATION
// =====================================================

$batas = 10;

$halaman = isset($_GET['halaman'])
    ? (int) $_GET['halaman']
    : 1;

if ($halaman < 1) {
    $halaman = 1;
}

// =====================================================
// FILTER & SEARCH INPUT
// =====================================================

$keyword = isset($_GET['cari'])
    ? trim($_GET['cari'])
    : '';

$filter_fakultas = isset($_GET['fakultas'])
    ? trim($_GET['fakultas'])
    : '';

$filter_prodi = isset($_GET['prodi'])
    ? trim($_GET['prodi'])
    : '';

// =====================================================
// SUSUN KLAUSA WHERE
// =====================================================

$where = [];

// Pencarian
if ($keyword !== '') {
    $kw_esc = mysqli_real_escape_string(
        $koneksi,
        $keyword
    );

    $where[] = "(
        judul_skripsi LIKE '%$kw_esc%'
        OR nama_penulis LIKE '%$kw_esc%'
        OR nim_mahasiswa LIKE '%$kw_esc%'
        OR tahun_lulus LIKE '%$kw_esc%'
    )";
}

// Filter Fakultas
if ($filter_fakultas !== '') {
    $fak_esc = mysqli_real_escape_string(
        $koneksi,
        $filter_fakultas
    );

    $where[] = "fakultas = '$fak_esc'";
}

// Filter Program Studi
if ($filter_prodi !== '') {
    $prodi_esc = mysqli_real_escape_string(
        $koneksi,
        $filter_prodi
    );

    $where[] = "prodi = '$prodi_esc'";
}

$sql_where = !empty($where)
    ? 'WHERE ' . implode(' AND ', $where)
    : '';

// =====================================================
// HITUNG TOTAL DATA
// =====================================================

$sql_count = "
    SELECT COUNT(*) AS total
    FROM skripsi
    $sql_where
";

$query_count = mysqli_query(
    $koneksi,
    $sql_count
);

$data_count = mysqli_fetch_assoc($query_count);

$total_data = (int) ($data_count['total'] ?? 0);

// =====================================================
// PAGINATION
// =====================================================

$total_halaman = max(
    1,
    (int) ceil($total_data / $batas)
);

if ($halaman > $total_halaman) {
    $halaman = $total_halaman;
}

$offset = ($halaman - 1) * $batas;

// =====================================================
// QUERY DATA SKRIPSI
// =====================================================

$sql_data = "
    SELECT *
    FROM skripsi
    $sql_where
    ORDER BY id_skripsi DESC
    LIMIT $offset, $batas
";

$query_data = mysqli_query(
    $koneksi,
    $sql_data
);

// =====================================================
// AMBIL DAFTAR FAKULTAS
// =====================================================

$sql_fakultas = "
    SELECT DISTINCT nama_fakultas
    FROM program_studi
    WHERE nama_fakultas IS NOT NULL
    AND nama_fakultas != ''
    ORDER BY nama_fakultas ASC
";

$query_fakultas = mysqli_query(
    $koneksi,
    $sql_fakultas
);

// =====================================================
// AMBIL DAFTAR PROGRAM STUDI
// =====================================================

$sql_prodi = "
    SELECT DISTINCT
        nama_prodi,
        nama_fakultas
    FROM program_studi
    WHERE nama_prodi IS NOT NULL
    AND nama_prodi != ''
";

if ($filter_fakultas !== '') {
    $fak_esc = mysqli_real_escape_string(
        $koneksi,
        $filter_fakultas
    );

    $sql_prodi .= "
        AND nama_fakultas = '$fak_esc'
    ";
}

$sql_prodi .= "
    ORDER BY nama_prodi ASC
";

$query_prodi = mysqli_query(
    $koneksi,
    $sql_prodi
);

// =====================================================
// QUERY STRING PAGINATION
// =====================================================

$params = [];

if ($keyword !== '') {
    $params['cari'] = $keyword;
}

if ($filter_fakultas !== '') {
    $params['fakultas'] = $filter_fakultas;
}

if ($filter_prodi !== '') {
    $params['prodi'] = $filter_prodi;
}

$query_string = !empty($params)
    ? '&' . http_build_query($params)
    : '';

// =====================================================
// NOMOR DATA
// =====================================================

$nomor_awal = $total_data > 0
    ? $offset + 1
    : 0;

$nomor_akhir = min(
    $offset + $batas,
    $total_data
);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Data Skripsi - Perpustakaan USTJ</title>

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
        .btn-import { padding: 10px 15px; border-radius: 8px; font-size: 13px; font-weight: 600; }

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

        .form-select:focus, .form-control:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 .2rem rgba(13,110,253,.10);
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
        .table tbody tr { transition: background-color .15s ease; }
        .table tbody tr:hover { background-color: #f8fafc; }

        .col-no { width: 50px; min-width: 50px; text-align: center; }
        .col-nim { width: 120px; min-width: 120px; white-space: nowrap; font-family: monospace; font-weight: 600; color:rgb(17, 16, 16) !important; }
        .col-penulis { width: 170px; min-width: 160px; font-weight: 600; }
        .col-judul { width: auto; min-width: 330px; max-width: 450px; color: #0f172a; }
        .judul-text { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.5; }
        .col-fakultas { width: 190px; min-width: 170px; }
        .col-prodi { width: 180px; min-width: 170px; }
        .col-tahun { width: 85px; min-width: 80px; text-align: center; white-space: nowrap; }
        .col-aksi { width: 100px; min-width: 95px; text-align: center; white-space: nowrap; }

        .year-badge {
            display: inline-block;
            padding: 5px 8px;
            border-radius: 6px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #475569;
            font-size: 11px;
            font-family: monospace;
            font-weight: 600;
        }

        .action-buttons { display: flex; align-items: center; justify-content: center; gap: 4px; }
        .action-buttons .btn { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; }
        .action-buttons i { font-size: 14px; }

        .empty-state { padding: 55px 20px !important; }
        .empty-icon { font-size: 42px; color: #cbd5e1; }
        .empty-state h5 { margin-top: 12px; margin-bottom: 5px; font-size: 15px; color: #334155; }
        .empty-state p { font-size: 12px; color: #94a3b8; }

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
            .page-description { font-size: 12px; }
            .date-badge { display: none; }
            .admin-avatar { width: 32px; height: 32px; }
            .filter-card .card-body { padding: 16px; }
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
                <span>Skripsi</span>
                <span class="mx-1">/</span>
                <span class="current">Kelola Data</span>
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
                    <i class="bi bi-mortarboard-fill text-primary"></i>
                    Kelola Data Skripsi
                </h1>
                <p class="page-description">
                    Kelola data tugas akhir mahasiswa Universitas Sains dan Teknologi Jayapura
                </p>
            </div>
            <button type="button" class="btn btn-primary btn-import d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalTambahSkripsi">
                <li class="bi bi-plus-lg"></li>
                Tambah Skripsi
            </button>
            <div>
                <a href="import_excel.php" class="btn btn-success btn-import d-inline-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-excel"></i>
                    Import Skripsi
                </a>
            </div>
        </div>

        <!-- FILTER -->
        <div class="custom-card filter-card">
            <div class="card-body">
                <form method="GET" action="skripsi.php">
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
                            <label class="filter-label">CARI DATA</label>
                            <div class="input-group">
                                <input type="text" name="cari" class="form-control" placeholder="Judul, NIM, nama penulis..." value="<?= htmlspecialchars($keyword); ?>">
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
                            <a href="skripsi.php" class="btn btn-outline-secondary btn-sm">
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
                Total data: <strong><?= number_format($total_data, 0, ',', '.'); ?></strong> skripsi
            </div>
            <div class="data-showing">
                Menampilkan <?= $nomor_awal; ?> - <?= $nomor_akhir; ?> dari <?= number_format($total_data, 0, ',', '.'); ?> data
            </div>
        </div>

        <!-- TABLE CARD -->
        <div class="custom-card table-card">
            <div class="table-wrapper">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th class="col-no">No</th>
                            <th class="col-nim">NIM</th>
                            <th class="col-penulis">Nama Penulis</th>
                            <th class="col-judul">Judul Skripsi</th>
                            <th class="col-fakultas">Fakultas</th>
                            <th class="col-prodi">Program Studi</th>
                            <th class="col-tahun">Tahun</th>
                            <th class="col-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($query_data) > 0): ?>
                            <?php 
                            $no = $offset + 1;
                            while ($row = mysqli_fetch_assoc($query_data)): 
                            ?>
                                <tr>
                                    <td class="col-no text-muted fw-semibold"><?= $no++; ?></td>
                                    <td class="col-nim"><?= htmlspecialchars($row['nim_mahasiswa'] ?? '-'); ?></td>
                                    <td class="col-penulis"><?= htmlspecialchars($row['nama_penulis'] ?? '-'); ?></td>
                                    <td class="col-judul">
                                        <div class="judul-text" title="<?= htmlspecialchars($row['judul_skripsi']); ?>">
                                            <?= htmlspecialchars($row['judul_skripsi']); ?>
                                        </div>
                                    </td>
                                    <td class="col-fakultas"><small><?= htmlspecialchars($row['fakultas'] ?? '-'); ?></small></td>
                                    <td class="col-prodi"><small><?= htmlspecialchars($row['prodi'] ?? '-'); ?></small></td>
                                    <td class="col-tahun">
                                        <span class="year-badge"><?= htmlspecialchars($row['tahun_lulus'] ?? '-'); ?></span>
                                    </td>
                                    <td class="col-aksi">
                                        <div class="action-buttons">
                                            <a href="edit_skripsi.php?id=<?= $row['id_skripsi']; ?>" class="btn btn-warning text-white btn-sm" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="hapus_skripsi.php?id=<?= $row['id_skripsi']; ?>" class="btn btn-danger btn-sm" title="Hapus" onclick="return confirm('Yakin ingin menghapus data skripsi ini?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center empty-state">
                                    <i class="bi bi-folder2-open empty-icon"></i>
                                    <h5>Tidak ada data skripsi ditemukan</h5>
                                    <p>Coba ubah kata kunci pencarian atau reset filter Anda.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PAGINASI RAPI (MAKSIMAL BEBERAPA NOMOR & PREV/NEXT) -->
        <?php if ($total_halaman > 1): ?>
        <div class="pagination-wrapper d-flex justify-content-center">
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <!-- Tombol Sebelumnya -->
                    <li class="page-item <?= ($halaman <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?halaman=<?= $halaman - 1; ?><<?= $query_string; ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>

                    <?php
                    $range = 2; // Jumlah nomor halaman di kiri/kanan halaman aktif
                    $start_page = max(1, $halaman - $range);
                    $end_page = min($total_halaman, $halaman + $range);

                    if ($start_page > 1) {
                        echo '<li class="page-item"><a class="page-link" href="?halaman=1' . $query_string . '">1</a></li>';
                        if ($start_page > 2) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                    }

                    for ($i = $start_page; $i <= $end_page; $i++):
                    ?>
                        <li class="page-item <?= ($i == $halaman) ? 'active' : ''; ?>">
                            <a class="page-link" href="?halaman=<?= $i; ?><?= $query_string; ?>"><?= $i; ?></a>
                        </li>
                    <?php
                    endfor;

                    if ($end_page < $total_halaman) {
                        if ($end_page < $total_halaman - 1) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
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
<script>
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar'); // Sesuaikan selector sidebar Anda jika perlu
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            // Logika toggle sidebar mobile jika ada
        });
    }
</script>

<!-- MODAL TAMBAH SKRIPSI -->
<div class="modal fade" id="modalTambahSkripsi" tabindex="-1" aria-labelledby="modalTambahSkripsiLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="modalTambahSkripsiLabel">
                    <i class="bi bi-mortarboard-fill me-2"></i> Tambah Data Skripsi Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="skripsi.php" method="POST">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">NIM Mahasiswa</label>
                            type="text" name="nim" class="form-control" placeholder="Contoh: 202101234">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nama Penulis <span class="text-danger">*</span></label>
                            <input type="text" name="nama_penulis" class="form-control" placeholder="Nama lengkap mahasiswa" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Judul Skripsi <span class="text-danger">*</span></label>
                            <textarea name="judul_skripsi" class="form-control" rows="3" placeholder="Masukkan judul lengkap skripsi..." required></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Fakultas <span class="text-danger">*</span></label>
                            <select name="fakultas" class="form-select" required>
                                <option value="">-- Pilih Fakultas --</option>
                                <?php
                                mysqli_data_seek($query_fakultas, 0);
                                while ($fak = mysqli_fetch_assoc($query_fakultas)):
                                ?>
                                    <option value="<?= htmlspecialchars($fak['nama_fakultas']); ?>">
                                        <?= htmlspecialchars($fak['nama_fakultas']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Program Studi <span class="text-danger">*</span></label>
                            <select name="prodi" class="form-select" required>
                                <option value="">-- Pilih Program Studi --</option>
                                <?php
                                mysqli_data_seek($query_prodi, 0);
                                while ($prodi = mysqli_fetch_assoc($query_prodi)):
                                ?>
                                    <option value="<?= htmlspecialchars($prodi['nama_prodi']); ?>">
                                        <?= htmlspecialchars($prodi['nama_prodi']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Tahun Lulus <span class="text-danger">*</span></label>
                            <input type="number" name="tahun_lulus" class="form-control" placeholder="Contoh: 2025" min="2000" max="2030" value="<?= date('Y'); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-3 px-4 rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_skripsi" class="btn btn-primary px-4">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>