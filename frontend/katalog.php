<?php

include '../koneksi.php';
include 'header.php';

/* =========================================================
   PAGINATION
========================================================= */

$per_page = 50;

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

if ($page < 1) {
    $page = 1;
}

$offset = ($page - 1) * $per_page;


/* =========================================================
   FILTER
========================================================= */

$cari = isset($_GET['cari'])
    ? trim($_GET['cari'])
    : '';

$fakultas = isset($_GET['fakultas'])
    ? trim($_GET['fakultas'])
    : '';

$prodi = isset($_GET['prodi'])
    ? trim($_GET['prodi'])
    : '';


/* =========================================================
   WHERE
========================================================= */

$where = [];


/* PENCARIAN */

if ($cari !== '') {

    $cari_safe = mysqli_real_escape_string(
        $koneksi,
        $cari
    );

    $where[] = "
        (
            b.judul_buku LIKE '%$cari_safe%'
            OR b.pengarang LIKE '%$cari_safe%'
            OR b.penerbit LIKE '%$cari_safe%'
            OR b.isbn LIKE '%$cari_safe%'
        )
    ";
}


/* FILTER FAKULTAS */

if ($fakultas !== '') {

    $fakultas_safe = mysqli_real_escape_string(
        $koneksi,
        $fakultas
    );

    $where[] = "
        p.nama_fakultas = '$fakultas_safe'
    ";
}


/* FILTER PRODI */

if ($prodi !== '') {

    $prodi_safe = mysqli_real_escape_string(
        $koneksi,
        $prodi
    );

    $where[] = "
        p.nama_prodi = '$prodi_safe'
    ";
}


/* =========================================================
   WHERE SQL
========================================================= */

$where_sql = '';

if (!empty($where)) {
    $where_sql = 'WHERE ' . implode(' AND ', $where);
}


/* =========================================================
   TOTAL BUKU
========================================================= */

$query_count = "
    SELECT COUNT(*) AS total
    FROM buku b
    LEFT JOIN program_studi p
        ON b.id_prodi = p.id_prodi
    $where_sql
";

$hasil_count = mysqli_query(
    $koneksi,
    $query_count
);

if (!$hasil_count) {

    die(
        'Query jumlah buku error: '
        . mysqli_error($koneksi)
    );
}

$data_count = mysqli_fetch_assoc(
    $hasil_count
);

$total_buku = (int) $data_count['total'];

$total_page = ($total_buku > 0)
    ? ceil($total_buku / $per_page)
    : 1;


/* =========================================================
   DATA BUKU
========================================================= */

$query_buku = "
    SELECT
        b.id_buku,
        b.id_prodi,
        b.judul_buku,
        b.pengarang,
        b.penerbit,
        b.tempat_terbit,
        b.isbn,
        b.edisi,
        b.tahun_terbit,
        b.jumlah_halaman,
        b.jumlah_eksemplar,
        b.no_klasifikasi,
        b.bibliografi,
        p.nama_fakultas,
        p.nama_prodi

    FROM buku b

    LEFT JOIN program_studi p
        ON b.id_prodi = p.id_prodi

    $where_sql

    ORDER BY b.id_buku DESC

    LIMIT $offset, $per_page
";

$hasil_buku = mysqli_query(
    $koneksi,
    $query_buku
);

if (!$hasil_buku) {

    die(
        'Query buku error: '
        . mysqli_error($koneksi)
    );
}


/* =========================================================
   DATA FAKULTAS
========================================================= */

$query_fakultas = "
    SELECT DISTINCT nama_fakultas

    FROM program_studi

    WHERE nama_fakultas IS NOT NULL
    AND nama_fakultas != ''

    ORDER BY nama_fakultas ASC
";

$hasil_fakultas = mysqli_query(
    $koneksi,
    $query_fakultas
);


/* =========================================================
   DATA PRODI
========================================================= */

$query_prodi = "
    SELECT DISTINCT nama_prodi

    FROM program_studi

    WHERE nama_prodi IS NOT NULL
    AND nama_prodi != ''

    ORDER BY nama_prodi ASC
";

$hasil_prodi = mysqli_query(
    $koneksi,
    $query_prodi
);


/* =========================================================
   URL PAGINATION
========================================================= */

function urlKatalogBuku($page)
{
    $params = $_GET;

    $params['page'] = $page;

    return '?' . http_build_query($params);
}

