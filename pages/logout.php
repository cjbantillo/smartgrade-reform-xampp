<?php
// pages/logout.php
session_start();

// Clear all session variables
$_SESSION = [];

// Destroy the session completely
session_destroy();

// Optional: clear the session cookie (extra safety)
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

// Redirect to login page (or index)
header("Location: ../index.php");
exit;