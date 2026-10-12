<?php
// =====================================================
// SISTEM INFORMASI PERPUSTAKAAN USTJ
// HALAMAN: PENGATURAN PROFIL MAHASISWA
// FILE: frontend/anggota/profil.php
// =====================================================
session_start();
require_once '../../koneksi.php';

if (!isset($_SESSION['user_login']) || $_SESSION['user_login'] !== true) {
    header("Location: ../login.php");
    exit;
}

$id_user = (int)($_SESSION['id_user'] ?? $_SESSION['user_id'] ?? $_SESSION['id'] ?? 0);

$pesan_sukses = '';
$pesan_error  = '';

// =====================================================
// PROSES UPLOAD FOTO PROFIL & UPDATE PASSWORD
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Update Foto Profil (jika ada file diunggah)
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES['foto_profil']['tmp_name'];
        $file_name = $_FILES['foto_profil']['name'];
        $ext       = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed   = ['jpg', 'jpeg', 'png'];

        if (in_array($ext, $allowed)) {
            $nama_foto_baru = 'user_' . $id_user . '_' . time() . '.' . $ext;
            $dir_target     = '../../assets/img/profil/';

            if (!is_dir($dir_target)) {
                mkdir($dir_target, 0777, true);
            }

            if (move_uploaded_file($file_tmp, $dir_target . $nama_foto_baru)) {
                // Cek apakah kolom foto ada di tabel users
                $check_col = mysqli_query($koneksi, "SHOW COLUMNS FROM users LIKE 'foto'");
                if ($check_col && mysqli_num_rows($check_col) > 0) {
                    mysqli_query($koneksi, "UPDATE users SET foto = '$nama_foto_baru' WHERE id_user = $id_user");
                }
                $pesan_sukses = 'Foto profil berhasil diperbarui!';
            } else {
                $pesan_error = 'Gagal mengunggah foto profil.';
            }
        } else {
            $pesan_error = 'Format foto tidak didukung! Gunakan format JPG, JPEG, atau PNG.';
        }
    }

    // 2. Update Password (jika diisi)
    if (!empty($_POST['pass_baru'])) {
        $pass_lama = $_POST['pass_lama'] ?? '';
        $pass_baru = $_POST['pass_baru'] ?? '';
        $pass_konf = $_POST['pass_konfirmasi'] ?? '';

        $q_cek = mysqli_query($koneksi, "SELECT password FROM users WHERE id_user = $id_user LIMIT 1");
        $u_row = mysqli_fetch_assoc($q_cek);

        if (!password_verify($pass_lama, $u_row['password']) && md5($pass_lama) !== $u_row['password']) {
            $pesan_error = 'Password lama yang Anda masukkan salah!';
        } elseif ($pass_baru !== $pass_konf) {
            $pesan_error = 'Konfirmasi password baru tidak cocok!';
        } elseif (strlen($pass_baru) < 6) {
            $pesan_error = 'Password baru minimal terdiri dari 6 karakter!';
        } else {
            $hash_baru = password_hash($pass_baru, PASSWORD_BCRYPT);
            mysqli_query($koneksi, "UPDATE users SET password = '$hash_baru' WHERE id_user = $id_user");
            $pesan_sukses = 'Password akun berhasil diubah!';
        }
    }
}

// =====================================================
// AMBIL DATA USER & ANGGOTA RELEVAN
// =====================================================
$query_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = $id_user LIMIT 1");
$data_user  = mysqli_fetch_assoc($query_user);

$nim          = $data_user['nim'] ?? '-';
$username     = $data_user['username'] ?? '-';
$email        = $data_user['email'] ?? '-';
$status       = strtolower($data_user['status'] ?? 'aktif');
$foto         = $data_user['foto'] ?? '';

