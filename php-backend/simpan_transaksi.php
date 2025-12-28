<?php
require_once __DIR__ . '/service/dbkeuangan.php'; // koneksi ke database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nominal = intval($_POST['nominal']);

    if ($nominal > 0) {
        $query = "INSERT INTO pemasukan (Nominal) VALUES ($nominal)";
        if (mysqli_query($koneksiDatabase, $query)) {
            echo "✅ Transaksi berhasil disimpan. <a href='Kasir.php'>Kembali</a>";
        } else {
            echo "❌ Gagal menyimpan transaksi: " . mysqli_error($koneksiDatabase);
        }
    } else {
        echo "❌ Nominal tidak valid.";
    }
} else {
    header("Location: Kasir.php");
    exit();
}
?>
