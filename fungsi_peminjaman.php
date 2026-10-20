<?php
function hitung_info_pengembalian($tanggal_pinjam, $tanggal_aktual = null)
{
    $tanggal_pinjam = DateTimeImmutable::createFromFormat('!Y-m-d', (string) $tanggal_pinjam);
    if (!$tanggal_pinjam) {
        $tanggal_pinjam = new DateTimeImmutable((string) $tanggal_pinjam);
    }

    $jatuh_tempo = $tanggal_pinjam->modify('+7 days');
    if ($tanggal_aktual === null || $tanggal_aktual === '' || $tanggal_aktual === '0000-00-00') {
        $tanggal_acuan = new DateTimeImmutable('today');
    } else {
        $tanggal_acuan = new DateTimeImmutable((string) $tanggal_aktual);
    }

    $hari_terlambat = $tanggal_acuan > $jatuh_tempo
        ? (int) $jatuh_tempo->diff($tanggal_acuan)->days
        : 0;

    return [
        'jatuh_tempo' => $jatuh_tempo,
        'hari_terlambat' => $hari_terlambat,
        'denda' => $hari_terlambat * 2000,
    ];
}