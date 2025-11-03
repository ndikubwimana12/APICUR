<?php

/**
 * Database Connection Test Script
 * Run this file to diagnose database issues
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Test</h2>";
echo "<hr>";

// Test 1: Check if database.php exists
echo "<h3>Test 1: Configuration Files</h3>";
if (file_exists(__DIR__ . '/config/database.php')) {
    echo "✅ database.php found<br>";
} else {
    echo "❌ database.php NOT found<br>";
}

if (file_exists(__DIR__ . '/config/config.php')) {
    echo "✅ config.php found<br>";
} else {
    echo "❌ config.php NOT found<br>";
}
echo "<hr>";

// Test 2: Try to connect to database
echo "<h3>Test 2: Database Connection</h3>";
try {
    require_once __DIR__ . '/config/database.php';
    $database = new Database();
    $conn = $database->getConnection();

    if ($conn) {
        echo "✅ Database connection successful!<br>";
        echo "Connection type: " . get_class($conn) . "<br>";
    } else {
        echo "❌ Database connection failed - returned null<br>";
    }
} catch (Exception $e) {
    echo "❌ Database connection error: " . $e->getMessage() . "<br>";
}
echo "<hr>";

// Test 3: Check if users table exists
echo "<h3>Test 3: Users Table Check</h3>";
try {
    $query = "SHOW TABLES LIKE 'users'";
    $stmt = $conn->query($query);
    $result = $stmt->fetch();

    if ($result) {
        echo "✅ Users table exists<br>";

        // Check if admin user exists
        $query = "SELECT * FROM users WHERE username = 'admin'";
        $stmt = $conn->query($query);
        $admin = $stmt->fetch();

        if ($admin) {
            echo "✅ Admin user found<br>";
            echo "Username: " . $admin['username'] . "<br>";
            echo "Email: " . $admin['email'] . "<br>";
            echo "Role: " . $admin['role'] . "<br>";
            echo "Status: " . $admin['status'] . "<br>";
            echo "Password Hash: " . substr($admin['password'], 0, 30) . "...<br>";
        } else {
            echo "❌ Admin user NOT found in database<br>";
        }
    } else {
        echo "❌ Users table does NOT exist<br>";
    }
} catch (Exception $e) {
    echo "❌ Table check error: " . $e->getMessage() . "<br>";
}
echo "<hr>";

// Test 4: Test password verification
echo "<h3>Test 4: Password Hash Test</h3>";
try {
    if (isset($admin) && $admin) {
        $test_password = 'admin123';
        $stored_hash = $admin['password'];

        echo "Testing password: '$test_password'<br>";
        echo "Stored hash: " . substr($stored_hash, 0, 50) . "...<br>";

        if (password_verify($test_password, $stored_hash)) {
            echo "✅ Password verification PASSED - login should work!<br>";
        } else {
            echo "❌ Password verification FAILED<br>";
            echo "Generating new hash for 'admin123': <br>";
            $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
            echo "<code>" . $new_hash . "</code><br>";
            echo "<br><strong>Run this SQL to fix the password:</strong><br>";
            echo "<code>UPDATE users SET password = '$new_hash' WHERE username = 'admin';</code><br>";
        }
    } else {
        echo "⚠️ Cannot test password - admin user not found<br>";
    }
} catch (Exception $e) {
    echo "❌ Password test error: " . $e->getMessage() . "<br>";
}
echo "<hr>";

// Test 5: PHP Version and Extensions
echo "<h3>Test 5: PHP Environment</h3>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "PDO Extension: " . (extension_loaded('pdo') ? '✅ Enabled' : '❌ Disabled') . "<br>";
echo "PDO MySQL: " . (extension_loaded('pdo_mysql') ? '✅ Enabled' : '❌ Disabled') . "<br>";
echo "Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? '✅ Active' : '⚠️ Not Active') . "<br>";
echo "<hr>";

echo "<h3>Summary</h3>";
echo "<p>If all tests pass with ✅, login should work. If any test fails, follow the instructions above to fix it.</p>";


echo password_hash('admin123', PASSWORD_DEFAULT);
