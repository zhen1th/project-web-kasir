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

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h3 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: black;
            font-weight: bold;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .btn-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .btn {
            background-color: #2c2c2c;
            border: none;
            color: white;
            padding: 10px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
            transition: background 0.2s ease-in-out;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            background-color: #f9f9f9;
            color: black;
        }

        .hapus-btn {
            background-color: #e74c3c;
        }

        .hapus-btn:hover {
            background-color: #c0392b;
        }

        table.tabel-laporan {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            border: 1px solid #ddd;
        }

        .tabel-laporan thead {
            background-color: #f2f2f2;
        }

        .tabel-laporan th,
        .tabel-laporan td {
            padding: 14px 16px;
            text-align: left;
            border: 1px solid #ddd;
        }

        .tabel-laporan th {
            font-weight: bold;
            color: #333;
        }

        .tabel-laporan tbody tr:hover {
            background-color: #f9f9f9;
        }


        /* Delete Styles */
        .Delete {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .Delete-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            width: 300px;
        }

        .close-btn {
            background-color: #212529;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            border-radius: 6px;
            margin-top: 10px;
        }

        .close-btn:hover {
            background-color: #f8f9fa;
            color: black;
        }
    </style>
</head>

<body>

    <div class="container">
        <a href="LaporanAnalitik.php" class="back-link">← Kembali</a>
        <h3>LAPORAN KEUANGAN TOKO ANDA</h3>

        <div class="btn-container">
            <a href="InputLaporan.php" class="btn">Input Laporan Baru</a>
        </div>

        <!-- /* Delete */ -->
        <div class="Delete" id="successDelete">
            <div class="Delete-content">
                <p>Data Berhasil Dihapus !</p><button class="close-btn" onclick="closeDelete()">Sip!</button>
            </div>
        </div>

        <script>
            function showDelete() {
                document.getElementById("successDelete").style.display = "flex";
            }

            function closeDelete() {
                document.getElementById("successDelete").style.display = "none";
                window.location.href = "Laporan.php"; // Redirect ke halaman laporan
            }
        </script>
        <table class="tabel-laporan">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Histori</th>
                    <th>Nominal Pemasukkan</th>
                    <th colspan="2">Aksi</th>
                </tr>
            </thead>
            <tbody><?php
                    include "service/dbkeuangan1.php";

                    $No = 1;
                    $Query = mysqli_query($koneksidata, "SELECT * FROM laporan_analitik");
                    while ($showdata = mysqli_fetch_array($Query)) {
                        echo "
                <tr>
                    <td>{$No}</td>
                    <td>{$showdata['Histori_Pemasukkan']}</td>
                    <td>Rp " . number_format($showdata['Nominal_Pemasukkan'], 0, ",", ".") . "</td>
                    <td><a href='?kode={$showdata['Id_Pemasukkan']}' class='btn hapus-btn'>Hapus</a></td>
                    <td><a href='EditLaporan.php?kode={$showdata['Id_Pemasukkan']}' class='btn'>Edit</a></td>
                </tr>";
                        $No++;
                    }
                    ?></tbody>
        </table><?php
                if (isset($_GET['kode'])) {
                    mysqli_query($koneksidata, "DELETE FROM laporan_analitik WHERE Id_Pemasukkan = '{$_GET['kode']}'");
                    echo "<script>showDelete()</script>";
                   
                }
                ?>
    </div>
</body>

</html>