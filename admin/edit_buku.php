<?php
// =====================================================
// SISTEM INFORMASI PERPUSTAKAAN USTJ
// HALAMAN: EDIT BUKU
// FILE: admin/edit_buku.php
// =====================================================

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../koneksi.php';

if (!$koneksi) {
    die('Koneksi database gagal: ' . mysqli_connect_error());
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: buku.php?pesan=error_id");
    exit();
}

$id_buku = (int)$_GET['id'];

// Ambil data buku berdasarkan ID
$query_buku = mysqli_query($koneksi, "SELECT * FROM buku WHERE id_buku = $id_buku");
$buku = mysqli_fetch_assoc($query_buku);

if (!$buku) {
    header("Location: buku.php?pesan=tidak_ditemukan");
    exit();
}

$pesan_gagal = '';

// Proses Simpan Perubahan Data Buku
if (isset($_POST['update_buku'])) {
    $judul         = mysqli_real_escape_string($koneksi, trim($_POST['judul'] ?? ''));
    $id_kategori   = (int)($_POST['id_kategori'] ?? 0);
    $penulis       = mysqli_real_escape_string($koneksi, trim($_POST['penulis'] ?? ''));
    $penerbit      = mysqli_real_escape_string($koneksi, trim($_POST['penerbit'] ?? ''));
    $tahun_terbit  = (int)($_POST['tahun_terbit'] ?? 0);
    $stok          = (int)($_POST['stok'] ?? 0);

    if (!empty($judul) && $id_kategori > 0) {
        // Catatan: Jika nama kolom di database Anda berbeda (misal: 'pengarang' bukan 'penulis'), 
        // sesuaikan nama kolom di bawah ini dengan struktur tabel di phpMyAdmin Anda.
        $query_update = "UPDATE buku SET 
                            judul = '$judul', 
                            id_kategori = $id_kategori, 
                            penulis = '$penulis', 
                            penerbit = '$penerbit', 
                            tahun_terbit = $tahun_terbit, 
                            stok = $stok 
                         WHERE id_buku = $id_buku";

        if (mysqli_query($koneksi, $query_update)) {
            header("Location: buku.php?pesan=sukses_edit");
            exit();
        } else {
            $pesan_gagal = "Gagal memperbarui data buku: " . mysqli_error($koneksi);
        }
    } else {
        $pesan_gagal = "Judul buku dan Kategori wajib diisi!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Buku - Perpustakaan USTJ</title>

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

        .content-body { padding: 28px; max-width: 1000px; margin: 0 auto; }
        .page-header { margin-bottom: 24px; }
        .page-title { margin: 0; font-size: 24px; font-weight: 700; color: var(--text-dark); }
        .page-description { margin-top: 6px; font-size: 13px; color: var(--text-muted); }

        .custom-card {
            border: 1px solid var(--border-color);
            border-radius: 12px; background: #ffffff;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
            padding: 30px;
        }

        .form-label { font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 6px; }
        .form-control, .form-select { min-height: 44px; border-color: #dbe2ea; border-radius: 8px; font-size: 13px; }
        .form-control:focus, .form-select:focus { box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15); border-color: #0d6efd; }

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
                <a href="buku.php" class="text-decoration-none">Data Buku</a>
                <span class="mx-1">/</span>
                <span class="current">Edit Buku</span>
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
                <i class="bi bi-pencil-square text-primary"></i>
                Edit Data Buku
            </h1>
            <p class="page-description">
                Perbarui informasi detail buku perpustakaan Universitas Sains dan Teknologi Jayapura.
            </p>
        </div>

        <?php if ($pesan_gagal !== ''): ?>
            <div class="alert alert-danger border-0 shadow-sm mb-4">
                <i class="bi bi-x-circle-fill me-2"></i> <?= $pesan_gagal; ?>
            </div>
        <?php endif; ?>

        <!-- FORM EDIT BUKU -->
        <div class="custom-card">
            <form action="" method="POST">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">JUDUL BUKU <span class="text-danger">*</span></label>
                        <input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($buku['judul'] ?? ''); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">KATEGORI BUKU <span class="text-danger">*</span></label>
                        <select name="id_kategori" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php
                            $query_kat = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
                            while ($kat = mysqli_fetch_assoc($query_kat)) {
                                $selected = ($kat['id_kategori'] == ($buku['id_kategori'] ?? 0)) ? 'selected' : '';
                                echo "<option value='{$kat['id_kategori']}' {$selected}>{$kat['nama_kategori']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">PENULIS / PENGARANG</label>
                        <input type="text" name="penulis" class="form-control" value="<?= htmlspecialchars($buku['penulis'] ?? ''); ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">PENERBIT</label>
                        <input type="text" name="penerbit" class="form-control" value="<?= htmlspecialchars($buku['penerbit'] ?? ''); ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">TAHUN TERBIT</label>
                        <input type="number" name="tahun_terbit" class="form-control" value="<?= htmlspecialchars($buku['tahun_terbit'] ?? ''); ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">STOK BUKU</label>
                        <input type="number" name="stok" class="form-control" value="<?= htmlspecialchars($buku['stok'] ?? ''); ?>">
                    </div>

                    <div class="col-12 mt-4 pt-2 border-top d-flex justify-content-end gap-2">
                        <a href="buku.php" class="btn btn-outline-secondary px-4 py-2 fw-semibold" style="border-radius: 8px;">Batal</a>
                        <button type="submit" name="update_buku" class="btn btn-primary px-4 py-2 fw-semibold" style="border-radius: 8px;">
                            <i class="bi bi-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </main>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>