<?php

/**
 * Logout Script
 * School Management System - APICUR TSS
 */
require_once '../config/config.php';

// Log activity before destroying session
if (isLoggedIn()) {
    $database = new Database();
    $conn = $database->getConnection();
    logActivity($conn, $_SESSION['user_id'], 'logout', 'user', $_SESSION['user_id'], 'User logged out');
}

// Clear session
session_unset();
session_destroy();

// Redirect to login
header('Location: login.php?logged_out=1');
exit();
