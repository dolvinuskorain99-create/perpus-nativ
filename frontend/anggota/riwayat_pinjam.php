<?php
// =====================================================
// SISTEM INFORMASI PERPUSTAKAAN USTJ
// HALAMAN: RIWAYAT PEMINJAMAN ANGGOTA
// FILE: frontend/anggota/riwayat_pinjam.php
// =====================================================
session_start();
require_once '../../koneksi.php';

if (!isset($_SESSION['user_login']) || $_SESSION['user_login'] !== true) {
    header("Location: ../login.php");
    exit;
}

// Cari id_anggota yang benar berdasarkan sesi login user: users.nim -> anggota.nim
$id_user = (int)($_SESSION['id_user'] ?? $_SESSION['user_id'] ?? $_SESSION['id'] ?? $_SESSION['id_anggota'] ?? 0);
$id_anggota = 0;
if ($id_user > 0) {
    $q_ang = mysqli_query($koneksi, "SELECT a.id_anggota, a.nim
                                     FROM users u
                                     JOIN anggota a ON a.nim = u.nim
                                     WHERE u.id_user = $id_user LIMIT 1");
    $r_ang = $q_ang ? mysqli_fetch_assoc($q_ang) : null;
    $id_anggota = (int)($r_ang['id_anggota'] ?? 0);
}

// Ambil data riwayat peminjaman berdasarkan id_anggota yang sedang login
$query_riwayat = null;
if ($id_anggota > 0) {
    $query_riwayat = mysqli_query($koneksi, "SELECT peminjaman.*, buku.judul_buku, buku.pengarang, buku.penerbit 
                                            FROM peminjaman 
                                            JOIN buku ON peminjaman.id_buku = buku.id_buku 
                                            WHERE peminjaman.id_anggota = $id_anggota 
                                            ORDER BY peminjaman.id_peminjaman DESC");
}

$nomor = 1;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pinjam - Perpustakaan USTJ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .main-content { margin-left: 260px; padding: 35px; }
        .card-custom { border: none; border-radius: 16px; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.04); background: #ffffff; }
        .table-custom th { background-color: #f1f3f5; font-weight: 600; color: #495057; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #dee2e6; }
        .table-custom td { vertical-align: middle; font-size: 0.9rem; color: #333; padding: 14px 12px; }
        .book-title { font-weight: 700; color: #2b2d42; font-size: 0.95rem; }
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
                    <h3 class="fw-bold text-dark mb-1"><i class="bi bi-clock-history text-primary me-2"></i> Riwayat Peminjaman</h3>
                    <p class="text-muted mb-0">Daftar buku yang pernah atau sedang Anda pinjam di perpustakaan USTJ.</p>
                </div>
            </div>

            <!-- Tabel Riwayat -->
            <div class="card card-custom p-4">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h5 class="fw-bold text-dark m-0">Catatan Transaksi Anda</h5>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-custom align-middle">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 5%;">No</th>
                                <th style="width: 35%;">Judul Buku</th>
                                <th style="width: 20%;">Tanggal Pinjam</th>
                                <th style="width: 20%;">Tanggal Kembali</th>
                                <th class="text-center" style="width: 20%;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($query_riwayat && mysqli_num_rows($query_riwayat) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($query_riwayat)): ?>
                                    <tr>
                                        <td class="text-center text-muted fw-bold"><?= $nomor++; ?></td>
                                        <td>
                                            <div class="book-title"><?= htmlspecialchars($row['judul_buku']); ?></div>
                                            <div class="text-muted small">
                                                <i class="bi bi-person text-secondary me-1"></i> <?= htmlspecialchars($row['pengarang'] ?? '-'); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-dark"><i class="bi bi-calendar-event text-muted me-1"></i> <?= date('d-m-Y', strtotime($row['tanggal_pinjam'])); ?></div>
                                        </td>
                                        <td>
                                            <div class="text-dark">
                                                <?php if (!empty($row['tanggal_kembali']) && $row['tanggal_kembali'] !== '0000-00-00'): ?>
                                                    <i class="bi bi-calendar-check text-success me-1"></i> <?= date('d-m-Y', strtotime($row['tanggal_kembali'])); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <?php 
                                                $status = trim($row['status']);
                                                if (strtolower($status) === 'dipinjam' || strtolower($status) === 'pinjam'): 
                                            ?>
                                                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold shadow-sm">
                                                    <i class="bi bi-hourglass-split me-1"></i> Sedang Dipinjam
                                                </span>
                                            <?php elseif (strtolower($status) === 'dikembalikan' || strtolower($status) === 'kembali'): ?>
                                                <span class="badge bg-success text-white px-3 py-2 rounded-pill fw-bold shadow-sm">
                                                    <i class="bi bi-check-circle me-1"></i> Dikembalikan
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary text-white px-3 py-2 rounded-pill fw-bold shadow-sm">
                                                    <?= htmlspecialchars($status); ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-journal-x display-5 d-block mb-2 text-secondary opacity-50"></i>
                                        Belum ada riwayat transaksi peminjaman buku yang tercatat di sistem.
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