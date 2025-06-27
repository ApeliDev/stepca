<?php
session_start();
require_once 'includes/auth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

logoutUser();
header('Location: login.php?logout=success');
exit;
?>
