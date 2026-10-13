<?php
// =====================================================
// SISTEM INFORMASI PERPUSTAKAAN USTJ
// HALAMAN: DASHBOARD ADMIN
// FILE: admin/index.php
// =====================================================

include '../koneksi.php';

if (!$koneksi) {
    die('Koneksi database gagal: ' . mysqli_connect_error());
}

// 1. Hitung total data buku
$query_buku = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM buku");
$t_buku = mysqli_fetch_assoc($query_buku)['total'] ?? 0;

// 2. Hitung total data skripsi
$query_skripsi = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM skripsi");
$t_skripsi = mysqli_fetch_assoc($query_skripsi)['total'] ?? 0;

// 3. Hitung total data anggota/users
$query_anggota = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM users");
$t_anggota = mysqli_fetch_assoc($query_anggota)['total'] ?? 0;

// 4. Ambil 5 data skripsi terbaru
$query_skripsi_terbaru = mysqli_query($koneksi, "SELECT * FROM skripsi ORDER BY id_skripsi DESC LIMIT 5");

// 5. Ambil 5 data buku terbaru
$query_buku_terbaru = mysqli_query($koneksi, "SELECT * FROM buku ORDER BY id_buku DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Perpustakaan USTJ</title>

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
            transition: all 0.3s ease;
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

        .stat-card {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
        }

        .custom-card {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        }

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

        @media (max-width: 991.98px) {
            #main-content-wrapper { margin-left: 0; width: 100%; }
            .content-body { padding: 15px; }
            .top-navbar { padding: 0 15px; }
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
            <div class="breadcrumb-admin">
                <span class="text-dark fw-semibold">PerpusAdmin USTJ</span>
                <span class="mx-1">/</span>
                <span class="current">Dashboard</span>
            </div>
        </div>

        <div class="d-flex align-items-center gap-3">
            <span class="date-badge d-none d-md-inline-block">
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
        <div class="page-header">
            <h1 class="page-title d-flex align-items-center gap-2">
                <i class="bi bi-speedometer2 text-primary"></i>
                Dashboard Utama
            </h1>
            <p class="page-description">
                Ringkasan statistik dan data terbaru sistem perpustakaan Universitas Sains dan Teknologi Jayapura.
            </p>
        </div>

        <!-- KARTU STATISTIK -->
        <div class="row g-4 mb-4">
            <!-- Total Buku -->
            <div class="col-xl-4 col-md-6">
                <div class="stat-card p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-uppercase text-muted fw-bold" style="font-size: 11px; letter-spacing: .04em;">Total Koleksi Buku</span>
                            <h2 class="mt-2 mb-0 fw-bold text-dark"><?= number_format($t_buku, 0, ',', '.'); ?></h2>
                        </div>
                        <div class="p-3 bg-primary-subtle text-primary rounded-3 fs-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                            <i class="bi bi-journal-bookmark-fill"></i>
                        </div>
                    </div>
                    <hr class="my-3 text-muted opacity-25">
                    <a href="buku.php" class="text-primary text-decoration-none small fw-semibold d-flex justify-content-between align-items-center">
                        <span>Kelola data buku</span> <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- Total Skripsi -->
            <div class="col-xl-4 col-md-6">
                <div class="stat-card p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-uppercase text-muted fw-bold" style="font-size: 11px; letter-spacing: .04em;">Total Data Skripsi</span>
                            <h2 class="mt-2 mb-0 fw-bold text-dark"><?= number_format($t_skripsi, 0, ',', '.'); ?></h2>
                        </div>
                        <div class="p-3 bg-success-subtle text-success rounded-3 fs-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                            <i class="bi bi-mortarboard-fill"></i>
                        </div>
                    </div>
                    <hr class="my-3 text-muted opacity-25">
                    <a href="skripsi.php" class="text-success text-decoration-none small fw-semibold d-flex justify-content-between align-items-center">
                        <span>Kelola data skripsi</span> <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- Total Anggota -->
            <div class="col-xl-4 col-md-12">
                <div class="stat-card p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-uppercase text-muted fw-bold" style="font-size: 11px; letter-spacing: .04em;">Total Anggota / Users</span>
                            <h2 class="mt-2 mb-0 fw-bold text-dark"><?= number_format($t_anggota, 0, ',', '.'); ?></h2>
                        </div>
                        <div class="p-3 bg-warning-subtle text-warning rounded-3 fs-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                            <i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                    <hr class="my-3 text-muted opacity-25">
                    <a href="anggota.php" class="text-warning text-decoration-none small fw-semibold d-flex justify-content-between align-items-center text-dark">
                        <span>Kelola data anggota</span> <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- TABEL BUKU TERBARU -->
        <div class="custom-card mb-4">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-journal-check text-primary"></i> Koleksi Buku Terbaru
                </h6>
                <a href="buku.php" class="btn btn-sm btn-outline-primary fw-semibold">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4" width="50">No</th>
                            <th>Judul Buku</th>
                            <th>Penulis</th>
                            <th>Penerbit</th>
                            <th class="pe-4 text-center" width="90">Tahun</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($query_buku_terbaru) > 0): ?>
                            <?php $no = 1; while ($row_b = mysqli_fetch_assoc($query_buku_terbaru)): 
                                $judul_buku   = $row_b['judul_buku'] ?? $row_b['judul'] ?? $row_b['nama_buku'] ?? '-';
                                $penulis_buku = $row_b['pengarang'] ?? $row_b['penulis'] ?? '-';
                                $penerbit_buku = $row_b['penerbit'] ?? '-';
                                $tahun_buku   = $row_b['tahun_terbit'] ?? $row_b['tahun'] ?? '-';
                            ?>
                                <tr>
                                    <td class="ps-4 text-muted fw-semibold"><?= $no++; ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark text-truncate" style="max-width: 320px;" title="<?= htmlspecialchars($judul_buku); ?>">
                                            <?= htmlspecialchars($judul_buku); ?>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($penulis_buku); ?></td>
                                    <td class="text-muted"><?= htmlspecialchars($penerbit_buku); ?></td>
                                    <td class="pe-4 text-center"><span class="badge bg-light text-dark border"><?= htmlspecialchars($tahun_buku); ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada data buku yang tersimpan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TABEL SKRIPSI TERBARU -->
        <div class="custom-card mb-4">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-mortarboard-fill text-success"></i> Data Skripsi / Tugas Akhir Terbaru
                </h6>
                <a href="skripsi.php" class="btn btn-sm btn-outline-success fw-semibold">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4" width="50">No</th>
                            <th>NIM</th>
                            <th>Nama Penulis</th>
                            <th>Judul Skripsi</th>
                            <th class="pe-4">Program Studi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($query_skripsi_terbaru) > 0): ?>
                            <?php $no = 1; while ($row = mysqli_fetch_assoc($query_skripsi_terbaru)): ?>
                                <tr>
                                    <td class="ps-4 text-muted fw-semibold"><?= $no++; ?></td>
                                    <td><span class="font-monospace text-primary fw-semibold"><?= htmlspecialchars($row['nim_mahasiswa'] ?? '-'); ?></span></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($row['nama_penulis'] ?? '-'); ?></td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 400px;" title="<?= htmlspecialchars($row['judul_skripsi'] ?? ''); ?>">
                                            <?= htmlspecialchars($row['judul_skripsi'] ?? '-'); ?>
                                        </div>
                                    </td>
                                    <td class="pe-4"><small class="text-muted"><?= htmlspecialchars($row['prodi'] ?? '-'); ?></small></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada data skripsi yang tersimpan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
