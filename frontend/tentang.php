<?php
include '../koneksi.php';

// ===========================
// STATISTIK DATABASE
// ===========================
$total_buku = mysqli_fetch_assoc(
    mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM buku")
)['total'];

$total_skripsi = mysqli_fetch_assoc(
    mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM skripsi")
)['total'];

$total_anggota = mysqli_fetch_assoc(
    mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM users")
)['total'];

include 'header.php';
?>

<style>
body{
    background:#f8fafc;
}

/* HERO */
.hero{
    position:relative;
    min-height:330px;
    border-radius:24px;
    overflow:hidden;
    background:url('../assets/img/belajar1.jpg') center/cover;
}

.hero::before{
    content:'';
    position:absolute;
    inset:0;
    background:linear-gradient(rgba(7,33,71,.82),rgba(13,110,253,.45));
}

.hero-content{
    position:relative;
    z-index:2;
    color:#fff;
    padding:55px 40px;
}

.glass{
    background:rgba(255,255,255,.12);
    border:1px solid rgba(255,255,255,.18);
    backdrop-filter:blur(10px);
    border-radius:16px;
    padding:18px;
    text-align:center;
}

.counter{
    font-size:28px;
    font-weight:bold;
}

/* SECTION */
.section-title{
    font-weight:700;
}

.section-sub{
    color:#64748b;
}

/* CARD */
.card-custom{
    border:none;
    border-radius:18px;
    transition:.3s;
}

.card-custom:hover{
    transform:translateY(-6px);
    box-shadow:0 15px 30px rgba(0,0,0,.12);
}

/* CAROUSEL */
.carousel-item img{
    height:360px;
    object-fit:cover;
}

.carousel-caption{
    background:rgba(0,0,0,.45);
    border-radius:14px;
    padding:14px;
    left:8%;
    right:8%;
    bottom:20px;
}

/* GALERI */
.gallery-card{
    border:none;
    border-radius:18px;
    overflow:hidden;
    transition:.3s;
}

.gallery-card img{
    height:180px;
    object-fit:cover;
}

.gallery-card:hover{
    transform:translateY(-6px);
    box-shadow:0 15px 30px rgba(0,0,0,.15);
}

.icon-box{
    width:55px;
    height:55px;
    border-radius:15px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:24px;
}
</style>

<div class="container my-4">

    <!-- ================================= HERO ================================ -->
    <section class="hero mb-5">

        <div class="hero-content">

            <span class="badge bg-warning text-dark px-3 py-2 mb-3">
                <i class="bi bi-building me-1"></i>
                Profil Perpustakaan USTJ
            </span>

            <h1 class="display-6 fw-bold">
                Perpustakaan Universitas Sains dan Teknologi Jayapura
            </h1>

            <p class="text-white-50 col-lg-8 mt-3">
                Pusat layanan literasi digital yang menyediakan koleksi buku,
                referensi ilmiah, dan arsip skripsi mahasiswa USTJ.
            </p>
            <div class="row g-3 mt-3">

<div class="col-md-4">
    <div class="glass text-center p-3">
        <i class="bi bi-book fs-3 mb-2"></i>
        <!-- Tambahkan atribut data-target di sini -->
        <div class="counter fw-bold fs-2" data-target="<?= $total_buku ?>"><?= $total_buku ?></div>
        <small>Koleksi Buku</small>
    </div>
</div>

<div class="col-md-4">
    <div class="glass text-center p-3">
        <i class="bi bi-journal-richtext fs-3 mb-2"></i>
        <!-- Tambahkan atribut data-target di sini -->
        <div class="counter fw-bold fs-2" data-target="<?= $total_skripsi ?>"><?= $total_skripsi ?></div>
        <small>Arsip Skripsi</small>
    </div>
</div>

<div class="col-md-4">
    <div class="glass text-center p-3">
        <i class="bi bi-people fs-3 mb-2"></i>
        <!-- Tambahkan atribut data-target di sini -->
        <div class="counter fw-bold fs-2" data-target="<?= $total_anggota ?>"><?= $total_anggota ?></div>
        <small>Anggota</small>
    </div>
</div>

