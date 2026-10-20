<?php
include '../koneksi.php';

/* =========================
   PENCARIAN & FILTER
========================= */
$keyword  = '';
$fakultas = '';
$prodi    = '';
$urutkan  = '';

// Ambil daftar fakultas dari database untuk dropdown
$dataFakultas = mysqli_query($koneksi, "
    SELECT DISTINCT nama_fakultas 
    FROM program_studi 
    WHERE nama_fakultas IS NOT NULL AND nama_fakultas != '' 
    ORDER BY nama_fakultas ASC
");

if(isset($_GET['cari']) || isset($_GET['fakultas']) || isset($_GET['prodi']) || isset($_GET['urutkan'])){
    $keyword  = isset($_GET['cari']) ? mysqli_real_escape_string($koneksi, trim($_GET['cari'])) : '';
    $fakultas = isset($_GET['fakultas']) ? mysqli_real_escape_string($koneksi, trim($_GET['fakultas'])) : '';
    $prodi    = isset($_GET['prodi']) ? mysqli_real_escape_string($koneksi, trim($_GET['prodi'])) : '';
    $urutkan  = isset($_GET['urutkan']) ? mysqli_real_escape_string($koneksi, trim($_GET['urutkan'])) : '';

    // Pengurutan Buku
    $order_buku = "ORDER BY id_buku DESC";
    if($urutkan == 'terlama') {
        $order_buku = "ORDER BY tahun_terbit ASC";
    } elseif($urutkan == 'terbaru') {
        $order_buku = "ORDER BY tahun_terbit DESC";
    }

    // Query Buku
    $query_buku = "SELECT * FROM buku WHERE 1=1";
    if($keyword != ''){
        $query_buku .= " AND (judul_buku LIKE '%$keyword%' OR pengarang LIKE '%$keyword%' OR penerbit LIKE '%$keyword%')";
    }
    $query_buku .= " $order_buku LIMIT 8";
    $hasil_buku = mysqli_query($koneksi, $query_buku);

    // Pengurutan Skripsi
    $order_skripsi = "ORDER BY s.tahun_lulus DESC, s.id_skripsi DESC";
    if($urutkan == 'terlama') {
        $order_skripsi = "ORDER BY s.tahun_lulus ASC";
    } elseif($urutkan == 'terbaru') {
        $order_skripsi = "ORDER BY s.tahun_lulus DESC";
    }

    // Query Skripsi (Join dengan tabel program_studi untuk filter fakultas & prodi)
    $query_skripsi = "
        SELECT s.*, ps.nama_fakultas, ps.nama_prodi 
        FROM skripsi s 
        LEFT JOIN program_studi ps ON s.prodi = ps.nama_prodi 
        WHERE 1=1
    ";
    if($keyword != ''){
        $query_skripsi .= " AND (s.judul_skripsi LIKE '%$keyword%' OR s.nama_penulis LIKE '%$keyword%' OR s.nim_mahasiswa LIKE '%$keyword%')";
    }
    if($fakultas != ''){
        $query_skripsi .= " AND ps.nama_fakultas = '$fakultas'";
    }
    if($prodi != ''){
        $query_skripsi .= " AND ps.nama_prodi = '$prodi'";
    }
    $query_skripsi .= " $order_skripsi LIMIT 6";
    $hasil_skripsi = mysqli_query($koneksi, $query_skripsi);

}else{
    // Default jika tidak sedang memfilter
    $hasil_buku = mysqli_query($koneksi,"
        SELECT * FROM buku
        ORDER BY id_buku DESC
        LIMIT 8
    ");

    $hasil_skripsi = mysqli_query($koneksi,"
        SELECT s.*, ps.nama_fakultas, ps.nama_prodi 
        FROM skripsi s 
        LEFT JOIN program_studi ps ON s.prodi = ps.nama_prodi 
        ORDER BY s.tahun_lulus DESC, s.id_skripsi DESC
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
    margin-top:40px;
    background:rgba(13,110,253,.20);
    backdrop-filter:blur(18px);
    border:1px solid rgba(255,255,255,.18);
    border-radius:28px;
    padding:30px;
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

/* Ikon Buku Polos/Standar tanpa latar biru mencolok */
.book-icon{
    height:95px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#f8fbff;
    color: #495057;
}


/* untuk atribut icon sosial media */
/* Styling untuk Floating Social Media Container */
.floating-contact {
    position: fixed;
    right: 20px;
    bottom: 25px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 12px; /* Jarak antar tombol */
}

/* Styling untuk Tombolnya */
.floating-btn {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    text-decoration: none;
    font-size: 22px;
}

/* Efek saat kursor mendekat atau disentuh */
.floating-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.4);
    color: #ffffff;
}

/* Warna khas masing-masing platform */
.btn-whatsapp { background-color: #25d366; }
.btn-instagram { background: radial-gradient(circle at 30% 107%, #fdf497 0%, #fdf497 5%, #fd5949 45%, #d6249f 60%, #285AEB 90%); }
.btn-tiktok { background-color: #000000; }
.btn-youtube { background-color: #ff0000; }
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
Portal digital untuk mencari buku, skripsi, dan tugas akhir mahasiswa USTJ secara cepat, mudah, dan terintegrasi berdasarkan fakultas dan program studi.
</p>

</div>

<!-- CARD PENCARIAN TERPADU -->

<div class="glass-card">

<form action="index.php" method="GET">

<div class="row g-3">
    <!-- Input Kata Kunci -->
    <div class="col-lg-12">
        <div class="input-group input-group-lg search-box">
            <span class="input-group-text bg-transparent border-0 ps-3">
                <i class="bi bi-search text-muted"></i>
            </span>
            <input
            type="text"
            name="cari"
            class="form-control"
            placeholder="Cari judul buku, pengarang, penerbit, judul skripsi, atau NIM..."
            value="<?= htmlspecialchars($keyword); ?>">
        </div>
    </div>

    <!-- Filter Fakultas -->
    <div class="col-md-4">
        <select name="fakultas" id="selectFakultas" class="form-select form-select-lg rounded-pill shadow-none border-0 py-2 px-3 fs-6">
            <option value="">-- Semua Fakultas --</option>
            <?php while($f = mysqli_fetch_assoc($dataFakultas)): ?>
                <option value="<?= htmlspecialchars($f['nama_fakultas']); ?>" <?= ($fakultas == $f['nama_fakultas']) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($f['nama_fakultas']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <!-- Filter Program Studi (Dinamis) -->
    <div class="col-md-4">
        <select name="prodi" id="selectProdi" class="form-select form-select-lg rounded-pill shadow-none border-0 py-2 px-3 fs-6">
            <option value="">-- Semua Program Studi --</option>
        </select>
    </div>

    <!-- Filter Tahun Terbaru/Terlama -->
    <div class="col-md-4">
        <select name="urutkan" class="form-select form-select-lg rounded-pill shadow-none border-0 py-2 px-3 fs-6">
            <option value="">-- Urutkan Koleksi --</option>
            <option value="terbaru" <?= ($urutkan == 'terbaru') ? 'selected' : ''; ?>>Terbaru (Tahun Rilis/Lulus)</option>
            <option value="terlama" <?= ($urutkan == 'terlama') ? 'selected' : ''; ?>>Terlama (Tahun Rilis/Lulus)</option>
        </select>
    </div>

    <!-- Tombol Aksi -->
    <div class="col-12 text-center mt-3">
        <button type="submit" class="btn btn-warning btn-lg rounded-pill px-5 fw-bold shadow">
            <i class="bi bi-search me-1"></i> Cari Koleksi
        </button>
        <?php if($keyword != '' || $fakultas != '' || $prodi != '' || $urutkan != ''): ?>
            <a href="index.php" class="btn btn-light btn-lg rounded-pill px-4 ms-2 text-danger fw-semibold">
                Reset Filter
            </a>
        <?php endif; ?>
    </div>
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
                Koleksi Buku
            </h3>
            <small class="text-muted">
                <?= mysqli_num_rows($hasil_buku) ?> koleksi buku ditampilkan
            </small>
        </div>
        <a href="katalog.php" class="btn btn-outline-primary btn-all">
            Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="row g-4">
        <?php if(mysqli_num_rows($hasil_buku) > 0): ?>
            <?php while($buku = mysqli_fetch_assoc($hasil_buku)): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="card card-book h-100 shadow-sm">
                        <!-- ICON BUKU STANDAR (POLOS TANPA WARNA BIRU MENCOLOK) -->
                        <div class="book-icon">
                            <i class="bi bi-book-fill display-5"></i>
                        </div>

                        <div class="card-body d-flex flex-column">
                            <span class="badge bg-secondary-subtle text-dark mb-2 w-fit">
                                Buku (<?= htmlspecialchars($buku['tahun_terbit'] ?? '-'); ?>)
                            </span>

                            <h6 class="fw-bold" style="min-height:48px;">
                                <?= htmlspecialchars($buku['judul_buku'] ?? '-'); ?>
                            </h6>

                            <p class="small text-muted mb-1">
                                <i class="bi bi-person me-1"></i>
                                <?= htmlspecialchars($buku['pengarang'] ?? '-'); ?>
                            </p>

                            <p class="small text-muted mb-2">
                                <i class="bi bi-building me-1"></i>
                                <?= htmlspecialchars($buku['penerbit'] ?? '-'); ?>
                            </p>

                            <div class="mb-3">
                                <span class="badge bg-success-subtle text-success">
                                    <i class="bi bi-box-seam me-1"></i>
                                    <?= htmlspecialchars($buku['jumlah_eksemplar'] ?? '0'); ?> Stok Buku
                                </span>
                            </div>

                            <button
                                type="button"
                                class="btn btn-primary rounded-pill mt-auto"
                                data-bs-toggle="modal"
                                data-bs-target="#detailBuku<?= $buku['id_buku']; ?>">
                                <i class="bi bi-eye me-1"></i> Lihat Detail
                            </button>
                        </div>
                    </div>
                </div>

                <!-- MODAL DETAIL BUKU -->
                <div class="modal fade" id="detailBuku<?= $buku['id_buku']; ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content rounded-4 border-0">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">
                                    <i class="bi bi-book me-2"></i> Detail Buku
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4">
                                <h4 class="fw-bold mb-4">
                                    <?= htmlspecialchars($buku['judul_buku'] ?? '-'); ?>
                                </h4>
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <small class="text-muted">Kode Buku</small>
                                        <div class="fw-semibold"><?= htmlspecialchars($buku['kode_buku'] ?? '-'); ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Penulis</small>
                                        <div class="fw-semibold"><?= htmlspecialchars($buku['pengarang'] ?? '-'); ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Penerbit</small>
                                        <div class="fw-semibold"><?= htmlspecialchars($buku['penerbit'] ?? '-'); ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Tahun Terbit</small>
                                        <div class="fw-semibold"><?= htmlspecialchars($buku['tahun_terbit'] ?? '-'); ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Jumlah Halaman</small>
                                        <div class="fw-semibold"><?= htmlspecialchars($buku['jumlah_halaman'] ?? '-'); ?> Halaman</div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Jumlah Stok</small>
                                        <div class="fw-bold text-primary">
                                            <i class="bi bi-box-seam me-1"></i> <?= htmlspecialchars($buku['jumlah_eksemplar'] ?? '0'); ?> Stok
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-4">
                <p class="text-muted">Tidak ada koleksi buku yang cocok dengan kriteria.</p>
            </div>
        <?php endif; ?>
    </div>

</section>

<!-- ================= SKRIPSI ================= -->

<section class="content-card">

<div class="section-header">
    <div>
        <h3 class="fw-bold mb-1">
            <i class="bi bi-mortarboard text-success me-2"></i>
            Arsip Skripsi
        </h3>
        <small class="text-muted">
            <?= mysqli_num_rows($hasil_skripsi) ?> arsip skripsi ditampilkan
        </small>
    </div>
    <a href="katalog_skripsi.php" class="btn btn-outline-success btn-all">
        Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
    </a>
</div>

<div class="row g-4">
<?php if(mysqli_num_rows($hasil_skripsi) > 0): ?>
    <?php while($skripsi = mysqli_fetch_assoc($hasil_skripsi)): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card card-book h-100 shadow-sm">
            <!-- ICON STANDAR POLOS -->
            <div class="book-icon">
                <i class="bi bi-file-earmark-richtext-fill display-5"></i>
            </div>
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-success-subtle text-success">Skripsi</span>
                    <small class="text-muted"><?= htmlspecialchars($skripsi['tahun_lulus']); ?></small>
                </div>

                <h6 class="fw-bold" style="min-height:52px;">
                    <?= htmlspecialchars($skripsi['judul_skripsi']); ?>
                </h6>

                <p class="small text-muted mb-1">
                    <strong>Penulis :</strong> <?= htmlspecialchars($skripsi['nama_penulis']); ?>
                </p>

                <p class="small text-muted mb-3">
                    <strong>Prodi :</strong> <?= htmlspecialchars($skripsi['nama_prodi'] ?? $skripsi['prodi']); ?>
                </p>

                <button
                class="btn btn-success rounded-pill mt-auto"
                data-bs-toggle="modal"
                data-bs-target="#detailSkripsi<?= $skripsi['id_skripsi']; ?>">
                Lihat Detail
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL DETAIL SKRIPSI -->
    <div class="modal fade" id="detailSkripsi<?= $skripsi['id_skripsi']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 border-0">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Detail Skripsi</h5>
                    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <h4 class="fw-bold mb-3"><?= htmlspecialchars($skripsi['judul_skripsi']); ?></h4>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="text-muted">Penulis</small>
                            <div class="fw-semibold"><?= htmlspecialchars($skripsi['nama_penulis']); ?></div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">NIM</small>
                            <div class="fw-semibold"><?= htmlspecialchars($skripsi['nim_mahasiswa']); ?></div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Fakultas</small>
                            <div class="fw-semibold"><?= htmlspecialchars($skripsi['nama_fakultas'] ?? '-'); ?></div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Program Studi</small>
                            <div class="fw-semibold"><?= htmlspecialchars($skripsi['nama_prodi'] ?? $skripsi['prodi']); ?></div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Tahun Lulus</small>
                            <div class="fw-semibold"><?= htmlspecialchars($skripsi['tahun_lulus']); ?></div>
                        </div>
                        <div class="col-12 mt-3">
                            <small class="text-muted">Abstrak</small>
                            <p class="mt-2" style="text-align:justify; line-height:1.6;">
                                <?= nl2br(htmlspecialchars($skripsi['abstrak'] ?? 'Tidak ada abstrak.')); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
<?php else: ?>
    <div class="col-12 text-center py-4">
        <p class="text-muted">Tidak ada arsip skripsi yang cocok dengan kriteria.</p>
    </div>
<?php endif; ?>
</div>

</section>

</main>

<!-- Tombol Floating Kontak / Sosmed di Samping -->
<div class="floating-contact">
    <!-- WhatsApp -->
    <a href="https://wa.me/6281234567890?text=Halo,%20saya%20ingin%20bertanya" target="_blank" rel="noopener noreferrer" class="floating-btn btn-whatsapp" aria-label="WhatsApp">
        <i class="bi bi-whatsapp"></i>
    </a>

    <!-- Instagram -->
    <a href="https://instagram.com/username_instagram_anda" target="_blank" rel="noopener noreferrer" class="floating-btn btn-instagram" aria-label="Instagram">
        <i class="bi bi-instagram"></i>
    </a>

    <!-- TikTok (Opsional) -->
    <a href="https://tiktok.com/@username_tiktok_anda" target="_blank" rel="noopener noreferrer" class="floating-btn btn-tiktok" aria-label="TikTok">
        <i class="bi bi-tiktok"></i>
    </a>

    <!-- YouTube (Opsional) -->
    <a href="https://youtube.com/@channel_anda" target="_blank" rel="noopener noreferrer" class="floating-btn btn-youtube" aria-label="YouTube">
        <i class="bi bi-youtube"></i>
    </a>
</div>

<script>
// Mapping relasi Fakultas dan Program Studi dari database untuk dropdown dinamis
const mappingProdi = {
    <?php
    $resultMap = mysqli_query($koneksi, "SELECT DISTINCT nama_fakultas, nama_prodi FROM program_studi WHERE nama_fakultas IS NOT NULL AND nama_prodi IS NOT NULL");
    $tempMap = [];
    while($rowMap = mysqli_fetch_assoc($resultMap)){
        $tempMap[$rowMap['nama_fakultas']][] = $rowMap['nama_prodi'];
    }
    foreach($tempMap as $fak => $prodis){
        echo '"' . addslashes($fak) . '": ["' . implode('", "', array_map('addslashes', $prodis)) . '"],' . "\n";
    }
    ?>
};

const selectFakultas = document.getElementById('selectFakultas');
const selectProdi = document.getElementById('selectProdi');
const currentProdi = "<?= htmlspecialchars($prodi, ENT_QUOTES); ?>";

function updateProdiOptions(selectedFakultas, selectedProdi = "") {
    selectProdi.innerHTML = '<option value="">-- Semua Program Studi --</option>';
    if (mappingProdi[selectedFakultas]) {
        mappingProdi[selectedFakultas].forEach(prodiNama => {
            let option = document.createElement('option');
            option.value = prodiNama;
            option.textContent = prodiNama;
            if (prodiNama === selectedProdi) {
                option.selected = true;
            }
            selectProdi.appendChild(option);
        });
    }
}

selectFakultas.addEventListener('change', function() {
    updateProdiOptions(this.value);
});

if (selectFakultas.value) {
    updateProdiOptions(selectFakultas.value, currentProdi);
}

// Animasi Angka Statistik Counter
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