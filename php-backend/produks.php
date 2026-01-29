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

$koneksiDatabase = new mysqli($host, $user, $password, $dbname);

if ($koneksiDatabase->connect_error) {
    die("Koneksi database gagal: " . $koneksiDatabase->connect_error);
}

// Proses penghapusan produk
if (isset($_GET['kode'])) {
    $kode = $_GET['kode'];

    // Pastikan produk yang dihapus milik user yang login
    $query = "DELETE FROM newproduct WHERE Id_Produk = ? AND user_id = ?";
    $stmt = $koneksiDatabase->prepare($query);
    $stmt->bind_param("ii", $kode, $user_id);

    if ($stmt->execute()) {
        $success_message = "Data Berhasil Terhapus";
    } else {
        $error_message = "Gagal menghapus data: " . $koneksiDatabase->error;
    }

    // Redirect untuk menghindari resubmission
    header("Location: produks.php?message=" . urlencode($success_message ?? $error_message));
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Data Produk Toko Anda</title>

    <!-- FAVICON -->
    <link href="Assets/Dompo$Hitam.png" rel="icon" media="(prefers-color-scheme: light)" />

    <link href="Assets/Dompo$Putih.png" rel="icon" media="(prefers-color-scheme: dark)" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            background-color: #ffe8d1;
        }

        .container {
            max-width: 1380px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar {
            width: 200px;
            background-color: #005246;
            color: white;
            padding: 1rem;
            position: fixed;
            height: 100vh;
            left: -200px;
            top: 0;
            transition: 0.3s ease;

        }

        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 15px 20px;
            display: block;
            margin: 5px 0;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .sidebar a:hover {
            background-color: #F37721;
        }

        .topbar {
            height: 70px;
            background-color: #005246;
            color: white;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
        }

        .topbar img {
            width: 140px;
            height: 40px;
            margin-left: 1060px;
        }

        .topbar a {
            width: 10px;
        }

        .logo-text {
            font-family: 'Georgia', serif;
            font-size: 20px;
            color: white;
        }

        .flex-grow-1 {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .form-container {
            background-color: #ffff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #dee2e6;
        }

        th {
            background-color: #212529;
            color: white;
            padding: 10px;
            text-align: left;
        }

        td {
            padding: 8px;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .btn {
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background-color: #f37721;
            color: white;
            border: none;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
            border: none;
        }

        .btn-warning {
            background-color: #ffc107;
            color: black;
            border: none;
        }

        .btn-light {
            background-color: black !important;
            color: white !important;
            border-color: black !important;
        }

        .btn-light:hover {
            background-color: white !important;
            color: black !important;
        }

        h3 {
            font-family: 'Segoe UI';
            font-weight: 700;
            text-align: center;
            margin-top: 20px;
            margin-bottom: 20px;
            color: #005246;
        }

        .search-container {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }

        .search-container input {
            padding: 8px;
            width: 300px;
            border-radius: 4px;
            border: 1px solid #ced4da;
        }

        .search-container button {
            padding: 8px 15px;
            background-color: #005246;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #212529;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        #toggleSidebar {
            display: none;
        }

        /* transisi dengan id input type checkbox ketika di klik (check) */
        #toggleSidebar:checked~.sidebar {
            left: 0;
        }

        /* Geser konten kalau sidebar buka */
        #toggleSidebar:checked~.flex-grow-1 {
            /* memberi jarak ketika sidebar dibuka */
            margin-left: 230px;
            transition: 0.3s;
        }

        .icon {
            cursor: pointer;
            color: #F37721;
        }
    </style>
</head>

<body class="d-flex">

    <input type="checkbox" id="toggleSidebar">

    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column p-3">
        <a href="Kasir.php"><i class="bi bi-credit-card me-2"></i>KASIR</a>
        <a href="produks.php"><i class="bi bi-box me-2"></i>PRODUK</a>
        <a href="KeuanganKasir.php"><i class="bi bi-cash-coin me-2"></i>KEUANGAN</a>
        <a href="logout.php"><i class="bi bi-box-arrow-left me-2"></i>LOG OUT</a>
    </div>

    <!-- Main content -->
    <div class="flex-grow-1 d-flex flex-column">
        <!-- Topbar -->
        <div class="topbar d-flex justify-content-between align-items-center">

            <label for="toggleSidebar" class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor"
                    class="bi bi-list" viewBox="0 0 16 16">
                    <path fill-rule="evenodd"
                        d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5" />
                </svg>
            </label>

        </div>

        <div class="content">
            <div class="container">



                <!-- Tampilkan pesan sukses/error -->
                <?php if (isset($_GET['message'])): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars(urldecode($_GET['message'])); ?>
                    </div>
                <?php endif; ?>

                <div class="form-container">
                    <form action="InputProduk.php" method="get" class="mb-3">
                        <button type="submit" class="btn btn-primary">Input Produk Baru</button>
                    </form>

                    <form action="SearchingProduct.php" method="POST" class="search-container">
                        <input type="text" placeholder="Cari Produk" name="caridata" value="<?php if (isset($_POST['caridata'])) {
                                                                                                echo htmlspecialchars($_POST['caridata']);
                                                                                            } ?>">
                        <button type="submit">Cari</button>
                    </form>
                </div>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Produk</th>
                            <th>Harga Produk</th>
                            <th>Kategori Produk</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Ambil data produk hanya untuk user yang login
                        $query = "SELECT * FROM newproduct WHERE user_id = ?";
                        $stmt = $koneksiDatabase->prepare($query);
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        $No = 1;
                        while ($menampilkandata = $result->fetch_assoc()) {
                            echo "
                        <tr>
                          <td>$No</td>
                          <td>" . htmlspecialchars($menampilkandata['Nama_Produk']) . "</td>
                          <td>Rp " . number_format($menampilkandata['Harga_Produk'], 0, ",", ".") . "</td>
                          <td>" . htmlspecialchars($menampilkandata['Kategori']) . "</td>
                          <td>
                            <a href='editproduk.php?kode=" . $menampilkandata['Id_Produk'] . "' class='btn btn-warning btn-sm'>Edit</a>
                            <a href='?kode=" . $menampilkandata['Id_Produk'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Apakah Anda yakin ingin menghapus produk ini?\")'>Hapus</a>
                          </td>
                        </tr>";
                            $No++;
                        }

                        // Jika tidak ada data
                        if ($No == 1) {
                            echo "<tr><td colspan='5' class='text-center'>Tidak ada data produk</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
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
                    if (link.href.includes('Kasir.php') ||
                        link.href.includes('produks.php') ||
                        link.href.includes('KeuanganKasir.php') ||
                        link.href.includes('Dashboard.php') ||
                        link.href.includes('InputProduk.php') ||
                        link.href.includes('editproduk.php')) {

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