?>

<style>

/* =========================================================
   GLOBAL
========================================================= */

body {
    background: #eef5f9;
}


/* =========================================================
   HERO
========================================================= */

.katalog-hero {

    margin: 25px auto 0;

    width: calc(100% - 120px);

    min-height: 255px;

    border-radius: 28px;

    position: relative;

    overflow: hidden;

    display: flex;

    align-items: center;

    color: white;

    background-image:
        url('../assets/img/R7.jpg');

    background-size: cover;

    background-position: center;
}


/* OVERLAY HIJAU */

.katalog-hero::before {

    content: "";

    position: absolute;

    inset: 0;

    background:
        linear-gradient(
            90deg,
            rgba(12, 106, 69, 0.94),
            rgba(12, 106, 69, 0.82),
            rgba(12, 106, 69, 0.68)
        );

    z-index: 1;
}


/* KONTEN HERO */

.katalog-hero-content {

    position: relative;

    z-index: 2;

    padding: 42px 40px;

    width: 70%;
}


.hero-label {

    display: inline-flex;

    align-items: center;

    gap: 7px;

    background: white;

    color: #198754;

    border-radius: 8px;

    padding: 8px 16px;

    font-size: 13px;

    font-weight: 700;

    margin-bottom: 22px;
}


.katalog-hero h1 {

    font-size: 38px;

    font-weight: 800;

    margin-bottom: 10px;
}


.katalog-hero p {

    max-width: 850px;

    color: rgba(255,255,255,0.86);

    font-size: 16px;

    line-height: 1.7;

    margin: 0;
}


/* =========================================================
   TOTAL BUKU
========================================================= */

.hero-total {

    position: absolute;

    z-index: 2;

    right: 45px;

    top: 50%;

    transform: translateY(-50%);

    text-align: center;

    min-width: 150px;
}


.hero-total-number {

    font-size: 58px;

    font-weight: 800;

    line-height: 1;

    text-shadow:
        0 3px 15px rgba(0,0,0,0.15);
}


.hero-total-text {

    margin-top: 12px;

    font-size: 16px;

    font-weight: 500;

    color: rgba(255,255,255,0.95);
}


/* =========================================================
   SEARCH / FILTER
========================================================= */

.filter-box {

    background: white;

    width: calc(100% - 120px);

    margin: 0 auto;

    padding: 25px;

    border-radius: 0 0 22px 22px;

    box-shadow: 0 8px 25px rgba(0,0,0,0.05);
}


.filter-box label {

    display: block;

    font-weight: 600;

    font-size: 14px;

    margin-bottom: 9px;

    color: #172033;
}


.filter-box .form-control,
.filter-box .form-select {

    height: 53px;

    border-radius: 13px;

    border: 1px solid #dce3ea;

    font-size: 16px;
}


.filter-box .form-control:focus,
.filter-box .form-select:focus {

    border-color: #198754;

    box-shadow:
        0 0 0 3px rgba(25,135,84,0.10);
}


.btn-cari {

    height: 53px;

    background: #198754;

    border: none;

    color: white;

    border-radius: 9px;

    font-weight: 600;

    font-size: 16px;
}


.btn-cari:hover {

    background: #157347;

    color: white;
}


/* =========================================================
   DAFTAR BUKU
========================================================= */

.daftar-buku {

    width: calc(100% - 120px);

    margin: 28px auto 60px;

    background: white;

    border-radius: 24px;

    overflow: hidden;

    box-shadow:
        0 7px 25px rgba(0,0,0,0.04);
}


.daftar-header {

    padding: 25px 28px;

    border-bottom: 1px solid #e5e7eb;
}


.daftar-header h3 {

    margin: 0;

    font-size: 23px;

    font-weight: 750;

    color: #172033;
}


.daftar-header i {

    color: #198754;
}


/* =========================================================
   TABEL
========================================================= */

.tabel-buku {

    width: 100%;

    border-collapse: collapse;
}


.tabel-buku thead th {

    padding: 15px 10px;

    font-size: 14px;

    font-weight: 700;

    color: #172033;

    border-bottom: 1px solid #dee2e6;

    white-space: nowrap;
}


.tabel-buku tbody td {

    padding: 16px 10px;

    border-bottom: 1px solid #e1e5e9;

    vertical-align: middle;

    font-size: 15px;
}


