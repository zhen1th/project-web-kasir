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
        header('Location: http://localhost:3000/login?redirect=inputproduk.php');
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
    header('Location: http://localhost:3000/login?redirect=inputproduk.php');
    exit;
}

// Koneksi database dompos
$host = "localhost";
$user = "root";
$password = "";
$dbname = "dompos";

$koneksiDatabase = new mysqli($host, $user, $password, $dbname);

if ($koneksiDatabase->connect_error) {
    die("Koneksi database gagal: " . $koneksiDatabase->connect_error);
}

// Proses input produk
if (isset($_POST['Tambahkan_Produk'])) {
    $nama = mysqli_real_escape_string($koneksiDatabase, $_POST['Nama_Produk']);
    $harga = mysqli_real_escape_string($koneksiDatabase, $_POST['Harga_Produk']);
    $kategori = mysqli_real_escape_string($koneksiDatabase, $_POST['Kategori']);

    // Query dengan menyertakan user_id untuk isolasi data
    $query = "INSERT INTO newproduct (user_id, Nama_Produk, Harga_Produk, Kategori)
              VALUES ('$user_id', '$nama', '$harga', '$kategori')";

    if (mysqli_query($koneksiDatabase, $query)) {
        // Redirect agar modal muncul (dengan ?sukses=1)
        header("Location: " . $_SERVER['PHP_SELF'] . "?sukses=1");
        exit;
    } else {
        echo "<script>alert('Gagal menambahkan produk: " . mysqli_error($koneksiDatabase) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Produk Toko Anda</title>
    <link href="Assets/Dompo$Hitam.png" rel="icon" media="(prefers-color-scheme: light)" />
    <link href="Assets/Dompo$Putih.png" rel="icon" media="(prefers-color-scheme: dark)" />
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 20px;
        }

        .container {
            background-color: #212529;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: auto;
            text-align: center;
        }

        h3 {
            color: #f8f9fa;
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            background-color: #f8f9fa;
            color: black;
            border: none;
            cursor: pointer;
            font-weight: bold;
            padding: 12px;
            border-radius: 6px;
        }

        button:hover {
            background-color: #212529;
            color: white;
        }

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
        
        .back-btn {
            display: inline-block;
            margin-top: 15px;
            color: #f8f9fa;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 4px;
            background-color: #495057;
        }
        
        .back-btn:hover {
            background-color: #6c757d;
        }
    </style>
</head>

<body>

    <div class="container">
        <h3>Input Produk</h3>
        <form method="post" action="">
            <input type="text" placeholder="Nama Produk" name="Nama_Produk" required />
            <input type="number" placeholder="Harga Produk" name="Harga_Produk" required />
            <select name="Kategori" required>
                <option value="">-- Pilih Kategori --</option>
                <option value="Makanan">Makanan</option>
                <option value="Minuman">Minuman</option>
                <option value="Snack">Snack</option>
                <option value="Lain-Lain">Lain-Lain</option>
            </select>
            <button type="submit" name="Tambahkan_Produk">Tambahkan Produk</button>
        </form>
        
        <a href="produks.php" class="back-btn">Kembali ke Daftar Produk</a>
    </div>

    <!-- Modal -->
    <div class="modal" id="successModal">
        <div class="modal-content">
            <p>Data Produk Berhasil Ditambahkan!</p>
            <button class="close-btn" onclick="closeModal()">Tutup</button>
        </div>
    </div>

    <script>
        function closeModal() {
            document.getElementById("successModal").style.display = "none";
            window.location.href = "produks.php";
        }

        // Jika dari PHP ingin menampilkan modal:
        <?php if (isset($_GET['sukses']) && $_GET['sukses'] == 1): ?>
        window.addEventListener('DOMContentLoaded', function () {
            document.getElementById("successModal").style.display = "flex";
        });
        <?php endif; ?>
        
        // Tambahkan token ke URL jika ada di localStorage
        document.addEventListener('DOMContentLoaded', function() {
            const token = localStorage.getItem('dompos_token');
            if (token) {
                // Tambahkan token ke semua form action
                const forms = document.querySelectorAll('form');
                forms.forEach(form => {
                    const url = new URL(form.action, window.location.href);
                    url.searchParams.set('token', token);
                    form.action = url.toString();
                });
                
                // Tambahkan token ke semua link
                const links = document.querySelectorAll('a');
                links.forEach(link => {
                    if (link.href.includes('produks.php') || 
                        link.href.includes('kasir.php') || 
                        link.href.includes('Dashboard.php')) {
                        
                        const url = new URL(link.href);
                        url.searchParams.set('token', token);
                        link.href = url.toString();
                    }
                });
            }
        });
    </script>

</body>

</html>