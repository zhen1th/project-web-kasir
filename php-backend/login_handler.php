<?php
session_start();

// Dalam praktik sebenarnya, validasi harus lebih ketat
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Validasi sederhana (dalam aplikasi nyata, gunakan metode yang lebih aman)
if (!empty($username) && !empty($password)) {
    // Simpan informasi login di sesi
    $_SESSION['loggedin'] = true;
    $_SESSION['username'] = $username;
    
    // Simpan timestamp login
    $_SESSION['login_time'] = time();
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Data login tidak valid']);
}
?>