</div>

        </div>

    </section>  

    <!-- =========================== TENTANG ========================== -->

    <div class="row align-items-center g-5 mb-5">

        <div class="col-lg-6">
            <img src="../assets/img/belajar2.jpg"
                 class="img-fluid rounded-4 shadow"
                 alt="Perpustakaan USTJ">
        </div>

        <div class="col-lg-6">

            <span class="badge bg-primary bg-opacity-10 text-primary mb-3">
                Tentang Kami
            </span>

            <h2 class="section-title mb-3">
                Pusat Informasi Akademik USTJ
            </h2>

            <p class="text-muted" style="text-align:justify;line-height:1.8;">
                Perpustakaan Universitas Sains dan Teknologi Jayapura merupakan
                pusat layanan informasi akademik yang menyediakan koleksi buku,
                jurnal, karya ilmiah, serta arsip skripsi mahasiswa dalam bentuk
                cetak maupun digital.
            </p>

            <p class="text-muted" style="text-align:justify;line-height:1.8;">
                Sistem informasi perpustakaan berbasis web ini memudahkan mahasiswa
                mencari referensi, melihat koleksi terbaru, dan memperoleh informasi
                skripsi secara cepat dan akurat.
            </p>

        </div>

    </div>

    <!-- ======================== VISI MISI ======================= -->

    <div class="row g-4 mb-5">

        <div class="col-lg-6">

            <div class="card card-custom bg-primary text-white h-100 p-4">

                <div class="icon-box bg-white bg-opacity-25 mb-3">
                    <i class="bi bi-eye-fill"></i>
                </div>

                <h3 class="fw-bold">Visi</h3>

                <p class="mb-0 mt-3" style="line-height:1.8;">
                    Menjadi perpustakaan perguruan tinggi yang unggul,
                    modern, dan terintegrasi dalam mendukung pendidikan,
                    penelitian, serta pengembangan ilmu pengetahuan di Papua.
                </p>

            </div>

        </div>

        <div class="col-lg-6">

            <div class="card card-custom h-100 p-4">

                <div class="icon-box bg-success bg-opacity-10 text-success mb-3">
                    <i class="bi bi-bullseye"></i>
                </div>

                <h3 class="fw-bold">Misi</h3>

                <ul class="text-muted mt-3" style="line-height:1.8;">
                    <li>Menyediakan koleksi literatur yang berkualitas.</li>
                    <li>Memberikan layanan informasi berbasis teknologi.</li>
                    <li>Mendukung penelitian mahasiswa dan dosen.</li>
                    <li>Menciptakan budaya literasi yang nyaman dan edukatif.</li>
                </ul>

            </div>

        </div>

    </div>

    <!-- ======================= CAROUSEL ======================== -->

    <div class="text-center mb-4">

        <h2 class="section-title">
            Galeri Perpustakaan
        </h2>

        <p class="section-sub">
            Dokumentasi ruang baca, koleksi buku, dan fasilitas belajar mahasiswa USTJ.
        </p>

    </div>

    <div id="galeriUSTJ"
         class="carousel slide rounded-4 overflow-hidden shadow mb-5"
         data-bs-ride="carousel"
         data-bs-interval="3000">

        <div class="carousel-indicators">
            <button data-bs-target="#galeriUSTJ" data-bs-slide-to="0" class="active"></button>
            <button data-bs-target="#galeriUSTJ" data-bs-slide-to="1"></button>
            <button data-bs-target="#galeriUSTJ" data-bs-slide-to="2"></button>
            <button data-bs-target="#galeriUSTJ" data-bs-slide-to="3"></button>
            <button data-bs-target="#galeriUSTJ" data-bs-slide-to="4"></button>
            <button data-bs-target="#galeriUSTJ" data-bs-slide-to="5"></button>
            <button data-bs-target="#galeriUSTJ" data-bs-slide-to="6"></button>
        </div>

        <div class="carousel-inner">

            <div class="carousel-item active">
                <img src="../assets/img/belajar1.jpg" class="d-block w-100">
                <div class="carousel-caption">
                    <h5>Ruang Baca Utama</h5>
                    <p>Area membaca yang nyaman dan tenang bagi mahasiswa USTJ.</p>
                </div>
            </div>

            <div class="carousel-item">
                <img src="../assets/img/belajar2.jpg" class="d-block w-100">
                <div class="carousel-caption">
                    <h5>Koleksi Referensi</h5>
                    <p>Berbagai buku teknik, ekonomi, kesehatan, dan sains.</p>
                </div>
            </div>

            <div class="carousel-item">
                <img src="../assets/img/belajar3.jpg" class="d-block w-100">
                <div class="carousel-caption">
                    <h5>Area Belajar</h5>
                    <p>Tempat belajar mandiri maupun diskusi kelompok mahasiswa.</p>
                </div>
            </div>

            <div class="carousel-item">
                <img src="../assets/img/r1.jpg" class="d-block w-100">
                <div class="carousel-caption">
                    <h5>Rak Buku Fakultas</h5>
                    <p>Koleksi disusun berdasarkan fakultas dan program studi.</p>
                </div>
            </div>

            <div class="carousel-item">
                <img src="../assets/img/r2.jpg" class="d-block w-100">
                <div class="carousel-caption">
                    <h5>Literatur Ilmiah</h5>
                    <p>Jurnal dan referensi ilmiah terbaru tersedia di perpustakaan.</p>
                </div>
            </div>

            <div class="carousel-item">
                <img src="../assets/img/r3.jpg" class="d-block w-100">
                <div class="carousel-caption">
                    <h5>Layanan Sirkulasi</h5>
                    <p>Peminjaman dan pengembalian buku dilakukan dengan cepat.</p>
                </div>
            </div>

            <div class="carousel-item">
                <img src="../assets/img/r4.jpg" class="d-block w-100">
                <div class="carousel-caption">
                    <h5>Arsip Skripsi Digital</h5>
                    <p>Dokumentasi skripsi mahasiswa dari berbagai program studi.</p>
                </div>
            </div>

        </div>

        <button class="carousel-control-prev"
                data-bs-target="#galeriUSTJ"
                data-bs-slide="prev">

            <span class="carousel-control-prev-icon"></span>

        </button>

        <button class="carousel-control-next"
                data-bs-target="#galeriUSTJ"
                data-bs-slide="next">

            <span class="carousel-control-next-icon"></span>

        </button>

    </div>

    <!-- ======================= FASILITAS ======================= -->

    <div class="text-center mb-4">

        <h2 class="section-title">
            Fasilitas Perpustakaan
        </h2>

        <p class="section-sub">
            Fasilitas utama yang mendukung kegiatan belajar dan penelitian mahasiswa.
        </p>

    </div>

    <div class="row g-4 mb-5">

        <div class="col-md-6 col-lg-3">

            <div class="card gallery-card h-100">

                <img src="../assets/img/r1.jpg">

                <div class="card-body">

                    <div class="icon-box bg-primary bg-opacity-10 text-primary mb-3">
                        <i class="bi bi-bookshelf"></i>
                    </div>

                    <h6 class="fw-bold">Rak Koleksi</h6>

                    <p class="text-muted small mb-0">
                        Ribuan koleksi buku disusun berdasarkan fakultas dan program studi.
                    </p>

                </div>

            </div>

        </div>

        <div class="col-md-6 col-lg-3">

            <div class="card gallery-card h-100">

                <img src="../assets/img/belajar2.jpg">

                <div class="card-body">

                    <div class="icon-box bg-success bg-opacity-10 text-success mb-3">
                        <i class="bi bi-book-half"></i>
                    </div>

                    <h6 class="fw-bold">Ruang Referensi</h6>

                    <p class="text-muted small mb-0">
                        Tempat membaca jurnal ilmiah dan buku referensi akademik.
                    </p>

                </div>

            </div>

        </div>

        <div class="col-md-6 col-lg-3">

            <div class="card gallery-card h-100">

                <img src="../assets/img/belajar3.jpg">

                <div class="card-body">

                    <div class="icon-box bg-info bg-opacity-10 text-info mb-3">
                        <i class="bi bi-people"></i>
                    </div>

                    <h6 class="fw-bold">Area Diskusi</h6>

                    <p class="text-muted small mb-0">
                        Ruang diskusi kelompok untuk tugas kuliah dan penelitian mahasiswa.
                    </p>

                </div>

            </div>

        </div>

        <div class="col-md-6 col-lg-3">

            <div class="card gallery-card h-100">

                <img src="../assets/img/r4.jpg">

                <div class="card-body">

                    <div class="icon-box bg-warning bg-opacity-10 text-warning mb-3">
                        <i class="bi bi-journal-richtext"></i>
                    </div>

                    <h6 class="fw-bold">Arsip Digital</h6>

                    <p class="text-muted small mb-0">
                        Koleksi skripsi dan tugas akhir mahasiswa Universitas USTJ.
                    </p>

                </div>

            </div>

        </div>

    </div>

    <script>

document.addEventListener("DOMContentLoaded", () => {
    const counters = document.querySelectorAll('.counter');
    
    counters.forEach(counter => {
        // Ambil target angka asli dari atribut data-target
        const target = +counter.getAttribute('data-target');
        
        // Set awal angka menjadi 0 saat halaman pertama kali dimuat
        counter.innerText = '0';
        
        const updateCount = () => {
            const count = +counter.innerText;
            
            // Kecepatan animasi (semakin kecil angka pembagi, semakin cepat hitungannya)
            const speed = 150; 
            const increment = target / speed;
            
            if (count < target) {
                counter.innerText = Math.ceil(count + increment);
                setTimeout(updateCount, 20);
            } else {
                counter.innerText = target; // Mengunci angka agar pas pada nilai akhir database
            }
        };
        
        updateCount();
    });
});
    </script>

</div>

<?php include 'footer.php'; ?>