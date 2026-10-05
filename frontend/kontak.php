<?php
include 'header.php';

$success = false;

if(isset($_POST['kirim'])){
    $nama   = htmlspecialchars($_POST['nama']);
    $email  = htmlspecialchars($_POST['email']);
    $subjek = htmlspecialchars($_POST['subjek']);
    $pesan  = htmlspecialchars($_POST['pesan']);

    // Nanti bisa INSERT ke database
    $success = true;
}
?>

<style>
body{
    background:#f4f7fb;
    font-family:'Segoe UI',sans-serif;
}

/* HERO */
.hero{
    position:relative;
    min-height:340px;
    border-radius:24px;
    overflow:hidden;
    background:url('../assets/img/belajar3.jpg') center/cover;
}

.hero::before{
    content:'';
    position:absolute;
    inset:0;
    background:linear-gradient(135deg,
    rgba(7,33,71,.88),
    rgba(13,110,253,.45));
}

.hero-content{
    position:relative;
    z-index:2;
    color:#fff;
    padding:55px 40px;
}

.hero h1{
    font-weight:700;
}

.hero p{
    color:rgba(255,255,255,.85);
}

/* CARD */
.info-card,
.form-card{
    border:none;
    border-radius:20px;
    box-shadow:0 10px 25px rgba(0,0,0,.06);
}

.info-card{
    transition:.3s;
}

.info-card:hover{
    transform:translateY(-6px);
    box-shadow:0 18px 30px rgba(0,0,0,.12);
}

.icon-circle{
    width:60px;
    height:60px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#eaf2ff;
    color:#0d6efd;
    font-size:26px;
}

.form-control,
.form-select{
    border-radius:12px;
    padding:12px 15px;
    border:1px solid #dce3ee;
}

.form-control:focus,
.form-select:focus{
    border-color:#0d6efd;
    box-shadow:0 0 0 .15rem rgba(13,110,253,.15);
}

.btn-send{
    border-radius:50px;
    padding:12px 28px;
    font-weight:600;
}

.list-contact i{
    color:#0d6efd;
    width:24px;
}

.social a{
    width:45px;
    height:45px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#fff;
    text-decoration:none;
}

