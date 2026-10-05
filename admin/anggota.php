<?php
// =====================================================
// SISTEM INFORMASI PERPUSTAKAAN USTJ
// HALAMAN: MANAJEMEN DATA ANGGOTA & CETAK KARTU
// FILE: admin/anggota.php
// =====================================================

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../koneksi.php';

if (!$koneksi) {
    die('Koneksi database gagal: ' . mysqli_connect_error());
}

// =====================================================
// PROSES TAMBAH ANGGOTA BARU
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
            $query_insert = "INSERT INTO users (nim, username, email, password) VALUES ('$nim', '$username', '$email', '$password')";
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
// KONFIGURASI PAGINATION & PENCARIAN
// =====================================================
$batas = 10;
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
if ($halaman < 1) { $halaman = 1; }

$keyword = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$where = [];

if ($keyword !== '') {
    $kw_esc = mysqli_real_escape_string($koneksi, $keyword);
    $where[] = "(
        nim LIKE '%$kw_esc%'
        OR username LIKE '%$kw_esc%'
        OR email LIKE '%$kw_esc%'
    )";
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
            margin: 0;
            padding: 0;
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
            width: 36px; height: 36px; border-radius: 50%;
            background: #0d6efd; color: white;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 700;
        }

        .content-body { padding: 28px; max-width: 1700px; margin: 0 auto; }
        .page-header { margin-bottom: 24px; }
        .page-title { margin: 0; font-size: 24px; font-weight: 700; color: var(--text-dark); }
        .page-description { margin-top: 6px; font-size: 13px; color: var(--text-muted); }

        .custom-card {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        }

        .filter-card { margin-bottom: 22px; }
        .filter-card .card-body { padding: 20px; }
        .filter-label { display: block; margin-bottom: 7px; font-size: 11px; font-weight: 700; color: #64748b; }

        .form-control { min-height: 42px; border-color: #dbe2ea; border-radius: 8px; font-size: 13px; }
        .search-button { min-width: 82px; border-radius: 8px; font-size: 13px; font-weight: 600; }

        .data-info { margin-bottom: 12px; font-size: 13px; color: #64748b; }
        .table-card { overflow: hidden; }
        .table-wrapper { overflow-x: auto; width: 100%; }
        .table { margin: 0; min-width: 950px; }

        .table thead th {
            padding: 13px 14px; background: #0f172a; color: #ffffff;
            border: none; font-size: 11px; font-weight: 700; text-transform: uppercase;
        }

        .table tbody td {
            padding: 13px 14px; font-size: 12.5px; color: #334155;
            vertical-align: middle; border-bottom: 1px solid #edf1f5;
        }
        .table tbody tr:hover { background-color: #f8fafc; }

        .action-buttons { display: flex; align-items: center; justify-content: center; gap: 5px; }
        .action-buttons .btn { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; }

        /* Styling Kartu Anggota Preview di Modal */
        .library-card-preview {
            width: 380px;
            height: 220px;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            border-radius: 12px;
            color: white;
            padding: 16px;
            position: relative;
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
            margin: 0 auto;
            border: 2px solid #fbbf24;
        }
        .card-header-uni {
            display: flex;
    align-items: center;
    gap: 10px; /* Jarak antara logo dan teks */
    border-bottom: 1px solid rgba(255,255,255,0.2);
    padding-bottom: 8px;
    margin-bottom: 12px;
        }
        .card-logo { 


            width: 38px;
    height: 38px;
    object-fit: contain;
    /* background: #ffffff; */
    border-radius: 50%;
    padding: 2px;
    flex-shrink: 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            
            }
        .card-title-text h6 { margin: 0; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .card-title-text small { font-size: 9px; color: #cbd5e1; }
        
        .card-body-content { display: flex; gap: 12px; align-items: center; }
        .card-avatar-box {
            width: 65px; height: 75px; background: #e2e8f0; border-radius: 6px;
            display: flex; align-items: center; justify-content: center; color: #475569;
            font-size: 24px; font-weight: bold; border: 2px solid white; flex-shrink: 0;
        }
        .card-info-box table { font-size: 11px; color: white; width: 100%; }
        .card-info-box td { padding: 2px 0; vertical-align: top; }
        .card-info-box td.label { width: 75px; color: #94a3b8; font-weight: 500; }
        
        .card-footer-uni {
            position: absolute; bottom: 12px; left: 16px; right: 16px;
            display: flex; justify-content: space-between; align-items: center;
            border-top: 1px solid rgba(255,255,255,0.15); padding-top: 8px;
            font-size: 8.5px; color: #cbd5e1;
        }

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
                <span class="current">Data Anggota & Kartu</span>
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
        <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h1 class="page-title d-flex align-items-center gap-2">
                    <i class="bi bi-people-fill text-primary"></i>
                    Manajemen Data Anggota & Kartu
                </h1>
                <p class="page-description">
                    Kelola data anggota perpustakaan dan cetak kartu anggota resmi Universitas Sains dan Teknologi Jayapura.
                </p>
            </div>
            
            <div>
                <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTambahAnggota" style="border-radius: 8px;">
                    <i class="bi bi-person-plus-fill"></i>
                    Tambah Anggota
                </button>
            </div>
        </div>

        <!-- ALERTS -->
        <?php if (isset($_GET['pesan']) && $_GET['pesan'] === 'sukses_tambah'): ?>
            <div class="alert alert-success border-0 shadow-sm mb-4">
                <i class="bi bi-check-circle-fill me-2"></i> Data anggota baru berhasil ditambahkan!
            </div>
        <?php endif; ?>

        <?php if (isset($pesan_gagal)): ?>
            <div class="alert alert-danger border-0 shadow-sm mb-4">
                <i class="bi bi-x-circle-fill me-2"></i> <?= $pesan_gagal; ?>
            </div>
        <?php endif; ?>

        <!-- FILTER & PENCARIAN -->
        <div class="custom-card filter-card">
            <div class="card-body">
                <form method="GET" action="anggota.php">
                    <div class="row g-3 align-items-end">
                        <div class="col-xl-8 col-md-12">
                            <label class="filter-label">CARI ANGGOTA</label>
                            <div class="input-group">
                                <input type="text" name="cari" class="form-control" placeholder="Cari NIM, username, atau email..." value="<?= htmlspecialchars($keyword); ?>">
                                <button type="submit" class="btn btn-primary search-button">
                                    <i class="bi bi-search me-1"></i> Cari
                                </button>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-12 text-xl-end">
                            <?php if ($keyword !== ''): ?>
                                <a href="anggota.php" class="btn btn-outline-secondary btn-sm px-3 py-2">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Pencarian
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- INFO DATA -->
        <div class="data-info d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>Total data: <strong><?= number_format($total_data, 0, ',', '.'); ?></strong> anggota</div>
            <div>Menampilkan <?= $nomor_awal; ?> - <?= $nomor_akhir; ?> dari <?= number_format($total_data, 0, ',', '.'); ?> data</div>
        </div>

        <!-- TABEL ANGGOTA -->
        <div class="custom-card table-card">
            <div class="table-wrapper">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th class="text-center" width="50">No</th>
                            <th>NIM</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th class="text-center" width="160">Aksi & Kartu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($query_data) > 0): ?>
                            <?php $no = $offset + 1; while ($row = mysqli_fetch_assoc($query_data)): ?>
                                <tr>
                                    <td class="text-center text-muted fw-semibold"><?= $no++; ?></td>
                                    <td><div class="fw-semibold text-dark"><?= htmlspecialchars($row['nim'] ?? '-'); ?></div></td>
                                    <td><span class="font-monospace text-primary"><?= htmlspecialchars($row['username'] ?? '-'); ?></span></td>
                                    <td><span class="text-muted"><?= htmlspecialchars($row['email'] ?? '-'); ?></span></td>
                                    <td class="text-center">
                                        <div class="action-buttons">
                                            <!-- Tombol Cetak Kartu Modal Trigger -->
                                            <button type="button" class="btn btn-info text-white btn-sm" title="Cetak Kartu Anggota" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalCetakKartu"
                                                    data-nim="<?= htmlspecialchars($row['nim'] ?? ''); ?>"
                                                    data-username="<?= htmlspecialchars($row['username'] ?? ''); ?>"
                                                    data-email="<?= htmlspecialchars($row['email'] ?? ''); ?>">
                                                <i class="bi bi-card-heading"></i>
                                            </button>
                                            <a href="edit_user.php?id=<?= $row['id_user']; ?>" class="btn btn-warning text-white btn-sm" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="hapus_user.php?id=<?= $row['id_user']; ?>" class="btn btn-danger btn-sm" title="Hapus" onclick="return confirm('Yakin ingin menghapus anggota ini?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-people fs-1 text-secondary opacity-50 d-block mb-2"></i>
                                    <h5>Tidak ada data anggota ditemukan</h5>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PAGINASI -->
        <?php if ($total_halaman > 1): ?>
        <div class="pagination-wrapper d-flex justify-content-center mt-4">
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <li class="page-item <?= ($halaman <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?halaman=<?= $halaman - 1; ?><?= $query_string; ?>"><i class="bi bi-chevron-left"></i></a>
                    </li>
                    <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                        <li class="page-item <?= ($i == $halaman) ? 'active' : ''; ?>">
                            <a class="page-link" href="?halaman=<?= $i; ?><?= $query_string; ?>"><?= $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($halaman >= $total_halaman) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?halaman=<?= $halaman + 1; ?><?= $query_string; ?>"><i class="bi bi-chevron-right"></i></a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>

    </main>
</div>

<!-- MODAL TAMBAH ANGGOTA -->
<div class="modal fade" id="modalTambahAnggota" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill me-2"></i> Tambah Anggota Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="anggota.php" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">NIM <span class="text-danger">*</span></label>
                        <input type="text" name="nim" class="form-control" placeholder="Contoh: 21411087" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control" placeholder="Masukkan username..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="nama@email.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                    </div>
                </div>
                <div class="modal-footer bg-light py-3 px-4 rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_anggota" class="btn btn-primary px-4">Simpan Anggota</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL PREVIEW & CETAK KARTU ANGGOTA -->
<div class="modal fade" id="modalCetakKartu" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-card-heading me-2 text-warning"></i> Kartu Anggota Perpustakaan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center bg-light">
                
                <!-- KARTU PREVIEW -->
                <!-- KARTU PREVIEW -->
<div id="printArea" class="library-card-preview text-start">
    <div class="card-header-uni">
        <!-- Logo Universitas (Sesuaikan path/lokasi file logo Anda, misal: ../assets/img/logo-ustj.png) -->
        <img src="../assets/img/logo.png" alt="Logo USTJ" class="card-logo" onerror="this.style.display='none'">
        
        <div class="card-title-text">
            <h6>Perpustakaan USTJ</h6>
            <small>Universitas Sains dan Teknologi Jayapura</small>
        </div>
    </div>
    <div class="card-body-content">
        <div class="card-avatar-box">
            <i class="bi bi-person-fill"></i>
        </div>
        <div class="card-info-box">
            <table>
                <tr>
                    <td class="label">NIM</td>
                    <td>: <span id="card-nim" class="fw-bold"></span></td>
                </tr>
                <tr>
                    <td class="label">Nama/User</td>
                    <td>: <span id="card-username" class="fw-bold"></span></td>
                </tr>
                <tr>
                    <td class="label">Email</td>
                    <td>: <span id="card-email"></span></td>
                </tr>
                <tr>
                    <td class="label">Berlaku</td>
                    <td>: Selama Menjadi Mahasiswa</td>
                </tr>
            </table>
        </div>
    </div>
    <div class="card-footer-uni">
        <span>www.ustj.ac.id</span>
        <span><strong>PERPUS USTJ CARD</strong></span>
    </div>
</div>
                <p class="text-muted small mt-3 mb-0">Tekan tombol cetak di bawah untuk mencetak kartu anggota ini.</p>
            </div>
            <div class="modal-footer bg-white py-3 px-4">
                <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary px-4" onclick="printCard()">
                    <i class="bi bi-printer-fill me-1"></i> Cetak Kartu
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS & Script Pendukung Modal Kartu -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Script untuk mengisi data dinamis ke modal kartu anggota
    const modalCetakKartu = document.getElementById('modalCetakKartu');
    modalCetakKartu.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const nim = button.getAttribute('data-nim');
        const username = button.getAttribute('data-username');
        const email = button.getAttribute('data-email');

        document.getElementById('card-nim').textContent = nim;
        document.getElementById('card-username').textContent = username;
        document.getElementById('card-email').textContent = email ? email : '-';
    });

    // Fungsi khusus untuk mencetak elemen kartu
    function printCard() {
        const printContents = document.getElementById('printArea').outerHTML;
        const originalContents = document.body.innerHTML;

        const printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Cetak Kartu Anggota Perpustakaan USTJ</title>');
        printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">');
        printWindow.document.write('<style>');
        printWindow.document.write('body { display: flex; justify-content: center; align-items: center; height: 100vh; background: white; margin: 0; }');
        printWindow.document.write('.library-card-preview { width: 420px; height: 240px; background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%) !important; border-radius: 12px; color: white; padding: 18px; position: relative; border: 2px solid #fbbf24; -webkit-print-color-adjust: exact; print-color-adjust: exact; }');
        printWindow.document.write('.card-header-uni { display: flex; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 8px; margin-bottom: 12px; }');
        printWindow.document.write('.card-title-text h6 { margin: 0; font-size: 12px; font-weight: 700; text-transform: uppercase; color: #ffffff; }');
        printWindow.document.write('.card-title-text small { font-size: 9.5px; color: #cbd5e1; }');
        printWindow.document.write('.card-body-content { display: flex; gap: 15px; align-items: center; }');
        printWindow.document.write('.card-avatar-box { width: 70px; height: 80px; background: #e2e8f0; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #475569; font-size: 28px; font-weight: bold; border: 2px solid white; flex-shrink: 0; }');
        printWindow.document.write('.card-info-box table { font-size: 11.5px; color: white; width: 100%; }');
        printWindow.document.write('.card-info-box td { padding: 2px 0; vertical-align: top; }');
        printWindow.document.write('.card-info-box td.label { width: 80px; color: #94a3b8; font-weight: 500; }');
        printWindow.document.write('.card-footer-uni { position: absolute; bottom: 12px; left: 18px; right: 18px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255,255,255,0.15); padding-top: 8px; font-size: 9px; color: #cbd5e1; }');
        printWindow.document.write('</style></head><body>');
        printWindow.document.write(printContents);
        printWindow.document.write('</body></html>');

        printWindow.document.close();
        printWindow.focus();
        
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 500);
    }
</script>

</body>
</html>