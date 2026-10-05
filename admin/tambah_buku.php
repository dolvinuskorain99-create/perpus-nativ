<?php
// =====================================================
// SISTEM INFORMASI PERPUSTAKAAN USTJ
// HALAMAN: TAMBAH BUKU BARU
// FILE: admin/tambah_buku.php
// =====================================================

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../koneksi.php';

// Ambil data kategori untuk pilihan dropdown kategori
$query_kategori = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori ASC");

// PROSES SIMPAN DATA KETIKA FORM DI-SUBMIT
$pesan_error = '';
if (isset($_POST['simpan_buku'])) {
    $id_kategori      = (int)$_POST['id_kategori'];
    $judul_buku       = mysqli_real_escape_string($koneksi, $_POST['judul_buku']);
    $pengarang        = mysqli_real_escape_string($koneksi, $_POST['pengarang']);
    $penerbit         = mysqli_real_escape_string($koneksi, $_POST['penerbit']);
    $tahun_terbit     = (int)$_POST['tahun_terbit'];
    $jumlah_eksemplar = (int)$_POST['jumlah_eksemplar'];

    // Query insert sesuai struktur kolom tabel buku di database Anda
    $sql_insert = "INSERT INTO buku (id_kategori, judul_buku, pengarang, penerbit, tahun_terbit, jumlah_eksemplar) 
                   VALUES ($id_kategori, '$judul_buku', '$pengarang', '$penerbit', $tahun_terbit, $jumlah_eksemplar)";
    
    $query_simpan = mysqli_query($koneksi, $sql_insert);

    if ($query_simpan) {
        header("Location: buku.php?pesan=sukses_tambah");
        exit();
    } else {
        $pesan_error = "Gagal menyimpan data buku ke database: " . mysqli_error($koneksi);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Buku Baru - Perpustakaan USTJ</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --sidebar-width: 250px;
            --main-bg: #f8fafc;
            --border-color: #e2e8f0;
            --text-dark: #0f172a;
            --text-muted: #64748b;
        }

        body {
            background: var(--main-bg);
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            color: #1e293b;
            margin: 0; padding: 0;
        }

        #main-content-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            width: calc(100% - var(--sidebar-width));
        }

        .top-navbar {
            min-height: 70px; padding: 0 28px;
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            display: flex; align-items: center; justify-content: space-between;
        }

        .content-body { padding: 28px; max-width: 1000px; margin: 0 auto; }

        .custom-card {
            border: 1px solid var(--border-color);
            border-radius: 12px; background: #ffffff;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
            overflow: hidden;
        }

        .card-header-custom {
            background: #0f172a; color: #ffffff;
            padding: 16px 20px; font-weight: 600; font-size: 15px;
            display: flex; align-items: center; gap: 8px;
        }

        .form-label { font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px; }
        .form-control, .form-select {
            padding: 10px 14px; font-size: 13px; border-radius: 8px; border-color: #cbd5e1;
        }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15); border-color: #0d6efd;
        }

        @media (max-width: 991.98px) {
            #main-content-wrapper { margin-left: 0; width: 100%; }
        }
    </style>
</head>

<body>

<?php include 'sidebar.php'; ?>

<div id="main-content-wrapper">

    <nav class="top-navbar">
        <div class="breadcrumb-admin text-secondary small">
            <span class="text-dark fw-semibold">PerpusAdmin USTJ</span> / 
            <a href="buku.php" class="text-decoration-none text-muted">Data Buku</a> / 
            <span class="text-primary fw-semibold">Tambah Buku</span>
        </div>
        <div>
            <a href="buku.php" class="btn btn-secondary btn-sm px-3 fw-semibold">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </nav>

    <main class="content-body">

        <div class="mb-4">
            <h1 class="fs-4 fw-bold text-dark m-0">Tambah Buku Baru</h1>
            <p class="text-muted small m-0">Masukkan informasi detail buku baru ke dalam database perpustakaan.</p>
        </div>

        <?php if ($pesan_error !== ''): ?>
            <div class="alert alert-danger border-0 shadow-sm mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $pesan_error; ?>
            </div>
        <?php endif; ?>

        <div class="custom-card">
            <div class="card-header-custom">
                <i class="bi bi-plus-circle-fill text-primary"></i> Form Input Data Buku
            </div>
            
            <div class="p-4">
                <form method="POST" action="">
                    
                    <div class="mb-3">
                        <label class="form-label">Kategori Buku</label>
                        <select name="id_kategori" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php while ($kat = mysqli_fetch_assoc($query_kategori)): ?>
                                <option value="<?= $kat['id_kategori']; ?>"><?= htmlspecialchars($kat['nama_kategori']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Judul Buku</label>
                        <input type="text" name="judul_buku" class="form-control" required placeholder="Contoh: Pemrograman Web Lanjut dengan PHP & MySQL">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pengarang / Penulis</label>
                            <input type="text" name="pengarang" class="form-control" required placeholder="Contoh: Imanuel Korain">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Penerbit</label>
                            <input type="text" name="penerbit" class="form-control" required placeholder="Contoh: USTJ Press">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tahun Terbit</label>
                            <input type="number" name="tahun_terbit" class="form-control" required placeholder="Contoh: 2026" min="1900" max="2030">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jumlah Eksemplar / Stok</label>
                            <input type="number" name="jumlah_eksemplar" class="form-control" required placeholder="Contoh: 10" min="1">
                        </div>
                    </div>

                    <hr class="my-4 text-secondary opacity-25">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="buku.php" class="btn btn-light border px-4 btn-sm fw-semibold">Batal</a>
                        <button type="submit" name="simpan_buku" class="btn btn-primary px-4 btn-sm fw-semibold">
                            <i class="bi bi-save me-1"></i> Simpan Data Buku
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>