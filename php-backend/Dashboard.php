<?php
session_start();

// Periksa apakah sudah ada session yang valid
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    // Jika session valid, lanjutkan
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

    // Set cookie untuk session
    setcookie('PHPSESSID', session_id(), time() + 3600, '/');
}
// Jika tidak ada session dan tidak ada token
else {
    // Redirect ke login dengan parameter redirect ke Dashboard.php
    header('Location: http://localhost:3000/login?redirect=Dashboard.php');
    exit;
}

// Koneksi database
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "dompos";

$db = new mysqli($host, $user, $pass, $dbname);

if ($db->connect_error) {
    die("Koneksi database gagal: " . $db->connect_error);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dompo$ Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- FAVICON -->
    <link href="Assets/Dompo$Hitam.png" rel="icon" media="(prefers-color-scheme: light)" />

    <link href="Assets/Dompo$Putih.png" rel="icon" media="(prefers-color-scheme: dark)" />

    <style>
        body {
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            background-color: #fbebdaff;
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
            height: 130px;
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

        .welcome {
            font-size: 24px;
            font-weight: bold;
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

        .btn-light {
            background-color: #f8f9fa;
            border-color: #f8f9fa;
        }

        .welcome {
            color: #005246;
        }

        img {
            width: 200px;
            height: 60px;
            margin-left: 50px;
        }

        input {
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
            <img src="assets/image/Logo Dompos Navbar Orange.png">
        </div>

        <!-- Content -->
        <div class="d-flex flex-grow-1 justify-content-center align-items-center">
            <h2 class="welcome">HI, WELCOME TO DOMPO$</h2>
        </div>
    </div>

    <!-- Bootstrap JS (opsional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Simpan token di localStorage setelah login pertama kali
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');

        if (token) {
            localStorage.setItem('dompos_token', token);

            // Hapus token dari URL
            urlParams.delete('token');
            const newUrl = window.location.pathname + '?' + urlParams.toString();
            window.history.replaceState({}, document.title, newUrl);
        }

        // Jika ada token di localStorage, tambahkan ke semua link internal
        document.querySelectorAll('a').forEach(link => {
            if (link.href.includes('Dashboard.php') ||
                link.href.includes('Kasir.php') ||
                link.href.includes('produks.php') ||
                link.href.includes('KeuanganKasir.php')) {

                link.addEventListener('click', function(e) {
                    const token = localStorage.getItem('dompos_token');
                    if (token) {
                        const url = new URL(this.href);
                        url.searchParams.set('token', token);
                        this.href = url.toString();
                    }
                });
            }
        });
    </script>
</body>

</html>