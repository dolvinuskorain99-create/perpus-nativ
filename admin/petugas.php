<?php
// =====================================================
// SISTEM INFORMASI PERPUSTAKAAN USTJ
// HALAMAN: DATA PETUGAS / ADMIN
// FILE: admin/petugas.php
// =====================================================

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../koneksi.php';

// PROSES TAMBAH PETUGAS
if (isset($_POST['tambah_petugas'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = md5($_POST['password']); // Atau password_hash() jika disesuaikan
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);

    $query_insert = "INSERT INTO petugas (username, password, nama_lengkap) VALUES ('$username', '$password', '$nama_lengkap')";
    if (mysqli_query($koneksi, $query_insert)) {
        header("Location: petugas.php?pesan=sukses_tambah");
        exit();
    }
}

// PROSES HAPUS PETUGAS
if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus' && isset($_GET['id'])) {
    $id_petugas = (int)$_GET['id'];
    mysqli_query($koneksi, "DELETE FROM petugas WHERE id_petugas = $id_petugas");
    header("Location: petugas.php?pesan=sukses_hapus");
    exit();
}

// Ambil data petugas (sesuaikan nama tabel jika di database Anda bernama 'admin' atau 'user')
$query_petugas = mysqli_query($koneksi, "SELECT * FROM petugas ORDER BY id_petugas DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Petugas - Perpustakaan USTJ</title>
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
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div id="main-content-wrapper">
    <nav class="top-navbar">
        <div class="breadcrumb-admin text-secondary small">
            <span class="text-dark fw-semibold">PerpusAdmin USTJ</span> / <span class="text-primary fw-semibold">Data Petugas</span>
        </div>
        <div class="fw-semibold small text-muted">Universitas Sains dan Teknologi Jayapura</div>
    </nav>

    <main class="content-body">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title fs-4 fw-bold text-dark m-0"><i class="bi bi-shield-lock text-success me-2"></i>Manajemen Data Petugas</h1>
                <p class="text-muted small m-0">Kelola akun administrator atau petugas yang memiliki akses ke sistem.</p>
            </div>
            <button type="button" class="btn btn-primary btn-sm px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-lg me-1"></i> Tambah Petugas
            </button>
        </div>

        <!-- NOTIFIKASI -->
        <?php if (isset($_GET['pesan']) && $_GET['pesan'] === 'sukses_tambah'): ?>
            <div class="alert alert-success border-0 shadow-sm mb-4">Petugas baru berhasil ditambahkan!</div>
        <?php elseif (isset($_GET['pesan']) && $_GET['pesan'] === 'sukses_hapus'): ?>
            <div class="alert alert-success border-0 shadow-sm mb-4">Data petugas berhasil dihapus!</div>
        <?php endif; ?>

        <!-- TABEL PETUGAS -->
        <div class="custom-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle m-0">
                    <thead>
                        <tr>
                            <th class="text-center" width="60">No</th>
                            <th>Nama Lengkap</th>
                            <th>Username</th>
                            <th class="text-center" width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($query_petugas) > 0): ?>
                            <?php $no = 1; while ($row = mysqli_fetch_assoc($query_petugas)): ?>
                                <tr>
                                    <td class="text-center text-muted"><?= $no++; ?></td>
                                    <td><span class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_lengkap']); ?></span></td>
                                    <td><code><?= htmlspecialchars($row['username']); ?></code></td>
                                    <td class="text-center">
                                        <a href="petugas.php?aksi=hapus&id=<?= $row['id_petugas']; ?>" class="btn btn-danger btn-sm px-2 py-1" style="font-size: 12px;" onclick="return confirm('Yakin ingin menghapus petugas ini?')">
                                            <i class="bi bi-trash"></i> Hapus
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">Belum ada data petugas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- MODAL TAMBAH PETUGAS -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fs-6 fw-bold">Tambah Petugas Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" required placeholder="Contoh: Imanuel Korain">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Username Login</label>
                        <input type="text" name="username" class="form-control" required placeholder="Contoh: admin_ustj">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Password</label>
                        <input type="password" name="password" class="form-control" required placeholder="••••••••">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_petugas" class="btn btn-primary btn-sm px-4">Simpan Petugas</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>