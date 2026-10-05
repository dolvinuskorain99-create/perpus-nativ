<?php
// =====================================================
// SISTEM INFORMASI PERPUSTAKAAN USTJ
// HALAMAN: MANAJEMEN DATA ANGGOTA & PERSETUJUAN
// FILE: admin/anggota.php
// =====================================================

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../koneksi.php';

if (!$koneksi) {
    die('Koneksi database gagal: ' . mysqli_connect_error());
}

$pesan_sukses = '';
$pesan_gagal = '';

// =====================================================
// PROSES APPROVAL / UBAH STATUS ANGGOTA
// =====================================================
if (isset($_GET['aksi']) && $_GET['aksi'] === 'setujui') {
    $id_user = (int)$_GET['id'];
    $query_approval = "UPDATE users SET status = 'aktif' WHERE id_user = $id_user";
    if (mysqli_query($koneksi, $query_approval)) {
        header("Location: anggota.php?pesan=sukses_setuju");
        exit();
    } else {
        header("Location: anggota.php?pesan=gagal_setuju");
        exit();
    }
}

// =====================================================
// PROSES TAMBAH ANGGOTA BARU OLEH ADMIN
// =====================================================
if (isset($_POST['tambah_anggota'])) {
    $nim = mysqli_real_escape_string($koneksi, trim($_POST['nim']));
    $username = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $email = mysqli_real_escape_string($koneksi, trim($_POST['email']));
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

    if (!empty($nim) && !empty($username) && !empty($_POST['password'])) {
        $cek_user = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username' OR email = '$email'");
        if (mysqli_num_rows($cek_user) > 0) {
            $pesan_gagal = "Username atau Email sudah terdaftar!";
        } else {
            // Admin menambahkan langsung berstatus aktif
            $query_insert = "INSERT INTO users (nim, username, email, password, status) VALUES ('$nim', '$username', '$email', '$password', 'aktif')";
            if (mysqli_query($koneksi, $query_insert)) {
                header("Location: anggota.php?pesan=sukses_tambah");
                exit();
            } else {
                $pesan_gagal = "Gagal menyimpan data: " . mysqli_error($koneksi);
            }
        }
    } else {
        $pesan_gagal = "Semua field wajib diisi!";
    }
}

// =====================================================
// PROSES EDIT ANGGOTA
// =====================================================
if (isset($_POST['edit_anggota'])) {
    $id_user = (int)$_POST['id_user'];
    $nim = mysqli_real_escape_string($koneksi, trim($_POST['nim']));
    $username = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $email = mysqli_real_escape_string($koneksi, trim($_POST['email']));
    $password_input = trim($_POST['password']);

    if (!empty($id_user) && !empty($nim) && !empty($username)) {
        $cek_user = mysqli_query($koneksi, "SELECT * FROM users WHERE (username = '$username' OR email = '$email') AND id_user != $id_user");
        if (mysqli_num_rows($cek_user) > 0) {
            $pesan_gagal = "Username atau Email sudah digunakan oleh anggota lain!";
        } else {
            if (!empty($password_input)) {
                $password_hash = password_hash($password_input, PASSWORD_DEFAULT);
                $query_update = "UPDATE users SET nim = '$nim', username = '$username', email = '$email', password = '$password_hash' WHERE id_user = $id_user";
            } else {
                $query_update = "UPDATE users SET nim = '$nim', username = '$username', email = '$email' WHERE id_user = $id_user";
            }

            if (mysqli_query($koneksi, $query_update)) {
                header("Location: anggota.php?pesan=sukses_edit");
                exit();
            } else {
                $pesan_gagal = "Gagal memperbarui data: " . mysqli_error($koneksi);
            }
        }
    } else {
        $pesan_gagal = "Field NIM dan Username wajib diisi!";
    }
}

// =====================================================
// PROSES HAPUS ANGGOTA
// =====================================================
if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus') {
    $id_user = (int)$_GET['id'];
    $query_hapus = "DELETE FROM users WHERE id_user = $id_user";
    if (mysqli_query($koneksi, $query_hapus)) {
        header("Location: anggota.php?pesan=sukses_hapus");
        exit();
    } else {
        header("Location: anggota.php?pesan=gagal_hapus");
        exit();
    }
}

