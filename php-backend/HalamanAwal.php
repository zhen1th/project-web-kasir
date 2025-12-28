<?php
// Halaman utama TIDAK memerlukan login
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Welcome To Dompo$</title>

  <!-- FAVICON -->
  <link href="Assets/Dompo$Hitam.png" rel="icon"
    media="(prefers-color-scheme: light)" />

  <link href="Assets/Dompo$Putih.png" rel="icon"
    media="(prefers-color-scheme: dark)" />

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
    <!-- Arahkan ke halaman login Node.js dengan parameter redirect -->
    <a href="http://localhost:3000/login?redirect=Dashboard.php">
      <i class="bi bi-credit-card me-2"></i>PAKET KASIR
    </a>
    <a href="http://localhost:3000/login?redirect=LaporanAnalitik.php">
      <i class="bi bi-cash-coin me-2"></i>PAKET LAPORAN
    </a>
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
    // Hapus token dari localStorage jika ada
    if (localStorage.getItem('dompos_token')) {
      localStorage.removeItem('dompos_token');
    }
  </script>
</body>
</html>