<?php

include '../koneksi.php';

mysqli_report(MYSQLI_REPORT_OFF);


/*
|--------------------------------------------------------------------------
| FUNGSI NORMALISASI TEKS
|--------------------------------------------------------------------------
*/

function normalisasi($text)
{
    $text = (string) $text;

    // NBSP
    $text = str_replace(
        "\xC2\xA0",
        ' ',
        $text
    );

    // Enter dan tab
    $text = str_replace(
        [
            "\r",
            "\n",
            "\t"
        ],
        ' ',
        $text
    );

    // Spasi berlebihan
    $text = preg_replace(
        '/\s+/',
        ' ',
        $text
    );

    return strtoupper(
        trim($text)
    );
}


/*
|--------------------------------------------------------------------------
| NORMALISASI NAMA SHEET
|--------------------------------------------------------------------------
*/

function normalisasiSheet($text)
{
    $text = normalisasi($text);

    $text = str_replace(
        [
            'PROGRAM STUDI',
            'PROGRAM  STUDI',
            'PRODI',
            'JURUSAN'
        ],
        '',
        $text
    );

    return trim($text);
}


/*
|--------------------------------------------------------------------------
| CARI PROGRAM STUDI BERDASARKAN NAMA SHEET
|--------------------------------------------------------------------------
*/

