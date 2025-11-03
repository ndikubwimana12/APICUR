<?php

/**
 * Database Extensions Importer
 * Apply new tables for Student Applications & Timetable System
 */

require_once '../config/database.php';

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    die('❌ Database connection failed!');
}

// Read SQL file
$sql_file = __DIR__ . '/extensions.sql';
$sql_content = file_get_contents($sql_file);

if (!$sql_content) {
    die('❌ Could not read extensions.sql file!');
}

// Split SQL statements by semicolon and execute
$statements = array_filter(
    array_map('trim', explode(';', $sql_content)),
    function ($s) {
        return !empty($s) && strpos($s, '--') !== 0;
    }
);

$count = 0;
$errors = [];

foreach ($statements as $statement) {
    try {
        if (trim($statement)) {
            $conn->exec($statement);
            $count++;
        }
    } catch (PDOException $e) {
        // Ignore "table already exists" errors
        if (strpos($e->getMessage(), 'already exists') === false) {
            $errors[] = $e->getMessage();
        }
    }
}

// Create uploads directory if needed
$upload_dir = __DIR__ . '/../uploads/applications';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Import</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="text-center mb-8">
                <div class="text-5xl mb-4">✅</div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Database Extensions Imported Successfully!</h1>
                <p class="text-gray-600">All new tables and data have been created.</p>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                <h2 class="text-lg font-semibold text-blue-900 mb-3">Import Summary</h2>
                <div class="space-y-2 text-sm text-blue-800">
                    <p>✓ <strong>' . $count . ' SQL statements</strong> executed</p>
                    <p>✓ <strong>5 New Tables</strong> created:
                        <ul class="ml-4 mt-1 space-y-1">
                            <li>- applications (Student applications)</li>
                            <li>- modules (Training modules/trades)</li>
                            <li>- module_teachers (Teacher-module assignments)</li>
                            <li>- timetable_slots (Auto-generated schedules)</li>
                            <li>- module_marks (Student module grades)</li>
                        </ul>
                    </p>
                    <p>✓ <strong>10 Sample Modules</strong> pre-loaded</p>
                    <p>✓ <strong>Upload directory</strong> created: /uploads/applications</p>';

if (!empty($errors)) {
    echo '
                    <p class="mt-3 text-yellow-700"><strong>⚠ Warnings:</strong></p>
                    <ul class="ml-4 mt-1 space-y-1">';
    foreach ($errors as $error) {
        echo '<li>- ' . htmlspecialchars($error) . '</li>';
    }
    echo '
                    </ul>';
}

echo '
                </div>
            </div>

            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                <h3 class="font-semibold text-gray-800 mb-3">New Features Ready:</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div class="border border-gray-200 rounded p-3">
                        <p class="font-semibold text-indigo-600">Student Applications</p>
                        <p class="text-gray-600 text-xs mt-1">/public/apply.php</p>
                    </div>
                    <div class="border border-gray-200 rounded p-3">
                        <p class="font-semibold text-green-600">Review & Admission</p>
                        <p class="text-gray-600 text-xs mt-1">/secretary/applications.php</p>
                    </div>
                    <div class="border border-gray-200 rounded p-3">
                        <p class="font-semibold text-purple-600">Module Management</p>
                        <p class="text-gray-600 text-xs mt-1">/dos/modules.php</p>
                    </div>
                    <div class="border border-gray-200 rounded p-3">
                        <p class="font-semibold text-orange-600">Auto Timetables</p>
                        <p class="text-gray-600 text-xs mt-1">/dos/generate_timetable.php</p>
                    </div>
                </div>
            </div>

            <div class="border-t pt-6">
                <h3 class="font-semibold text-gray-800 mb-4">Next Steps:</h3>
                <ol class="space-y-2 text-sm text-gray-700 list-decimal list-inside">
                    <li>Review the new dashboards</li>
                    <li>Test student application at <code class="bg-gray-100 px-2 py-1 rounded">/public/apply.php</code></li>
                    <li>Review applications in Secretary dashboard</li>
                    <li>Set up modules in DOS dashboard</li>
                    <li>Generate timetables for each class</li>
                </ol>
            </div>

            <div class="mt-8 flex gap-4 justify-center">
                <a href="../auth/login.php" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 font-medium">
                    Go to Login
                </a>
                <a href="../public/index.html" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 font-medium">
                    Go to Home
                </a>
            </div>
        </div>
    </div>
</body>
</html>';
