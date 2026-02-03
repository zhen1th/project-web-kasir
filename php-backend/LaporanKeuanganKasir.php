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
    ?>


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan</title>
    <link href="Assets/Dompo$Hitam.png" rel="icon" media="(prefers-color-scheme: light)" />
    <link href="Assets/Dompo$Putih.png" rel="icon" media="(prefers-color-scheme: dark)" />
    <link rel="stylesheet" href="Assets/DetailLaporan.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #ffe8d1;
        }

        .topbar {
            height: 100px;
            width: 100%;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
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
            max-width: 1400px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .Form {
            background-color: #ffff;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

         .search-container {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .search-container input {
            padding: 8px;
            width: 300px;
            border-radius: 4px;
            border: 1px solid #ced4da;
        }


        /* Modal Styles */
        .modal {
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

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            width: 300px;
            font-weight: bold;
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

        /* Styling untuk tabel */
        .tabel-laporan {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border: none;
        }

        .tabel-laporan th,
        .tabel-laporan td {
            border: none;
            padding: 8px;
            text-align: left;
        }

        .tabel-laporan th {
            background-color: #f2f2f2;
        }

        .hapus-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }

        .hapus-btn:hover {
            background-color: #c82333;
        }



        h3 {
            color: #f37721;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: #f37721;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .logo-text {
            font-family: 'Georgia', serif;
            font-size: 20px;
            color: white;
        }

        .btn-light {
            background-color: #f8f9fa;
            color: #212529;
            border: 1px solid #dee2e6;
        }

        .btn-light:hover {
            background-color: #e9ecef;
        }

        .btn {
            background-color: #F37721;
            color: white;
        }

        .btn:hover {
            background-color: #dd5c00;
            color: white;
        }
    </style>
</head>

<body>
   

    <!-- Topbar -->
    <div class="topbar">
        <button type="button" class="btn" onclick="document.location='KeuanganKasir.php'">← Kembali</button>
    </div>

    
    <div class="container">
        <div class="Form">
        <form method="POST" class="search-container">
            <input type="text" class="form-control me-2" placeholder="Cari Kode Transaksi..." name="carikode" 
            value="<?php if (isset($_POST['carikode'])) {
            echo htmlspecialchars($_POST['carikode']);
            } ?>" />
            <button type="submit" class="btn">
                <i class="bi bi-search"></i> Cari
            </button>
        </form>
    </div>
        <table class="tabel-laporan">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode Transaksi</th>
                    <th>Histori</th>
                    <th>Nominal Pemasukkan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php

                function cari($carikode , $user_id , $koneksidata)
                {
                    $searchquery = "SELECT * FROM pemasukkan WHERE user_id = $user_id AND Kode_Pemasukkan LIKE '%$carikode%'";
                    return mysqli_query($koneksidata,$searchquery);
                }

                if (isset($_POST['carikode'])) {
                    $searching = $_POST['carikode'];
                    $Query = cari($searching , $user_id , $koneksidata);
                }else{
                    // Query data pemasukkan hanya untuk user yang login
                    $Query = mysqli_query($koneksidata, "SELECT * FROM pemasukkan WHERE user_id = $user_id");
                }

               $No = 1;
        
                if (mysqli_num_rows($Query) > 0) {
                    while ($showdata = mysqli_fetch_array($Query)) {
                        echo "
                        <tr>
                            <td>{$No}</td>
                            <td>{$showdata['Kode_Pemasukkan']}</td>
                            <td>{$showdata['HIstori']}</td>
                            <td>Rp " . number_format($showdata['Nominal'], 0, ',', '.') . "</td>
                            <td>
                                <form method='POST'>
                                    <button type='submit' class='hapus-btn' name='hapus' value='{$showdata['Kode_Pemasukkan']}'>Hapus</button>
                                </form>
                            </td>
                        </tr>";
                        $No++;
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align: center;'>Tidak ada data pemasukkan</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    <div class="modal" id="successModal">
        <div class="modal-content">
            <p>Data Berhasil Dihapus!</p>
            <button class="close-btn" onclick="closeModal()">Sip!</button>
        </div>
    </div>

    <script>
        function showModal() {
            document.getElementById("successModal").style.display = "flex";
        }

        function closeModal() {
            document.getElementById("successModal").style.display = "none";
            // Redirect dengan menambahkan token jika ada
            const token = localStorage.getItem('dompos_token');
            if (token) {
                window.location.href = "LaporanKeuanganKasir.php?token=" + token;
            } else {
                window.location.href = "LaporanKeuanganKasir.php";
            }
        }

        // Tambahkan token ke URL jika ada di localStorage
        document.addEventListener('DOMContentLoaded', function() {
            const token = localStorage.getItem('dompos_token');
            if (token) {
                // Tambahkan token ke link Kembali
                const backLink = document.querySelector('.back-link');
                const url = new URL(backLink.href, window.location.href);
                url.searchParams.set('token', token);
                backLink.href = url.toString();

                // Tambahkan token ke tombol Home
                const homeBtn = document.querySelector('.btn-light');
                const homeUrl = new URL(homeBtn.href, window.location.href);
                homeUrl.searchParams.set('token', token);
                homeBtn.href = homeUrl.toString();
            }
        });
    </script>

    <?php
    // Proses penghapusan data
    if (isset($_POST['hapus'])) {
        $kode = $_POST['hapus'];
        $deleteQuery = "DELETE FROM pemasukkan WHERE Kode_Pemasukkan = '{$kode}' AND user_id = $user_id";

        if (mysqli_query($koneksidata, $deleteQuery)) {
            echo "<script>showModal();</script>";
        } else {
            echo "<script>alert('Error menghapus data: " . mysqli_error($koneksidata) . "');</script>";
        }
    }

    // Tutup koneksi
    mysqli_close($koneksidata);
    ?>
</body>

</html>