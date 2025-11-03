<?php

/**
 * Secretary - Student Details
 * Displays a student's full profile with quick actions
 */
require_once '../config/config.php';
requireLogin();

if (!hasRole('secretary')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$studentIdParam = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($studentIdParam <= 0) {
    header('Location: students.php');
    exit();
}

$page_title = 'Student Details';

$database = new Database();
$conn = $database->getConnection();

// Fetch student data with class information
$studentQuery = 'SELECT students.*, classes.class_name, classes.section
                 FROM students
                 LEFT JOIN classes ON students.class_id = classes.id
                 WHERE students.id = :id';

$stmt = $conn->prepare($studentQuery);
$stmt->execute([':id' => $studentIdParam]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    header('Location: students.php');
    exit();
}

// Fetch related documents
$documentsStmt = $conn->prepare('SELECT * FROM documents WHERE related_class_id = :class_id OR FIND_IN_SET("secretary", JSON_EXTRACT(access_roles, "$[*]")) LIMIT 10');
$classId = $student['class_id'] ?? null;
$documentsStmt->execute([':class_id' => $classId]);
$documents = $documentsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch attendance summary
$attendanceStmt = $conn->prepare('SELECT status, COUNT(*) as total FROM attendance WHERE student_id = :student_id GROUP BY status');
$attendanceStmt->execute([':student_id' => $studentIdParam]);
$attendanceSummary = $attendanceStmt->fetchAll(PDO::FETCH_ASSOC);
$attendanceStats = [
    'present' => 0,
    'absent' => 0,
    'late' => 0,
    'excused' => 0
];
foreach ($attendanceSummary as $row) {
    $attendanceStats[$row['status']] = (int) $row['total'];
}

$sidebar_menu = '
<ul class="space-y-2">
    <li><a href="dashboard.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
    <li><a href="students.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg"><i class="fas fa-user-graduate"></i><span>Students</span></a></li>
    <li><a href="register_student.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-user-plus"></i><span>Register Student</span></a></li>
    <li><a href="documents.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-file-alt"></i><span>Documents</span></a></li>
    <li><a href="meetings.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-calendar-alt"></i><span>Meetings</span></a></li>
    <li><a href="reports.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chart-bar"></i><span>Reports</span></a></li>
</ul>';

include '../includes/header.php';
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Student Profile</h1>
            <p class="text-gray-500 text-sm">Detailed information for <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="edit_student.php?id=<?php echo $student['id']; ?>" class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-700 px-4 py-2 rounded-lg shadow-sm hover:bg-gray-100">
                <i class="fas fa-edit"></i>
                <span>Edit Student</span>
            </a>
            <a href="students.php" class="inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg shadow-sm hover:bg-indigo-700">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Students</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex flex-col md:flex-row md:items-center gap-6">
                    <div class="w-32 h-32 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-white text-3xl font-bold">
                        <?php echo strtoupper(substr($student['first_name'], 0, 1)); ?>
                    </div>
                    <div class="flex-1 space-y-3">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800">
                                <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                            </h2>
                            <p class="text-gray-500 text-sm">Student ID: <?php echo htmlspecialchars($student['student_id']); ?></p>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-500">Admission Number</p>
                                <p class="font-medium text-gray-800"><?php echo htmlspecialchars($student['admission_number']); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500">Class</p>
                                <p class="font-medium text-gray-800"><?php echo htmlspecialchars($student['class_name'] ?? 'Unassigned'); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500">Admission Date</p>
                                <p class="font-medium text-gray-800"><?php echo formatDate($student['admission_date']); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500">Status</p>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                    <?php
                                    $statusClass = [
                                        'active' => 'bg-green-100 text-green-800',
                                        'graduated' => 'bg-blue-100 text-blue-800',
                                        'transferred' => 'bg-yellow-100 text-yellow-800',
                                        'dropped' => 'bg-red-100 text-red-800'
                                    ];
                                    echo $statusClass[$student['status']] ?? 'bg-gray-100 text-gray-800';
                                    ?>"><?php echo ucfirst($student['status']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 border-t pt-6 grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                    <div>
                        <p class="text-gray-500 mb-1">Date of Birth</p>
                        <p class="font-medium text-gray-800"><?php echo formatDate($student['date_of_birth']); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500 mb-1">Gender</p>
                        <p class="font-medium text-gray-800"><?php echo htmlspecialchars($student['gender']); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500 mb-1">Recorded By</p>
                        <p class="font-medium text-gray-800">Secretary</p>
                    </div>
                </div>

                <div class="mt-6">
                    <p class="text-gray-500 mb-1 text-sm">Address</p>
                    <p class="text-gray-700 text-sm leading-relaxed"><?php echo nl2br(htmlspecialchars($student['address'] ?? 'Not provided')); ?></p>
                </div>

                <div class="mt-4">
                    <p class="text-gray-500 mb-1 text-sm">Medical Information</p>
                    <p class="text-gray-700 text-sm leading-relaxed"><?php echo nl2br(htmlspecialchars($student['medical_info'] ?? 'No medical notes')); ?></p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Parent/Guardian Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Name</p>
                        <p class="font-medium text-gray-800"><?php echo htmlspecialchars($student['parent_name'] ?? 'Not provided'); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500">Phone</p>
                        <p class="font-medium text-gray-800"><?php echo htmlspecialchars($student['parent_phone'] ?? 'Not provided'); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500">Email</p>
                        <a href="mailto:<?php echo htmlspecialchars($student['parent_email'] ?? ''); ?>" class="font-medium text-indigo-600 hover:text-indigo-700"><?php echo htmlspecialchars($student['parent_email'] ?? 'Not provided'); ?></a>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Recent Documents</h3>
                    <a href="documents.php?student_id=<?php echo $student['id']; ?>" class="text-sm text-indigo-600 hover:text-indigo-700">View all</a>
                </div>
                <?php if (empty($documents)): ?>
                    <div class="text-center py-8 text-gray-500 text-sm">
                        <i class="fas fa-file-alt text-3xl opacity-30 mb-3"></i>
                        <p>No documents linked to this student yet.</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($documents as $document): ?>
                            <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-lg">
                                <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-file"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-800 text-sm"><?php echo htmlspecialchars($document['title']); ?></h4>
                                    <p class="text-gray-500 text-xs mb-2">Category: <?php echo ucfirst($document['category']); ?></p>
                                    <div class="flex items-center gap-3 text-xs text-gray-500">
                                        <span><i class="far fa-clock mr-1"></i> <?php echo formatDate($document['created_at']); ?></span>
                                        <a href="<?php echo BASE_URL . htmlspecialchars($document['file_path']); ?>" target="_blank" class="text-indigo-600 hover:text-indigo-700 font-medium">Open</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="edit_student.php?id=<?php echo $student['id']; ?>" class="flex items-center gap-3 px-4 py-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                        <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Edit Student Record</p>
                            <p class="text-xs text-gray-500">Update personal details, class, or status</p>
                        </div>
                    </a>
                    <a href="documents.php?action=upload&student_id=<?php echo $student['id']; ?>" class="flex items-center gap-3 px-4 py-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                        <div class="w-10 h-10 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-upload"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Upload Document</p>
                            <p class="text-xs text-gray-500">Attach meeting notes or administrative files</p>
                        </div>
                    </a>
                    <a href="meetings.php?action=schedule&student_id=<?php echo $student['id']; ?>" class="flex items-center gap-3 px-4 py-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                        <div class="w-10 h-10 bg-green-100 text-green-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Schedule Meeting</p>
                            <p class="text-xs text-gray-500">Arrange parent or staff meeting</p>
                        </div>
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Attendance Summary</h3>
                <div class="grid grid-cols-2 gap-4 text-center">
                    <div class="p-4 bg-green-50 rounded-lg">
                        <p class="text-xs text-gray-500 uppercase">Present</p>
                        <p class="text-2xl font-bold text-green-600"><?php echo $attendanceStats['present']; ?></p>
                    </div>
                    <div class="p-4 bg-red-50 rounded-lg">
                        <p class="text-xs text-gray-500 uppercase">Absent</p>
                        <p class="text-2xl font-bold text-red-600"><?php echo $attendanceStats['absent']; ?></p>
                    </div>
                    <div class="p-4 bg-yellow-50 rounded-lg">
                        <p class="text-xs text-gray-500 uppercase">Late</p>
                        <p class="text-2xl font-bold text-yellow-600"><?php echo $attendanceStats['late']; ?></p>
                    </div>
                    <div class="p-4 bg-blue-50 rounded-lg">
                        <p class="text-xs text-gray-500 uppercase">Excused</p>
                        <p class="text-2xl font-bold text-blue-600"><?php echo $attendanceStats['excused']; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">System Metadata</h3>
                <div class="space-y-3 text-sm text-gray-600">
                    <div class="flex justify-between">
                        <span>Created At</span>
                        <span><?php echo formatDate($student['created_at'], DATETIME_FORMAT); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Last Updated</span>
                        <span><?php echo formatDate($student['updated_at'], DATETIME_FORMAT); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>