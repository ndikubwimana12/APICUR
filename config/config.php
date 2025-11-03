<?php

/**
 * General Configuration
 * School Management System - APICUR TSS
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Base URL Configuration
define('BASE_URL', 'http://localhost/SchoolManagementSystem/');
define('SITE_NAME', 'APICUR TSS');
define('SITE_TITLE', 'APICUR TSS - School Management System');

// File Upload Configuration
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('ALLOWED_DOCUMENT_TYPES', ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);

// Pagination
define('RECORDS_PER_PAGE', 20);

// Current Academic Year and Term
define('CURRENT_ACADEMIC_YEAR', '2024');
define('CURRENT_TERM', '1');

// Date Format
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'd/m/Y');

// Error Reporting (Set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Africa/Dar_es_Salaam');

// Include database configuration
require_once __DIR__ . '/database.php';

/**
 * Check if user is logged in
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user data
 */
function getCurrentUser()
{
    if (!isLoggedIn()) {
        return null;
    }

    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'full_name' => $_SESSION['full_name'],
        'role' => $_SESSION['role'],
        'email' => $_SESSION['email']
    ];
}

/**
 * Check if user has specific role
 */
function hasRole($role)
{
    return isLoggedIn() && $_SESSION['role'] === $role;
}

/**
 * Check if user has any of the specified roles
 */
function hasAnyRole($roles)
{
    if (!isLoggedIn()) {
        return false;
    }
    return in_array($_SESSION['role'], $roles);
}

/**
 * Redirect to login if not logged in
 */
function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'auth/login.php');
        exit();
    }
}

/**
 * Redirect to dashboard based on role
 */
function redirectToDashboard()
{
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'auth/login.php');
        exit();
    }

    $role = $_SESSION['role'];
    $dashboards = [
        'admin' => 'admin/dashboard.php',
        'secretary' => 'secretary/dashboard.php',
        'teacher' => 'teacher/dashboard.php',
        'dos' => 'dos/dashboard.php',
        'head_teacher' => 'head_teacher/dashboard.php',
        'accountant' => 'accountant/dashboard.php',
        'discipline_officer' => 'discipline/dashboard.php'
    ];

    $dashboard = isset($dashboards[$role]) ? $dashboards[$role] : 'auth/login.php';
    header('Location: ' . BASE_URL . $dashboard);
    exit();
}

/**
 * Sanitize input
 */
function sanitize($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Format date for display
 */
function formatDate($date, $format = DISPLAY_DATE_FORMAT)
{
    if (empty($date)) return '';
    $timestamp = strtotime($date);
    return $timestamp ? date($format, $timestamp) : '';
}

/**
 * Generate random string
 */
function generateRandomString($length = 10)
{
    return bin2hex(random_bytes($length / 2));
}

/**
 * Format file size for display
 */
function formatFileSize($bytes)
{
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

/**
 * Log activity
 */
function logActivity($conn, $user_id, $action, $entity_type = null, $entity_id = null, $description = null)
{
    try {
        $query = "INSERT INTO activity_logs (user_id, action, entity_type, entity_id, description, ip_address, user_agent)
                  VALUES (:user_id, :action, :entity_type, :entity_id, :description, :ip_address, :user_agent)";

        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':user_id' => $user_id,
            ':action' => $action,
            ':entity_type' => $entity_type,
            ':entity_id' => $entity_id,
            ':description' => $description,
            ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    } catch (Exception $e) {
        // Log error silently
        error_log("Activity log error: " . $e->getMessage());
    }
}
