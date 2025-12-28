<?php
// Fungsi helper untuk verifikasi JWT di sisi PHP
// (Dalam implementasi ini kita menggunakan endpoint Node.js,
// tapi ini contoh jika ingin verifikasi di PHP)

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function verifyJWT($token, $secret) {
    try {
        $decoded = JWT::decode($token, new Key($secret, 'HS256'));
        return (array) $decoded;
    } catch (Exception $e) {
        return null;
    }
}
?>