<?php
include '../koneksi.php';

// Tentukan password baru yang Anda inginkan di sini:
$password_baru = 'Imanuel2026!'; 

// Buat hash yang aman secara otomatis menggunakan algoritma BCRYPT / Argon
$password_hacked = password_hash($password_baru, PASSWORD_DEFAULT);

// Update ke database untuk user 'imanuel'
$query = "UPDATE users SET password = '$password_hacked' WHERE username = 'imanuel'";
$execute = mysqli_query($koneksi, $query);

if ($execute) {
    echo "<h3>Berhasil! Password Anda telah diubah dan di-hash dengan aman.</h3>";
    echo "<p>Password baru Anda: <b>$password_baru</b></p>";
    echo "<p><a href='login.php'>Kembali ke Halaman Login</a></p>";
} else {
    echo "Gagal mengubah password: " . mysqli_error($koneksi);
}
?>