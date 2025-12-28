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
    // Redirect ke login
    header('Location: http://localhost:3000/login?redirect=' . basename($_SERVER['PHP_SELF']));
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
  <link href="Assets/Dompo$Hitam.png" rel="icon"
    media="(prefers-color-scheme: light)" />

  <link href="Assets/Dompo$Putih.png" rel="icon"
    media="(prefers-color-scheme: dark)" />

  <style>
    body {
      height: 100vh;
      margin: 0;
      font-family: Arial, sans-serif;
      display: flex;
    }

    .sidebar {
      width: 200px;
      background-color: #212529;
      color: white;
      display: flex;
      flex-direction: column;
      padding: 1rem;
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
      background-color: #343a40;
    }

    .topbar {
      height: 50px;
      background-color: #212529;
      padding: 0 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
    
  </style>
</head>

<body class="d-flex">
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
      <div class="logo-text">Dompo$</div>
    </div>

    <!-- Content -->
    <div class="d-flex flex-grow-1 justify-content-center align-items-center bg-white">
      <div class="welcome text-dark">HI, WELCOME TO DOMPO$</div>
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