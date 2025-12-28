<?php
session_start();
header('Content-Type: application/json');

$token = $_POST['token'] ?? '';
$redirect = $_POST['redirect'] ?? 'HalamanAwal.php';

if (empty($token)) {
    echo json_encode(['success' => false, 'message' => 'Token tidak valid']);
    exit;
}

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
    echo json_encode(['success' => false, 'message' => 'Token tidak valid']);
    exit;
}

// Token valid, simpan informasi user di session
$_SESSION['loggedin'] = true;
$_SESSION['username'] = $result['username'];
$_SESSION['user_id'] = $result['user_id'];
$_SESSION['login_time'] = time();

echo json_encode(['success' => true, 'redirect' => $redirect]);
?>