.tabel-buku tbody tr:hover {

    background: #f8fbfa;
}


/* =========================================================
   NOMOR
========================================================= */

.col-no {

    width: 50px;

    text-align: center;
}


/* =========================================================
   JUDUL
========================================================= */

.judul-buku {

    min-width: 300px;

    max-width: 450px;

    color: #172033;

    font-weight: 650;

    line-height: 1.55;
}


/* =========================================================
   PENGARANG
========================================================= */

.pengarang {

    min-width: 190px;
}


.pengarang strong {

    display: block;

    color: #172033;

    font-weight: 700;
}


.penerbit {

    color: #64748b;

    font-size: 13px;

    margin-top: 4px;
}


/* =========================================================
   FAKULTAS
========================================================= */

.fakultas {

    min-width: 210px;

    line-height: 1.5;
}


/* =========================================================
   PRODI BADGE
========================================================= */

.prodi-badge {

    display: inline-block;

    background: #e8f7ef;

    color: #198754;

    border-radius: 30px;

    padding: 6px 12px;

    font-size: 12px;

    font-weight: 600;

    white-space: nowrap;
}


/* =========================================================
   TAHUN
========================================================= */

.tahun {

    text-align: center;

    min-width: 70px;

    white-space: nowrap;
}


/* =========================================================
   DETAIL
========================================================= */

.aksi {

    text-align: center;

    min-width: 90px;
}


.btn-detail {

    display: inline-block;

    background: #198754;

    color: white;

    text-decoration: none;

    border: none;

    border-radius: 20px;

    padding: 8px 16px;

    font-size: 14px;

    font-weight: 600;

    transition: 0.2s;
}


.btn-detail:hover {

    background: #157347;

    color: white;

    transform: translateY(-1px);
}


/* =========================================================
   MODAL DETAIL
========================================================= */

.modal-detail-buku .modal-dialog {

    max-width: 850px;
}


.modal-detail-buku .modal-content {

    border: none;

    border-radius: 18px;

    overflow: hidden;

    box-shadow:
        0 20px 60px rgba(0,0,0,0.20);
}


.modal-detail-buku .modal-header {

    background: #198754;

    color: white;

    padding: 20px 25px;

    border: none;
}


.modal-detail-buku .modal-title {

    font-size: 23px;

    font-weight: 700;
}


.modal-detail-buku .btn-close {

    filter: brightness(0) invert(1);

    opacity: .9;
}


.modal-detail-buku .modal-body {

    padding: 30px;
}


/* JUDUL MODAL */

.detail-judul {

    font-size: 25px;

    font-weight: 800;

    color: #172033;

    line-height: 1.4;

    margin-bottom: 30px;
}


/* ITEM DETAIL */

.detail-item {

    margin-bottom: 23px;
}


.detail-label {

    color: #64748b;

    font-size: 14px;

    margin-bottom: 5px;
}


.detail-value {

    color: #172033;

    font-size: 17px;

    font-weight: 600;

    line-height: 1.5;
}


.detail-value.normal {

    font-weight: 400;
}


/* BOX INFO */

.detail-info {

    background: #f8faf9;

    border-radius: 13px;

    padding: 18px;

    height: 100%;
}


.detail-info-icon {

    color: #198754;

    margin-right: 6px;
}


/* =========================================================
   PAGINATION
========================================================= */

.pagination-area {

    padding: 25px;

    text-align: center;
}


.pagination {

    justify-content: center;

    margin-bottom: 15px;
}


.page-link {

    border: none;

    color: #198754;

    width: 40px;

    height: 40px;

    display: flex;

    align-items: center;

    justify-content: center;

    margin: 0 3px;

    border-radius: 10px !important;
}


.page-item.active .page-link {

    background: #198754;

    color: white;
}


.page-link:hover {

    background: #e8f7ef;

    color: #157347;
}


.page-item.disabled .page-link {

    background: #f1f3f5;

    color: #adb5bd;
}


/* =========================================================
   EMPTY
========================================================= */

.empty-data {

    padding: 70px 20px;

    text-align: center;
}


.empty-data i {

    font-size: 50px;

    color: #adb5bd;

    margin-bottom: 15px;
}


.empty-data h4 {

    font-weight: 700;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 992px) {

    .katalog-hero,
    .filter-box,
    .daftar-buku {

        width: calc(100% - 30px);
    }


    .hero-total {

        display: none;
    }


    .katalog-hero-content {

        width: 100%;
    }
}


