<?php
// =====================================================
// SISTEM INFORMASI PERPUSTAKAAN USTJ
// HALAMAN: IMPORT DATA DARI EXCEL (MULTI-SHEET)
// FILE: admin/import_excel.php
// =====================================================

include '../koneksi.php';

ini_set('memory_limit', '512M');
set_time_limit(300);
mysqli_report(MYSQLI_REPORT_OFF);

$pesan = '';

function normalisasi($text) {
    $text = trim((string)$text);
    $text = str_replace(["\xC2\xA0", "\r", "\n", "\t"], ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    return strtoupper(trim($text));
}

function normalisasiSheet($text) {
    $text = normalisasi($text);
    $hapus = ['PROGRAM STUDI', 'PRODI', 'JURUSAN', 'BUKU INDUK', 'KOLEKSI'];
    foreach ($hapus as $item) {
        $text = str_replace($item, '', $text);
    }
    return trim($text);
}

function cariProdiDariSheet($koneksi, $nama_sheet, $fakultas_dipilih) {
    $sheet_normal = normalisasiSheet($nama_sheet);
    $fakultas_db = mysqli_real_escape_string($koneksi, $fakultas_dipilih);

    $query = "
        SELECT id_prodi, nama_fakultas, nama_prodi
        FROM program_studi
        WHERE nama_fakultas = '$fakultas_db'
        ORDER BY id_prodi ASC
    ";
    $result = mysqli_query($koneksi, $query);
    if (!$result) return null;

    while ($row = mysqli_fetch_assoc($result)) {
        $nama_prodi_normal = normalisasi($row['nama_prodi']);
        $nama_tanpa_jenjang = trim(preg_replace('/\s*\((S1|S2|S3|D3|D4)\)\s*/i', '', $nama_prodi_normal));

        if ($sheet_normal === $nama_prodi_normal || $sheet_normal === $nama_tanpa_jenjang) {
            return $row;
        }
        if ($sheet_normal !== '' && (strpos($nama_prodi_normal, $sheet_normal) !== false || strpos($sheet_normal, $nama_tanpa_jenjang) !== false)) {
            return $row;
        }
        $kata = preg_split('/\s+/', $nama_tanpa_jenjang);
        $singkatan = '';
        foreach ($kata as $k) {
            $k = trim($k);
            if ($k !== '') $singkatan .= substr($k, 0, 1);
        }
        if ($sheet_normal !== '' && $sheet_normal === $singkatan) {
            return $row;
        }
    }
    return null;
}

function ambilNilaiCell($c, $shared_strings) {
    $v = isset($c->v) ? (string)$c->v : '';
    $type = isset($c['t']) ? (string)$c['t'] : '';
    if ($type === 's') {
        $index = intval($v);
        return trim($shared_strings[$index] ?? '');
    } elseif ($type === 'inlineStr') {
        return trim(isset($c->is->t) ? (string)$c->is->t : '');
    }
    return trim($v);
}

function bacaSharedStrings($zip) {
    $shared_strings = [];
    if ($zip->locateName('xl/sharedStrings.xml') === false) return $shared_strings;
    $xml = simplexml_load_string($zip->getFromName('xl/sharedStrings.xml'));
    if (!$xml) return $shared_strings;

    foreach ($xml->si as $si) {
        $text = '';
        if (isset($si->t)) {
            $text = (string)$si->t;
        } else {
            foreach ($si->r as $r) {
                if (isset($r->t)) $text .= (string)$r->t;
            }
        }
        $shared_strings[] = $text;
    }
    return $shared_strings;
}

function bacaSheet($zip, $sheet_file, $shared_strings) {
    $rows = [];
    if ($zip->locateName($sheet_file) === false) return $rows;
    $xml = simplexml_load_string($zip->getFromName($sheet_file));
    if (!$xml) return $rows;

    foreach ($xml->sheetData->row as $row) {
        $data = [];
        foreach ($row->c as $c) {
            $cellRef = (string)$c['r'];
            preg_match('/([A-Z]+)/', $cellRef, $matches);
            $column = $matches[1] ?? '';
            $data[$column] = ambilNilaiCell($c, $shared_strings);
        }
        $rows[] = $data;
    }
    return $rows;
}

function excelColumnIndex($column) {
    $column = strtoupper($column);
    $index = 0;
    for ($i = 0; $i < strlen($column); $i++) {
        $index = $index * 26 + (ord($column[$i]) - ord('A') + 1);
    }
    return $index - 1;
}

function normalisasiRow($row) {
    $result = [];
    foreach ($row as $column => $value) {
        $index = excelColumnIndex($column);
        $result[$index] = $value;
    }
    if (!empty($result)) {
        $max = max(array_keys($result));
        for ($i = 0; $i <= $max; $i++) {
            if (!isset($result[$i])) $result[$i] = '';
        }
        ksort($result);
    }
    return $result;
}

function cariHeader($rows) {
    foreach ($rows as $index => $row) {
        $row_normal = [];
        foreach ($row as $key => $value) {
            $row_normal[$key] = normalisasi($value);
        }
        $hasil = [];
        foreach ($row_normal as $key => $value) {
            if (strpos($value, 'NIM') !== false) $hasil['nim'] = $key;
            if (strpos($value, 'NAMA PENULIS') !== false || strpos($value, 'NAMA MAHASISWA') !== false || $value === 'NAMA' || strpos($value, 'PENULIS') !== false || strpos($value, 'PENGARANG') !== false) $hasil['nama_penulis'] = $key;
            if (strpos($value, 'JUDUL SKRIPSI') !== false || strpos($value, 'JUDUL BUKU') !== false || strpos($value, 'JUDUL UTAMA') !== false || $value === 'JUDUL' || strpos($value, 'TITLE') !== false) $hasil['judul_skripsi'] = $key;
            if (strpos($value, 'TAHUN LULUS') !== false || strpos($value, 'TAHUN TERBIT') !== false || strpos($value, 'TAHUN') !== false) $hasil['tahun_lulus'] = $key;
        }
        if (isset($hasil['judul_skripsi']) || isset($hasil['nama_penulis'])) {
            return ['header_index' => $index, 'columns' => $hasil];
        }
    }
    return null;
}

$daftar_fakultas = [];
$q_fakultas = mysqli_query($koneksi, "
    SELECT DISTINCT nama_fakultas FROM program_studi
    WHERE nama_fakultas IS NOT NULL AND nama_fakultas != ''
    ORDER BY nama_fakultas ASC
");
if ($q_fakultas) {
    while ($row = mysqli_fetch_assoc($q_fakultas)) {
        $daftar_fakultas[] = $row['nama_fakultas'];
    }
}

$sample_sql_error = '';

if (isset($_POST['submit'])) {
    $fakultas_dipilih = trim($_POST['fakultas'] ?? '');

    if ($fakultas_dipilih === '') {
        $pesan = "<div class='alert alert-warning border-0 shadow-sm'><i class='bi bi-exclamation-triangle me-2'></i> Silakan pilih fakultas terlebih dahulu.</div>";
    } elseif (!isset($_FILES['file_excel']) || $_FILES['file_excel']['error'] !== UPLOAD_ERR_OK) {
        $pesan = "<div class='alert alert-danger border-0 shadow-sm'><i class='bi bi-x-circle me-2'></i> Berkas Excel gagal diunggah.</div>";
    } else {
        $file_tmp = $_FILES['file_excel']['tmp_name'];
        $nama_file = $_FILES['file_excel']['name'];
        $extension = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));

        if ($extension !== 'xlsx') {
            $pesan = "<div class='alert alert-danger border-0 shadow-sm'><i class='bi bi-file-earmark-x me-2'></i> Berkas harus berformat <b>.xlsx</b>.</div>";
        } else {
            $zip = new ZipArchive();
            if ($zip->open($file_tmp) === TRUE) {
                $shared_strings = bacaSharedStrings($zip);
                $workbook_xml = simplexml_load_string($zip->getFromName('xl/workbook.xml'));
                $rels_xml = simplexml_load_string($zip->getFromName('xl/_rels/workbook.xml.rels'));

                $relationships = [];
                if ($rels_xml) {
                    foreach ($rels_xml->Relationship as $rel) {
                        $id = (string)$rel['Id'];
                        $target = str_replace('\\', '/', (string)$rel['Target']);
                        if (strpos($target, 'worksheets/') === 0) $target = 'xl/' . $target;
                        $relationships[$id] = $target;
                    }
                }

                $total_masuk = 0;
                $total_gagal = 0;
                $total_dilewati = 0;
                $error_list = [];

                if ($workbook_xml) {
                    foreach ($workbook_xml->sheets->sheet as $sheet) {
                        $nama_sheet = trim((string)$sheet['name']);
                        $rId = '';
                        $r_attr = $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships');
                        if ($r_attr !== null && isset($r_attr->id)) $rId = (string)$r_attr->id;
                        if ($rId === '') {
                            $r_attr = $sheet->attributes('r');
                            if ($r_attr !== null && isset($r_attr->id)) $rId = (string)$r_attr->id;
                        }

                        $sheet_file = $relationships[$rId] ?? '';
                        if ($sheet_file === '') {
                            $total_dilewati++;
                            continue;
                        }

                        $data_prodi = cariProdiDariSheet($koneksi, $nama_sheet, $fakultas_dipilih);
                        if (!$data_prodi) {
                            $total_dilewati++;
                            continue;
                        }

                        $id_prodi = intval($data_prodi['id_prodi']);
                        $nama_fakultas = $data_prodi['nama_fakultas'];
                        $nama_prodi = $data_prodi['nama_prodi'];

                        $rows = bacaSheet($zip, $sheet_file, $shared_strings);
                        if (empty($rows)) {
                            $total_dilewati++;
                            continue;
                        }

                        $rows_normal = [];
                        foreach ($rows as $row) {
                            $rows_normal[] = normalisasiRow($row);
                        }

                        $header = cariHeader($rows_normal);
                        if (!$header) {
                            $total_dilewati++;
                            continue;
                        }

                        $header_index = $header['header_index'];
                        $columns = $header['columns'];

                        for ($i = $header_index + 1; $i < count($rows_normal); $i++) {
                            $row = $rows_normal[$i];
                            $ambil = function ($nama_kolom) use ($row, $columns) {
                                if (!isset($columns[$nama_kolom])) return '';
                                $idx = $columns[$nama_kolom];
                                return trim($row[$idx] ?? '');
                            };

                            $nim = $ambil('nim');
                            $nama_penulis = $ambil('nama_penulis');
                            $judul_skripsi = $ambil('judul_skripsi');
                            $tahun_lulus = $ambil('tahun_lulus');

                            if ($judul_skripsi === '' || strtoupper(trim($judul_skripsi)) === 'TOTAL' || is_numeric($judul_skripsi)) continue;

                            $nim = trim($nim);
                            if ($nim === '') $nim = '-';

                            // Handle tahun_lulus agar tidak null / error
                            if ($tahun_lulus !== '') {
                                $tahun_lulus = preg_replace('/[^0-9]/', '', $tahun_lulus);
                                if (strlen($tahun_lulus) !== 4) $tahun_lulus = '2024';
                            } else {
                                $tahun_lulus = '2024'; // Tahun default pengganti jika kosong
                            }

                            $nim_db = mysqli_real_escape_string($koneksi, $nim);
                            $nama_penulis_db = mysqli_real_escape_string($koneksi, $nama_penulis);
                            $judul_db = mysqli_real_escape_string($koneksi, $judul_skripsi);
                            $fakultas_db = mysqli_real_escape_string($koneksi, $nama_fakultas);
                            $prodi_db = mysqli_real_escape_string($koneksi, $nama_prodi);

                            $cek_duplikat = mysqli_query($koneksi, "
                                SELECT id_skripsi FROM skripsi 
                                WHERE id_prodi = $id_prodi AND judul_skripsi = '$judul_db'
                                LIMIT 1
                            ");

                            if ($cek_duplikat && mysqli_num_rows($cek_duplikat) > 0) {
                                $total_dilewati++;
                                continue;
                            }

                            $query_insert = "
                                INSERT INTO skripsi (id_prodi, fakultas, prodi, nim_mahasiswa, nama_penulis, judul_skripsi, tahun_lulus)
                                VALUES ($id_prodi, '$fakultas_db', '$prodi_db', '$nim_db', '$nama_penulis_db', '$judul_db', $tahun_lulus)
                            ";

                            if (mysqli_query($koneksi, $query_insert)) {
                                $total_masuk++;
                            } else {
                                $total_gagal++;
                                if (count($error_list) < 3) {
                                    $error_list[] = mysqli_error($koneksi);
                                }
                            }
                        }
                    }
                }
                $zip->close();

                if (!empty($error_list)) {
                    $sample_sql_error = "<div class='alert alert-danger mt-3 small'><b>Contoh Error Database MySQL:</b><br>" . implode("<br>", array_unique($error_list)) . "</div>";
                }

                $pesan = "
                <div class='card border-0 shadow-sm mb-4 overflow-hidden'>
                    <div class='card-header bg-success text-white py-3'>
                        <h6 class='fw-bold mb-0'><i class='bi bi-check-circle-fill me-2'></i> Proses Import Selesai</h6>
                    </div>
                    <div class='card-body p-4'>
                        <div class='row text-center g-3'>
                            <div class='col-md-3'>
                                <div class='p-3 bg-success-subtle rounded-3 border border-success-subtle'>
                                    <div class='fs-3 fw-bold text-success'>$total_masuk</div>
                                    <small class='text-muted fw-semibold'>Berhasil Disimpan</small>
                                </div>
                            </div>
                            <div class='col-md-3'>
                                <div class='p-3 bg-danger-subtle rounded-3 border border-danger-subtle'>
                                    <div class='fs-3 fw-bold text-danger'>$total_gagal</div>
                                    <small class='text-muted fw-semibold'>Gagal</small>
                                </div>
                            </div>
                            <div class='col-md-3'>
                                <div class='p-3 bg-warning-subtle rounded-3 border border-warning-subtle'>
                                    <div class='fs-3 fw-bold text-warning'>$total_dilewati</div>
                                    <small class='text-muted fw-semibold'>Dilewati / Duplikat</small>
                                </div>
                            </div>
                            <div class='col-md-3'>
                                <div class='p-3 bg-primary-subtle rounded-3 border border-primary-subtle'>
                                    <div class='fs-6 fw-bold text-primary text-truncate'>" . htmlspecialchars($fakultas_dipilih) . "</div>
                                    <small class='text-muted fw-semibold'>Fakultas</small>
                                </div>
                            </div>
                        </div>
                        $sample_sql_error
                    </div>
                </div>
                ";
            } else {
                $pesan = "<div class='alert alert-danger border-0 shadow-sm'><i class='bi bi-x-circle me-2'></i> Berkas Excel tidak dapat dibuka.</div>";
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
    <title>Import Data Skripsi - Perpustakaan USTJ</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; color: #1e293b; }
        #admin-wrapper { width: 100%; min-height: 100vh; }
        #main-content-wrapper { margin-left: 250px; width: calc(100% - 250px); min-height: 100vh; display: flex; flex-direction: column; }
        @media (max-width: 767.98px) { #main-content-wrapper { margin-left: 0; width: 100%; } }
        .top-navbar { background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 14px 28px; }
        .content-body { padding: 24px 28px; }
        .card { border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .dropzone-box { border: 2px dashed #cbd5e1; border-radius: 12px; padding: 30px; text-align: center; background-color: #f8fafc; cursor: pointer; }
        .dropzone-box:hover { border-color: #10b981; background-color: #f0fdf4; }
    </style>
</head>
<body>
<div id="admin-wrapper">
    <?php include 'sidebar.php'; ?>
    <div id="main-content-wrapper">
        <nav class="top-navbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-light d-md-none" id="sidebarToggle"><i class="bi bi-list fs-5"></i></button>
                <div class="text-muted small">
                    <span class="text-dark fw-semibold">PerpusAdmin USTJ</span> / <a href="skripsi.php" class="text-decoration-none text-muted">Skripsi</a> / <span class="text-success fw-medium">Import Excel</span>
                </div>
            </div>
            <div><a href="skripsi.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a></div>
        </nav>
        <div class="content-body">
            <div class="mb-4">
                <h1 class="h3 fw-bold text-dark mb-1"><i class="bi bi-file-earmark-excel-fill text-success"></i> Import Data Koleksi / Skripsi</h1>
                <p class="text-muted small mb-0">Import massal data koleksi pustaka atau skripsi mahasiswa USTJ berdasarkan lembar kerja sheet prodi.</p>
            </div>
            <?= $pesan ?>
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card">
                        <div class="card-header bg-white py-3 border-bottom"><h6 class="fw-bold mb-0 text-dark"><i class="bi bi-cloud-arrow-up-fill text-primary"></i> Form Upload File Excel</h6></div>
                        <div class="card-body p-4">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-4">
                                    <label class="form-label fw-semibold text-dark">Pilih Fakultas <span class="text-danger">*</span></label>
                                    <select name="fakultas" class="form-select" required>
                                        <option value="">-- Pilih Fakultas --</option>
                                        <?php foreach ($daftar_fakultas as $fakultas): ?>
                                            <option value="<?= htmlspecialchars($fakultas); ?>" <?= (isset($_POST['fakultas']) && $_POST['fakultas'] === $fakultas) ? 'selected' : ''; ?>><?= htmlspecialchars($fakultas); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-semibold text-dark">File Excel (.xlsx) <span class="text-danger">*</span></label>
                                    <div class="dropzone-box" onclick="document.getElementById('file_excel').click()">
                                        <i class="bi bi-file-earmark-spreadsheet-fill text-success display-5 d-block mb-2"></i>
                                        <span class="fw-semibold text-dark d-block mb-1" id="file_label">Klik untuk memilih berkas Excel (.xlsx)</span>
                                        <input type="file" name="file_excel" id="file_excel" class="d-none" accept=".xlsx" required onchange="updateFileName(this)">
                                    </div>
                                </div>
                                <button type="submit" name="submit" class="btn btn-success px-4"><i class="bi bi-upload"></i> Mulai Import</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card">
                        <div class="card-header bg-white py-3 border-bottom"><h6 class="fw-bold mb-0 text-dark"><i class="bi bi-lightbulb-fill text-warning"></i> Informasi Format</h6></div>
                        <div class="card-body p-4 small text-muted">
                            <p>Sistem telah diperbarui untuk mengisi nilai default tahun secara otomatis jika data tahun tidak ditemukan pada berkas Excel buku perpustakaan.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function updateFileName(input) {
        if (input.files && input.files[0]) {
            document.getElementById('file_label').innerHTML = '<span class="text-success fw-bold"><i class="bi bi-file-earmark-check me-1"></i> ' + input.files[0].name + '</span>';
        }
    }
</script>
</body>
</html>