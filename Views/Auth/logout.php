<?php
error_log('[Logout] Logout script accessed');
session_start();

// Unset all session variables
$_SESSION = [];
error_log('[Logout] Session variables cleared');

// Destroy the session
session_destroy();

// Clear session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirect to login page with absolute path
header("Location: login.php");
exit();
?>