function cariProdiDariSheet(
    $koneksi,
    $nama_sheet,
    $nama_fakultas
) {

    $sheetNormal =
        normalisasiSheet(
            $nama_sheet
        );


    /*
    |--------------------------------------------------------------------------
    | Ambil program studi berdasarkan fakultas
    |--------------------------------------------------------------------------
    */

    $fakultasDB =
        mysqli_real_escape_string(
            $koneksi,
            $nama_fakultas
        );


    $query =
        mysqli_query(
            $koneksi,
            "
            SELECT
                id_prodi,
                nama_fakultas,
                nama_prodi
            FROM program_studi
            WHERE nama_fakultas = '$fakultasDB'
            ORDER BY id_prodi ASC
            "
        );


    if (!$query) {
        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Alias sheet Excel
    |--------------------------------------------------------------------------
    */

    $alias = [

        'ELEKTRO' => [
            'TEKNIK ELEKTRO'
        ],

        'MESIN' => [
            'TEKNIK MESIN'
        ],

        'PERTAMBANGAN' => [
            'TEKNIK PERTAMBANGAN'
        ],

        'GEOLOGI' => [
            'TEKNIK GEOLOGI'
        ]

    ];


    /*
    |--------------------------------------------------------------------------
    | Loop program studi
    |--------------------------------------------------------------------------
    */

    while (
        $prodi =
        mysqli_fetch_assoc($query)
    ) {

        $namaProdi =
            normalisasi(
                $prodi['nama_prodi']
            );


        /*
        |--------------------------------------------------------------------------
        | Hilangkan jenjang
        |--------------------------------------------------------------------------
        |
        | Teknik Elektro (S1)
        | menjadi
        | Teknik Elektro
        |
        |--------------------------------------------------------------------------
        */

        $namaTanpaJenjang =
            preg_replace(
                '/\s*\((S1|S2|S3|D3|D4)\)\s*/i',
                '',
                $namaProdi
            );


        $namaTanpaJenjang =
            trim(
                $namaTanpaJenjang
            );


        /*
        |--------------------------------------------------------------------------
        | COCOK PERSIS
        |--------------------------------------------------------------------------
        */

        if (
            $sheetNormal === $namaProdi
            ||
            $sheetNormal === $namaTanpaJenjang
        ) {

            return $prodi;
        }


        /*
        |--------------------------------------------------------------------------
        | COCOK ALIAS
        |--------------------------------------------------------------------------
        */

        if (
            isset(
                $alias[$sheetNormal]
            )
        ) {

            foreach (
                $alias[$sheetNormal]
                as $namaAlias
            ) {

                $namaAlias =
                    normalisasi(
                        $namaAlias
                    );


                if (
                    $namaAlias ===
                    $namaTanpaJenjang
                ) {

                    return $prodi;
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | COCOK SEBAGIAN
        |--------------------------------------------------------------------------
        */

        if (
            $sheetNormal !== ''
            &&
            (
                strpos(
                    $namaTanpaJenjang,
                    $sheetNormal
                ) !== false

                ||

                strpos(
                    $sheetNormal,
                    $namaTanpaJenjang
                ) !== false
            )
        ) {

            return $prodi;
        }
    }


    return null;
}


/*
|--------------------------------------------------------------------------
| AMBIL NILAI CELL EXCEL
|--------------------------------------------------------------------------
*/

function ambilNilaiCell(
    $cell,
    $sharedStrings
) {

    $tipe = '';

    if (
        isset(
            $cell['t']
        )
    ) {

        $tipe =
            (string)$cell['t'];
    }


    $nilai = '';

    if (
        isset(
            $cell->v
        )
    ) {

        $nilai =
            (string)$cell->v;
    }


    /*
    |--------------------------------------------------------------------------
    | Shared String
    |--------------------------------------------------------------------------
    */

    if (
        $tipe === 's'
    ) {

        $index =
            intval(
                $nilai
            );


        return
            $sharedStrings[$index]
            ?? '';
    }


    /*
    |--------------------------------------------------------------------------
    | Inline String
    |--------------------------------------------------------------------------
    */

    if (
        $tipe === 'inlineStr'
    ) {

        $hasil = '';


        if (
            isset(
                $cell->is->t
            )
        ) {

            $hasil =
                (string)$cell->is->t;

        } else {

            foreach (
                $cell->is->r
                as $r
            ) {

                if (
                    isset(
                        $r->t
                    )
                ) {

                    $hasil .=
                        (string)$r->t;
                }
            }
        }


        return trim(
            $hasil
        );
    }


    /*
    |--------------------------------------------------------------------------
    | String biasa
    |--------------------------------------------------------------------------
    */

    return trim(
        $nilai
    );
}


/*
|--------------------------------------------------------------------------
| BACA SHARED STRINGS
|--------------------------------------------------------------------------
*/

function bacaSharedStrings($zip)
{
    $hasil = [];


    if (
        $zip->locateName(
            'xl/sharedStrings.xml'
        ) === false
    ) {

        return $hasil;
    }


    $content =
        $zip->getFromName(
            'xl/sharedStrings.xml'
        );


    if (
        $content === false
    ) {

        return $hasil;
    }


    $xml =
        simplexml_load_string(
            $content
        );


    if (!$xml) {

        return $hasil;
    }


    /*
    |--------------------------------------------------------------------------
    | Gunakan XPath local-name agar aman terhadap namespace
    |--------------------------------------------------------------------------
    */

    $items =
        $xml->xpath(
            '//*[local-name()="si"]'
        );


    if (
        !$items
    ) {

        return $hasil;
    }


    foreach (
        $items
        as $si
    ) {

        $text = '';


        /*
        |--------------------------------------------------------------------------
        | Cari semua text
        |--------------------------------------------------------------------------
        */

        $texts =
            $si->xpath(
                './/*[local-name()="t"]'
            );


        if (
            $texts
        ) {

            foreach (
                $texts
                as $t
            ) {

                $text .=
                    (string)$t;
            }
        }


        $hasil[] =
            $text;
    }


    return $hasil;
}


/*
|--------------------------------------------------------------------------
| KONVERSI KOLOM EXCEL KE INDEX
|--------------------------------------------------------------------------
|
| A  = 0
| B  = 1
| C  = 2
| Z  = 25
| AA = 26
|
|--------------------------------------------------------------------------
*/

function excelColumnIndex(
    $column
) {

    $column =
        strtoupper(
            trim(
                $column
            )
        );


    $index = 0;


    for (
        $i = 0;
        $i < strlen($column);
        $i++
    ) {

        $index =
            ($index * 26)
            +
            (
                ord(
                    $column[$i]
                )
                -
                ord('A')
                +
                1
            );
    }


    return $index - 1;
}


/*
|--------------------------------------------------------------------------
| BACA SHEET EXCEL
|--------------------------------------------------------------------------
*/

function bacaSheet(
    $zip,
    $sheetFile,
    $sharedStrings
) {

    $rows = [];


    if (
        $sheetFile === ''
    ) {

        return $rows;
    }


    if (
        $zip->locateName(
            $sheetFile
        ) === false
    ) {

        return $rows;
    }


    $content =
        $zip->getFromName(
            $sheetFile
        );


    if (
        $content === false
    ) {

        return $rows;
    }


    $xml =
        simplexml_load_string(
            $content
        );


    if (!$xml) {

        return $rows;
    }


    /*
    |--------------------------------------------------------------------------
    | Cari semua row menggunakan XPath
    |--------------------------------------------------------------------------
    */

    $rowNodes =
        $xml->xpath(
            '//*[local-name()="sheetData"]/*[local-name()="row"]'
        );


    if (
        !$rowNodes
    ) {

        return $rows;
    }


    foreach (
        $rowNodes
        as $row
    ) {

        $data = [];


        /*
        |--------------------------------------------------------------------------
        | Cari cell
        |--------------------------------------------------------------------------
        */

        $cells =
            $row->xpath(
                './*[local-name()="c"]'
            );


        if (
            !$cells
        ) {

            continue;
        }


        foreach (
            $cells
            as $cell
        ) {

            /*
            |--------------------------------------------------------------------------
            | Referensi cell
            |--------------------------------------------------------------------------
            |
            | Contoh:
            | A1
            | B1
            | C1
            |
            |--------------------------------------------------------------------------
            */

            $ref = '';


            if (
                isset(
                    $cell['r']
                )
            ) {

                $ref =
                    (string)$cell['r'];
            }


            /*
            |--------------------------------------------------------------------------
            | Ambil huruf kolom
            |--------------------------------------------------------------------------
            */

            preg_match(
                '/^([A-Z]+)/i',
                strtoupper(
                    $ref
                ),
                $matches
            );


            if (
                !isset(
                    $matches[1]
                )
            ) {

                continue;
            }


            $column =
                $matches[1];


            $index =
                excelColumnIndex(
                    $column
                );


            $data[$index] =
                ambilNilaiCell(
                    $cell,
                    $sharedStrings
                );
        }


        if (
            !empty($data)
        ) {

            $rows[] =
                $data;
        }
    }


    return $rows;
}


/*
|--------------------------------------------------------------------------
| NORMALISASI ROW
|--------------------------------------------------------------------------
*/

function normalisasiRows(
    $rows
) {

    $hasil = [];


    foreach (
        $rows
        as $row
    ) {

        if (
            empty($row)
        ) {

            continue;
        }


        $keys =
            array_keys(
                $row
            );


        $max =
            max(
                $keys
            );


        $normal = [];


        for (
            $i = 0;
            $i <= $max;
            $i++
        ) {

            $normal[$i] =
                $row[$i]
                ?? '';
        }


        $hasil[] =
            $normal;
    }


    return $hasil;
}


/*
|--------------------------------------------------------------------------
| CARI HEADER EXCEL
|--------------------------------------------------------------------------
*/

function cariHeader(
    $rows
) {

    foreach (
        $rows
        as $index => $row
    ) {

        $hasil = [];


        foreach (
            $row
            as $kolom => $nilai
        ) {

            $nilaiNormal =
                normalisasi(
                    $nilai
                );


            /*
            |--------------------------------------------------------------------------
            | NO
            |--------------------------------------------------------------------------
            */

            if (
                $nilaiNormal === 'NO'
                ||
                $nilaiNormal === 'NOMOR'
            ) {

                $hasil['no'] =
                    $kolom;
            }


            /*
            |--------------------------------------------------------------------------
            | JUDUL
            |--------------------------------------------------------------------------
            */

            if (
                strpos(
                    $nilaiNormal,
                    'JUDUL BUKU'
                ) !== false
                ||
                $nilaiNormal === 'JUDUL'
            ) {

                $hasil['judul'] =
                    $kolom;
            }


            /*
            |--------------------------------------------------------------------------
            | PENGARANG
            |--------------------------------------------------------------------------
            */

            if (
                strpos(
                    $nilaiNormal,
                    'PENGARANG'
                ) !== false
                ||
                strpos(
                    $nilaiNormal,
                    'PENULIS'
                ) !== false
                ||
                strpos(
                    $nilaiNormal,
                    'AUTHOR'
                ) !== false
            ) {

                $hasil['pengarang'] =
                    $kolom;
            }


            /*
            |--------------------------------------------------------------------------
            | PENERBIT
            |--------------------------------------------------------------------------
            */

            if (
                strpos(
                    $nilaiNormal,
                    'PENERBIT'
                ) !== false
                ||
                strpos(
                    $nilaiNormal,
                    'PUBLISHER'
                ) !== false
            ) {

                $hasil['penerbit'] =
                    $kolom;
            }


            /*
            |--------------------------------------------------------------------------
            | ISBN
            |--------------------------------------------------------------------------
            */

            if (
                strpos(
                    $nilaiNormal,
                    'ISBN'
                ) !== false
            ) {

                $hasil['isbn'] =
                    $kolom;
            }


            /*
            |--------------------------------------------------------------------------
            | HALAMAN
            |--------------------------------------------------------------------------
            */

            if (
                $nilaiNormal === 'HLM'
                ||
                $nilaiNormal === 'HALAMAN'
                ||
                strpos(
                    $nilaiNormal,
                    'HALAMAN'
                ) !== false
            ) {

                $hasil['halaman'] =
                    $kolom;
            }


            /*
            |--------------------------------------------------------------------------
            | TAHUN
            |--------------------------------------------------------------------------
            */

            if (
                $nilaiNormal === 'TAHUN'
                ||
                strpos(
                    $nilaiNormal,
                    'TAHUN'
                ) !== false
            ) {

                $hasil['tahun'] =
                    $kolom;
            }


            /*
            |--------------------------------------------------------------------------
            | JUMLAH
            |--------------------------------------------------------------------------
            */

            if (
                $nilaiNormal === 'JUMLAH'
                ||
                $nilaiNormal === 'EKSEMPLAR'
                ||
                strpos(
                    $nilaiNormal,
                    'EKSEMPLAR'
                ) !== false
            ) {

                $hasil['jumlah'] =
                    $kolom;
            }


            /*
            |--------------------------------------------------------------------------
            | KLASIFIKASI
            |--------------------------------------------------------------------------
            */

            if (
                strpos(
                    $nilaiNormal,
                    'KLASIFIKASI'
                ) !== false
            ) {

                $hasil['klasifikasi'] =
                    $kolom;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Minimal harus ada JUDUL
        |--------------------------------------------------------------------------
        */

        if (
            isset(
                $hasil['judul']
            )
        ) {

            return [
                'index' =>
                    $index,

                'columns' =>
                    $hasil
            ];
        }
    }


    return null;
}


/*
|--------------------------------------------------------------------------
| AMBIL ANGKA
|--------------------------------------------------------------------------
*/

function ambilAngka(
    $nilai
) {

    $nilai =
        trim(
            (string)$nilai
        );


    if (
        $nilai === ''
    ) {

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Ambil angka saja
    |--------------------------------------------------------------------------
    */

    $angka =
        preg_replace(
            '/[^0-9]/',
            '',
            $nilai
        );


    if (
        $angka === ''
    ) {

        return null;
    }


    return intval(
        $angka
    );
}


/*
|--------------------------------------------------------------------------
| NORMALISASI PATH TARGET XML
|--------------------------------------------------------------------------
*/

function normalisasiTargetSheet(
    $target
) {

    $target =
        trim(
            $target
        );


    $target =
        str_replace(
            '\\',
            '/',
            $target
        );


    /*
    |--------------------------------------------------------------------------
    | Hilangkan ./ di depan
    |--------------------------------------------------------------------------
    */

    while (
        strpos(
            $target,
            './'
        ) === 0
    ) {

        $target =
            substr(
                $target,
                2
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Hilangkan / di awal
    |--------------------------------------------------------------------------
    */

    $target =
        ltrim(
            $target,
            '/'
        );


    /*
    |--------------------------------------------------------------------------
    | Jika sudah xl/
    |--------------------------------------------------------------------------
    */

    if (
        strpos(
            $target,
            'xl/'
        ) === 0
    ) {

        return $target;
    }


    /*
    |--------------------------------------------------------------------------
    | Target workbook biasanya:
    |
    | worksheets/sheet1.xml
    |
    | menjadi:
    |
    | xl/worksheets/sheet1.xml
    |--------------------------------------------------------------------------
    */

    return
        'xl/' . $target;
}


/*
|--------------------------------------------------------------------------
| VALIDASI REQUEST
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] !== 'POST'
    ||
    !isset(
        $_POST['submit']
    )
) {

    header(
        'Location: import_buku.php'
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| AMBIL FAKULTAS
|--------------------------------------------------------------------------
*/

$fakultas =
    trim(
        $_POST['fakultas']
        ?? ''
    );


if (
    $fakultas === ''
) {

    die(
        "
        <script>
            alert('Silakan pilih fakultas terlebih dahulu.');
            window.location.href='import_buku.php';
        </script>
        "
    );
}


/*
|--------------------------------------------------------------------------
| VALIDASI FILE
|--------------------------------------------------------------------------
*/

if (
    !isset(
        $_FILES['file_excel']
    )
) {

    die(
        "
        <script>
            alert('File Excel belum dipilih.');
            window.location.href='import_buku.php';
        </script>
        "
    );
}


$file =
    $_FILES['file_excel'];


/*
|--------------------------------------------------------------------------
| CEK ERROR UPLOAD
|--------------------------------------------------------------------------
*/

if (
    $file['error']
    !== UPLOAD_ERR_OK
) {

    die(
        "
        <script>
            alert('File Excel gagal diupload.');
            window.location.href='import_buku.php';
        </script>
        "
    );
}


/*
|--------------------------------------------------------------------------
| CEK EXTENSION
|--------------------------------------------------------------------------
*/

$extension =
    strtolower(
        pathinfo(
            $file['name'],
            PATHINFO_EXTENSION
        )
    );


if (
    $extension !== 'xlsx'
) {

    die(
        "
        <script>
            alert('File harus berformat .xlsx');
            window.location.href='import_buku.php';
        </script>
        "
    );
}


/*
|--------------------------------------------------------------------------
| BUKA FILE XLSX
|--------------------------------------------------------------------------
*/

$zip =
    new ZipArchive();


$openResult =
    $zip->open(
        $file['tmp_name']
    );


if (
    $openResult !== true
) {

    die(
        "
        <script>
            alert('File Excel tidak dapat dibuka.');
            window.location.href='import_buku.php';
        </script>
        "
    );
}


/*
|--------------------------------------------------------------------------
| BACA SHARED STRINGS
|--------------------------------------------------------------------------
*/

$sharedStrings =
    bacaSharedStrings(
        $zip
    );


/*
|--------------------------------------------------------------------------
| BACA WORKBOOK.XML
|--------------------------------------------------------------------------
*/

$workbookContent =
    $zip->getFromName(
        'xl/workbook.xml'
    );


/*
|--------------------------------------------------------------------------
| BACA WORKBOOK RELATIONSHIPS
|--------------------------------------------------------------------------
*/

$relsContent =
    $zip->getFromName(
        'xl/_rels/workbook.xml.rels'
    );


if (
    $workbookContent === false
    ||
    $relsContent === false
) {

    $zip->close();


    die(
        "
        <script>
            alert('Struktur file Excel tidak lengkap.');
            window.location.href='import_buku.php';
        </script>
        "
    );
}


/*
|--------------------------------------------------------------------------
| LOAD XML
|--------------------------------------------------------------------------
*/

$workbook =
    simplexml_load_string(
        $workbookContent
    );


$rels =
    simplexml_load_string(
        $relsContent
    );


if (
    !$workbook
    ||
    !$rels
) {

    $zip->close();


    die(
        "
        <script>
            alert('Workbook Excel tidak dapat dibaca.');
            window.location.href='import_buku.php';
        </script>
        "
    );
}


/*
|--------------------------------------------------------------------------
| BACA RELATIONSHIP
|--------------------------------------------------------------------------
*/

$relationships = [];


/*
|--------------------------------------------------------------------------
| Gunakan XPath local-name
|--------------------------------------------------------------------------
|
| Ini penting agar tidak bermasalah dengan namespace XML.
|--------------------------------------------------------------------------
*/

$relationshipNodes =
    $rels->xpath(
        '//*[local-name()="Relationship"]'
    );


if (
    $relationshipNodes
) {

    foreach (
        $relationshipNodes
        as $relationship
    ) {

        $id = '';

        $target = '';


        if (
            isset(
                $relationship['Id']
            )
        ) {

            $id =
                trim(
                    (string)$relationship['Id']
                );
        }


        if (
            isset(
                $relationship['Target']
            )
        ) {

            $target =
                trim(
                    (string)$relationship['Target']
                );
        }


        if (
            $id === ''
            ||
            $target === ''
        ) {

            continue;
        }


        $relationships[$id] =
            normalisasiTargetSheet(
                $target
            );
    }
}


/*
|--------------------------------------------------------------------------
| COUNTER
|--------------------------------------------------------------------------
*/

$berhasil = 0;

$dilewati = 0;

$gagal = 0;

$detail = [];


/*
|--------------------------------------------------------------------------
| CARI SEMUA SHEET
|--------------------------------------------------------------------------
*/

$sheetNodes =
    $workbook->xpath(
        '//*[local-name()="sheets"]/*[local-name()="sheet"]'
    );


if (
    !$sheetNodes
) {

    $zip->close();


    die(
        "
        <script>
            alert('Tidak ada sheet yang ditemukan di dalam file Excel.');
            window.location.href='import_buku.php';
        </script>
        "
    );
}


/*
|--------------------------------------------------------------------------
| LOOP SHEET
|--------------------------------------------------------------------------
*/

foreach (
    $sheetNodes
    as $sheet
) {

    /*
    |--------------------------------------------------------------------------
    | Nama sheet
    |--------------------------------------------------------------------------
    */

    $namaSheet = '';

    if (
        isset(
            $sheet['name']
        )
    ) {

        $namaSheet =
            trim(
                (string)$sheet['name']
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Ambil r:id
    |--------------------------------------------------------------------------
    */

    $rId = '';


    $relationshipNamespace =
        'http://schemas.openxmlformats.org/officeDocument/2006/relationships';


    /*
    |--------------------------------------------------------------------------
    | Cara pertama
    |--------------------------------------------------------------------------
    */

    $sheetAttributes =
        $sheet->attributes(
            $relationshipNamespace
        );


    if (
        $sheetAttributes !== null
        &&
        isset(
            $sheetAttributes['id']
        )
    ) {

        $rId =
            trim(
                (string)$sheetAttributes['id']
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Cara kedua jika belum ditemukan
    |--------------------------------------------------------------------------
    */

    if (
        $rId === ''
    ) {

        $allAttributes =
            $sheet->attributes();


        if (
            $allAttributes
        ) {

            foreach (
                $allAttributes
                as $attributeName => $attributeValue
            ) {

                if (
                    strtolower(
                        (string)$attributeName
                    ) === 'id'
                ) {

                    $rId =
                        trim(
                            (string)$attributeValue
                        );

                    break;
                }
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Sheet1 bukan data buku
    |--------------------------------------------------------------------------
    */

    if (
        normalisasi(
            $namaSheet
        ) === 'SHEET1'
    ) {

        $dilewati++;


        $detail[] =
            "
            <li>
                Sheet
                <b>Sheet1</b>
                dilewati karena bukan data buku.
            </li>
            ";


        continue;
    }


    /*
    |--------------------------------------------------------------------------
    | Cari file XML berdasarkan r:id
    |--------------------------------------------------------------------------
    */

    $sheetFile =
        $relationships[$rId]
        ?? '';


    /*
    |--------------------------------------------------------------------------
    | Jika mapping relationship gagal,
    | coba cari berdasarkan nama sheet secara langsung
    |--------------------------------------------------------------------------
    */

    if (
        $sheetFile === ''
    ) {

        /*
        |--------------------------------------------------------------------------
        | Cari semua file worksheet
        |--------------------------------------------------------------------------
        */

        $jumlahFile =
            $zip->numFiles;


        for (
            $i = 0;
            $i < $jumlahFile;
            $i++
        ) {

            $namaFileZip =
                $zip->getNameIndex(
                    $i
                );


            if (
                preg_match(
                    '#^xl/worksheets/sheet[0-9]+\.xml$#i',
                    $namaFileZip
                )
            ) {

                /*
                |--------------------------------------------------------------------------
                | Untuk fallback, belum langsung digunakan
                | jika r:id tidak diketahui.
                |--------------------------------------------------------------------------
                */
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Jika tetap tidak ditemukan
    |--------------------------------------------------------------------------
    */

    if (
        $sheetFile === ''
    ) {

        $dilewati++;


        $detail[] =
            "
            <li>
                Sheet
                <b>" .
                htmlspecialchars(
                    $namaSheet
                ) .
                "</b>
                dilewati karena file sheet tidak ditemukan.
                <br>
                <small class='text-muted'>
                    r:id: " .
                    htmlspecialchars(
                        $rId
                    ) .
                    "
                </small>
            </li>
            ";


        continue;
    }


    /*
    |--------------------------------------------------------------------------
    | Cari Program Studi
    |--------------------------------------------------------------------------
    */

    $prodi =
        cariProdiDariSheet(
            $koneksi,
            $namaSheet,
            $fakultas
        );


    if (
        !$prodi
    ) {

        $dilewati++;


        $detail[] =
            "
            <li>
                Sheet
                <b>" .
                htmlspecialchars(
                    $namaSheet
                ) .
                "</b>
                tidak cocok dengan program studi
                pada fakultas
                <b>" .
                htmlspecialchars(
                    $fakultas
                ) .
                "</b>.
            </li>
            ";


        continue;
    }


    /*
    |--------------------------------------------------------------------------
    | ID PRODI
    |--------------------------------------------------------------------------
    */

    $idProdi =
        intval(
            $prodi['id_prodi']
        );


    $namaProdi =
        $prodi['nama_prodi'];


    /*
    |--------------------------------------------------------------------------
    | BACA SHEET
    |--------------------------------------------------------------------------
    */

    $rows =
        bacaSheet(
            $zip,
            $sheetFile,
            $sharedStrings
        );


    if (
        empty($rows)
    ) {

        $dilewati++;


        $detail[] =
            "
            <li>
                Sheet
                <b>" .
                htmlspecialchars(
                    $namaSheet
                ) .
                "</b>
                berhasil ditemukan tetapi
                tidak mempunyai data.
            </li>
            ";


        continue;
    }


    /*
    |--------------------------------------------------------------------------
    | Normalisasi rows
    |--------------------------------------------------------------------------
    */

    $rows =
        normalisasiRows(
            $rows
        );


    /*
    |--------------------------------------------------------------------------
    | Cari header
    |--------------------------------------------------------------------------
    */

    $header =
        cariHeader(
            $rows
        );


    if (
        !$header
    ) {

        $gagal++;


        $detail[] =
            "
            <li>
                Sheet
                <b>" .
                htmlspecialchars(
                    $namaSheet
                ) .
                "</b>
                tidak memiliki header
                <b>JUDUL BUKU</b>.
            </li>
            ";


        continue;
    }


    $headerIndex =
        $header['index'];


    $columns =
        $header['columns'];


    /*
    |--------------------------------------------------------------------------
    | PROSES SETIAP BARIS
    |--------------------------------------------------------------------------
    */

    for (
        $i = $headerIndex + 1;
        $i < count($rows);
        $i++
    ) {

        $row =
            $rows[$i];


        /*
        |--------------------------------------------------------------------------
        | Ambil nomor
        |--------------------------------------------------------------------------
        */

        $nomor = '';


        if (
            isset(
                $columns['no']
            )
        ) {

            $nomor =
                trim(
                    $row[
                        $columns['no']
                    ]
                    ??
                    ''
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Jika kolom NO ada,
        | maka hanya baris bernomor yang diproses.
        |--------------------------------------------------------------------------
        */

        if (
            isset(
                $columns['no']
            )
        ) {

            if (
                $nomor === ''
                ||
                !is_numeric(
                    $nomor
                )
            ) {

                continue;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | JUDUL
        |--------------------------------------------------------------------------
        */

        $judul =
            trim(
                $row[
                    $columns['judul']
                ]
                ??
                ''
            );


        /*
        |--------------------------------------------------------------------------
        | Jika judul kosong, lewati
        |--------------------------------------------------------------------------
        */

        if (
            $judul === ''
        ) {

            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | PENGARANG
        |--------------------------------------------------------------------------
        */

        $pengarang = '';


        if (
            isset(
                $columns['pengarang']
            )
        ) {

            $pengarang =
                trim(
                    $row[
                        $columns['pengarang']
                    ]
                    ??
                    ''
                );
        }


        /*
        |--------------------------------------------------------------------------
        | PENERBIT
        |--------------------------------------------------------------------------
        */

        $penerbit = '';


        if (
            isset(
                $columns['penerbit']
            )
        ) {

            $penerbit =
                trim(
                    $row[
                        $columns['penerbit']
                    ]
                    ??
                    ''
                );
        }


        /*
        |--------------------------------------------------------------------------
        | ISBN
        |--------------------------------------------------------------------------
        */

        $isbn = '';


        if (
            isset(
                $columns['isbn']
            )
        ) {

            $isbn =
                trim(
                    $row[
                        $columns['isbn']
                    ]
                    ??
                    ''
                );
        }


        /*
        |--------------------------------------------------------------------------
        | HALAMAN
        |--------------------------------------------------------------------------
        */

        $jumlahHalaman = null;


        if (
            isset(
                $columns['halaman']
            )
        ) {

            $jumlahHalaman =
                ambilAngka(
                    $row[
                        $columns['halaman']
                    ]
                    ??
                    ''
                );
        }


        /*
        |--------------------------------------------------------------------------
        | TAHUN
        |--------------------------------------------------------------------------
        */

        $tahunTerbit = null;


        if (
            isset(
                $columns['tahun']
            )
        ) {

            $tahunTerbit =
                ambilAngka(
                    $row[
                        $columns['tahun']
                    ]
                    ??
                    ''
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Validasi tahun
        |--------------------------------------------------------------------------
        */

        if (
            $tahunTerbit !== null
            &&
            (
                $tahunTerbit < 1000
                ||
                $tahunTerbit > 2100
            )
        ) {

            $tahunTerbit = null;
        }


        /*
        |--------------------------------------------------------------------------
        | JUMLAH EKSEMPLAR
        |--------------------------------------------------------------------------
        */

        $jumlahEksemplar = 1;


        if (
            isset(
                $columns['jumlah']
            )
        ) {

            $jumlah =
                ambilAngka(
                    $row[
                        $columns['jumlah']
                    ]
                    ??
                    ''
                );


            if (
                $jumlah !== null
                &&
                $jumlah > 0
            ) {

                $jumlahEksemplar =
                    $jumlah;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | NO KLASIFIKASI
        |--------------------------------------------------------------------------
        */

        $noKlasifikasi = '';


        if (
            isset(
                $columns['klasifikasi']
            )
        ) {

            $noKlasifikasi =
                trim(
                    $row[
                        $columns['klasifikasi']
                    ]
                    ??
                    ''
                );
        }


        /*
        |--------------------------------------------------------------------------
        | ESCAPE DATABASE
        |--------------------------------------------------------------------------
        */

        $judulDB =
            mysqli_real_escape_string(
                $koneksi,
                $judul
            );


        $pengarangDB =
            mysqli_real_escape_string(
                $koneksi,
                $pengarang
            );


        $penerbitDB =
            mysqli_real_escape_string(
                $koneksi,
                $penerbit
            );


        $isbnDB =
            mysqli_real_escape_string(
                $koneksi,
                $isbn
            );


        $klasifikasiDB =
            mysqli_real_escape_string(
                $koneksi,
                $noKlasifikasi
            );


        $sumberDB =
            mysqli_real_escape_string(
                $koneksi,
                $fakultas
            );


        /*
        |--------------------------------------------------------------------------
        | SQL NULL
        |--------------------------------------------------------------------------
        */

        $tahunSQL =
            $tahunTerbit === null
            ? 'NULL'
            : intval(
                $tahunTerbit
            );


        $halamanSQL =
            $jumlahHalaman === null
            ? 'NULL'
            : intval(
                $jumlahHalaman
            );


        /*
        |--------------------------------------------------------------------------
        | CEK DUPLIKAT
        |--------------------------------------------------------------------------
        */

        $cek =
            mysqli_query(
                $koneksi,
                "
                SELECT id_buku
                FROM buku
                WHERE judul_buku = '$judulDB'
                  AND id_prodi = $idProdi
                LIMIT 1
                "
            );


        if (
            $cek
            &&
            mysqli_num_rows(
                $cek
            ) > 0
        ) {

            $dilewati++;

            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | INSERT DATA BUKU
        |--------------------------------------------------------------------------
        */

        $sql =
            "
            INSERT INTO buku
            (
                id_prodi,
                judul_buku,
                pengarang,
                penerbit,
                isbn,
                tahun_terbit,
                jumlah_halaman,
                jumlah_eksemplar,
                no_klasifikasi,
                sumber,
                created_at
            )
            VALUES
            (
                $idProdi,
                '$judulDB',
                '$pengarangDB',
                '$penerbitDB',
                '$isbnDB',
                $tahunSQL,
                $halamanSQL,
                $jumlahEksemplar,
                '$klasifikasiDB',
                '$sumberDB',
                NOW()
            )
            ";


        /*
        |--------------------------------------------------------------------------
        | EKSEKUSI INSERT
        |--------------------------------------------------------------------------
        */

        if (
            mysqli_query(
                $koneksi,
                $sql
            )
        ) {

            $berhasil++;

        } else {

            $gagal++;


            $detail[] =
                "
                <li>
                    Gagal memasukkan buku:
                    <b>" .
                    htmlspecialchars(
                        $judul
                    ) .
                    "</b>

                    <br>

                    Program Studi:
                    <b>" .
                    htmlspecialchars(
                        $namaProdi
                    ) .
                    "</b>

                    <br>

                    Error:
                    " .
                    htmlspecialchars(
                        mysqli_error(
                            $koneksi
                        )
                    ) .
                    "
                </li>
                ";
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Detail sheet berhasil
    |--------------------------------------------------------------------------
    */

    $detail[] =
        "
        <li class='text-success'>
            Sheet
            <b>" .
            htmlspecialchars(
                $namaSheet
            ) .
            "</b>
            →
            <b>" .
            htmlspecialchars(
                $namaProdi
            ) .
            "</b>
            →
            id_prodi
            <b>" .
            $idProdi .
            "</b>
            berhasil dibaca.
        </li>
        ";
}


/*
|--------------------------------------------------------------------------
| TUTUP ZIP
|--------------------------------------------------------------------------
*/

$zip->close();

?>

<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Hasil Import Buku
    </title>


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


        .result-card {
            max-width: 1000px;
            margin: 50px auto;
            border: none;
            border-radius: 18px;
            overflow: hidden;
        }


        .result-header {
            background-color: #198754;
            color: white;
            padding: 25px 30px;
        }


        .stat-card {
            border: none;
            border-radius: 15px;
        }


        .stat-icon {
            font-size: 38px;
        }


        .detail-card {
            border-radius: 12px;
        }


        .btn {
            border-radius: 8px;
        }

    </style>

</head>


<body>


<div class="container">

    <div class="card shadow result-card">


        <!-- HEADER -->

        <div class="result-header">

            <h3 class="fw-bold mb-2">

                <i
                    class="bi bi-check-circle-fill me-2"
                ></i>

                Import Buku Selesai

            </h3>


            <div>

                Fakultas:

                <strong>

                    <?= htmlspecialchars(
                        $fakultas
                    ) ?>

                </strong>

            </div>

        </div>


        <div class="card-body p-4">


            <!-- STATISTIK -->

            <div class="row g-3 mb-4">


                <!-- BERHASIL -->

                <div class="col-md-4">

                    <div
                        class="card stat-card bg-success-subtle"
                    >

                        <div
                            class="card-body text-center p-4"
                        >

                            <i
                                class="bi bi-check-circle-fill text-success stat-icon"
                            ></i>


                            <h2
                                class="fw-bold text-success mb-1"
                            >

                                <?= $berhasil ?>

                            </h2>


                            <div>

                                Buku berhasil diimport

                            </div>

                        </div>

                    </div>

                </div>


                <!-- DILEWATI -->

                <div class="col-md-4">

                    <div
                        class="card stat-card bg-warning-subtle"
                    >

                        <div
                            class="card-body text-center p-4"
                        >

                            <i
                                class="bi bi-skip-forward-circle-fill text-warning stat-icon"
                            ></i>


                            <h2
                                class="fw-bold text-warning mb-1"
                            >

                                <?= $dilewati ?>

                            </h2>


                            <div>

                                Dilewati / duplikat

                            </div>

                        </div>

                    </div>

                </div>


                <!-- GAGAL -->

                <div class="col-md-4">

                    <div
                        class="card stat-card bg-danger-subtle"
                    >

                        <div
                            class="card-body text-center p-4"
                        >

                            <i
                                class="bi bi-x-circle-fill text-danger stat-icon"
                            ></i>


                            <h2
                                class="fw-bold text-danger mb-1"
                            >

                                <?= $gagal ?>

                            </h2>


                            <div>

                                Gagal

                            </div>

                        </div>

                    </div>

                </div>


            </div>


            <!-- PEMETAAN -->

            <div class="alert alert-primary">


                <h5 class="fw-bold">

                    <i
                        class="bi bi-diagram-3-fill me-2"
                    ></i>

                    Pemetaan Program Studi

                </h5>


                <ul class="mb-0">


                    <li>

                        <strong>ELEKTRO</strong>

                        →

                        Teknik Elektro (S1)

                        →

                        id_prodi

                        <strong>5</strong>

                    </li>


                    <li>

                        <strong>MESIN</strong>

                        →

                        Teknik Mesin (S1)

                        →

                        id_prodi

                        <strong>6</strong>

                    </li>


                    <li>

                        <strong>PERTAMBANGAN</strong>

                        →

                        Teknik Pertambangan (S1)

                        →

                        id_prodi

                        <strong>7</strong>

                    </li>


                    <li>

                        <strong>GEOLOGI</strong>

                        →

                        Teknik Geologi (S1)

                        →

                        id_prodi

                        <strong>8</strong>

                    </li>


                    <li>

                        <strong>Sheet1</strong>

                        →

                        Dilewati

                    </li>


                </ul>

            </div>


            <!-- DETAIL -->

            <?php if (
                !empty($detail)
            ): ?>


                <div
                    class="card detail-card border mb-4"
                >


                    <div
                        class="card-header bg-light py-3"
                    >

                        <h5 class="mb-0 fw-bold">

                            <i
                                class="bi bi-info-circle me-2"
                            ></i>

                            Detail Proses Import

                        </h5>

                    </div>


                    <div class="card-body">


                        <ul class="mb-0">

                            <?php foreach (
                                $detail
                                as $item
                            ): ?>

                                <?= $item ?>

                            <?php endforeach; ?>

                        </ul>


                    </div>

                </div>


            <?php endif; ?>


            <!-- BUTTON -->

            <div class="d-flex gap-2">


                <a
                    href="import_buku.php"
                    class="btn btn-success px-4"
                >

                    <i
                        class="bi bi-file-earmark-excel me-2"
                    ></i>

                    Import Lagi

                </a>


                <a
                    href="buku.php"
                    class="btn btn-primary px-4"
                >

                    <i
                        class="bi bi-book me-2"
                    ></i>

                    Lihat Data Buku

                </a>


            </div>


        </div>

    </div>

</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>