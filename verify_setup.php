<?php
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();
$tables = ['modules', 'applications', 'module_teachers', 'timetable_slots', 'module_marks'];

echo "<pre>";
foreach ($tables as $t) {
    try {
        $stmt = $conn->query('SELECT 1 FROM ' . $t . ' LIMIT 1');
        echo "✓ $t table exists\n";
    } catch (Exception $e) {
        echo "✗ $t table missing\n";
    }
}
echo "</pre>";
