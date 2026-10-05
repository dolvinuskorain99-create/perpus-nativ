<?php
include '../koneksi.php';

/* =========================
   PENCARIAN
========================= */
$keyword = '';

if(isset($_GET['cari']) && trim($_GET['cari']) != ''){

    $keyword = mysqli_real_escape_string($koneksi, trim($_GET['cari']));

    $hasil_buku = mysqli_query($koneksi,"
        SELECT * FROM buku
        WHERE judul_buku LIKE '%$keyword%'
        OR penulis LIKE '%$keyword%'
        OR penerbit LIKE '%$keyword%'
        ORDER BY id_buku DESC
        LIMIT 8
    ");

    $hasil_skripsi = mysqli_query($koneksi,"
        SELECT * FROM skripsi
        WHERE judul_skripsi LIKE '%$keyword%'
        OR nama_penulis LIKE '%$keyword%'
        OR nim_mahasiswa LIKE '%$keyword%'
        OR prodi LIKE '%$keyword%'
        ORDER BY id_skripsi DESC
        LIMIT 6
    ");

}else{

    $hasil_buku = mysqli_query($koneksi,"
        SELECT * FROM buku
        ORDER BY id_buku DESC
        LIMIT 8
    ");

    $hasil_skripsi = mysqli_query($koneksi,"
        SELECT * FROM skripsi
        ORDER BY id_skripsi DESC
        LIMIT 6
    ");

}

/* =========================
   STATISTIK
========================= */

$total_buku = mysqli_fetch_assoc(
    mysqli_query($koneksi,"SELECT COUNT(*) total FROM buku")
)['total'];

$total_skripsi = mysqli_fetch_assoc(
    mysqli_query($koneksi,"SELECT COUNT(*) total FROM skripsi")
)['total'];

$total_anggota = mysqli_fetch_assoc(
    mysqli_query($koneksi,"SELECT COUNT(*) total FROM users")
)['total'];

include 'header.php';
?>

<style>
body{
    background:#f5f7fb;
}

/* HERO */
.hero{
    position:relative;
    min-height:620px;
    overflow:hidden;
}

.hero-slide{
    position:absolute;
    inset:0;
    background-size:cover;
    background-position:center;
    animation:slide 28s infinite;
}

@keyframes slide{
    0%,12%{background-image:url('../assets/img/belajar1.jpg');}
    14%,26%{background-image:url('../assets/img/belajar2.jpg');}
    28%,40%{background-image:url('../assets/img/belajar3.jpg');}
    42%,54%{background-image:url('../assets/img/r1.jpg');}
    56%,68%{background-image:url('../assets/img/r2.jpg');}
    70%,82%{background-image:url('../assets/img/r3.jpg');}
    84%,100%{background-image:url('../assets/img/r4.jpg');}
}

.hero::before{
    content:'';
    position:absolute;
    inset:0;
    background:linear-gradient(rgba(5,25,50,.84),rgba(13,110,253,.55));
    z-index:1;
}

.hero-content{
    position:relative;
    z-index:2;
    padding-top:80px;
}

/* GLASS */
.glass-card{
    margin-top:60px;
    background:rgba(13,110,253,.20);
    backdrop-filter:blur(18px);
    border:1px solid rgba(255,255,255,.18);
    border-radius:28px;
    padding:35px;
    box-shadow:0 20px 50px rgba(0,0,0,.25);
}

.search-box{
    background:#fff;
    border-radius:50px;
    padding:6px;
}

.search-box input{
    border:none;
    box-shadow:none;
}

.counter-box{
    background:rgba(255,255,255,.10);
    border-radius:18px;
    padding:18px;
}

.counter-box h2{
    margin:0;
    font-weight:bold;
}

/* SECTION */
.content-card{
    background:#fff;
    border-radius:24px;
    padding:28px;
    box-shadow:0 10px 30px rgba(15,23,42,.06);
    margin-bottom:40px;
}

.section-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:24px;
    flex-wrap:wrap;
    gap:15px;
}

.btn-all{
    border-radius:50px;
    padding:10px 18px;
    font-weight:600;
}

/* CARD */
.card-book{
    border:none;
    border-radius:18px;
    transition:.3s;
    overflow:hidden;
}

.card-book:hover{
    transform:translateY(-6px);
    box-shadow:0 18px 35px rgba(0,0,0,.12);
}

.book-icon{
    height:95px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#f8fbff;
}
</style>

<!-- ================= HERO ================= -->
<header class="hero">

<div class="hero-slide"></div>

<div class="container hero-content">

<div class="text-center text-white">

<span class="badge bg-warning text-dark px-3 py-2 rounded-pill mb-3">
📚 Perpustakaan Universitas Sains dan Teknologi Jayapura
</span>

<h1 class="display-4 fw-bold">
Selamat Datang di Perpustakaan USTJ
</h1>

<p class="lead text-white-50 col-lg-8 mx-auto">
Portal digital untuk mencari buku, skripsi, dan tugas akhir mahasiswa USTJ secara cepat, mudah, dan gratis.
</p>

</div>

<!-- CARD PENCARIAN -->

<div class="glass-card">

<form action="index.php" method="GET">

<div class="input-group input-group-lg search-box">

<span class="input-group-text bg-transparent border-0">
<i class="bi bi-search"></i>
</span>

<input
type="text"
name="cari"
class="form-control"
placeholder="Cari judul buku, penulis, skripsi, NIM..."
value="<?= htmlspecialchars($keyword); ?>">

<button class="btn btn-primary rounded-pill px-4">
Cari
</button>

</div>

</form>

<!-- COUNTER -->

<div class="row g-3 text-center text-white mt-4">

<div class="col-md-4">

<div class="counter-box">

<i class="bi bi-book fs-2"></i>

<h2 class="counter" data-target="<?= $total_buku ?>">0</h2>

<small>Koleksi Buku</small>

</div>

</div>

<div class="col-md-4">

<div class="counter-box">

<i class="bi bi-journal-richtext fs-2"></i>

<h2 class="counter" data-target="<?= $total_skripsi ?>">0</h2>

<small>Arsip Skripsi</small>

</div>

</div>

<div class="col-md-4">

<div class="counter-box">

<i class="bi bi-people fs-2"></i>

<h2 class="counter" data-target="<?= $total_anggota ?>">0</h2>

<small>Anggota</small>

</div>

</div>

</div>

</div>

</div>

</header>

<!-- ================= ISI ================= -->

<main class="container py-5">

<!-- ================= BUKU ================= -->

<section class="content-card">

<div class="section-header">

<div>

<h3 class="fw-bold mb-1">
<i class="bi bi-book text-primary me-2"></i>
Koleksi Buku Terbaru
</h3>

<small class="text-muted">
<?= $total_buku ?> koleksi buku tersedia
</small>

</div>

<a href="katalog.php" class="btn btn-outline-primary btn-all">

Lihat Semua

<i class="bi bi-arrow-right ms-1"></i>

</a>

</div>

<div class="row g-4">

<?php while($buku=mysqli_fetch_assoc($hasil_buku)): ?>

<div class="col-md-6 col-lg-3">

<div class="card card-book h-100 shadow-sm">

<div class="book-icon">

<i class="bi bi-book-fill display-5 text-primary"></i>

</div>

<div class="card-body d-flex flex-column">

    <span class="badge bg-primary-subtle text-primary mb-2">
        Buku
    </span>

    <h6 class="fw-bold" style="min-height:48px;">
        <?= htmlspecialchars($buku['judul_buku'] ?? '-'); ?>
    </h6>

    <!-- PENGARANG / PENULIS -->
    <p class="small text-muted mb-1">
        <i class="bi bi-person me-1"></i>
        <?= htmlspecialchars($buku['penulis'] ?? '-'); ?>
    </p>

    <!-- PENERBIT -->
    <p class="small text-muted mb-3">
        <i class="bi bi-building me-1"></i>
        <?= htmlspecialchars($buku['penerbit'] ?? '-'); ?>
    </p>

    <!-- TOMBOL LIHAT DETAIL BUKU -->
    <button
    class="btn btn-primary rounded-pill mt-auto"
    data-bs-toggle="modal"
    data-bs-target="#detailBuku<?= $buku['id_buku']; ?>">

    Lihat Detail

    </button>

</div>  

</div>

</div>

<!-- MODAL DETAIL BUKU -->

<div class="modal fade" id="detailBuku<?= $buku['id_buku']; ?>">

<div class="modal-dialog modal-lg modal-dialog-centered">

<div class="modal-content rounded-4 border-0">

<div class="modal-header bg-primary text-white">

<h5 class="modal-title">

Detail Buku

</h5>

<button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>

</div>

<div class="modal-body p-4">

<h4 class="fw-bold mb-3">
<?= htmlspecialchars($buku['judul_buku'] ?? '-'); ?>
</h4>

<div class="row g-3">

<div class="col-md-6">

<small class="text-muted">Kode Buku</small>

<div><?= htmlspecialchars($buku['kode_buku'] ?? '-'); ?></div>

</div>

<div class="col-md-6">

<small class="text-muted">Penulis</small>

<div><?= htmlspecialchars($buku['pengarang'] ?? '-'); ?></div>

</div>

<div class="col-md-6">

<small class="text-muted">Penerbit</small>

<div><?= htmlspecialchars($buku['penerbit'] ?? '-'); ?></div>

</div>

<div class="col-md-6">

<small class="text-muted">Tahun Terbit</small>

<div><?= htmlspecialchars($buku['tahun_terbit'] ?? '-'); ?></div>

</div>

<div class="col-1id mt-3">

<small class="text-muted">Stok Buku</small>

<div class="fw-bold text-primary"><?= htmlspecialchars($buku['stok'] ?? '-'); ?> Eksemplar</div>

</div>

</div>

</div>

</div>

</div>

</div>

<?php endwhile; ?>

</div>

</section>

<!-- ================= SKRIPSI ================= -->

<section class="content-card">

<div class="section-header">

<div>

<h3 class="fw-bold mb-1">
<i class="bi bi-mortarboard text-success me-2"></i>
Skripsi Terbaru
</h3>

<small class="text-muted">
<?= $total_skripsi ?> arsip skripsi tersedia
</small>

</div>

<a href="katalog_skripsi.php" class="btn btn-outline-success btn-all">

Lihat Semua

<i class="bi bi-arrow-right ms-1"></i>

</a>

</div>

<div class="row g-4">

<?php while($skripsi=mysqli_fetch_assoc($hasil_skripsi)): ?>

<div class="col-md-6 col-lg-4">

<div class="card card-book h-100 shadow-sm">

<div class="book-icon bg-success bg-opacity-10">

<i class="bi bi-file-earmark-richtext-fill display-5 text-success"></i>

</div>

<div class="card-body d-flex flex-column">

<div class="d-flex justify-content-between align-items-center mb-2">

<span class="badge bg-success-subtle text-success">
Skripsi
</span>

<small class="text-muted">
<?= $skripsi['tahun_lulus']; ?>
</small>

</div>

<h6 class="fw-bold" style="min-height:52px;">
<?= htmlspecialchars($skripsi['judul_skripsi']); ?>
</h6>

<p class="small text-muted mb-1">

<strong>Penulis :</strong>

<?= htmlspecialchars($skripsi['nama_penulis']); ?>

</p>

<p class="small text-muted mb-3">

<strong>Prodi :</strong>

<?= htmlspecialchars($skripsi['prodi']); ?>

</p>

<button
class="btn btn-success rounded-pill mt-auto"
data-bs-toggle="modal"
data-bs-target="#detail<?= $skripsi['id_skripsi']; ?>">

Lihat Detail

</button>

</div>

</div>

</div>

<!-- MODAL DETAIL -->

<div class="modal fade" id="detail<?= $skripsi['id_skripsi']; ?>">

<div class="modal-dialog modal-lg modal-dialog-centered">

<div class="modal-content rounded-4 border-0">

<div class="modal-header bg-success text-white">

<h5 class="modal-title">

Detail Skripsi

</h5>

<button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>

</div>

<div class="modal-body p-4">

<h4 class="fw-bold mb-3">
<?= htmlspecialchars($skripsi['judul_skripsi']); ?>
</h4>

<div class="row g-3">

<div class="col-md-6">

<small class="text-muted">Penulis</small>

<div><?= htmlspecialchars($skripsi['nama_penulis']); ?></div>

</div>

<div class="col-md-6">

<small class="text-muted">NIM</small>

<div><?= htmlspecialchars($skripsi['nim_mahasiswa']); ?></div>

</div>

<div class="col-md-6">

<small class="text-muted">Program Studi</small>

<div><?= htmlspecialchars($skripsi['prodi']); ?></div>

</div>

<div class="col-md-6">

<small class="text-muted">Tahun Lulus</small>

<div><?= htmlspecialchars($skripsi['tahun_lulus']); ?></div>

</div>

<div class="col-12 mt-3">

<small class="text-muted">Abstrak</small>

<p class="mt-2" style="text-align:justify;">

<?= nl2br(htmlspecialchars($skripsi['abstrak'] ?? 'Tidak ada abstrak.')); ?>

</p>

</div>

</div>

</div>

</div>

</div>

</div>

<?php endwhile; ?>

</div>

</section>

</main>

<script>

document.querySelectorAll('.counter').forEach(counter=>{

const target=+counter.dataset.target;

let count=0;

const update=()=>{

const inc=Math.ceil(target/80);

count+=inc;

if(count<target){

counter.innerText=count;

requestAnimationFrame(update);

}else{

counter.innerText=target;

}

};

update();

});

</script>

<?php include 'footer.php'; ?>