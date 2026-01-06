<?php
session_start();

// Hapus semua data sesi
$_SESSION = array();

// Hapus cookie sesi
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Hancurkan sesi
session_destroy();

// Hapus token dari localStorage (via JavaScript) dan redirect ke Login Node.js
echo '<!DOCTYPE html>
<html>
<head>
    <title>Logging out...</title>
    <script>
        // Hapus token dari localStorage
        localStorage.removeItem("dompos_token");
        // Redirect ke Login Node.js
        window.location.href = "http://localhost:3000/login";
    </script>
</head>
<body>
    <p>Logging out...</p>
</body>
</html>';
exit;