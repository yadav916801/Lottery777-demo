<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function check_auth($allowed_role) {
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $allowed_role) {
        header("Location: ../login.php");
        exit;
    }
}
?>