.fb{background:#1877f2;}
.ig{background:#e1306c;}
.wa{background:#25d366;}

.map-box{
    border-radius:20px;
    overflow:hidden;
}

/* MOBILE */
@media(max-width:768px){

.hero{
    min-height:420px;
}

.hero-content{
    padding:30px 22px;
}

.hero h1{
    font-size:28px;
}

}
</style>

<div class="container py-4">

<!-- HERO -->
<section class="hero mb-5">

<div class="hero-content">

<span class="badge bg-warning text-dark px-3 py-2 mb-3">
📞 Hubungi Perpustakaan USTJ
</span>

<h1>Kami Siap Membantu Anda</h1>

<p class="col-lg-7 mt-3">
Silakan menghubungi Perpustakaan Universitas Sains dan Teknologi Jayapura
apabila membutuhkan informasi mengenai koleksi buku, skripsi, keanggotaan,
atau layanan perpustakaan.
</p>

</div>

</section>

<!-- INFORMASI -->
<div class="row g-4 mb-5">

<div class="col-md-6 col-lg-3">
<div class="info-card bg-white p-4 h-100 text-center">
<div class="icon-circle mx-auto mb-3">
<i class="bi bi-geo-alt-fill"></i>
</div>
<h6 class="fw-bold">Alamat</h6>
<p class="text-muted small mb-0">
USTJ Abepura<br>
Kota Jayapura, Papua
</p>
</div>
</div>

<div class="col-md-6 col-lg-3">
<div class="info-card bg-white p-4 h-100 text-center">
<div class="icon-circle mx-auto mb-3">
<i class="bi bi-telephone-fill"></i>
</div>
<h6 class="fw-bold">Telepon</h6>
<p class="text-muted small mb-0">
(0967) 123456
</p>
</div>
</div>

<div class="col-md-6 col-lg-3">
<div class="info-card bg-white p-4 h-100 text-center">
<div class="icon-circle mx-auto mb-3">
<i class="bi bi-envelope-fill"></i>
</div>
<h6 class="fw-bold">Email</h6>
<p class="text-muted small mb-0">
perpus@ustj.ac.id
</p>
</div>
</div>

<div class="col-md-6 col-lg-3">
<div class="info-card bg-white p-4 h-100 text-center">
<div class="icon-circle mx-auto mb-3">
<i class="bi bi-clock-fill"></i>
</div>
<h6 class="fw-bold">Jam Buka</h6>
<p class="text-muted small mb-0">
Senin–Jumat<br>
08.00–16.00 WIT
</p>
</div>
</div>

</div>

<!-- FORM -->
<div class="row g-4 mb-5">

<div class="col-lg-7">

<div class="form-card bg-white p-4">

<h3 class="fw-bold mb-2">
Kirim Pesan
</h3>

<p class="text-muted mb-4">
Masukkan pertanyaan atau informasi yang ingin Anda sampaikan kepada petugas perpustakaan.
</p>

<?php if($success): ?>
<div class="alert alert-success border-0 rounded-3">
<i class="bi bi-check-circle-fill me-2"></i>
Terima kasih <strong><?= $nama ?></strong>, pesan Anda berhasil dikirim.
</div>
<?php endif; ?>

<form method="POST">

<div class="row g-3">

<div class="col-md-6">
<label class="form-label fw-semibold">Nama Lengkap</label>
<input type="text" name="nama" class="form-control" required>
</div>

<div class="col-md-6">
<label class="form-label fw-semibold">Email</label>
<input type="email" name="email" class="form-control" required>
</div>

<div class="col-12">
<label class="form-label fw-semibold">Subjek</label>
<select name="subjek" class="form-select" required>
<option value="">Pilih Subjek</option>
<option>Informasi Buku</option>
<option>Informasi Skripsi</option>
<option>Keanggotaan</option>
<option>Layanan Perpustakaan</option>
<option>Lainnya</option>
</select>
</div>

<div class="col-12">
<label class="form-label fw-semibold">Pesan</label>
<textarea name="pesan" rows="6" class="form-control"
placeholder="Tuliskan pesan Anda..." required></textarea>
</div>

<div class="col-12">
<button type="submit" name="kirim" class="btn btn-primary btn-send">
<i class="bi bi-send-fill me-2"></i>
Kirim Pesan
</button>
</div>

</div>

</form>

</div>

</div>

<!-- SIDEBAR -->
<div class="col-lg-5">

<div class="form-card bg-white p-4 mb-4">

<h4 class="fw-bold mb-3">
Informasi Kontak
</h4>

<div class="list-contact">

<p><i class="bi bi-building"></i> Perpustakaan Universitas Sains dan Teknologi Jayapura</p>

<p><i class="bi bi-geo-alt"></i> Jl. Raya Sentani – Padang Bulan, Abepura</p>

<p><i class="bi bi-envelope"></i> perpus@ustj.ac.id</p>

<p><i class="bi bi-telephone"></i> (0967) 123456</p>

<p><i class="bi bi-clock"></i> Senin – Jumat (08.00–16.00 WIT)</p>

</div>

<hr>

<h6 class="fw-bold mb-3">
Ikuti Kami
</h6>

<div class="social d-flex gap-2">

<a href="#" class="fb">
<i class="bi bi-facebook"></i>
</a>

<a href="#" class="ig">
<i class="bi bi-instagram"></i>
</a>

<a href="#" class="wa">
<i class="bi bi-whatsapp"></i>
</a>

</div>

</div>

<div class="form-card bg-primary text-white p-4">

<h5 class="fw-bold">
Butuh Bantuan Cepat?
</h5>

<p class="mb-4" style="opacity:.9;">
Petugas perpustakaan siap membantu informasi koleksi buku, skripsi, dan keanggotaan.
</p>

<a href="#" class="btn btn-light rounded-pill px-4">
<i class="bi bi-chat-dots me-2"></i>
Hubungi Petugas
</a>

</div>

</div>

</div>

<!-- MAP -->
<div class="form-card bg-white p-3">

<div class="d-flex justify-content-between align-items-center mb-3">

<div>
<h4 class="fw-bold mb-1">
Lokasi Perpustakaan
</h4>
<p class="text-muted small mb-0">
Universitas Sains dan Teknologi Jayapura
</p>
</div>

<span class="badge bg-primary px-3 py-2">
Abepura
</span>

</div>

<div class="map-box">

<iframe
src="https://www.google.com/maps?q=Universitas%20Sains%20dan%20Teknologi%20Jayapura&output=embed"
width="100%"
height="380"
style="border:0;"
allowfullscreen=""
loading="lazy">
</iframe>

</div>

</div>

</div>

<?php include 'footer.php'; ?>