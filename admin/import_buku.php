<?php
include '../koneksi.php';

// Ambil daftar fakultas
$daftar_fakultas = [];

$query_fakultas = mysqli_query(
    $koneksi,
    "
    SELECT DISTINCT nama_fakultas
    FROM program_studi
    WHERE nama_fakultas IS NOT NULL
      AND nama_fakultas != ''
    ORDER BY nama_fakultas ASC
    "
);

if ($query_fakultas) {
    while ($row = mysqli_fetch_assoc($query_fakultas)) {
        $daftar_fakultas[] = $row['nama_fakultas'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Import Buku - Perpustakaan USTJ</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >

    <style>

        body {
            background-color: #f5f7fb;
        }

        .content-wrapper {
            padding: 30px;
        }

        .card {
            border: none;
            border-radius: 15px;
        }

        .card-header {
            border-radius: 15px 15px 0 0 !important;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            padding: 11px 14px;
        }

        .btn {
            border-radius: 8px;
        }

        .icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #e8f5e9;
            color: #198754;
            font-size: 22px;
        }

        .step-box {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 10px;
        }

        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #0d6efd;
            color: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 8px;
            font-weight: bold;
        }

        code {
            color: #d63384;
        }

    </style>

</head>

<body>

<div class="container-fluid">

    <div class="row">

        <!-- SIDEBAR -->
        <?php
        if (file_exists('sidebar.php')) {
            include 'sidebar.php';
        }
        ?>

        <!-- CONTENT -->
        <main class="col-md-9 ms-sm-auto col-lg-10">

            <div class="content-wrapper">

                <!-- HEADER -->
                <div class="d-flex justify-content-between align-items-center mb-4">

                    <div>

                        <h3 class="fw-bold mb-1">

                            <i class="bi bi-file-earmark-excel-fill text-success me-2"></i>

                            Import Buku

                        </h3>

                        <p class="text-muted mb-0">

                            Import data buku dari file Excel berdasarkan fakultas.

                        </p>

                    </div>

                    <a
                        href="buku.php"
                        class="btn btn-secondary"
                    >

                        <i class="bi bi-arrow-left me-1"></i>

                        Kembali

                    </a>

                </div>


                <div class="row g-4">

                    <!-- FORM IMPORT -->
                    <div class="col-lg-8">

                        <div class="card shadow-sm">

                            <div class="card-header bg-white py-3">

                                <div class="d-flex align-items-center">

                                    <div class="icon-box me-3">

                                        <i class="bi bi-upload"></i>

                                    </div>

                                    <div>

                                        <h5 class="fw-bold mb-0">

                                            Upload File Excel

                                        </h5>

                                        <small class="text-muted">

                                            Format file yang diperbolehkan: .xlsx

                                        </small>

                                    </div>

                                </div>

                            </div>


                            <div class="card-body p-4">

                                <form
                                    action="proses_import_buku.php"
                                    method="POST"
                                    enctype="multipart/form-data"
                                >

                                    <!-- FAKULTAS -->
                                    <div class="mb-4">

                                        <label
                                            for="fakultas"
                                            class="form-label fw-semibold"
                                        >

                                            <i class="bi bi-building me-1"></i>

                                            Pilih Fakultas

                                        </label>

                                        <select
                                            name="fakultas"
                                            id="fakultas"
                                            class="form-select"
                                            required
                                        >

                                            <option value="">

                                                -- Pilih Fakultas --

                                            </option>

                                            <?php foreach (
                                                $daftar_fakultas
                                                as $fakultas
                                            ): ?>

                                                <option
                                                    value="<?= htmlspecialchars($fakultas) ?>"
                                                >

                                                    <?= htmlspecialchars($fakultas) ?>

                                                </option>

                                            <?php endforeach; ?>

                                        </select>

                                        <div class="form-text">

                                            Pilih fakultas yang sesuai dengan
                                            isi file Excel.

                                        </div>

                                    </div>


                                    <!-- FILE -->
                                    <div class="mb-4">

                                        <label
                                            for="file_excel"
                                            class="form-label fw-semibold"
                                        >

                                            <i class="bi bi-file-earmark-excel me-1"></i>

                                            File Excel

                                        </label>

                                        <input
                                            type="file"
                                            name="file_excel"
                                            id="file_excel"
                                            class="form-control"
                                            accept=".xlsx"
                                            required
                                        >

                                        <div class="form-text">

                                            Contoh:
                                            <code>FTIK(5).xlsx</code>

                                        </div>

                                    </div>


                                    <!-- INFORMASI -->
                                    <div class="alert alert-info">

                                        <h6 class="fw-bold">

                                            <i class="bi bi-info-circle-fill me-2"></i>

                                            Sistem Import

                                        </h6>

                                        <p class="small mb-2">

                                            Anda cukup memilih fakultas.
                                            Program studi akan ditentukan
                                            otomatis berdasarkan nama sheet
                                            pada Excel.

                                        </p>

                                        <ul class="small mb-0">

                                            <li>
                                                Sheet <b>ELEKTRO</b> →
                                                Teknik Elektro
                                            </li>

                                            <li>
                                                Sheet <b>MESIN</b> →
                                                Teknik Mesin
                                            </li>

                                            <li>
                                                Sheet <b>PERTAMBANGAN</b> →
                                                Teknik Pertambangan
                                            </li>

                                            <li>
                                                Sheet <b>GEOLOGI</b> →
                                                Teknik Geologi
                                            </li>

                                        </ul>

                                    </div>


                                    <!-- BUTTON -->
                                    <div class="d-flex gap-2">

                                        <button
                                            type="submit"
                                            name="submit"
                                            class="btn btn-success px-4"
                                        >

                                            <i class="bi bi-upload me-2"></i>

                                            Import Buku

                                        </button>

                                        <a
                                            href="buku.php"
                                            class="btn btn-light border px-4"
                                        >

                                            Batal

                                        </a>

                                    </div>

                                </form>

                            </div>

                        </div>

                    </div>


                    <!-- PETUNJUK -->
                    <div class="col-lg-4">

                        <div class="card shadow-sm">

                            <div class="card-header bg-white">

                                <h6 class="fw-bold mb-0">

                                    <i
                                        class="bi bi-lightbulb-fill text-warning me-2"
                                    ></i>

                                    Cara Import

                                </h6>

                            </div>


                            <div class="card-body">

                                <div class="step-box">

                                    <span class="step-number">1</span>

                                    Pilih fakultas.

                                </div>

                                <div class="step-box">

                                    <span class="step-number">2</span>

                                    Pilih file Excel.

                                </div>

                                <div class="step-box">

                                    <span class="step-number">3</span>

                                    Klik <b>Import Buku</b>.

                                </div>

                                <div class="step-box">

                                    <span class="step-number">4</span>

                                    Sistem membaca setiap sheet.

                                </div>

                                <div class="step-box">

                                    <span class="step-number">5</span>

                                    Sheet dicocokkan dengan program studi.

                                </div>

                                <div class="step-box mb-0">

                                    <span class="step-number">6</span>

                                    Data buku masuk ke database.

                                </div>


                                <hr>

                                <div class="alert alert-warning small mb-0">

                                    <i
                                        class="bi bi-exclamation-triangle me-1"
                                    ></i>

                                    Buku yang sudah ada dengan judul dan
                                    program studi yang sama akan dilewati
                                    agar tidak terjadi duplikat.

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </main>

    </div>

</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

</body>

</html>