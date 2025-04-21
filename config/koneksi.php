<?php
session_start();

// ngatur db
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_kas_kelas";
$port = "3306";

$koneksi = mysqli_connect($host, $user, $pass, $db, $port);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

function clean($data) {
    global $koneksi;
    return mysqli_real_escape_string($koneksi, htmlspecialchars(trim($data)));
}

//  buat mengecek user sudah login atau belum
function cekLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit();
    }
}

// format mata uang rupiah
function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

// buat dapet total kas masuk
function getTotalKasMasuk() {
    global $koneksi;
    $query = mysqli_query($koneksi, "SELECT SUM(jumlah) as total FROM transaksi WHERE jenis='masuk'");
    $data = mysqli_fetch_assoc($query);
    return $data['total'] ? $data['total'] : 0;
}

// buat dapet total kas keluar
function getTotalKasKeluar() {
    global $koneksi;
    $query = mysqli_query($koneksi, "SELECT SUM(jumlah) as total FROM transaksi WHERE jenis='keluar'");
    $data = mysqli_fetch_assoc($query);
    return $data['total'] ? $data['total'] : 0;
}

// buat dapet total sisa saldo kas
function getSaldoKas() {
    return getTotalKasMasuk() - getTotalKasKeluar();
}
?>