@media (max-width: 768px) {

    .katalog-hero {

        min-height: auto;
    }


    .katalog-hero-content {

        padding: 35px 25px;
    }


    .katalog-hero h1 {

        font-size: 28px;
    }


    .filter-box {

        border-radius: 0 0 20px 20px;
    }


    .daftar-buku {

        overflow-x: auto;
    }


    .tabel-buku {

        min-width: 1100px;
    }


    .modal-detail-buku .modal-body {

        padding: 22px;
    }


    .detail-judul {

        font-size: 21px;
    }
}

</style>


<!-- =====================================================
     HERO
===================================================== -->

<section class="katalog-hero">

    <div class="katalog-hero-content">

        <div class="hero-label">

            <i class="bi bi-book-fill"></i>

            Koleksi Buku USTJ

        </div>


        <h1>
            Katalog Buku
        </h1>


        <p>

            Temukan koleksi buku Perpustakaan Universitas
            Sains dan Teknologi Jayapura berdasarkan judul,
            pengarang, fakultas, maupun program studi.

        </p>

    </div>


    <!-- TOTAL BUKU -->

    <div class="hero-total">

        <div
            class="hero-total-number"
            id="totalBukuCounter"
            data-target="<?= $total_buku; ?>"
        >
            0
        </div>

        <div class="hero-total-text">

            Total Buku

        </div>

    </div>

</section>


<!-- =====================================================
     FILTER
===================================================== -->

<section class="filter-box">

    <form method="GET">

        <div class="row g-3 align-items-end">


            <!-- KATA KUNCI -->

            <div class="col-lg-4">

                <label>
                    Kata Kunci
                </label>

                <input
                    type="text"
                    name="cari"
                    class="form-control"
                    placeholder="Judul / Pengarang / ISBN"
                    value="<?= htmlspecialchars($cari); ?>"
                >

            </div>


            <!-- FAKULTAS -->

            <div class="col-lg-3">

                <label>
                    Fakultas
                </label>

                <select
                    name="fakultas"
                    class="form-select"
                >

                    <option value="">
                        Semua Fakultas
                    </option>


                    <?php while ($f = mysqli_fetch_assoc($hasil_fakultas)): ?>

                        <option
                            value="<?= htmlspecialchars($f['nama_fakultas']); ?>"
                            <?= ($fakultas === $f['nama_fakultas'])
                                ? 'selected'
                                : ''; ?>
                        >

                            <?= htmlspecialchars($f['nama_fakultas']); ?>

                        </option>

                    <?php endwhile; ?>

                </select>

            </div>


            <!-- PRODI -->

            <div class="col-lg-3">

                <label>
                    Program Studi
                </label>

                <select
                    name="prodi"
                    class="form-select"
                >

                    <option value="">
                        Semua Prodi
                    </option>


                    <?php while ($p = mysqli_fetch_assoc($hasil_prodi)): ?>

                        <option
                            value="<?= htmlspecialchars($p['nama_prodi']); ?>"
                            <?= ($prodi === $p['nama_prodi'])
                                ? 'selected'
                                : ''; ?>
                        >

                            <?= htmlspecialchars($p['nama_prodi']); ?>

                        </option>

                    <?php endwhile; ?>

                </select>

            </div>


            <!-- TOMBOL -->

            <div class="col-lg-2">

                <button
                    type="submit"
                    class="btn btn-cari w-100"
                >

                    <i class="bi bi-search me-1"></i>

                    Cari

                </button>

            </div>

        </div>

    </form>

</section>


<!-- =====================================================
     DAFTAR BUKU
===================================================== -->

