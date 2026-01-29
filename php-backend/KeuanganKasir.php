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
    // Redirect ke login dengan parameter redirect ke Dashboard.php
    header('Location: http://localhost:3000/login?redirect=Dashboard.php');
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

// Pie Chart

// Untuk Pemasukkan
$Query_Pemasukkan = mysqli_query($koneksidata, "SELECT COALESCE(SUM(nominal),0) as total_pemasukkan
                FROM pemasukkan WHERE user_id = $user_id");

$RowP = mysqli_fetch_assoc($Query_Pemasukkan);
$Total_Pemasukkan = $RowP['total_pemasukkan'] ? (int)$RowP['total_pemasukkan'] : 0;
// apakah baris $RowP ada isinya ? jika tidak ada maka cetak 0

// Untuk Pengeluaran
$Query_Pengeluaran = mysqli_query($koneksidata, "SELECT jenis_pengeluaran as jenis , 
                COALESCE(SUM(nominal),0) as total_pengeluaran ,  COUNT(*) as jumlah_transaksi
                FROM pengeluaran 
                WHERE user_id = $user_id 
                GROUP BY jenis_pengeluaran 
                ORDER BY total_pengeluaran DESC");


// Kita Proses Datanya

$Data_Pengeluaran = []; // Array kosong untuk menyimpan data
$total_pengeluaran = 0; // Inisialisasi biar mulai dari 0

while ($NewRow = mysqli_fetch_assoc($Query_Pengeluaran)) {
    $jenis = $NewRow['jenis']; // untuk akses kolom jenis pengeluaran
    $newTotal = (int)$NewRow['total_pengeluaran']; // ambil total nominal dari baris data trus di konversi ke integer

    $Data_Pengeluaran[] =
        [
            'jenis' => $jenis,
            'total_pengeluaran' => $newTotal
        ];

    $total_pengeluaran += $newTotal;
};

// Data Untuk Pie Chart
// Warna Pie Chart 

$Warna_Chart =  ['#4CAF50'];
// Perbaiki array warna
$Warna_Pengeluaran = [
    '#FF6384',
    '#36A2EB',
    '#FFCE56',
    '#4BC0C0',
    '#9966FF',
    '#FF9F40',
    '#C9CBCF',
    '#FF6384',
    '#8AC926',
    '#1982C4',
    '#6A4C93',
    '#F15BB5'
];
// Label dan data pertama untuk pie chartnya
$labels_data = ['pemasukkan'];
$data_piechart = [$Total_Pemasukkan];
$background_colors = ['#4CAF50'];

// Data pengeluaran per kategori
$color_index = 0;
foreach ($Data_Pengeluaran as $pengeluaran) {
    $labels_data[] = $pengeluaran['jenis'];
    $data_piechart[] = $pengeluaran['total_pengeluaran'];
    $background_colors[] = $Warna_Pengeluaran[$color_index % count($Warna_Pengeluaran)]; // <-- PERHATIKAN INI!
    $color_index++;
}

$total_keseluruhan = array_sum($data_piechart);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan</title>

    <!-- FAVICON -->
    <link href="Assets/Dompo$Hitam.png" rel="icon" media="(prefers-color-scheme: light)" />
    <link href="Assets/Dompo$Putih.png" rel="icon" media="(prefers-color-scheme: dark)" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Link CSS
    <link rel="stylesheet" href="Assets/GrafikLaporan.css"> -->
    <!-- ini tak offkan dulu gais kelihatannya ga kepake nanti biar di cek orang FE ye -->

    <!-- Hubungkan ke library CDN Chart.js dan Swiper -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #FFE8D1;
            margin: 0;
            padding: 20px;
        }

        .topbar {
            height: 70px;
            width: 100%;
            border-radius: 20px;
            background-color: #005246;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: relative;
            transition: 0.3s ease;
        }


        .container {
            max-width: 1900px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .pemasukan {
            width: 350px;
            height: 120px;
            background-color: white;
            border-bottom-right-radius: 10px;
            border-top-right-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .judul {
            color: #005246;
            font-size: 20px;
            margin-top: -110px;
            margin-left: 20px;
        }

        .rupiah {
            font-size: 17px;
            margin-top: -10px;
            margin-left: 25px;
        }

        .pemasukan a {
            text-decoration: none;
            color: inherit;
            margin-left: 20px;
        }

        .pemasukan-dalam {
            width: 10px;
            height: 120px;
            background-color: #005246;
            border-bottom-right-radius: 10px;
            border-top-right-radius: 10px;
        }

        .pengeluaran {
            width: 350px;
            height: 120px;
            background-color: white;
            border-bottom-right-radius: 10px;
            border-top-right-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: -120px;
            margin-left: 370px;
        }

        .pengeluaran-dalam {
            width: 10px;
            height: 120px;
            background-color: #ff0000;
            border-bottom-right-radius: 10px;
            border-top-right-radius: 10px;
        }

        .pengeluaran a {
            text-decoration: none;
            color: inherit;
            margin-left: 20px;
        }

        .selisih {
            width: 350px;
            height: 120px;
            background-color: white;
            border-bottom-right-radius: 10px;
            border-top-right-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: -120px;
            margin-left: 740px;
        }

        .selisih-dalam {
            width: 10px;
            height: 120px;
            background-color: #F37721;
            border-bottom-right-radius: 10px;
            border-top-right-radius: 10px;
        }

        .topbar-bungkus {
            width: 750px;
            height: 40px;
            background-color: #005246;
            border-radius: 4px;
        }

        .bungkus-grafik {
            width: 750px;
            height: 480px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            background-color: #ffffff;
            border-radius: 4px;
        }

        .grafik {
            width: 730px;
            margin-left: 10px;
            margin-top: 10px
        }

        .bungkus-lingkaran {
            width: 550px;
            height: 480px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
            margin-top: -480px;
            margin-left: 785px;
            border-radius: 4px;
        }

        .topbar-lingkaran {
            width: 550px;
            height: 40px;
            background-color: #005246;
            border-radius: 4px;
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
            color: #005246;
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

        .btn {
            background-color: #F37721;
            color: white;
        }

        .btn:hover {
            background-color: #dd5c00;
            color: white;
        }

        /* Pie Chart */

        /* Untuk Tooltip Custom */
        .chart-tooltip {
            position: absolute;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            pointer-events: none;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .tooltip-color {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 2px;
            margin-right: 6px;
            vertical-align: middle;
        }

        .total-pengeluaran {
            color: #ff0000;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <!-- Topbar -->
    <div class="topbar">
        <button type="button" class="btn" onclick="document.location='Dashboard.php'">← Kembali</button>
    </div>

    <div class="container">


        <div class="pemasukan">
            <div class="pemasukan-dalam"></div>
            <p class="judul">Pemasukkan</p>
            <?php
            $Query_Total_Pemasukkan = mysqli_query($koneksidata, "SELECT SUM(nominal) AS total 
               FROM pemasukkan WHERE user_id = $user_id");

            $row_total_pemasukkan = mysqli_fetch_assoc($Query_Total_Pemasukkan);
            //    $row_total mengambil satu baris dari kolom nominal
            $TotalPemasukkan = $row_total_pemasukkan['total'] ? (int)$row_total_pemasukkan['total'] : 0;
            //  singkatan dari if else jika kolom total ada isinya dan tidak null maka
            // akan mengambil satu baris datanya jika kosong maka akan 0
            ?>
            <p class="rupiah">Rp <?php echo number_format($TotalPemasukkan) ?></p>
            <div class="cek-detail">
                <a href="LaporanKeuanganKasir.php">Cek Detail</a>
            </div>
        </div>

        <div class="pengeluaran">
            <div class="pengeluaran-dalam"></div>
            <p class="judul" style="color: #ff0000;">Pengeluaran</p>
            <?php
            $Query_Total_Pengeluaran = mysqli_query($koneksidata, "SELECT SUM(nominal) AS totalpengeluaran
            FROM pengeluaran WHERE user_id = $user_id");

            $row_total_pengeluaran = mysqli_fetch_assoc($Query_Total_Pengeluaran);
            $TotalPengeluaran = $row_total_pengeluaran['totalpengeluaran'] ? (int)$row_total_pengeluaran['totalpengeluaran'] : 0;
            ?>
            <p class="rupiah">Rp <?php echo number_format($TotalPengeluaran) ?></p>
            <div class="cek-detail">
                <a href="LaporanPengeluaran.php">Cek Detail</a>
            </div>
        </div>

        <div class="selisih">
            <div class="selisih-dalam"></div>
            <p class="judul" style="color: #F37721;">Selisih</p>
            <?php
            $Selisih = $TotalPemasukkan - $TotalPengeluaran;

            if ($Selisih < 0) {
            ?>
                <p class="rupiah" style="color: #ff0000;"> Rp <?php echo number_format($Selisih) ?></p>
            <?php
            } else {
            ?>
                <p class="rupiah" style="color: #009a12;"> Rp +<?php echo number_format($Selisih) ?></p>
            <?php
            }
            ?>
        </div>

        <div class="bungkus-grafik">
            <div class="topbar-bungkus"></div>
            <div class="grafik">
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
            </div> <!-- Tutup Div Grafik-->
        </div> <!-- Tutup Div Bungkus Grafik -->
        <div class="bungkus-lingkaran">
            <div class="topbar-lingkaran"></div>
            <div class="grafik-lingkaran">
                <canvas id="piechart" width="350" height="350"></canvas>
                <div id="customTooltip" class="chart-tooltip"></div>
            </div>

            <!-- Legenda -->
            <div style="margin: top 20px;; max-height:100px; overflow-y:auto;">
                <div style="display:flex; flex-wrap:wrap; gap:10px; justify-content:center;">
                    <?php
                    $index = 0;
                    foreach ($labels_data as $label):
                        $presentase = ($total_keseluruhan > 0) ?
                            round(($data_piechart[$index] / $total_keseluruhan) * 100, 1) : 0;
                    ?>
                        <div style="display:flex; align-items:center; margin-right:15px;">
                            <div style="width: 12px; height: 12px; background: <?php echo $background_colors[$index]; ?>; border-radius: 2px; margin-right: 5px;"></div>
                            <span style="font-size: 12px;">
                                <?php echo htmlspecialchars($label); ?> (<?php echo $presentase; ?>%)
                            </span>
                        </div>
                    <?php $index++;
                    endforeach; ?>
                </div>
            </div>
        </div>
    </div> <!-- Tutup Div Container -->

    <!-- Grafik -->
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
                            backgroundColor: '#2c2c2c',
                            borderColor: '#2c2c2c',
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
                const detailLink = document.querySelector('.cek-detail a');
                const url = new URL(detailLink.href, window.location.href);
                url.searchParams.set('token', token);
                detailLink.href = url.toString();


            }
        });
    </script>

    <!-- PIE CHART SCRIPT  -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('piechart').getContext('2d');
            const customTooltip = document.getElementById('customTooltip');

            // Data dari PHP (via JSON encode)
            const pieData = {
                labels: <?php echo json_encode($labels_data); ?>,
                datasets: [{
                    data: <?php echo json_encode($data_piechart); ?>,
                    backgroundColor: <?php echo json_encode($background_colors); ?>,
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 15
                }]
            };

            // Hitung total untuk persentase
            const total = pieData.datasets[0].data.reduce((a, b) => a + b, 0);

            // Buat Pie Chart dengan tooltip hover
            const pieChart = new Chart(ctx, {
                type: 'pie',
                data: pieData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: false, // Nonaktifkan tooltip default
                            external: function(context) {
                                const tooltipModel = context.tooltip;

                                // Sembunyikan tooltip jika tidak ada data
                                if (tooltipModel.opacity === 0) {
                                    customTooltip.style.opacity = 0;
                                    return;
                                }

                                // Ambil data dari segment yang dihover
                                if (tooltipModel.dataPoints && tooltipModel.dataPoints.length > 0) {
                                    const dataPoint = tooltipModel.dataPoints[0];
                                    const index = dataPoint.dataIndex;
                                    const label = pieData.labels[index];
                                    const value = pieData.datasets[0].data[index];
                                    const color = pieData.datasets[0].backgroundColor[index];

                                    // Hitung persentase
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;

                                    // Format Rupiah
                                    const formattedValue = new Intl.NumberFormat('id-ID', {
                                        style: 'currency',
                                        currency: 'IDR',
                                        minimumFractionDigits: 0
                                    }).format(value);

                                    // Update konten tooltip
                                    customTooltip.innerHTML = `
                                    <div style="display: flex; align-items: center; margin-bottom: 5px;">
                                        <div style="width: 12px; height: 12px; background: ${color}; border-radius: 2px; margin-right: 6px;"></div>
                                        <strong>${label}</strong>
                                    </div>
                                    <div style="margin-bottom: 3px;">
                                        <strong>${formattedValue}</strong>
                                    </div>
                                    <div style="font-size: 12px; color: #ccc;">
                                        ${percentage}%
                                    </div>
                                `;

                                    // Posisi tooltip
                                    const position = context.chart.canvas.getBoundingClientRect();
                                    const left = position.left + window.pageXOffset + tooltipModel.caretX;
                                    const top = position.top + window.pageYOffset + tooltipModel.caretY;

                                    customTooltip.style.left = (left - customTooltip.offsetWidth / 2) + 'px';
                                    customTooltip.style.top = (top - customTooltip.offsetHeight - 10) + 'px';
                                    customTooltip.style.opacity = 1;
                                }
                            }
                        }
                    },
                    // Interaksi hover
                    interaction: {
                        intersect: true,
                        mode: 'nearest'
                    },
                    // Animasi
                    animation: {
                        animateScale: true,
                        animateRotate: true,
                        duration: 1000
                    },
                    onHover: function(event, chartElements) {
                        // Ubah cursor jadi pointer saat hover
                        if (chartElements.length > 0) {
                            ctx.canvas.style.cursor = 'pointer';
                        } else {
                            ctx.canvas.style.cursor = 'default';
                        }
                    }
                }
            });

            // Sembunyikan tooltip saat mouse keluar
            ctx.canvas.addEventListener('mouseleave', function() {
                customTooltip.style.opacity = 0;
                ctx.canvas.style.cursor = 'default';
            });

            // Klik pada segment (opsional)
            ctx.canvas.addEventListener('click', function(evt) {
                const points = pieChart.getElementsAtEventForMode(evt, 'nearest', {
                    intersect: true
                }, true);
                if (points.length) {
                    const index = points[0].index;
                    const label = pieData.labels[index];
                    const value = pieData.datasets[0].data[index];
                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;

                    alert(`${label}: Rp ${value.toLocaleString('id-ID')} (${percentage}%)`);
                }
            });
        });
    </script>


    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</body>

</html>