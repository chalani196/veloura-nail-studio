<?php
session_start(); // Session එක ආරම්භ කිරීම

// Session variables සියල්ල ඉවත් කිරීම
$_SESSION = array();

// Session එක සම්පූර්ණයෙන්ම destroy කිරීම
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Home page එකට හෝ Login page එකට redirect කිරීම (ඔබේ home page එකේ නම මෙතැනට දෙන්න, උදාහරණයක් ලෙස index.php හෝ owner_login.php)
header("Location: index.php"); 
exit();
?>