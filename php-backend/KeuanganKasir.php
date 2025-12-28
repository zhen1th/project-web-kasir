<?php
session_start();

// Periksa apakah sudah ada session yang valid
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $user_id = $_SESSION['user_id'];
} 
// Jika ada token di URL (saat pertama kali login)
else if (!empty($_GET['token'])) {
    $token = $_GET['token'];

    // Verifikasi token dengan Node.js
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost:3000/verify-token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['token' => $token]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if (!$result || !$result['valid']) {
        // Token tidak valid, redirect ke login
        header('Location: http://localhost:3000/login?redirect=' . basename($_SERVER['PHP_SELF']));
        exit;
    }

    // Token valid, simpan informasi user di session
    $_SESSION['loggedin'] = true;
    $_SESSION['username'] = $result['username'];
    $_SESSION['user_id'] = $result['user_id'];
    $_SESSION['login_time'] = time();
    
    $user_id = $_SESSION['user_id'];
}
// Jika tidak ada session dan tidak ada token
else {
    // Redirect ke login
    header('Location: http://localhost:3000/login?redirect=' . basename($_SERVER['PHP_SELF']));
    exit;
}

// Koneksi database dompos
$host = "localhost";
$user = "root";
$password = "";
$dbname = "dompos";

$koneksidata = new mysqli($host, $user, $password, $dbname);

if ($koneksidata->connect_error) {
    die("Koneksi database gagal: " . $koneksidata->connect_error);
}

// Query data pemasukkan hanya untuk user yang login
$query = mysqli_query($koneksidata, "
    SELECT DATE_FORMAT(histori, '%M') AS bulan, SUM(nominal) AS total 
    FROM pemasukkan 
    WHERE user_id = $user_id
    GROUP BY DATE_FORMAT(histori, '%Y-%m')
    ORDER BY histori ASC
");

$bulan = [];
$nominal = [];

while ($data = mysqli_fetch_assoc($query)) {
    $bulan[] = $data['bulan'];
    $nominal[] = (int)$data['total'];
}

// Jika tidak ada data, tampilkan pesan
if (empty($bulan)) {
    $no_data = true;
} else {
    $chunks_bulan = array_chunk($bulan, 5);
    $chunks_nominal = array_chunk($nominal, 5);
    $no_data = false;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan</title>

    <!-- FAVICON -->
    <link href="Assets/Dompo$Hitam.png" rel="icon"
        media="(prefers-color-scheme: light)" />

    <link href="Assets/Dompo$Putih.png" rel="icon"
        media="(prefers-color-scheme: dark)" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="Assets/GrafikLaporan.css">
    
    <!-- Hubungkan ke library CDN Chart.js dan Swiper -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }

        .topbar {
            height: 50px;
            background-color: #212529;
            color: white;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .logo-text {
            font-family: 'Georgia', serif;
            font-size: 20px;
            color: white;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .center-text {
            text-align: center;
            margin-bottom: 20px;
        }

        .center-text a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #212529;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .center-text a:hover {
            background-color: #343a40;
        }

        h3 {
            text-align: center;
            margin-bottom: 30px;
            color: #212529;
        }

        .swiper {
            width: 100%;
            height: 400px;
            margin-bottom: 30px;
        }

        .swiper-slide {
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        .swiper-button-prev,
        .swiper-button-next {
            color: #212529;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }

        .no-data i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #dee2e6;
        }

        .btn-light {
            background-color: #f8f9fa;
            color: #212529;
            border: 1px solid #dee2e6;
        }

        .btn-light:hover {
            background-color: #e9ecef;
        }
    </style>
</head>

<body>
    <!-- Topbar -->
    <div class="topbar">
        <div class="logo-text">Dompo$</div>
        <a href="Dashboard.php" class="btn btn-light btn-sm">Home</a>
    </div>

    <div class="container">
        <div class="center-text">
            <a href="LaporanKeuangankasir.php">Detail Laporan</a>
        </div>
        <h3>LAPORAN KEUANGAN TOKO ANDA</h3>

        <?php if ($no_data): ?>
            <div class="no-data">
                <i class="bi bi-graph-up"></i>
                <h4>Belum Ada Data Keuangan</h4>
                <p>Mulai lakukan transaksi untuk melihat grafik keuangan</p>
            </div>
        <?php else: ?>
            <div class="swiper">
                <div class="swiper-wrapper">
                    <?php for ($i = 0; $i < count($chunks_bulan); $i++): ?>
                        <div class="swiper-slide">
                            <canvas id="chart<?= htmlspecialchars($i, ENT_QUOTES, 'UTF-8') ?>"></canvas>
                        </div>
                    <?php endfor; ?>
                </div>

                <!-- Tombol navigasi Swiper -->
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Variabel PHP dikirim ke JS -->
    <script>
        <?php if (!$no_data): ?>
        const chunksBulan = <?= json_encode($chunks_bulan) ?>;
        const chunksNominal = <?= json_encode($chunks_nominal) ?>;
        <?php endif; ?>

        // Inisialisasi Swiper
        const swiper = new Swiper('.swiper', {
            direction: 'horizontal',
            loop: true,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
        });

        <?php if (!$no_data): ?>
        // Buat chart untuk setiap slide
        chunksBulan.forEach((bulanArray, index) => {
            const ctx = document.getElementById(`chart${index}`);
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: bulanArray,
                    datasets: [{
                        label: 'Pemasukkan per Bulan',
                        data: chunksNominal[index],
                        backgroundColor: 'rgba(0, 0, 0, 1)',
                        borderColor: 'rgba(0, 0, 0, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Rp ' + context.raw.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });
        });
        <?php endif; ?>

        // Tambahkan token ke URL jika ada di localStorage
        document.addEventListener('DOMContentLoaded', function() {
            const token = localStorage.getItem('dompos_token');
            if (token) {
                // Tambahkan token ke link Detail Laporan
                const detailLink = document.querySelector('.center-text a');
                const url = new URL(detailLink.href, window.location.href);
                url.searchParams.set('token', token);
                detailLink.href = url.toString();
                
                // Tambahkan token ke tombol Home
                const homeBtn = document.querySelector('.btn-light');
                const homeUrl = new URL(homeBtn.href, window.location.href);
                homeUrl.searchParams.set('token', token);
                homeBtn.href = homeUrl.toString();
            }
        });
    </script>

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</body>

</html>