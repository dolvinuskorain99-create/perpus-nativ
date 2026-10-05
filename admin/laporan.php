<?php
// =====================================================
// SISTEM INFORMASI PERPUSTAKAAN USTJ
// HALAMAN: LAPORAN TRANSAKSI
// FILE: admin/laporan.php
// =====================================================

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../koneksi.php';

// Filter berdasarkan tanggal jika ada
$tgl_mulai = isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : '';
$tgl_selesai = isset($_GET['tgl_selesai']) ? $_GET['tgl_selesai'] : '';

$where_clause = "";
if (!empty($tgl_mulai) && !empty($tgl_selesai)) {
    $where_clause = "WHERE peminjaman.tanggal_pinjam BETWEEN '$tgl_mulai' AND '$tgl_selesai'";
}

// Ambil data laporan dari database
$query_laporan = mysqli_query($koneksi, "SELECT peminjaman.*, buku.judul_buku AS judul, anggota.nama AS nama_anggota, anggota.nim 
                                         FROM peminjaman 
                                         JOIN buku ON peminjaman.id_buku = buku.id_buku 
                                         JOIN anggota ON peminjaman.id_anggota = anggota.id_anggota 
                                         $where_clause 
                                         ORDER BY peminjaman.id_peminjaman DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Perpustakaan - USTJ</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root { --sidebar-width: 250px; --main-bg: #f8fafc; --border-color: #e2e8f0; }
        body { background: var(--main-bg); font-family: 'Plus Jakarta Sans', sans-serif; color: #1e293b; }
        #main-content-wrapper { margin-left: var(--sidebar-width); min-height: 100vh; width: calc(100% - var(--sidebar-width)); }
        .top-navbar { min-height: 70px; padding: 0 28px; background: #fff; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; }
        .content-body { padding: 28px; max-width: 1400px; margin: 0 auto; }
        .custom-card { border: 1px solid var(--border-color); border-radius: 12px; background: #fff; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04); overflow: hidden; }
        .table thead th { background: #0f172a; color: #fff; font-size: 11px; text-transform: uppercase; padding: 13px 16px; border: none; }
        .table tbody td { padding: 14px 16px; font-size: 13px; border-bottom: 1px solid #edf1f5; vertical-align: middle; }
        
        @media print {
            #sidebar, .top-navbar, .no-print { display: none !important; }
            #main-content-wrapper { margin-left: 0 !important; width: 100% !important; background: #fff !important; }
            .custom-card { border: none !important; box-shadow: none !important; }
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div id="main-content-wrapper">
    <nav class="top-navbar no-print">
        <div class="breadcrumb-admin text-secondary small">
            <span class="text-dark fw-semibold">PerpusAdmin USTJ</span> / <span class="text-primary fw-semibold">Laporan</span>
        </div>
        <div class="fw-semibold small text-muted">Universitas Sains dan Teknologi Jayapura</div>
    </nav>

    <main class="content-body">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title fs-4 fw-bold text-dark m-0"><i class="bi bi-file-earmark-text text-primary me-2"></i>Laporan Transaksi Peminjaman</h1>
                <p class="text-muted small m-0">Rekapitulasi data peminjaman dan pengembalian buku perpustakaan.</p>
            </div>
            <div class="no-print">
                <button onclick="window.print()" class="btn btn-dark btn-sm px-3 fw-semibold"><i class="bi bi-printer me-1"></i> Cetak Laporan</button>
            </div>
        </div>

        <!-- FILTER TANGGAL -->
        <div class="custom-card p-3 mb-4 no-print">
            <form method="GET" action="" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Dari Tanggal</label>
                    <input type="date" name="tgl_mulai" class="form-control form-control-sm" value="<?= $tgl_mulai; ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Sampai Tanggal</label>
                    <input type="date" name="tgl_selesai" class="form-control form-control-sm" value="<?= $tgl_selesai; ?>">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm px-3 fw-semibold"><i class="bi bi-filter me-1"></i> Filter</button>
                    <a href="laporan.php" class="btn btn-light btn-sm px-3 border"><i class="bi bi-arrow-counterclockwise me-1"></i> Reset</a>
                </div>
            </form>
        </div>

        <!-- TABEL LAPORAN -->
        <div class="custom-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle m-0">
                    <thead>
                        <tr>
                            <th class="text-center" width="50">No</th>
                            <th>Peminjam (NIM)</th>
                            <th>Judul Buku</th>
                            <th class="text-center">Tgl Pinjam</th>
                            <th class="text-center">Tgl Kembali</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($query_laporan) > 0): ?>
                            <?php $no = 1; while ($row = mysqli_fetch_assoc($query_laporan)): ?>
                                <tr>
                                    <td class="text-center text-muted"><?= $no++; ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_anggota']); ?></div>
                                        <small class="text-muted">NIM: <?= htmlspecialchars($row['nim']); ?></small>
                                    </td>
                                    <td><span class="fw-semibold text-primary"><?= htmlspecialchars($row['judul']); ?></span></td>
                                    <td class="text-center"><?= date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?></td>
                                    <td class="text-center"><?= $row['tanggal_kembali'] ? date('d/m/Y', strtotime($row['tanggal_kembali'])) : '-'; ?></td>
                                    <td class="text-center">
                                        <?php if ($row['status'] === 'Dipinjam'): ?>
                                            <span class="badge bg-warning text-dark px-2 py-1" style="font-size: 11px;">Dipinjam</span>
                                        <?php else: ?>
                                            <span class="badge bg-success px-2 py-1" style="font-size: 11px;">Kembali</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Tidak ada data laporan transaksi.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>