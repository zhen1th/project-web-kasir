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
        header('Location: http://localhost:3000/login?redirect=editproduk.php');
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
    header('Location: http://localhost:3000/login?redirect=editproduk.php');
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

// Ambil kode produk dari URL
$kode = $_GET['kode'] ?? 0;

// Ambil data produk dengan memastikan produk milik user yang login
$query = "SELECT * FROM newproduct WHERE Id_Produk = ? AND user_id = ?";
$stmt = $koneksiDatabase->prepare($query);
$stmt->bind_param("ii", $kode, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Produk tidak ditemukan atau bukan milik user
    header("Location: produks.php?error=Produk tidak ditemukan");
    exit;
}

$data = $result->fetch_assoc();

// Proses update ketika tombol Simpan diklik
if (isset($_POST['Simpan'])) {
    $nama = mysqli_real_escape_string($koneksiDatabase, $_POST['Nama_Produk']);
    $harga = mysqli_real_escape_string($koneksiDatabase, $_POST['Harga_Produk']);
    $kategori = mysqli_real_escape_string($koneksiDatabase, $_POST['Kategori']);

    // Update data dengan memastikan produk milik user yang login
    $query = "UPDATE newproduct SET 
                Nama_Produk = '$nama', 
                Harga_Produk = '$harga', 
                Kategori = '$kategori' 
              WHERE Id_Produk = '$kode' AND user_id = '$user_id'";

    if (mysqli_query($koneksiDatabase, $query)) {
        // Redirect ke halaman yang sama dengan parameter sukses
        header("Location: " . $_SERVER['PHP_SELF'] . "?kode=$kode&sukses=1");
        exit;
    } else {
        echo "<script>alert('Gagal mengubah produk: " . mysqli_error($koneksiDatabase) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Produk</title>

    <!-- FAVICON -->
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
        }

        h3 {
            text-align: center;
            margin-bottom: 20px;
            color: #f8f9fa;
        }

        a {
            display: inline-block;
            margin-bottom: 15px;
            color: #f8f9fa;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 4px;
            background-color: #495057;
        }

        a:hover {
            background-color: #6c757d;
            text-decoration: none;
            color: white;
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
            margin-top: 10px;
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
            padding: 10px 20px;
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
    <a href="produks.php">← Kembali</a>
    <h3>Edit Data Produk</h3>
    <form method="post" action="">
        <input type="text" placeholder="Nama Produk" name="Nama_Produk" required value="<?= htmlspecialchars($data['Nama_Produk']); ?>" />
        <input type="number" placeholder="Harga Produk" name="Harga_Produk" required value="<?= htmlspecialchars($data['Harga_Produk']); ?>" />
        <select name="Kategori" required>
            <option value="">-- Pilih Kategori --</option>
            <option value="Makanan" <?= ($data['Kategori'] == "Makanan") ? "selected" : ""; ?>>Makanan</option>
            <option value="Minuman" <?= ($data['Kategori'] == "Minuman") ? "selected" : ""; ?>>Minuman</option>
            <option value="Snack" <?= ($data['Kategori'] == "Snack") ? "selected" : ""; ?>>Snack</option>
            <option value="Lain-Lain" <?= ($data['Kategori'] == "Lain-Lain") ? "selected" : ""; ?>>Lain-Lain</option>
        </select>
        <button type="submit" name="Simpan">Simpan</button>
    </form>
</div>

<!-- Modal sukses -->
<div class="modal" id="successModal">
    <div class="modal-content">
        <p>Data Produk Berhasil Diubah!</p>
        <button class="close-btn" onclick="closeModal()">Tutup</button>
    </div>
</div>

<script>
    function closeModal() {
        document.getElementById("successModal").style.display = "none";
        window.location.href = "produks.php"; // Redirect ke halaman produk
    }

    <?php if (isset($_GET['sukses']) && $_GET['sukses'] == 1): ?>
    window.addEventListener('DOMContentLoaded', function () {
        document.getElementById("successModal").style.display = "flex";
    });
    <?php endif; ?>
    
    // Tambahkan token ke URL jika ada di localStorage
    document.addEventListener('DOMContentLoaded', function() {
        const token = localStorage.getItem('dompos_token');
        if (token) {
            // Tambahkan token ke form action
            const form = document.querySelector('form');
            const url = new URL(form.action, window.location.href);
            url.searchParams.set('token', token);
            form.action = url.toString();
            
            // Tambahkan token ke link kembali
            const backLink = document.querySelector('a');
            const backUrl = new URL(backLink.href);
            backUrl.searchParams.set('token', token);
            backLink.href = backUrl.toString();
        }
    });
</script>

</body>
</html>