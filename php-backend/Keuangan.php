<?php
session_start();

require_once __DIR__ . '/libs/JWT.php';
require_once __DIR__ . '/libs/Key.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if (isset($_GET['token'])) {
    try {
        $decoded = JWT::decode($_GET['token'], new Key("SECRET_KAMU", 'HS256'));
        $_SESSION['username'] = $decoded->username;
    } catch (Exception $e) {
        header("Location: http://localhost:7000/login");
        exit();
    }
}

if (!isset($_SESSION['username'])) {
    header("Location: http://localhost:7000/login");
    exit();
}

include "service/dbkeuangan1.php";

$query = mysqli_query($koneksidata, "
    SELECT DATE_FORMAT(histori, '%M') AS bulan, SUM(nominal) AS total 
    FROM pemasukkan 
    GROUP BY DATE_FORMAT(histori, '%Y-%m')
    ORDER BY histori ASC
");

$bulan = [];
$nominal = [];

while ($data = mysqli_fetch_assoc($query)) {
    $bulan[] = $data['bulan'];
    $nominal[] = (int)$data['total'];
}

$chunks_bulan = array_chunk($bulan, 5);
$chunks_nominal = array_chunk($nominal, 5);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan</title>

    <!-- Hubungkan ke file CSS eksternal -->
    <link rel="stylesheet" href="Assets/Grafik.css">

    <!-- Hubungkan ke library CDN Chart.js dan Swiper -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
</head>
<body>

<div class="container">
    <a href="Dashboard.php">Kembali ke Dashboard</a>
    <h3>LAPORAN KEUANGAN TOKO ANDA</h3>

    <div class="swiper">
        <div class="swiper-wrapper">
            <?php for ($i = 0; $i < count($chunks_bulan); $i++): ?>
                <div class="swiper-slide">
                    <canvas id="chart<?= $i ?>"></canvas>
                </div>
            <?php endfor; ?>
        </div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>
    </div>
</div>

<!-- Variabel PHP dikirim ke JS -->
<script>
    const chunksBulan = <?= json_encode($chunks_bulan) ?>;
    const chunksNominal = <?= json_encode($chunks_nominal) ?>;
</script>

<!-- Hubungkan ke file JavaScript eksternal -->
<script src="Assets/Grafik.js"></script>

</body>
</html>