<section class="daftar-buku">


    <div class="daftar-header">

        <h3>

            <i class="bi bi-bookshelf me-2"></i>

            Daftar Buku

        </h3>

    </div>


    <?php if (mysqli_num_rows($hasil_buku) > 0): ?>


        <div class="table-responsive">

            <table class="tabel-buku">

                <thead>

                    <tr>

                        <th class="col-no">
                            No
                        </th>

                        <th>
                            Judul Buku
                        </th>

                        <th>
                            Pengarang
                        </th>

                        <th>
                            Fakultas
                        </th>

                        <th>
                            Prodi
                        </th>

                        <th class="tahun">
                            Tahun
                        </th>

                        <th class="aksi">
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody>


                    <?php

                    $nomor = $offset + 1;

                    while ($buku = mysqli_fetch_assoc($hasil_buku)):

                        $judul =
                            $buku['judul_buku'] ?? '-';

                        $pengarang =
                            $buku['pengarang'] ?? '-';

                        $penerbit =
                            $buku['penerbit'] ?? '-';

                        $fakultas_data =
                            $buku['nama_fakultas'] ?? '-';

                        $prodi_data =
                            $buku['nama_prodi'] ?? '-';

                        $tahun =
                            $buku['tahun_terbit'] ?? '-';

                        $tempat_terbit =
                            $buku['tempat_terbit'] ?? '-';

                        $isbn =
                            $buku['isbn'] ?? '-';

                        $edisi =
                            $buku['edisi'] ?? '-';

                        $jumlah_halaman =
                            $buku['jumlah_halaman'] ?? '-';

                        $jumlah_eksemplar =
                            $buku['jumlah_eksemplar'] ?? '-';

                        $no_klasifikasi =
                            $buku['no_klasifikasi'] ?? '-';

                        $bibliografi =
                            $buku['bibliografi'] ?? '-';

                    ?>


                        <tr>


                            <!-- NOMOR -->

                            <td class="col-no">

                                <?= $nomor; ?>

                            </td>


                            <!-- JUDUL -->

                            <td class="judul-buku">

                                <?= htmlspecialchars($judul); ?>

                            </td>


                            <!-- PENGARANG -->

                            <td class="pengarang">

                                <strong>

                                    <?= htmlspecialchars($pengarang); ?>

                                </strong>


                                <?php if (
                                    $penerbit !== ''
                                    && $penerbit !== '-'
                                ): ?>

                                    <div class="penerbit">

                                        <?= htmlspecialchars($penerbit); ?>

                                    </div>

                                <?php endif; ?>

                            </td>


                            <!-- FAKULTAS -->

                            <td class="fakultas">

                                <?= htmlspecialchars(
                                    $fakultas_data
                                ); ?>

                            </td>


                            <!-- PRODI -->

                            <td>

                                <?php if ($prodi_data !== '-'): ?>

                                    <span class="prodi-badge">

                                        <?= htmlspecialchars(
                                            $prodi_data
                                        ); ?>

                                    </span>

                                <?php else: ?>

                                    -

                                <?php endif; ?>

                            </td>


                            <!-- TAHUN -->

                            <td class="tahun">

                                <?= htmlspecialchars(
                                    $tahun
                                ); ?>

                            </td>


                            <!-- DETAIL -->

                            <td class="aksi">

                                <button
                                    type="button"
                                    class="btn-detail"
                                    data-bs-toggle="modal"
                                    data-bs-target="#detailBuku<?= (int)$buku['id_buku']; ?>"
                                >

                                    <i class="bi bi-eye me-1"></i>

                                    Detail

                                </button>

                            </td>


                        </tr>


                        <!-- =================================================
                             MODAL DETAIL BUKU
                        ================================================== -->

                        <div
                            class="modal fade modal-detail-buku"
                            id="detailBuku<?= (int)$buku['id_buku']; ?>"
                            tabindex="-1"
                            aria-hidden="true"
                        >

                            <div class="modal-dialog modal-dialog-centered">

                                <div class="modal-content">


                                    <!-- HEADER -->

                                    <div class="modal-header">

                                        <h5 class="modal-title">

                                            <i class="bi bi-book me-2"></i>

                                            Detail Buku

                                        </h5>


                                        <button
                                            type="button"
                                            class="btn-close"
                                            data-bs-dismiss="modal"
                                            aria-label="Close"
                                        ></button>

                                    </div>


                                    <!-- BODY -->

                                    <div class="modal-body">


                                        <!-- JUDUL -->

                                        <div class="detail-judul">

                                            <?= htmlspecialchars($judul); ?>

                                        </div>


                                        <div class="row">


                                            <!-- PENGARANG -->

                                            <div class="col-md-6">

                                                <div class="detail-item">

                                                    <div class="detail-label">

                                                        Pengarang

                                                    </div>

                                                    <div class="detail-value">

                                                        <?= htmlspecialchars(
                                                            $pengarang
                                                        ); ?>

                                                    </div>

                                                </div>

                                            </div>


                                            <!-- PENERBIT -->

                                            <div class="col-md-6">

                                                <div class="detail-item">

                                                    <div class="detail-label">

                                                        Penerbit

                                                    </div>

                                                    <div class="detail-value">

                                                        <?= htmlspecialchars(
                                                            $penerbit
                                                        ); ?>

                                                    </div>

                                                </div>

                                            </div>


                                            <!-- FAKULTAS -->

                                            <div class="col-md-6">

                                                <div class="detail-item">

                                                    <div class="detail-label">

                                                        Fakultas

                                                    </div>

                                                    <div class="detail-value">

                                                        <?= htmlspecialchars(
                                                            $fakultas_data
                                                        ); ?>

                                                    </div>

                                                </div>

                                            </div>


                                            <!-- PRODI -->

                                            <div class="col-md-6">

                                                <div class="detail-item">

                                                    <div class="detail-label">

                                                        Program Studi

                                                    </div>

                                                    <div class="detail-value">

                                                        <?= htmlspecialchars(
                                                            $prodi_data
                                                        ); ?>

                                                    </div>

                                                </div>

                                            </div>


                                            <!-- TAHUN -->

                                            <div class="col-md-4">

                                                <div class="detail-info">

                                                    <div class="detail-label">

                                                        <i class="bi bi-calendar3 detail-info-icon"></i>

                                                        Tahun Terbit

                                                    </div>

                                                    <div class="detail-value">

                                                        <?= htmlspecialchars(
                                                            $tahun
                                                        ); ?>

                                                    </div>

                                                </div>

                                            </div>


                                            <!-- ISBN -->

                                            <div class="col-md-4">

                                                <div class="detail-info">

                                                    <div class="detail-label">

                                                        <i class="bi bi-upc-scan detail-info-icon"></i>

                                                        ISBN

                                                    </div>

                                                    <div class="detail-value">

                                                        <?= htmlspecialchars(
                                                            $isbn
                                                        ); ?>

                                                    </div>

                                                </div>

                                            </div>


                                            <!-- EDISI -->

                                            <div class="col-md-4">

                                                <div class="detail-info">

                                                    <div class="detail-label">

                                                        <i class="bi bi-layers detail-info-icon"></i>

                                                        Edisi

                                                    </div>

                                                    <div class="detail-value">

                                                        <?= htmlspecialchars(
                                                            $edisi
                                                        ); ?>

                                                    </div>

                                                </div>

                                            </div>


                                            <!-- TEMPAT TERBIT -->

                                            <div class="col-md-6 mt-3">

                                                <div class="detail-item">

                                                    <div class="detail-label">

                                                        Tempat Terbit

                                                    </div>

                                                    <div class="detail-value">

                                                        <?= htmlspecialchars(
                                                            $tempat_terbit
                                                        ); ?>

                                                    </div>

                                                </div>

                                            </div>


                                            <!-- HALAMAN -->

                                            <div class="col-md-6 mt-3">

                                                <div class="detail-item">

                                                    <div class="detail-label">

                                                        Jumlah Halaman

                                                    </div>

                                                    <div class="detail-value">

                                                        <?= htmlspecialchars(
                                                            $jumlah_halaman
                                                        ); ?>

                                                    </div>

                                                </div>

                                            </div>


                                            <!-- EKSEMPLAR -->

                                            <div class="col-md-6">

                                                <div class="detail-item">

                                                    <div class="detail-label">

                                                        Jumlah Eksemplar

                                                    </div>

                                                    <div class="detail-value">

                                                        <?= htmlspecialchars(
                                                            $jumlah_eksemplar
                                                        ); ?>

                                                    </div>

                                                </div>

                                            </div>


                                            <!-- KLASIFIKASI -->

                                            <div class="col-md-6">

                                                <div class="detail-item">

                                                    <div class="detail-label">

                                                        Nomor Klasifikasi

                                                    </div>

                                                    <div class="detail-value">

                                                        <?= htmlspecialchars(
                                                            $no_klasifikasi
                                                        ); ?>

                                                    </div>

                                                </div>

                                            </div>


                                            <!-- BIBLIOGRAFI -->

                                            <?php if (
                                                $bibliografi !== ''
                                                && $bibliografi !== '-'
                                            ): ?>

                                                <div class="col-12">

                                                    <div class="detail-item">

                                                        <div class="detail-label">

                                                            Bibliografi

                                                        </div>

                                                        <div class="detail-value normal">

                                                            <?= nl2br(
                                                                htmlspecialchars(
                                                                    $bibliografi
                                                                )
                                                            ); ?>

                                                        </div>

                                                    </div>

                                                </div>

                                            <?php endif; ?>


                                        </div>

                                    </div>


                                </div>

                            </div>

                        </div>


                    <?php

                        $nomor++;

                    endwhile;

                    ?>


                </tbody>

            </table>

        </div>


        <!-- =====================================================
             PAGINATION
        ===================================================== -->

        <?php if ($total_page > 1): ?>


            <div class="pagination-area">

                <nav>

                    <ul class="pagination">


                        <!-- PREVIOUS -->

                        <?php if ($page > 1): ?>

                            <li class="page-item">

                                <a
                                    class="page-link"
                                    href="<?= urlKatalogBuku($page - 1); ?>"
                                >

                                    «

                                </a>

                            </li>

                        <?php else: ?>

                            <li class="page-item disabled">

                                <span class="page-link">

                                    «

                                </span>

                            </li>

                        <?php endif; ?>


                        <?php

                        $start_page = max(
                            1,
                            $page - 2
                        );

                        $end_page = min(
                            $total_page,
                            $page + 2
                        );

                        ?>


                        <?php for (
                            $i = $start_page;
                            $i <= $end_page;
                            $i++
                        ): ?>


                            <li
                                class="page-item <?= ($i == $page)
                                    ? 'active'
                                    : ''; ?>"
                            >

                                <a
                                    class="page-link"
                                    href="<?= urlKatalogBuku($i); ?>"
                                >

                                    <?= $i; ?>

                                </a>

                            </li>


                        <?php endfor; ?>


                        <!-- NEXT -->

                        <?php if ($page < $total_page): ?>

                            <li class="page-item">

                                <a
                                    class="page-link"
                                    href="<?= urlKatalogBuku($page + 1); ?>"
                                >

                                    »

                                </a>

                            </li>

                        <?php else: ?>

                            <li class="page-item disabled">

                                <span class="page-link">

                                    »

                                </span>

                            </li>

                        <?php endif; ?>


                    </ul>

                </nav>


                <div class="text-muted">

                    Menampilkan

                    <strong>

                        <?= $total_buku > 0
                            ? $offset + 1
                            : 0; ?>

                    </strong>

                    -

                    <strong>

                        <?= min(
                            $offset + $per_page,
                            $total_buku
                        ); ?>

                    </strong>

                    dari

                    <strong>

                        <?= $total_buku; ?>

                    </strong>

                    buku

                </div>

            </div>


        <?php endif; ?>


    <?php else: ?>


        <!-- =====================================================
             DATA KOSONG
        ===================================================== -->

        <div class="empty-data">

            <i class="bi bi-book"></i>

            <h4>

                Buku Tidak Ditemukan

            </h4>

            <p class="text-muted">

                Tidak ada buku yang sesuai dengan
                pencarian atau filter Anda.

            </p>

            <a
                href="katalog.php"
                class="btn btn-success"
            >

                Tampilkan Semua Buku

            </a>

        </div>


    <?php endif; ?>


</section>


<!-- =====================================================
     ANIMASI JUMLAH BUKU
===================================================== -->

<script>

document.addEventListener("DOMContentLoaded", function () {

    const counter =
        document.getElementById("totalBukuCounter");

    if (!counter) {
        return;
    }

    const target =
        parseInt(counter.getAttribute("data-target")) || 0;

    let current = 0;

    const duration = 1200;

    const startTime = performance.now();


    function animateCounter(currentTime) {

        const elapsed =
            currentTime - startTime;

        const progress =
            Math.min(elapsed / duration, 1);


        /*
         * Efek ease-out
         */

        const ease =
            1 - Math.pow(1 - progress, 3);


        current =
            Math.floor(ease * target);


        counter.textContent =
            current.toLocaleString('id-ID');


        if (progress < 1) {

            requestAnimationFrame(
                animateCounter
            );

        } else {

            counter.textContent =
                target.toLocaleString('id-ID');

        }

    }


    requestAnimationFrame(
        animateCounter
    );

});

</script>


<?php

include 'footer.php';

?>