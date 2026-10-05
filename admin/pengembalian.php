<?php
// =====================================================
// SISTEM INFORMASI PERPUSTAKAAN USTJ
// HALAMAN: PENGEMBALIAN BUKU
// FILE: admin/pengembalian.php
// =====================================================

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../koneksi.php';

if (!$koneksi) {
    die('Koneksi database gagal: ' . mysqli_connect_error());
}

$pesan_sukses = '';
$pesan_gagal = '';

// PROSES KEMBALIKAN BUKU
if (isset($_GET['aksi']) && $_GET['aksi'] === 'proses_kembali' && isset($_GET['id'])) {
    $id_pinjam = (int)$_GET['id'];

    // Ambil data peminjaman yang statusnya masih 'Dipinjam'
    // Sesuaikan 'judul_buku' jika di database Anda kolomnya berbeda
    $q_pinjam = mysqli_query($koneksi, "SELECT peminjaman.*, buku.id_buku FROM peminjaman 
                                        JOIN buku ON peminjaman.id_buku = buku.id_buku 
                                        WHERE peminjaman.id_peminjaman = $id_pinjam AND peminjaman.status = 'Dipinjam'");
    $dt_pinjam = mysqli_fetch_assoc($q_pinjam);

    if ($dt_pinjam) {
        $id_buku_kembali = $dt_pinjam['id_buku'];
        $tanggal_sekarang = date('Y-m-d');

        // 1. Update status peminjaman menjadi 'Kembali' dan catat tanggal kembali
        $update_peminjaman = mysqli_query($koneksi, "UPDATE peminjaman SET status = 'Kembali', tanggal_kembali = '$tanggal_sekarang' WHERE id_peminjaman = $id_pinjam");

        // 2. Kembalikan stok buku (+1)
        mysqli_query($koneksi, "UPDATE buku SET stok = stok + 1 WHERE id_buku = $id_buku_kembali");

        if ($update_peminjaman) {
            header("Location: pengembalian.php?pesan=sukses_kembali");
            exit();
        } else {
            $pesan_gagal = "Gagal memproses pengembalian buku!";
        }
    }
}

// Ambil daftar transaksi yang statusnya masih 'Dipinjam'
// (Sesuaikan 'judul_buku' dengan struktur tabel buku Anda)
$query_aktif = mysqli_query($koneksi, "SELECT peminjaman.*, buku.judul_buku AS judul, anggota.nama AS nama_anggota, anggota.nim 
                                       FROM peminjaman 
                                       JOIN buku ON peminjaman.id_buku = buku.id_buku 
                                       JOIN anggota ON peminjaman.id_anggota = anggota.id_anggota 
                                       WHERE peminjaman.status = 'Dipinjam' 
                                       ORDER BY peminjaman.id_peminjaman DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengembalian Buku - Perpustakaan USTJ</title>

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
            overflow: hidden;
        }

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

        .badge-status { padding: 6px 12px; font-size: 11px; font-weight: 600; border-radius: 6px; background: #fef3c7; color: #d97706; }

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
                <span class="current">Pengembalian Buku</span>
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
        <div class="page-header">
            <h1 class="page-title d-flex align-items-center gap-2">
                <i class="bi bi-journal-check text-success"></i>
                Daftar Buku Yang Sedang Dipinjam
            </h1>
            <p class="page-description">
                Proses pengembalian buku perpustakaan Universitas Sains dan Teknologi Jayapura.
            </p>
        </div>

        <!-- ALERTS -->
        <?php if (isset($_GET['pesan']) && $_GET['pesan'] === 'sukses_kembali'): ?>
            <div class="alert alert-success border-0 shadow-sm mb-4">
                <i class="bi bi-check-circle-fill me-2"></i> Buku berhasil dikembalikan dan stok buku di database telah dipulihkan!
            </div>
        <?php endif; ?>

        <?php if ($pesan_gagal !== ''): ?>
            <div class="alert alert-danger border-0 shadow-sm mb-4">
                <i class="bi bi-x-circle-fill me-2"></i> <?= $pesan_gagal; ?>
            </div>
        <?php endif; ?>

        <!-- TABEL BUKU AKTIF DIPINJAM -->
        <div class="custom-card">
            <div class="table-wrapper">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th class="text-center" width="60">No</th>
                            <th>Nama Peminjam (NIM)</th>
                            <th>Judul Buku</th>
                            <th class="text-center">Tgl Pinjam</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" width="160">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($query_aktif) > 0): ?>
                            <?php $no = 1; while ($row = mysqli_fetch_assoc($query_aktif)): ?>
                                <tr>
                                    <td class="text-center text-muted fw-semibold"><?= $no++; ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_anggota']); ?></div>
                                        <small class="text-muted">NIM: <?= htmlspecialchars($row['nim']); ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-primary"><?= htmlspecialchars($row['judul']); ?></div>
                                    </td>
                                    <td class="text-center"><?= date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?></td>
                                    <td class="text-center">
                                        <span class="badge-status">Dipinjam</span>
                                    </td>
                                    <td class="text-center">
                                        <a href="pengembalian.php?aksi=proses_kembali&id=<?= $row['id_peminjaman']; ?>" class="btn btn-success btn-sm px-3 py-1 text-white fw-semibold" style="font-size: 12px; border-radius: 6px;" onclick="return confirm('Konfirmasi bahwa buku ini sudah dikembalikan oleh anggota?')">
                                            <i class="bi bi-check2-circle me-1"></i> Kembalikan
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-check2-all fs-1 text-success opacity-50 d-block mb-2"></i>
                                    <h5>Tidak ada buku yang sedang dipinjam saat ini</h5>
                                    <p class="small text-muted mb-0">Semua buku pinjaman sudah dikembalikan.</p>
                                </td>
                            </tr>
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