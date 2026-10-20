<?php
session_start();
include '../koneksi.php';

if (empty($_SESSION['admin_login']) || empty($_SESSION['admin_id'])) {
	header('Location: ../frontend/login.php');
	exit();
}

$id_admin = (int) $_SESSION['admin_id'];
$pesan_sukses = '';
$pesan_gagal = '';
$dir_foto_admin = __DIR__ . '/../assets/img/profil/';
$foto_admin_db = '';
$stmt_foto = mysqli_prepare($koneksi, 'SELECT foto FROM admin WHERE id_admin = ? LIMIT 1');
if ($stmt_foto) {
	mysqli_stmt_bind_param($stmt_foto, 'i', $id_admin);
	mysqli_stmt_execute($stmt_foto);
	mysqli_stmt_bind_result($stmt_foto, $foto_admin_db);
	mysqli_stmt_fetch($stmt_foto);
	mysqli_stmt_close($stmt_foto);
}
$foto_admin = basename((string) $foto_admin_db);
if ($foto_admin === '' || !is_file($dir_foto_admin . $foto_admin)) {
	$foto_admin = '';
}

if (empty($_SESSION['csrf_pengaturan'])) {
	$_SESSION['csrf_pengaturan'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$token = $_POST['csrf_token'] ?? '';
	$username_baru = trim($_POST['username'] ?? '');
	$password_lama = $_POST['password_lama'] ?? '';
	$password_baru = $_POST['password_baru'] ?? '';
	$konfirmasi_password = $_POST['konfirmasi_password'] ?? '';

	if (!hash_equals($_SESSION['csrf_pengaturan'], $token)) {
		$pesan_gagal = 'Permintaan tidak valid. Muat ulang halaman, lalu coba kembali.';
	} elseif (isset($_POST['simpan_foto'])) {
		$file_foto = $_FILES['foto_profil'] ?? null;
		if (!$file_foto || $file_foto['error'] !== UPLOAD_ERR_OK) {
			$pesan_gagal = 'Pilih foto profil yang valid untuk diunggah.';
		} elseif ($file_foto['size'] > 2 * 1024 * 1024) {
			$pesan_gagal = 'Ukuran foto maksimal 2 MB.';
		} else {
			$info_foto = @getimagesize($file_foto['tmp_name']);
			$tipe_foto = $info_foto['mime'] ?? '';
			$ekstensi_foto = ['image/jpeg' => 'jpg', 'image/png' => 'png'][$tipe_foto] ?? '';

			if ($ekstensi_foto === '') {
				$pesan_gagal = 'Format foto tidak didukung. Gunakan JPG, JPEG, atau PNG.';
			} elseif (!is_dir($dir_foto_admin) && !mkdir($dir_foto_admin, 0755, true)) {
				$pesan_gagal = 'Folder penyimpanan foto tidak dapat dibuat.';
			} else {
				$nama_foto_baru = 'admin_' . $id_admin . '_' . bin2hex(random_bytes(8)) . '.' . $ekstensi_foto;
				if (move_uploaded_file($file_foto['tmp_name'], $dir_foto_admin . $nama_foto_baru)) {
					$stmt_foto = mysqli_prepare($koneksi, 'UPDATE admin SET foto = ? WHERE id_admin = ?');
					if ($stmt_foto) {
						mysqli_stmt_bind_param($stmt_foto, 'si', $nama_foto_baru, $id_admin);
						$foto_tersimpan = mysqli_stmt_execute($stmt_foto);
						mysqli_stmt_close($stmt_foto);
					} else {
						$foto_tersimpan = false;
					}

					if ($foto_tersimpan) {
						if (preg_match('/^admin_' . $id_admin . '(?:_[a-f0-9]{16})?\.(?:jpg|jpeg|png)$/', (string) $foto_admin_db)) {
							$foto_lama = $dir_foto_admin . $foto_admin_db;
							if (is_file($foto_lama)) {
								unlink($foto_lama);
							}
						}
						$_SESSION['csrf_pengaturan'] = bin2hex(random_bytes(32));
						header('Location: pengaturan.php?status=foto_berhasil');
						exit();
					}

					unlink($dir_foto_admin . $nama_foto_baru);
					$pesan_gagal = 'Foto profil gagal disimpan ke database.';
				}
				if ($pesan_gagal === '') {
					$pesan_gagal = 'Foto profil gagal disimpan. Periksa izin folder penyimpanan.';
				}
			}
		}
	} elseif ($username_baru === '' || $password_lama === '') {
		$pesan_gagal = 'Username dan password saat ini wajib diisi.';
	} elseif ($password_baru !== '' && strlen($password_baru) < 8) {
		$pesan_gagal = 'Password baru minimal 8 karakter.';
	} elseif ($password_baru !== $konfirmasi_password) {
		$pesan_gagal = 'Konfirmasi password baru tidak cocok.';
	} else {
		$stmt = mysqli_prepare($koneksi, 'SELECT username, password FROM admin WHERE id_admin = ? LIMIT 1');
		mysqli_stmt_bind_param($stmt, 'i', $id_admin);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $username_saat_ini, $password_tersimpan);
		$admin_ditemukan = mysqli_stmt_fetch($stmt);
		mysqli_stmt_close($stmt);

		if (!$admin_ditemukan) {
			$pesan_gagal = 'Akun admin tidak ditemukan.';
		} elseif (!password_verify($password_lama, $password_tersimpan) && !hash_equals((string) $password_tersimpan, $password_lama)) {
			$pesan_gagal = 'Password saat ini tidak sesuai.';
		} else {
			$stmt = mysqli_prepare($koneksi, 'SELECT id_admin FROM admin WHERE username = ? AND id_admin <> ? LIMIT 1');
			mysqli_stmt_bind_param($stmt, 'si', $username_baru, $id_admin);
			mysqli_stmt_execute($stmt);
			mysqli_stmt_store_result($stmt);
			$username_dipakai = mysqli_stmt_num_rows($stmt) > 0;
			mysqli_stmt_close($stmt);

			if ($username_dipakai) {
				$pesan_gagal = 'Username tersebut sudah digunakan admin lain.';
			} else {
				$password_simpan = $password_baru !== '' ? password_hash($password_baru, PASSWORD_DEFAULT) : $password_tersimpan;
				$stmt = mysqli_prepare($koneksi, 'UPDATE admin SET username = ?, password = ? WHERE id_admin = ?');
				mysqli_stmt_bind_param($stmt, 'ssi', $username_baru, $password_simpan, $id_admin);
				$berhasil = mysqli_stmt_execute($stmt);
				mysqli_stmt_close($stmt);

				if ($berhasil) {
					$_SESSION['admin_user'] = $username_baru;
					$_SESSION['csrf_pengaturan'] = bin2hex(random_bytes(32));
					header('Location: pengaturan.php?status=berhasil');
					exit();
				}

				$pesan_gagal = 'Pengaturan akun belum berhasil disimpan. Silakan coba kembali.';
			}
		}
	}
}

