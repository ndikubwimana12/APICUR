<?php
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "<h2>System Verification Report</h2>";

// Test 1: Database Tables
echo "<h3>1. Database Tables Status</h3>";
$tables = ['modules', 'applications', 'module_teachers', 'timetable_slots', 'module_marks', 'subjects'];
foreach ($tables as $t) {
    try {
        $stmt = $conn->query('SELECT COUNT(*) FROM ' . $t);
        $count = $stmt->fetchColumn();
        echo "✓ <b>$t</b> - exists (" . $count . " records)<br>";
    } catch (Exception $e) {
        echo "✗ <b>$t</b> - missing<br>";
    }
}

// Test 2: Modules data
echo "<h3>2. Modules Data</h3>";
try {
    $stmt = $conn->query('SELECT COUNT(*) FROM modules WHERE status = "active"');
    $count = $stmt->fetchColumn();
    echo "✓ Active modules: " . $count . "<br>";
} catch (Exception $e) {
    echo "✗ Could not fetch modules: " . $e->getMessage() . "<br>";
}

// Test 3: Subjects fallback
echo "<h3>3. Subjects as Modules Fallback</h3>";
try {
    $stmt = $conn->query('SELECT COUNT(*) FROM subjects WHERE status = "active"');
    $count = $stmt->fetchColumn();
    echo "✓ Active subjects (for module fallback): " . $count . "<br>";
} catch (Exception $e) {
    echo "✗ Could not fetch subjects: " . $e->getMessage() . "<br>";
}

// Test 4: Users (Teachers)
echo "<h3>4. Teachers Available</h3>";
try {
    $stmt = $conn->query('SELECT COUNT(*) FROM users WHERE role = "teacher" AND status = "active"');
    $count = $stmt->fetchColumn();
    echo "✓ Active teachers: " . $count . "<br>";
} catch (Exception $e) {
    echo "✗ Could not fetch teachers: " . $e->getMessage() . "<br>";
}

// Test 5: Classes
echo "<h3>5. Classes Available</h3>";
try {
    $stmt = $conn->query('SELECT COUNT(*) FROM classes WHERE status = "active"');
    $count = $stmt->fetchColumn();
    echo "✓ Active classes: " . $count . "<br>";
} catch (Exception $e) {
    echo "✗ Could not fetch classes: " . $e->getMessage() . "<br>";
}

// Test 6: Test application insert
echo "<h3>6. Application Recording Test</h3>";
try {
    $test_app = "TEST-" . time();
    $insert_query = "INSERT INTO applications (
        application_number, first_name, last_name, date_of_birth, gender, 
        parent_name, parent_phone, level_applied, status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->execute([
        $test_app,
        'Test',
        'User',
        '2005-01-01',
        'Male',
        'Parent Name',
        '1234567890',
        'Level 3',
        'pending'
    ]);

    echo "✓ Application test record saved successfully (ID: $test_app)<br>";

    // Verify it was saved
    $verify_stmt = $conn->prepare('SELECT COUNT(*) FROM applications WHERE application_number = ?');
    $verify_stmt->execute([$test_app]);
    $count = $verify_stmt->fetchColumn();
    echo "✓ Verified: " . $count . " record(s) found<br>";

    // Clean up
    $delete_stmt = $conn->prepare('DELETE FROM applications WHERE application_number = ?');
    $delete_stmt->execute([$test_app]);
    echo "✓ Test record cleaned up<br>";
} catch (Exception $e) {
    echo "✗ Application test failed: " . $e->getMessage() . "<br>";
}

echo "<h3>✅ System Verification Complete</h3>";
