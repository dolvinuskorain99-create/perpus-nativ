<?php
// =====================================================
// SISTEM INFORMASI PERPUSTAKAAN USTJ
// HALAMAN: RIWAYAT / DAFTAR BUKU YANG SUDAH DIKEMBALIKAN
// FILE: admin/pengembalian.php
// =====================================================

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include '../koneksi.php';

if (!$koneksi) {
    die('Koneksi database gagal: ' . mysqli_connect_error());
}

// Cek kolom apa saja yang ada di tabel anggota agar query aman dari error
$kolom_nama_anggota = 'nama';
$check_cols = mysqli_query($koneksi, "SHOW COLUMNS FROM anggota");
if ($check_cols) {
    while ($col = mysqli_fetch_assoc($check_cols)) {
        if ($col['Field'] === 'nama_lengkap') {
            $kolom_nama_anggota = 'nama_lengkap';
            break;
        } elseif ($col['Field'] === 'nama_anggota') {
            $kolom_nama_anggota = 'nama_anggota';
            break;
        } elseif ($col['Field'] === 'nama') {
            $kolom_nama_anggota = 'nama';
        }
    }
}

// Ambil daftar transaksi yang statusnya sudah 'Kembali' (Selesai dikembalikan)
$query_kembali = mysqli_query($koneksi, "SELECT peminjaman.*, buku.judul_buku AS judul, 
                                         anggota.$kolom_nama_anggota AS nama_anggota, 
                                         anggota.nim 
                                         FROM peminjaman 
                                         JOIN buku ON peminjaman.id_buku = buku.id_buku 
                                         JOIN anggota ON peminjaman.id_anggota = anggota.id_anggota 
                                         WHERE LOWER(peminjaman.status) = 'kembali' 
                                         ORDER BY peminjaman.id_peminjaman DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pengembalian Buku - Perpustakaan USTJ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .main-content { margin-left: 260px; padding: 35px; }
        .card-custom { border: none; border-radius: 16px; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.04); background: #ffffff; }
        .table-custom th { background-color: #f1f3f5; font-weight: 600; color: #495057; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #dee2e6; }
        .table-custom td { vertical-align: middle; font-size: 0.9rem; color: #333; padding: 14px 12px; }
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
                    <h3 class="fw-bold text-dark mb-1"><i class="bi bi-journal-check text-success me-2"></i> Data Pengembalian Buku</h3>
                    <p class="text-muted mb-0">Daftar riwayat buku yang telah sukses dikembalikan oleh anggota perpustakaan.</p>
                </div>
            </div>

            <!-- Tabel Daftar Buku yang Sudah Dikembalikan -->
            <div class="card card-custom p-4">
                <h5 class="fw-bold text-dark mb-3">Riwayat Pengembalian Selesai</h5>

                <div class="table-responsive">
                    <table class="table table-hover table-custom align-middle">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 5%;">No</th>
                                <th style="width: 30%;">Nama Peminjam (NIM)</th>
                                <th style="width: 30%;">Judul Buku</th>
                                <th style="width: 15%;">Tgl Pinjam</th>
                                <th style="width: 15%;">Tgl Kembali</th>
                                <th class="text-center" style="width: 10%;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if ($query_kembali && mysqli_num_rows($query_kembali) > 0): 
                                while ($row = mysqli_fetch_assoc($query_kembali)): 
                            ?>
                                <tr>
                                    <td class="text-center text-muted fw-bold"><?= $no++; ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_anggota'] ?? 'Mahasiswa'); ?></div>
                                        <div class="text-muted small">NIM: <?= htmlspecialchars($row['nim']); ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-primary"><?= htmlspecialchars($row['judul']); ?></div>
                                    </td>
                                    <td>
                                        <div><i class="bi bi-calendar-event text-muted me-1"></i> <?= date('d-m-Y', strtotime($row['tanggal_pinjam'])); ?></div>
                                    </td>
                                    <td>
                                        <div><i class="bi bi-calendar-check text-success me-1"></i> <?= !empty($row['tanggal_kembali']) ? date('d-m-Y', strtotime($row['tanggal_kembali'])) : '-'; ?></div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-bold shadow-sm">
                                            <i class="bi bi-check-circle-fill me-1"></i> Kembali
                                        </span>
                                    </td>
                                </tr>
                            <?php 
                                endwhile; 
                            else: 
                            ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-journal-x display-5 d-block mb-2 text-secondary opacity-50"></i>
                                        Belum ada data buku yang dikembalikan.<br>
                                        <small>Buku yang selesai dikembalikan akan otomatis masuk ke halaman ini.</small>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>