// Notifikasi pesan URL
if (isset($_GET['pesan'])) {
    if ($_GET['pesan'] === 'sukses_tambah') $pesan_sukses = "Data anggota baru berhasil ditambahkan!";
    if ($_GET['pesan'] === 'sukses_edit') $pesan_sukses = "Data anggota berhasil diperbarui!";
    if ($_GET['pesan'] === 'sukses_setuju') $pesan_sukses = "Akun anggota berhasil disetujui (aktif)!";
    if ($_GET['pesan'] === 'sukses_hapus') $pesan_sukses = "Data anggota berhasil dihapus!";
    if ($_GET['pesan'] === 'gagal_hapus') $pesan_gagal = "Gagal menghapus data anggota!";
}

// =====================================================
// PAGINATION & PENCARIAN (DENGAN FILTER STATUS)
// =====================================================
$batas = 10;
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
if ($halaman < 1) { $halaman = 1; }

$keyword = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';
$where = [];

if ($keyword !== '') {
    $kw_esc = mysqli_real_escape_string($koneksi, $keyword);
    $where[] = "(nim LIKE '%$kw_esc%' OR username LIKE '%$kw_esc%' OR email LIKE '%$kw_esc%')";
}

if ($filter_status !== '') {
    $st_esc = mysqli_real_escape_string($koneksi, $filter_status);
    $where[] = "status = '$st_esc'";
}

$sql_where = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$sql_count = "SELECT COUNT(*) AS total FROM users $sql_where";
$query_count = mysqli_query($koneksi, $sql_count);
$data_count = mysqli_fetch_assoc($query_count);
$total_data = (int)($data_count['total'] ?? 0);

$total_halaman = max(1, (int)ceil($total_data / $batas));
if ($halaman > $total_halaman) { $halaman = $total_halaman; }
$offset = ($halaman - 1) * $batas;

$sql_data = "SELECT * FROM users $sql_where ORDER BY id_user DESC LIMIT $offset, $batas";
$query_data = mysqli_query($koneksi, $sql_data);

$params = [];
if ($keyword !== '') { $params['cari'] = $keyword; }
if ($filter_status !== '') { $params['status'] = $filter_status; }
$query_string = !empty($params) ? '&' . http_build_query($params) : '';

