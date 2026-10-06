<?php
// =====================================================
// SISTEM INFORMASI PERPUSTAKAAN USTJ
// HALAMAN: PEMINJAMAN MANDIRI
// FILE: frontend/anggota/peminjaman_mandiri.php
// =====================================================
session_start();
require_once '../../koneksi.php';

if (!isset($_SESSION['user_login']) || $_SESSION['user_login'] !== true) {
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['user_id'];
$nama_lengkap = $_SESSION['nama_lengkap'] ?? 'Mahasiswa';

// Ambil data user untuk NIM / ID Anggota
$query_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = '$id_user'");
$data_user = mysqli_fetch_assoc($query_user);
$nim = $data_user['nim'] ?? '-';

// Proses aksi pinjam buku jika tombol diklik
$pesan_sukses = '';
$pesan_error = '';

if (isset($_POST['pinjam_buku'])) {
    $id_buku = mysqli_real_escape_string($koneksi, $_POST['id_buku']);
    $tanggal_pinjam = date('Y-m-d');
    $tanggal_kembali = date('Y-m-d', strtotime('+7 days'));
    $status = 'dipinjam';

    $cek_buku = mysqli_query($koneksi, "SELECT jumlah_eksemplar, judul_buku FROM buku WHERE id_buku = '$id_buku'");
    $data_buku = mysqli_fetch_assoc($cek_buku);

    if ($data_buku && $data_buku['jumlah_eksemplar'] > 0) {
        $query_pinjam = "INSERT INTO peminjaman (id_buku, id_anggota, tanggal_pinjam, tanggal_kembali, status) 
                         VALUES ('$id_buku', '$id_user', '$tanggal_pinjam', '$tanggal_kembali', '$status')";
        
        if (mysqli_query($koneksi, $query_pinjam)) {
            mysqli_query($koneksi, "UPDATE buku SET jumlah_eksemplar = jumlah_eksemplar - 1 WHERE id_buku = '$id_buku'");
            $pesan_sukses = "Berhasil mengajukan peminjaman untuk buku: <strong>" . htmlspecialchars($data_buku['judul_buku']) . "</strong>!";
        } else {
            $pesan_error = "Gagal memproses peminjaman: " . mysqli_error($koneksi);
        }
    } else {
        $pesan_error = "Maaf, stok eksemplar buku ini sedang habis!";
    }
}

// Logika Pencarian Buku (PERBAIKAN: Menambahkan tanda $ di depan kata search)
$search = isset($_GET['cari']) ? mysqli_real_escape_string($koneksi, $_GET['cari']) : '';
if ($search != '') {
    $query_buku = mysqli_query($koneksi, "SELECT * FROM buku WHERE judul_buku LIKE '%$search%' OR pengarang LIKE '%$search%' OR penerbit LIKE '%$search%' ORDER BY id_buku DESC");
} else {
    $query_buku = mysqli_query($koneksi, "SELECT * FROM buku ORDER BY id_buku DESC LIMIT 20");
}
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
        .main-content { margin-left: 260px; padding: 30px; }
        @media (max-width: 768px) { .main-content { margin-left: 0; padding: 15px; } }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <!-- Header & Search Bar -->
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div>
                    <h3 class="fw-bold text-dark mb-1">Peminjaman Mandiri</h3>
                    <p class="text-muted mb-0">Cari buku koleksi perpustakaan dan ajukan peminjaman dengan cepat.</p>
                </div>
                <form action="" method="GET" class="d-flex gap-2">
                    <input type="text" name="cari" class="form-control form-control-sm rounded-3 px-3" placeholder="Cari judul/pengarang..." value="<?= htmlspecialchars($search); ?>" style="width: 240px;">
                    <button type="submit" class="btn btn-primary btn-sm rounded-3 px-3"><i class="bi bi-search"></i></button>
                </form>
            </div>

            <!-- Notifikasi Sukses / Error -->
            <?php if (!empty($pesan_sukses)): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> <?= $pesan_sukses; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($pesan_error)): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $pesan_error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Daftar Buku dalam Tabel -->
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h5 class="fw-bold text-dark mb-3"><i class="bi bi-journal-richtext me-2 text-primary"></i> Katalog Buku Tersedia</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="py-3">No</th>
                                <th class="py-3">Judul Buku</th>
                                <th class="py-3">Pengarang</th>
                                <th class="py-3">Penerbit</th>
                                <th class="py-3 text-center">Tahun</th>
                                <th class="py-3 text-center">Stok</th>
                                <th class="py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($query_buku) > 0): ?>
                                <?php $no = 1; while ($buku = mysqli_fetch_assoc($query_buku)): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td>
                                            <span class="fw-bold text-dark d-block"><?= htmlspecialchars($buku['judul_buku']); ?></span>
                                            <small class="text-muted">ID Buku: #<?= $buku['id_buku']; ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($buku['pengarang'] ?? '-'); ?></td>
                                        <td><?= htmlspecialchars($buku['penerbit'] ?? '-'); ?></td>
                                        <td class="text-center"><?= htmlspecialchars($buku['tahun_terbit'] ?? '-'); ?></td>
                                        <td class="text-center">
                                            <?php if ($buku['jumlah_eksemplar'] > 0): ?>
                                                <span class="badge bg-success bg-opacity-15 text-success px-2 py-1"><?= $buku['jumlah_eksemplar']; ?> Tersedia</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger bg-opacity-15 text-danger px-2 py-1">Habis</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($buku['jumlah_eksemplar'] > 0): ?>
                                                <form action="" method="POST" class="d-inline" onsubmit="return confirm('Ajukan peminjaman untuk buku <?= htmlspecialchars($buku['judul_buku']); ?>?')">
                                                    <input type="hidden" name="id_buku" value="<?= $buku['id_buku']; ?>">
                                                    <button type="submit" name="pinjam_buku" class="btn btn-sm btn-primary rounded-3 px-3 fw-semibold">
                                                        <i class="bi bi-box-arrow-in-right me-1"></i> Pinjam
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-secondary rounded-3 px-3" disabled>Habis</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Belum ada data buku ditemukan.</td>
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