<?php
session_start();
include '../koneksi.php';

$error_login = '';
$error_register = '';
$success_register = '';
$active_tab = 'login'; 

// PROSES LOGIN
if (isset($_POST['login'])) {
    $active_tab = 'login';
    $username       = trim($_POST['username']);
    $password       = trim($_POST['password']);
    $login_sebagai  = $_POST['login_sebagai'];

    if (empty($username) || empty($password)) {
        $error_login = "Username dan password tidak boleh kosong!";
    } else {
        if ($login_sebagai === 'admin') {
            $query = "SELECT * FROM admin WHERE username = '$username' LIMIT 1";
            $result = mysqli_query($koneksi, $query);

            if ($result && mysqli_num_rows($result) === 1) {
                $row = mysqli_fetch_assoc($result);
                if ($password === $row['password'] || password_verify($password, $row['password'])) {
                    $_SESSION['admin_login'] = true;
                    $_SESSION['admin_user']  = $row['username'];
                    $_SESSION['admin_id']    = $row['id_admin'];
                    header("Location: ../admin/index.php");
                    exit;
                } else {
                    $error_login = "Password administrator salah!";
                }
            } else {
                $error_login = "Username administrator tidak ditemukan!";
            }
        } else {
            $query = "SELECT * FROM users WHERE username = '$username' OR nim = '$username' LIMIT 1";
            $result = mysqli_query($koneksi, $query);

            if ($result && mysqli_num_rows($result) === 1) {
                $row = mysqli_fetch_assoc($result);
                
                // Cek apakah akun sudah disetujui oleh admin (kolom status harus 'active')
                // Catatan: Jika tabel users Anda belum ada kolom status, pastikan menambahkan kolom status (enum/varchar) dengan default 'pending'
                $status_akun = isset($row['status']) ? $row['status'] : 'active';
                
                if ($status_akun === 'pending') {
                    $error_login = "Akun Anda masih menunggu persetujuan (approval) dari admin perpustakaan dan penyelesaian biaya administrasi Rp 15.000.";
                } elseif ($status_akun === 'rejected') {
                    $error_login = "Mohon maaf, pendaftaran akun Anda ditolak oleh admin. Silakan hubungi petugas perpustakaan.";
                } else {
                    if ($password === $row['password'] || password_verify($password, $row['password']) || md5($password) === $row['password']) {
                        $_SESSION['user_login']   = true;
                        $_SESSION['username']     = $row['username'];
                        $_SESSION['user_id']      = $row['id_user'];
                        $_SESSION['nama_lengkap'] = $row['nama_lengkap'] ?? 'Mahasiswa';
                        header("Location: profil_user.php");
                        exit;
                    } else {
                        $error_login = "Password yang Anda masukkan salah!";
                    }
                }
            } else {
                $error_login = "Username atau NIM tidak terdaftar!";
            }
        }
    }
}