$nomor_awal = $total_data > 0 ? $offset + 1 : 0;
$nomor_akhir = min($offset + $batas, $total_data);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Data Anggota - Perpustakaan USTJ</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --sidebar-width: 250px; --main-bg: #f8fafc; --border-color: #e2e8f0; --text-dark: #0f172a; --text-muted: #64748b; }
        body { margin: 0; background: var(--main-bg); color: #1e293b; font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
        #main-content-wrapper { margin-left: var(--sidebar-width); min-height: 100vh; width: calc(100% - var(--sidebar-width)); background: var(--main-bg); }
        .top-navbar { min-height: 70px; padding: 0 28px; background: #ffffff; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; }
        .content-body { padding: 28px; max-width: 1700px; margin: 0 auto; }
        .custom-card { border: 1px solid var(--border-color); border-radius: 12px; background: #ffffff; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04); }
        .table thead th { padding: 13px 14px; background: #0f172a; color: #ffffff; border: none; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .table tbody td { padding: 13px 14px; font-size: 12.5px; color: #334155; vertical-align: middle; border-bottom: 1px solid #edf1f5; }
        .action-buttons { display: flex; align-items: center; justify-content: center; gap: 5px; }
        .action-buttons .btn { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; }
        .library-card-preview { width: 380px; height: 220px; background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); border-radius: 12px; color: white; padding: 16px; position: relative; margin: 0 auto; border: 2px solid #fbbf24; }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div id="main-content-wrapper">
    <nav class="top-navbar justify-content-between">
        <div class="breadcrumb-admin">
            <span class="text-dark fw-semibold">PerpusAdmin USTJ</span> / <span class="text-primary fw-semibold">Data Anggota</span>
        </div>
    </nav>

    <main class="content-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 fw-bold text-dark"><i class="bi bi-people-fill text-primary"></i> Manajemen Data Anggota</h1>
                <p class="text-muted small">Kelola pendaftaran akun, verifikasi status pending, dan cetak kartu anggota perpustakaan.</p>
            </div>
            <button class="btn btn-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTambahAnggota">
                <i class="bi bi-person-plus-fill me-1"></i> Tambah Anggota
            </button>
        </div>

        <?php if (!empty($pesan_sukses)): ?>
            <div class="alert alert-success border-0 shadow-sm mb-4"><i class="bi bi-check-circle-fill me-2"></i> <?= $pesan_sukses; ?></div>
        <?php endif; ?>
        <?php if (!empty($pesan_gagal)): ?>
            <div class="alert alert-danger border-0 shadow-sm mb-4"><i class="bi bi-x-circle-fill me-2"></i> <?= $pesan_gagal; ?></div>
        <?php endif; ?>

        <!-- FILTER & PENCARIAN -->
        <div class="custom-card p-3 mb-4">
            <form method="GET" action="anggota.php">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Cari Data</label>
                        <input type="text" name="cari" class="form-control" placeholder="NIM, username, atau email..." value="<?= htmlspecialchars($keyword); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Filter Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="pending" <?= $filter_status === 'pending' ? 'selected' : ''; ?>>Pending (Menunggu Persetujuan)</option>
                            <option value="aktif" <?= $filter_status === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i> Filter</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- TABEL -->
        <div class="custom-card overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" width="50">No</th>
                            <th>NIM</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" width="180">Aksi & Kartu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($query_data) > 0): ?>
                            <?php $no = $offset + 1; while ($row = mysqli_fetch_assoc($query_data)): ?>
                                <tr>
                                    <td class="text-center text-muted"><?= $no++; ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($row['nim'] ?? '-'); ?></td>
                                    <td><span class="font-monospace text-primary"><?= htmlspecialchars($row['username'] ?? '-'); ?></span></td>
                                    <td class="text-muted"><?= htmlspecialchars($row['email'] ?? '-'); ?></td>
                                    <td class="text-center">
                                        <?php if (isset($row['status']) && $row['status'] === 'pending'): ?>
                                            <span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i> Pending</span>
                                        <?php else: ?>
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Aktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="action-buttons">
                                            <?php if (isset($row['status']) && $row['status'] === 'pending'): ?>
                                                <!-- Tombol Setujui Akun -->
                                                <a href="anggota.php?aksi=setujui&id=<?= $row['id_user']; ?>" class="btn btn-success text-white btn-sm" title="Setujui Akun">
                                                    <i class="bi bi-check-lg"></i>
                                                </a>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-info text-white btn-sm" title="Cetak Kartu" 
                                                    data-bs-toggle="modal" data-bs-target="#modalCetakKartu"
                                                    data-nim="<?= htmlspecialchars($row['nim'] ?? ''); ?>"
                                                    data-username="<?= htmlspecialchars($row['username'] ?? ''); ?>"
                                                    data-email="<?= htmlspecialchars($row['email'] ?? ''); ?>">
                                                <i class="bi bi-card-heading"></i>
                                            </button>
                                            <button type="button" class="btn btn-warning text-white btn-sm" title="Edit"
                                                    data-bs-toggle="modal" data-bs-target="#modalEditAnggota"
                                                    data-id="<?= $row['id_user']; ?>" data-nim="<?= htmlspecialchars($row['nim'] ?? ''); ?>"
                                                    data-username="<?= htmlspecialchars($row['username'] ?? ''); ?>" data-email="<?= htmlspecialchars($row['email'] ?? ''); ?>">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" title="Hapus"
                                                    data-bs-toggle="modal" data-bs-target="#modalHapusAnggota"
                                                    data-id="<?= $row['id_user']; ?>" data-username="<?= htmlspecialchars($row['username'] ?? ''); ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-5 text-muted">Tidak ada data anggota.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Modal Tambah, Edit, Hapus, dan Cetak Kartu disesuaikan seperti sebelumnya -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>