if (isset($_GET['status']) && $_GET['status'] === 'berhasil') {
	$pesan_sukses = 'Pengaturan akun berhasil diperbarui.';
} elseif (isset($_GET['status']) && $_GET['status'] === 'foto_berhasil') {
	$pesan_sukses = 'Foto profil admin berhasil diperbarui.';
}

$username_tampil = $_SESSION['admin_user'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Pengaturan - Perpustakaan USTJ</title>
	<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
	<style>
		:root { --sidebar-width: 250px; --main-bg: #f8fafc; --border-color: #e2e8f0; }
		* { box-sizing: border-box; }
		body { margin: 0; background: var(--main-bg); color: #1e293b; font-family: 'Plus Jakarta Sans', sans-serif; }
		#main-content-wrapper { width: calc(100% - var(--sidebar-width)); min-height: 100vh; margin-left: var(--sidebar-width); }
		.top-navbar { min-height: 70px; padding: 0 28px; background: #fff; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; }
		.content-body { max-width: 1400px; margin: 0 auto; padding: 28px; }
		.page-title { color: #0f172a; font-size: 24px; font-weight: 700; }
		.settings-panel { overflow: hidden; border: 1px solid var(--border-color); border-radius: 10px; background: #fff; box-shadow: 0 1px 3px rgba(15, 23, 42, .04); }
		.panel-heading { padding: 20px 22px; border-bottom: 1px solid #edf1f5; }
		.panel-body { padding: 22px; }
		.identity-icon { width: 48px; height: 48px; display: grid; place-items: center; border-radius: 10px; background: #e8f3ef; color: #18734f; font-size: 21px; }
		.form-label { font-size: 13px; font-weight: 600; color: #334155; }
		.form-control { min-height: 42px; border-color: #d8e0e8; }
		.form-control:focus { border-color: #16815a; box-shadow: 0 0 0 .2rem rgba(22, 129, 90, .12); }
		.security-note { padding: 14px 16px; border-left: 3px solid #16815a; background: #f1f8f5; color: #36594a; font-size: 13px; }
		.admin-avatar { width: 56px; height: 56px; display: grid; place-items: center; flex: 0 0 56px; overflow: hidden; border-radius: 50%; background: #e8f3ef; color: #18734f; font-size: 24px; }
		.admin-avatar img { width: 100%; height: 100%; object-fit: cover; }
		@media (max-width: 768px) {
			#main-content-wrapper { width: 100%; margin-left: 0; }
			.top-navbar { min-height: 60px; padding: 0 16px; }
			.content-body { padding: 20px 16px; }
			.panel-body, .panel-heading { padding: 18px; }
		}
	</style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div id="main-content-wrapper">
	<nav class="top-navbar">
		<div class="small text-secondary"><span class="fw-semibold text-dark">PerpusAdmin USTJ</span> / <span class="fw-semibold text-success">Pengaturan</span></div>
		<div class="small fw-semibold text-muted">Universitas Sains dan Teknologi Jayapura</div>
	</nav>

	<main class="content-body">
		<header class="mb-4">
			<h1 class="page-title mb-1"><i class="bi bi-gear text-success me-2"></i>Pengaturan Akun</h1>
			<p class="text-muted small mb-0">Perbarui username dan password untuk akun administrator.</p>
		</header>

		<?php if ($pesan_sukses !== ''): ?>
			<div class="alert alert-success" role="alert"><i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($pesan_sukses, ENT_QUOTES, 'UTF-8'); ?></div>
		<?php endif; ?>
		<?php if ($pesan_gagal !== ''): ?>
			<div class="alert alert-danger" role="alert"><i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($pesan_gagal, ENT_QUOTES, 'UTF-8'); ?></div>
		<?php endif; ?>

		<section class="settings-panel mb-4">
			<div class="panel-heading d-flex align-items-center gap-3">
				<div class="admin-avatar">
					<?php if ($foto_admin !== ''): ?>
						<img src="../assets/img/profil/<?= rawurlencode($foto_admin); ?>" alt="Foto profil admin">
					<?php else: ?>
						<i class="bi bi-person-gear"></i>
					<?php endif; ?>
				</div>
				<div>
					<h2 class="h6 fw-bold mb-1">Akun administrator</h2>
					<p class="small text-muted mb-0">Username aktif: <strong><?= htmlspecialchars($username_tampil, ENT_QUOTES, 'UTF-8'); ?></strong></p>
				</div>
			</div>
			<div class="panel-body">
				<form method="POST" action="pengaturan.php" enctype="multipart/form-data" class="mb-4">
					<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_pengaturan'], ENT_QUOTES, 'UTF-8'); ?>">
					<label for="foto_profil" class="form-label">Foto profil admin</label>
					<div class="row g-2 align-items-center">
						<div class="col-md-9">
							<input type="file" id="foto_profil" name="foto_profil" class="form-control" accept="image/jpeg,image/png" required>
							<div class="form-text">Format JPG atau PNG, maksimal 2 MB.</div>
						</div>
						<div class="col-md-3 d-grid">
							<button type="submit" name="simpan_foto" value="1" class="btn btn-outline-success"><i class="bi bi-camera me-1"></i>Unggah Foto</button>
						</div>
					</div>
				</form>
				<hr class="mb-4">
				<form method="POST" action="pengaturan.php" autocomplete="off">
					<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_pengaturan'], ENT_QUOTES, 'UTF-8'); ?>">

					<div class="row g-3">
						<div class="col-md-6">
							<label for="username" class="form-label">Username baru</label>
							<input type="text" id="username" name="username" class="form-control" value="<?= htmlspecialchars($_POST['username'] ?? $username_tampil, ENT_QUOTES, 'UTF-8'); ?>" minlength="3" maxlength="50" required>
						</div>
						<div class="col-md-6">
							<label for="password_lama" class="form-label">Password saat ini</label>
							<input type="password" id="password_lama" name="password_lama" class="form-control" autocomplete="current-password" required>
						</div>
						<div class="col-md-6">
							<label for="password_baru" class="form-label">Password baru <span class="fw-normal text-muted">(opsional)</span></label>
							<input type="password" id="password_baru" name="password_baru" class="form-control" minlength="8" autocomplete="new-password" aria-describedby="passwordHelp">
							<div id="passwordHelp" class="form-text">Kosongkan jika hanya ingin mengubah username. Minimal 8 karakter.</div>
						</div>
						<div class="col-md-6">
							<label for="konfirmasi_password" class="form-label">Konfirmasi password baru</label>
							<input type="password" id="konfirmasi_password" name="konfirmasi_password" class="form-control" minlength="8" autocomplete="new-password">
						</div>
					</div>

					<div class="security-note mt-4"><i class="bi bi-shield-check me-2"></i>Password baru disimpan dalam bentuk hash dan tidak dapat dilihat kembali.</div>
					<div class="d-flex justify-content-end mt-4">
						<button type="submit" class="btn btn-success px-4"><i class="bi bi-check2 me-1"></i>Simpan Perubahan</button>
					</div>
				</form>
			</div>
		</section>

		<section class="settings-panel">
			<div class="panel-heading">
				<h2 class="h6 fw-bold mb-1"><i class="bi bi-info-circle text-success me-2"></i>Informasi aplikasi</h2>
				<p class="small text-muted mb-0">Lingkungan operasional sistem perpustakaan.</p>
			</div>
			<div class="panel-body">
				<div class="row g-3">
					<div class="col-sm-6">
						<div class="small text-muted mb-1">Nama sistem</div>
						<div class="fw-semibold">Sistem Informasi Perpustakaan USTJ</div>
					</div>
					<div class="col-sm-6">
						<div class="small text-muted mb-1">Zona waktu server</div>
						<div class="fw-semibold"><?= htmlspecialchars(date_default_timezone_get(), ENT_QUOTES, 'UTF-8'); ?></div>
					</div>
				</div>
			</div>
		</section>
	</main>
</div>
</body>
</html>