// PROSES REGISTER
if (isset($_POST['register'])) {
    $active_tab = 'register';
    $nim           = trim($_POST['nim']);
    $nama_lengkap  = trim($_POST['nama_lengkap']);
    $username      = trim($_POST['username']);
    $email         = trim($_POST['email']);
    $password      = $_POST['password'];
    $konfirmasi    = $_POST['konfirmasi_password'];

    if (empty($nim) || empty($nama_lengkap) || empty($username) || empty($email) || empty($password)) {
        $error_register = "Semua kolom wajib diisi!";
    } elseif ($password !== $konfirmasi) {
        $error_register = "Konfirmasi password tidak cocok!";
    } else {
        $cek_data = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username' OR nim = '$nim' LIMIT 1");
        if (mysqli_num_rows($cek_data) > 0) {
            $error_register = "Username atau NIM tersebut sudah terdaftar!";
        } else {
            $password_hashed = password_hash($password, PASSWORD_DEFAULT);
            // Menyimpan dengan status 'pending' agar butuh persetujuan admin
            // Pastikan tabel users memiliki kolom 'status' (tipe VARCHAR/ENUM dengan default 'pending')
            $query_insert = "INSERT INTO users (nim, nama_lengkap, username, email, password, status) VALUES ('$nim', '$nama_lengkap', '$username', '$email', '$password_hashed', 'pending')";
            
            if (mysqli_query($koneksi, $query_insert)) {
                $success_register = "Registrasi berhasil! Akun Anda sedang menunggu persetujuan admin serta pelunasan biaya kartu (Rp 15.000). Silakan tunggu.";
            } else {
                $error_register = "Terjadi kesalahan sistem, coba lagi nanti.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk & Daftar - Perpustakaan USTJ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('../assets/img/r1.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
            padding: 15px;
        }
        .main-container {
            position: relative;
            width: 950px;
            max-width: 100%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
            overflow: hidden;
            min-height: 620px;
            border: 1px solid rgba(255, 255, 255, 0.25);
        }
        
        .form-container {
            position: absolute;
            top: 0;
            height: 100%;
            transition: all 0.6s ease-in-out;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 25px 40px;
            box-sizing: border-box;
            width: 50%;
            z-index: 10;
        }
        .login-container {
            left: 0;
        }
        .register-container {
            left: 0;
            opacity: 0;
            z-index: 1;
        }
        .main-container.right-panel-active .login-container {
            transform: translateX(100%);
            opacity: 0;
            z-index: 1;
        }
        .main-container.right-panel-active .register-container {
            transform: translateX(100%);
            opacity: 1;
            z-index: 15;
            animation: show 0.6s;
        }
        @keyframes show {
            0%, 49% { opacity: 0; z-index: 1; }
            50%, 100% { opacity: 1; z-index: 15; }
        }

        /* Background Slideshow Full Cover di dalam Card */
        .form-slideshow-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            opacity: 0;
            transition: opacity 1.2s ease-in-out;
            z-index: 1;
        }
        .form-slideshow-bg.active {
            opacity: 1;
        }
        .form-overlay-mask {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.75) 0%, rgba(10, 88, 202, 0.85) 100%);
            z-index: 2;
        }

        /* CARD TRANSPARAN MURNI (Tanpa background putih padat) */
        .form-content-wrapper {
            position: relative;
            z-index: 5;
            width: 100%;
            background: rgba(255, 255, 255, 0.08); /* Sangat transparan murni */
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }

        .overlay-container {
            position: absolute;
            top: 0;
            left: 50%;
            width: 50%;
            height: 100%;
            overflow: hidden;
            transition: transform 0.6s ease-in-out;
            z-index: 20;
        }
        .main-container.right-panel-active .overlay-container {
            transform: translateX(-100%);
        }
        
        .overlay {
            position: relative;
            left: -100%;
            height: 100%;
            width: 200%;
            transform: translateX(0);
            transition: transform 0.6s ease-in-out;
        }
        .main-container.right-panel-active .overlay {
            transform: translateX(50%);
        }

        .slideshow-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            opacity: 0;
            transition: opacity 1.2s ease-in-out;
            z-index: 1;
        }
        .slideshow-bg.active {
            opacity: 1;
        }

        .overlay-mask {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.65) 0%, rgba(10, 88, 202, 0.75) 100%);
            z-index: 2;
        }

        .overlay-panel {
            position: absolute;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 40px;
            text-align: center;
            top: 0;
            height: 100%;
            width: 50%;
            box-sizing: border-box;
            transition: transform 0.6s ease-in-out;
            z-index: 3;
        }
        .overlay-left {
            transform: translateX(-20%);
            left: 0;
        }
        .main-container.right-panel-active .overlay-left {
            transform: translateX(0);
        }
        .overlay-right {
            right: 0;
            transform: translateX(0);
        }
        .main-container.right-panel-active .overlay-right {
            transform: translateX(20%);
        }

        /* Input form semi-transparan */
        .form-control, .form-select, .input-group-text {
            background-color: rgba(255, 255, 255, 0.85) !important;
            border: 1px solid rgba(255, 255, 255, 0.5) !important;
        }

        .scrollable-form {
            max-height: 100%;
            overflow-y: auto;
            width: 100%;
            padding-right: 2px;
        }
        .scrollable-form::-webkit-scrollbar {
            width: 4px;
        }
        .scrollable-form::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.4);
            border-radius: 4px;
        }

        .mobile-switch { display: none; }
        
        /* PENGATURAN KHUSUS HP (MOBILE) */
        @media (max-width: 768px) {
            body { padding: 10px; background: #f0f2f5; }
            .main-container { min-height: auto; width: 100%; background: rgba(255, 255, 255, 0.1); border-radius: 15px; }
            .form-container { width: 100%; padding: 15px; position: relative; height: auto; }
            .overlay-container { display: none; }

            .login-container { left: 0; transform: translateX(0) !important; opacity: 1; z-index: 2; display: flex; }
            .register-container { left: 0; transform: translateX(0) !important; opacity: 0; z-index: 1; display: none; }

            .main-container.right-panel-active .login-container { display: none; opacity: 0; }
            .main-container.right-panel-active .register-container { display: flex; opacity: 1; z-index: 5; }

            .mobile-switch { display: block; }
            
            .form-content-wrapper { background: rgba(255, 255, 255, 0.12); backdrop-filter: blur(10px); box-shadow: none; padding: 20px; border: 1px solid rgba(255, 255, 255, 0.25); }
            .form-content-wrapper h4, .form-content-wrapper h5 { font-size: 1.4rem !important; color: #fff !important; text-shadow: 0 1px 3px rgba(0,0,0,0.3); }
            .form-content-wrapper p, .form-content-wrapper .text-muted { color: #f8f9fa !important; font-size: 0.95rem !important; }
            .form-label { font-size: 0.95rem !important; font-weight: 600 !important; color: #fff !important; }
            .form-control, .form-select { font-size: 1rem !important; padding: 0.6rem 0.75rem !important; background-color: rgba(255, 255, 255, 0.9) !important; }
            .input-group-text { font-size: 1.1rem !important; padding: 0.6rem 0.75rem !important; }
            .btn-sm { font-size: 1rem !important; padding: 0.6rem 1rem !important; }
            .small { font-size: 0.9rem !important; }
        }
    </style>
</head>
<body>

    <div class="main-container <?= ($active_tab === 'register') ? 'right-panel-active' : ''; ?>" id="mainContainer">
        
        <!-- FORM LOGIN -->
        <div class="form-container login-container">
            <div class="form-slideshow-bg active" style="background-image: url('../assets/img/r1.jpg');"></div>
            <div class="form-slideshow-bg" style="background-image: url('../assets/img/r2.jpg');"></div>
            <div class="form-slideshow-bg" style="background-image: url('../assets/img/r3.jpg');"></div>
            <div class="form-slideshow-bg" style="background-image: url('../assets/img/r4.jpg');"></div>
            <div class="form-overlay-mask"></div>

            <div class="form-content-wrapper">
                <form action="" method="POST" class="w-100">
                    
                    <!-- LOGO NATURAL TANPA LINGKARAN PUTIH -->
                    <div class="text-center mb-3">
                        <img src="../assets/img/logo.png" alt="Logo USTJ" style="width: 80px; height: auto;" class="mb-2">
                        <h4 class="fw-bold text-white mb-1">Perpustakaan USTJ</h4>
                        <p class="text-light small mb-2">Silakan masuk ke akun Anda</p>
                    </div>

                    <?php if (!empty($error_login)) : ?>
                        <div class="alert alert-danger py-2 px-3 small mb-3" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($error_login); ?>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label text-light small fw-semibold mb-1">Login Sebagai</label>
                        <select class="form-select shadow-none" name="login_sebagai" required>
                            <option value="user">Pengguna / Mahasiswa</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-light small fw-semibold mb-1">Username / NIM</label>
                        <div class="input-group">
                            <span class="input-group-text text-muted"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control shadow-none" name="username" placeholder="Username atau NIM" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-light small fw-semibold mb-1">Password</label>
                        <div class="input-group">
                            <span class="input-group-text text-muted"><i class="bi bi-key"></i></span>
                            <input type="password" class="form-control shadow-none" name="password" id="loginPassword" placeholder="Password" required>
                            <button class="btn btn-light border text-muted px-3" type="button" onclick="togglePassword('loginPassword', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" name="login" class="btn btn-primary rounded-pill py-2 fw-semibold shadow-sm">
                            Masuk Sistem
                        </button>
                    </div>

                    <div class="text-center mt-3 mobile-switch">
                        <span class="text-white">Belum punya akun?</span> 
                        <button type="button" class="btn btn-link p-0 fw-semibold text-decoration-none ms-1 text-warning" id="switchToRegister">Daftar</button>
                    </div>

                    <div class="text-center mt-3">
                        <a href="index.php" class="text-decoration-none text-light small">
                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- FORM REGISTER -->
        <div class="form-container register-container">
            <div class="form-slideshow-bg active" style="background-image: url('../assets/img/r1.jpg');"></div>
            <div class="form-slideshow-bg" style="background-image: url('../assets/img/r2.jpg');"></div>
            <div class="form-slideshow-bg" style="background-image: url('../assets/img/r3.jpg');"></div>
            <div class="form-slideshow-bg" style="background-image: url('../assets/img/r4.jpg');"></div>
            <div class="form-overlay-mask"></div>

            <div class="form-content-wrapper scrollable-form">
                <form action="" method="POST" class="w-100">
                    
                    <!-- LOGO NATURAL TANPA LINGKARAN PUTIH -->
                    <div class="text-center mb-3">
                        <img src="../assets/img/logo.png" alt="Logo USTJ" style="width: 70px; height: auto;" class="mb-2">
                        <h5 class="fw-bold text-white mb-1">Daftar Akun Baru</h5>
                        <p class="text-light mb-2 small">Pendaftaran anggota perpustakaan</p>
                    </div>

                    <!-- PEMBERITAHUAN BIAYA & PERSETUJUAN ADMIN -->
                    <div class="alert alert-warning py-2 px-3 small mb-3 border-0 shadow-sm" role="alert" style="background-color: rgba(255, 193, 7, 0.95); color: #000;">
                        <div class="fw-bold mb-1"><i class="bi bi-info-circle-fill me-1"></i> Informasi Penting:</div>
                        <ul class="mb-0 ps-3" style="line-height: 1.4;">
                            <li>Akun baru memerlukan <strong>persetujuan (approval) dari admin</strong> perpustakaan sebelum dapat digunakan untuk login.</li>
                            <li>Terdapat biaya administrasi sebesar <strong>Rp 15.000</strong> untuk pencetakan kartu anggota fisik dan aktivasi peminjaman mandiri.</li>
                        </ul>
                    </div>

                    <?php if (!empty($error_register)) : ?>
                        <div class="alert alert-danger py-2 px-3 small mb-3" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($error_register); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success_register)) : ?>
                        <div class="alert alert-success py-2 px-3 small mb-3" role="alert">
                            <i class="bi bi-check-circle-fill me-1"></i> <?= htmlspecialchars($success_register); ?>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label text-light mb-1">NIM</label>
                        <input type="text" class="form-control shadow-none" name="nim" placeholder="NIM Mahasiswa" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-light mb-1">Nama Lengkap</label>
                        <input type="text" class="form-control shadow-none" name="nama_lengkap" placeholder="Nama Lengkap" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-light mb-1">Username</label>
                        <input type="text" class="form-control shadow-none" name="username" placeholder="Username" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-light mb-1">Email</label>
                        <input type="email" class="form-control shadow-none" name="email" placeholder="Email aktif" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-light mb-1">Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control shadow-none" name="password" id="regPassword" placeholder="Password" required>
                            <button class="btn btn-light border text-muted px-3" type="button" onclick="togglePassword('regPassword', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-light mb-1">Konfirmasi Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control shadow-none" name="konfirmasi_password" id="regConfirmPassword" placeholder="Ulangi Password" required>
                            <button class="btn btn-light border text-muted px-3" type="button" onclick="togglePassword('regConfirmPassword', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" name="register" class="btn btn-primary rounded-pill py-2 fw-semibold shadow-sm">
                            Daftar Sekarang
                        </button>
                    </div>

                    <div class="text-center mt-3 mobile-switch">
                        <span class="text-white">Sudah punya akun?</span> 
                        <button type="button" class="btn btn-link p-0 fw-semibold text-decoration-none ms-1 text-warning" id="switchToLogin">Masuk</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- PANEL ANIMASI GESER (DESKTOP) -->
        <div class="overlay-container">
            <div class="overlay">
                <div class="slideshow-bg active" style="background-image: url('../assets/img/r1.jpg');"></div>
                <div class="slideshow-bg" style="background-image: url('../assets/img/r2.jpg');"></div>
                <div class="slideshow-bg" style="background-image: url('../assets/img/r3.jpg');"></div>
                <div class="slideshow-bg" style="background-image: url('../assets/img/r4.jpg');"></div>
                
                <div class="overlay-mask"></div>

                <div class="overlay-panel overlay-left">
                    <h3 class="fw-bold mb-2 text-white shadow-sm">Sudah Punya Akun?</h3>
                    <p class="small mb-3 px-3 text-light">Silakan masuk untuk mengakses sistem perpustakaan.</p>
                    <button class="btn btn-outline-light rounded-pill px-4 py-2 fw-semibold shadow-none" id="signIn">Masuk</button>
                </div>
                <div class="overlay-panel overlay-right">
                    <h3 class="fw-bold mb-2 text-white shadow-sm">Belum Punya Akun?</h3>
                    <p class="small mb-3 px-3 text-light">Daftarkan diri Anda sebagai anggota perpustakaan baru di sini.</p>
                    <button class="btn btn-outline-light rounded-pill px-4 py-2 fw-semibold shadow-none" id="signUp">Daftar Akun</button>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const signUpButton = document.getElementById('signUp');
        const signInButton = document.getElementById('signIn');
        const switchToRegister = document.getElementById('switchToRegister');
        const switchToLogin = document.getElementById('switchToLogin');
        const mainContainer = document.getElementById('mainContainer');

        if (signUpButton) {
            signUpButton.addEventListener('click', () => mainContainer.classList.add("right-panel-active"));
        }
        if (signInButton) {
            signInButton.addEventListener('click', () => mainContainer.classList.remove("right-panel-active"));
        }
        if (switchToRegister) {
            switchToRegister.addEventListener('click', () => mainContainer.classList.add("right-panel-active"));
        }
        if (switchToLogin) {
            switchToLogin.addEventListener('click', () => mainContainer.classList.remove("right-panel-active"));
        }

        function togglePassword(fieldId, toggleBtn) {
            const passwordField = document.getElementById(fieldId);
            const icon = toggleBtn.querySelector('i');
            
            if (passwordField.type === "password") {
                passwordField.type = "text";
                icon.classList.remove("bi-eye");
                icon.classList.add("bi-eye-slash");
            } else {
                passwordField.type = "password";
                icon.classList.remove("bi-eye-slash");
                icon.classList.add("bi-eye");
            }
        }

        function nextSlide() {
            const slides = document.querySelectorAll('.overlay-container .slideshow-bg');
            if (slides.length > 0) {
                let current = Array.from(slides).findIndex(slide => slide.classList.contains('active'));
                slides[current].classList.remove('active');
                let next = (current + 1) % slides.length;
                slides[next].classList.add('active');
            }

            const formSlides = document.querySelectorAll('.form-slideshow-bg');
            if (formSlides.length > 0) {
                let currentForm = Array.from(formSlides).findIndex(slide => slide.classList.contains('active'));
                if (currentForm !== -1) {
                    formSlides[currentForm].classList.remove('active');
                    let nextForm = (currentForm + 1) % formSlides.length;
                    formSlides[nextForm].classList.add('active');
                }
            }
        }

        setInterval(nextSlide, 4000);
    </script>
</body>
</html>