// Ambil nama dari tabel users atau tabel anggota
$nama_lengkap = $data_user['nama_lengkap'] ?? '';
if (empty($nama_lengkap) && !empty($nim) && $nim !== '-') {
    $q_ang = mysqli_query($koneksi, "SELECT * FROM anggota WHERE nim = '$nim' LIMIT 1");
    if ($q_ang && mysqli_num_rows($q_ang) > 0) {
        $r_ang = mysqli_fetch_assoc($q_ang);
        $nama_lengkap = $r_ang['nama_lengkap'] ?? ($r_ang['nama'] ?? '');
    }
}
if (empty($nama_lengkap)) {
    $nama_lengkap = $_SESSION['nama_lengkap'] ?? 'Mahasiswa';
}

// Generasi Inisial Nama (misal: Imanuel Dolvinus Korain -> IK)
$words = explode(' ', trim($nama_lengkap));
$inisial = strtoupper(substr($words[0], 0, 1));
if (count($words) > 1) {
    $inisial .= strtoupper(substr(end($words), 0, 1));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Profil - Perpustakaan USTJ</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { 
            background-color: #f8f9fa; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        }
        .main-content { 
            margin-left: 260px; 
            padding: 35px 30px; 
            transition: all 0.3s ease;
        }
        .card-custom { 
            border: none; 
            border-radius: 20px; 
            box-shadow: 0 0.5rem 1.2rem rgba(0, 0, 0, 0.03); 
            background: #ffffff; 
        }
        .avatar-circle {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0d6efd 0%, #0dcef decision);
            color: #ffffff;
            font-size: 2.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(13, 110, 253, 0.25);
            margin: 0 auto;
        }
        .avatar-img {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            border: 3px solid #ffffff;
            margin: 0 auto;
        }
        .form-control:read-only {
            background-color: #f1f3f5;
            color: #495057;
            font-weight: 600;
        }
        @media (max-width: 768px) { 
            .main-content { 
                margin-left: 0; 
                padding: 20px 15px; 
            } 
        }
    </style>
</head>
<body>

    <!-- PANGGIL SIDEBAR -->
    <?php include 'sidebar.php'; ?>

    <!-- KONTEN UTAMA PROFIL -->
    <div class="main-content">
        <div class="container-fluid">

            <!-- Header Halaman -->
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <div>
                    <h3 class="fw-bold text-dark mb-1"><i class="bi bi-person-gear text-primary me-2"></i> Pengaturan Profil</h3>
                    <p class="text-muted mb-0">Kelola identitas akun dan informasi keamanan profil mahasiswa Anda.</p>
                </div>
            </div>

            <!-- Notifikasi Pesan -->
            <?php if (!empty($pesan_sukses)): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 bg-success bg-opacity-10 text-success mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($pesan_sukses); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($pesan_error)): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 bg-danger bg-opacity-10 text-danger mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($pesan_error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                
                <!-- KARTU 1: FOTO PROFIL & KARTU IDENTITAS RINGKAS -->
                <div class="col-lg-4">
                    <div class="card card-custom p-4 text-center h-100">
                        <div class="mb-3 position-relative">
                            <?php if (!empty($foto) && file_exists('../../assets/img/profil/' . $foto)): ?>
                                <img src="../../assets/img/profil/<?= htmlspecialchars($foto); ?>" alt="Foto Profil" class="avatar-img d-block">
                            <?php else: ?>
                                <div class="avatar-circle">
                                    <?= $inisial; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($nama_lengkap); ?></h5>
                        <p class="text-muted small mb-3">NIM: <?= htmlspecialchars($nim); ?></p>

                        <div class="mb-4">
                            <?php if ($status === 'aktif'): ?>
                                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-bold">
                                    <i class="bi bi-patch-check-fill me-1"></i> Anggota Aktif
                                </span>
                            <?php else: ?>
                                <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill fw-bold">
                                    <i class="bi bi-clock-history me-1"></i> Menunggu Verifikasi
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Form Unggah Foto Baru -->
                        <form action="" method="POST" enctype="multipart/form-data" class="mt-auto border-top pt-3">
                            <label class="form-label text-muted small fw-semibold d-block text-start mb-2"><i class="bi bi-camera me-1"></i> Ubah Foto Profil</label>
                            <div class="input-group input-group-sm mb-2">
                                <input type="file" name="foto_profil" class="form-control rounded-start-pill" accept="image/png, image/jpeg, image/jpg" required>
                                <button type="submit" class="btn btn-primary rounded-end-pill px-3 fw-semibold"><i class="bi bi-upload"></i></button>
                            </div>
                            <span class="text-muted d-block text-start" style="font-size: 11px;">Format disarankan: JPG, JPEG, PNG (Maks 2MB).</span>
                        </form>
                    </div>
                </div>

                <!-- KARTU 2: DETAIL INFORMASI AKUN & GANTI PASSWORD -->
                <div class="col-lg-8">
                    <div class="card card-custom p-4 h-100">
                        
                        <!-- Tab Navigasi Sederhana -->
                        <ul class="nav nav-pills mb-4 border-bottom pb-3 gap-2" id="profileTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active rounded-pill px-4 fw-semibold" id="info-tab" data-bs-toggle="pill" data-bs-target="#info-pane" type="button" role="tab"><i class="bi bi-person-lines-fill me-1"></i> Informasi Akun</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-pill px-4 fw-semibold" id="security-tab" data-bs-toggle="pill" data-bs-target="#security-pane" type="button" role="tab"><i class="bi bi-shield-lock-fill me-1"></i> Keamanan & Password</button>
                            </li>
                        </ul>

                        <div class="tab-content" id="profileTabContent">
                            
                            <!-- TAB 1: INFORMASI DATA AKUN -->
                            <div class="tab-pane fade show active" id="info-pane" role="tabpanel">
                                <form>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label text-muted small fw-semibold">Nama Lengkap Mahasiswa</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-person"></i></span>
                                                <input type="text" class="form-control border-start-0" value="<?= htmlspecialchars($nama_lengkap); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label text-muted small fw-semibold">NIM (Nomor Induk Mahasiswa)</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-card-text"></i></span>
                                                <input type="text" class="form-control border-start-0" value="<?= htmlspecialchars($nim); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label text-muted small fw-semibold">Username Login</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-at"></i></span>
                                                <input type="text" class="form-control border-start-0" value="<?= htmlspecialchars($username); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label text-muted small fw-semibold">Alamat Email</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-envelope"></i></span>
                                                <input type="email" class="form-control border-start-0" value="<?= htmlspecialchars($email); ?>" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-4 p-3 rounded-4 bg-light text-muted small border">
                                        <i class="bi bi-info-circle-fill text-primary me-1"></i> Data NIM dan Nama Mahasiswa tersinkronisasi langsung dengan pangkalan data universitas. Jika terdapat kekeliruan data, silakan hubungi petugas perpustakaan.
                                    </div>
                                </form>
                            </div>

                            <!-- TAB 2: KEAMANAN & UBAH PASSWORD -->
                            <div class="tab-pane fade" id="security-pane" role="tabpanel">
                                <form action="" method="POST">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label text-muted small fw-semibold">Password Saat Ini</label>
                                            <input type="password" name="pass_lama" class="form-control rounded-3" placeholder="Masukkan password lama" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label text-muted small fw-semibold">Password Baru</label>
                                            <input type="password" name="pass_baru" class="form-control rounded-3" placeholder="Minimal 6 karakter" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label text-muted small fw-semibold">Konfirmasi Password Baru</label>
                                            <input type="password" name="pass_konfirmasi" class="form-control rounded-3" placeholder="Ulangi password baru" required>
                                        </div>
                                    </div>
                                    <div class="mt-4 text-end">
                                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                                            <i class="bi bi-key me-1"></i> Perbarui Password
                                        </button>
                                    </div>
                                </form>
                            </div>

                        </div>

                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>