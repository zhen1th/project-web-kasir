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

$koneksiDatabase = new mysqli($host, $user, $password, $dbname);

if ($koneksiDatabase->connect_error) {
    die("Koneksi database gagal: " . $koneksiDatabase->connect_error);
}

// Ambil data dari request
$data = json_decode(file_get_contents('php://input'), true);
$total = $data['total'];

// Simpan data pemasukkan ke database
$query = "INSERT INTO pemasukkan (user_id, HIstori, Nominal) VALUES (?, NOW(), ?)";
$stmt = $koneksiDatabase->prepare($query);
$stmt->bind_param("ii", $user_id, $total);

if ($stmt->execute()) {
    echo "Pembayaran berhasil dicatat!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$koneksiDatabase->close();
?>