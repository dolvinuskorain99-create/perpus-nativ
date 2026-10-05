<?php
session_start();

// Hapus semua variabel session
$_SESSION = array();

// Hancurkan session
session_destroy();

// Redirect kembali ke halaman utama / login
header("Location: login.php");
exit;
?>