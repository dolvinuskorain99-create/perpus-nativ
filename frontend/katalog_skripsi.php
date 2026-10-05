<?php
include '../koneksi.php';

/* ===========================
   PAGINATION
=========================== */
$limit = 50;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1) $page = 1;
$start = ($page-1) * $limit;

/* ===========================
   FILTER
=========================== */
$keyword  = $_GET['cari'] ?? '';
$fakultas = $_GET['fakultas'] ?? '';
$prodi    = $_GET['prodi'] ?? '';

/* ===========================
   DROPDOWN
=========================== */
$dataFakultas = mysqli_query($koneksi," 
SELECT DISTINCT nama_fakultas
FROM program_studi
ORDER BY nama_fakultas ASC
");

$dataProdi = mysqli_query($koneksi,"
SELECT DISTINCT nama_prodi
FROM program_studi
ORDER BY nama_prodi ASC
");

/* ===========================
   WHERE
=========================== */
$where = " WHERE 1=1 ";

if($keyword!=''){
    $k = mysqli_real_escape_string($koneksi,$keyword);
    $where .= " AND (
        s.judul_skripsi LIKE '%$k%' OR
        s.nama_penulis LIKE '%$k%' OR
        s.nim_mahasiswa LIKE '%$k%'
    )";
}

if($fakultas!=''){
    $f = mysqli_real_escape_string($koneksi,$fakultas);
    $where .= " AND ps.nama_fakultas='$f'";
}

if($prodi!=''){
    $p = mysqli_real_escape_string($koneksi,$prodi);
    $where .= " AND ps.nama_prodi='$p'";
}

/* ===========================
   TOTAL
=========================== */
$totalData = mysqli_fetch_assoc(mysqli_query($koneksi,"
SELECT COUNT(*) total
FROM skripsi s
LEFT JOIN program_studi ps
ON s.prodi = ps.nama_prodi
$where
"));

$total = $totalData['total'];
$totalPage = ceil($total/$limit);

/* ===========================
   DATA
=========================== */
$query = mysqli_query($koneksi,"
SELECT
    s.*,
    ps.nama_fakultas,
    ps.nama_prodi
FROM skripsi s
LEFT JOIN program_studi ps
ON s.prodi = ps.nama_prodi
$where
ORDER BY s.tahun_lulus DESC, s.id_skripsi DESC
LIMIT $start,$limit
");

include 'header.php';
?>

<style>

body{
    background:#eef3f8;
    font-family:'Segoe UI',sans-serif;
}

/* HERO */

.hero{
    background:
    linear-gradient(rgba(20,108,67,.82),rgba(15,81,50,.88)),
    url('../assets/img/belajar3.jpg') center/cover;
    color:#fff;
    border-radius:24px;
    overflow:hidden;
}

.hero-content{
    padding:45px 20px;
}

.hero h2{
    font-weight:700;
}

/* FILTER */

.filter-box{
    background:#fff;
    border-radius:18px;
    padding:22px;
    box-shadow:0 8px 25px rgba(0,0,0,.06);
    margin-top:-25px;
    position:relative;
    z-index:10;
}

.form-control,.form-select{
    border-radius:12px;
    height:48px;
}

/* TABLE */

.table-card{
    background:#fff;
    border-radius:20px;
    overflow:hidden;
    box-shadow:0 8px 25px rgba(0,0,0,.06);
}

.table-title{
    padding:18px 22px;
    border-bottom:1px solid #eee;
    font-weight:700;
    font-size:20px;
}

.table{
    margin:0;
}

.table thead{
    background:#198754;
    color:#fff;
}

.table th{
    font-size:14px;
    white-space:nowrap;
}

.table td{
    vertical-align:middle;
}

.table tbody tr:hover{
    background:#f8fcfa;
}

.badge-prodi{
    background:#e9f8ef;
    color:#198754;
    padding:6px 12px;
    border-radius:30px;
    font-size:12px;
    font-weight:600;
}

.judul{
    font-weight:600;
    line-height:1.5;
}

.btn-detail{
    border-radius:30px;
}

/* PAGINATION */

.pagination .page-link{
    border:none;
    color:#198754;
    border-radius:10px;
    margin:0 3px;
}

.pagination .active .page-link{
    background:#198754;
    color:#fff;
}

/* MOBILE CARD */

.mobile-view{
    display:none;
}

@media(max-width:768px){

.container{
padding-left:10px;
padding-right:10px;
}

.hero-content{
padding:28px 15px;
}

.hero h2{
font-size:24px;
}

.filter-box{
margin-top:15px;
padding:16px;
}

.table-responsive{
display:none;
}

.mobile-view{
display:block;
}

.mobile-item{
background:#fff;
border-radius:16px;
padding:16px;
margin:12px;
box-shadow:0 4px 15px rgba(0,0,0,.05);
}

.mobile-item h6{
font-weight:700;
line-height:1.5;
margin:12px 0;
}

.info{
font-size:13px;
color:#64748b;
margin-bottom:6px;
}

.info b{
color:#111827;
}

.btn-detail{
width:100%;
margin-top:10px;
}

}

</style>

<div class="container my-4">

    <!-- HERO -->

    <section class="hero mb-4">
        <div class="hero-content row align-items-center">

            <div class="col-lg-8">

                <span class="badge bg-light text-success px-3 py-2 mb-3">
                    🎓 Arsip Digital USTJ
                </span>

                <h2>Katalog Skripsi & Tugas Akhir</h2>

                <p class="text-white-50 mb-0">
                    Temukan skripsi mahasiswa Universitas Sains dan Teknologi Jayapura berdasarkan judul, penulis, fakultas, maupun program studi.
                </p>

            </div>

            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">

            <h2 class="counter fw-bold text-white" data-target="4852">0</h2>
            <small class="text-white-50">Total Skripsi</small>

            </div>

        </div>
    </section>

    <!-- FILTER -->

    <div class="filter-box mb-4">

        <form method="GET">

            <div class="row g-3">

                <div class="col-lg-4">

                    <label class="form-label fw-semibold">Kata Kunci</label>

                    <input type="text"
                           name="cari"
                           class="form-control"
                           placeholder="Judul / Penulis / NIM"
                           value="<?= htmlspecialchars($keyword); ?>">

                </div>

                <div class="col-lg-3">

                    <label class="form-label fw-semibold">Fakultas</label>

                    <select name="fakultas" class="form-select">

                        <option value="">Semua Fakultas</option>

                        <?php while($f=mysqli_fetch_assoc($dataFakultas)): ?>

                        <option value="<?= $f['nama_fakultas']; ?>"
                        <?= ($fakultas==$f['nama_fakultas'])?'selected':''; ?>>

                        <?= $f['nama_fakultas']; ?>

                        </option>

                        <?php endwhile; ?>

                    </select>

                </div>

                <div class="col-lg-3">

                    <label class="form-label fw-semibold">Program Studi</label>

                    <select name="prodi" class="form-select">

                        <option value="">Semua Prodi</option>

                        <?php while($p=mysqli_fetch_assoc($dataProdi)): ?>

                        <option value="<?= $p['nama_prodi']; ?>"
                        <?= ($prodi==$p['nama_prodi'])?'selected':''; ?>>

                        <?= $p['nama_prodi']; ?>

                        </option>

                        <?php endwhile; ?>

                    </select>

                </div>

                <div class="col-lg-2 d-grid">

                    <label class="form-label">&nbsp;</label>

                    <button class="btn btn-success">
                        Cari
                    </button>

                </div>

            </div>

        </form>

    </div>

    <!-- TABLE -->

    <div class="table-card">

        <div class="table-title d-flex justify-content-between align-items-center">

            📚 Daftar Skripsi

            <?php if($keyword || $fakultas || $prodi): ?>

            <a href="katalog_skripsi.php" class="btn btn-outline-secondary btn-sm">
                Reset
            </a>

            <?php endif; ?>

        </div>

        <!-- DESKTOP -->

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>

                <tr>
                    <th>No</th>
                    <th>Judul Skripsi</th>
                    <th>Penulis</th>
                    <th>Fakultas</th>
                    <th>Prodi</th>
                    <th>Tahun</th>
                    <th>Aksi</th>
                </tr>

                </thead>

                <tbody>

                <?php
                $no=$start+1;
                while($row=mysqli_fetch_assoc($query)):
                ?>

                <tr>

                    <td><?= $no++; ?></td>

                    <td width="32%">

                        <div class="judul">
                            <?= htmlspecialchars($row['judul_skripsi']); ?>
                        </div>

                    </td>

                    <td>

                        <strong><?= $row['nama_penulis']; ?></strong><br>

                        <small class="text-muted"><?= $row['nim_mahasiswa']; ?></small>

                    </td>

                    <td><?= $row['nama_fakultas']; ?></td>

                    <td>

                        <span class="badge badge-prodi">

                            <?= $row['nama_prodi']; ?>

                        </span>

                    </td>

                    <td><?= $row['tahun_lulus']; ?></td>

                    <td>

                        <button class="btn btn-success btn-sm btn-detail"
                        data-bs-toggle="modal"
                        data-bs-target="#m<?= $row['id_skripsi']; ?>">

                        Detail

                        </button>

                    </td>

                </tr>

                <!-- MODAL -->

                <div class="modal fade" id="m<?= $row['id_skripsi']; ?>">

                    <div class="modal-dialog modal-lg modal-dialog-centered">

                        <div class="modal-content border-0 rounded-4">

                            <div class="modal-header bg-success text-white">

                                <h5 class="modal-title">Detail Skripsi</h5>

                                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>

                            </div>

                            <div class="modal-body p-4">

                                <h4 class="fw-bold mb-3">

                                    <?= htmlspecialchars($row['judul_skripsi']); ?>

                                </h4>

                                <div class="row g-3 mb-4">

                                    <div class="col-md-6">

                                        <small class="text-muted">Penulis</small>

                                        <div class="fw-semibold"><?= $row['nama_penulis']; ?></div>

                                    </div>

                                    <div class="col-md-6">

                                        <small class="text-muted">NIM</small>

                                        <div class="fw-semibold"><?= $row['nim_mahasiswa']; ?></div>

                                    </div>

                                    <div class="col-md-6">

                                        <small class="text-muted">Fakultas</small>

                                        <div class="fw-semibold"><?= $row['nama_fakultas']; ?></div>

                                    </div>

                                    <div class="col-md-6">

                                        <small class="text-muted">Program Studi</small>

                                        <div class="fw-semibold"><?= $row['nama_prodi']; ?></div>

                                    </div>

                                    <div class="col-md-6">

                                        <small class="text-muted">Tahun</small>

                                        <div class="fw-semibold"><?= $row['tahun_lulus']; ?></div>

                                    </div>

                                </div>

                                

                            </div>

                        </div>

                    </div>

                </div>

                <?php endwhile; ?>

                </tbody>
                <script>
document.addEventListener("DOMContentLoaded", () => {
    const counters = document.querySelectorAll('.counter');
    
    counters.forEach(counter => {
        const updateCount = () => {
            const target = +counter.getAttribute('data-target');
            const count = +counter.innerText;
            
            // Atur kecepatan animasi (semakin kecil pembagi, semakin cepat)
            const speed = 200; 
            const increment = target / speed;
            
            if (count < target) {
                counter.innerText = Math.ceil(count + increment);
                setTimeout(updateCount, 15);
            } else {
                counter.innerText = target; // Memastikan angka akhir pas
            }
        };
        
        updateCount();
    });
});

                </script>

            </table>

        </div>

        <!-- MOBILE -->

        <div class="mobile-view">

        <?php
        mysqli_data_seek($query,0);
        while($row=mysqli_fetch_assoc($query)):
        ?>

            <div class="mobile-item">

                <span class="badge bg-success mb-2">
                    <?= $row['tahun_lulus']; ?>
                </span>

                <h6>
                    <?= htmlspecialchars($row['judul_skripsi']); ?>
                </h6>

                <div class="info">
                    <b>Penulis :</b>
                    <?= $row['nama_penulis']; ?>
                </div>

                <div class="info">
                    <b>NIM :</b>
                    <?= $row['nim_mahasiswa']; ?>
                </div>

                <div class="info">
                    <b>Fakultas :</b>
                    <?= $row['nama_fakultas']; ?>
                </div>

                <span class="badge badge-prodi">
                    <?= $row['nama_prodi']; ?>
                </span>

                <button class="btn btn-success btn-detail"
                data-bs-toggle="modal"
                data-bs-target="#m<?= $row['id_skripsi']; ?>">

                Lihat Detail

                </button>

            </div>

        <?php endwhile; ?>

        </div>

        <!-- PAGINATION -->

        <?php if($totalPage>1): ?>

        <div class="p-3 border-top">

            <nav>

                <ul class="pagination justify-content-center">

                    <li class="page-item <?= ($page<=1)?'disabled':''; ?>">

                        <a class="page-link"
                        href="?page=<?= $page-1; ?>&cari=<?= urlencode($keyword); ?>&fakultas=<?= urlencode($fakultas); ?>&prodi=<?= urlencode($prodi); ?>">

                        «

                        </a>

                    </li>

                    <?php
                    $s=max(1,$page-2);
                    $e=min($totalPage,$page+2);

                    for($i=$s;$i<=$e;$i++):
                    ?>

                    <li class="page-item <?= ($i==$page)?'active':''; ?>">

                        <a class="page-link"
                        href="?page=<?= $i; ?>&cari=<?= urlencode($keyword); ?>&fakultas=<?= urlencode($fakultas); ?>&prodi=<?= urlencode($prodi); ?>">

                        <?= $i; ?>

                        </a>

                    </li>

                    <?php endfor; ?>

                    <li class="page-item <?= ($page>=$totalPage)?'disabled':''; ?>">

                        <a class="page-link"
                        href="?page=<?= $page+1; ?>&cari=<?= urlencode($keyword); ?>&fakultas=<?= urlencode($fakultas); ?>&prodi=<?= urlencode($prodi); ?>">

                        »

                        </a>

                    </li>

                </ul>

            </nav>

            <div class="text-center text-muted small">

                Menampilkan
                <strong><?= $start+1 ?></strong> -
                <strong><?= min($start+$limit,$total); ?></strong>
                dari
                <strong><?= $total ?></strong> skripsi

            </div>

        </div>

        <?php endif; ?>

    </div>

</div>

<?php include 'footer.php'; ?>