<?php

/**
 * Teacher Dashboard
 * School Management System - APICUR TSS
 */
require_once '../config/config.php';
requireLogin();

// Check if user has teacher role
if (!hasRole('teacher')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Teacher Dashboard';

$database = new Database();
$conn = $database->getConnection();
$teacher_id = $_SESSION['user_id'];

// Get statistics - Try modules first, fallback to subjects if modules table doesn't exist
$stats = [];
$assigned_classes = [];

try {
    $stats_query = "SELECT 
        (SELECT COUNT(DISTINCT mt.class_id) 
         FROM module_teachers mt 
         WHERE mt.teacher_id = ? AND mt.academic_year = ?) as total_classes,
        (SELECT COUNT(DISTINCT mt.module_id) 
         FROM module_teachers mt 
         WHERE mt.teacher_id = ? AND mt.academic_year = ?) as total_modules,
        (SELECT COUNT(*) 
         FROM students s 
         INNER JOIN module_teachers mt ON s.class_id = mt.class_id 
         WHERE mt.teacher_id = ? AND s.status = 'active') as total_students,
        (SELECT COUNT(*) 
         FROM attendance a 
         WHERE a.marked_by = ? AND DATE(a.created_at) = CURDATE()) as attendance_marked_today";

    $stats_stmt = $conn->prepare($stats_query);
    $stats_stmt->execute([
        $teacher_id,
        CURRENT_ACADEMIC_YEAR,
        $teacher_id,
        CURRENT_ACADEMIC_YEAR,
        $teacher_id,
        $teacher_id
    ]);
    $stats = $stats_stmt->fetch();

    // Get assigned modules
    $modules_query = "SELECT DISTINCT c.*, m.module_name, m.module_code, mt.module_id
        FROM module_teachers mt
        INNER JOIN classes c ON mt.class_id = c.id
        INNER JOIN modules m ON mt.module_id = m.id
        WHERE mt.teacher_id = ? AND mt.academic_year = ?
        ORDER BY c.class_name, m.module_name";
    $modules_stmt = $conn->prepare($modules_query);
    $modules_stmt->execute([
        $teacher_id,
        CURRENT_ACADEMIC_YEAR
    ]);
    $assigned_classes = $modules_stmt->fetchAll();
} catch (Exception $e) {
    // Fallback to traditional subject-based system
    $stats_query = "SELECT 
        (SELECT COUNT(DISTINCT ts.class_id) 
         FROM teacher_subjects ts 
         WHERE ts.teacher_id = ? AND ts.academic_year = ?) as total_classes,
        (SELECT COUNT(DISTINCT ts.subject_id) 
         FROM teacher_subjects ts 
         WHERE ts.teacher_id = ? AND ts.academic_year = ?) as total_modules,
        (SELECT COUNT(*) 
         FROM students s 
         INNER JOIN teacher_subjects ts ON s.class_id = ts.class_id 
         WHERE ts.teacher_id = ? AND s.status = 'active') as total_students,
        (SELECT COUNT(*) 
         FROM attendance a 
         WHERE a.marked_by = ? AND DATE(a.created_at) = CURDATE()) as attendance_marked_today";

    $stats_stmt = $conn->prepare($stats_query);
    $stats_stmt->execute([
        $teacher_id,
        CURRENT_ACADEMIC_YEAR,
        $teacher_id,
        CURRENT_ACADEMIC_YEAR,
        $teacher_id,
        $teacher_id
    ]);
    $stats = $stats_stmt->fetch();

    // Get assigned subjects
    $modules_query = "SELECT DISTINCT c.*, s.subject_name as module_name, s.subject_code as module_code, ts.subject_id as module_id
        FROM teacher_subjects ts
        INNER JOIN classes c ON ts.class_id = c.id
        INNER JOIN subjects s ON ts.subject_id = s.id
        WHERE ts.teacher_id = ? AND ts.academic_year = ?
        ORDER BY c.class_name, s.subject_name";
    $modules_stmt = $conn->prepare($modules_query);
    $modules_stmt->execute([
        $teacher_id,
        CURRENT_ACADEMIC_YEAR
    ]);
    $assigned_classes = $modules_stmt->fetchAll();
}

// Get today's timetable
$timetable_query = "SELECT t.*, c.class_name, s.subject_name
    FROM timetable t
    INNER JOIN classes c ON t.class_id = c.id
    INNER JOIN subjects s ON t.subject_id = s.id
    WHERE t.teacher_id = ? 
    AND t.day_of_week = ?
    AND t.academic_year = ?
    ORDER BY t.start_time";
$timetable_stmt = $conn->prepare($timetable_query);
$timetable_stmt->execute([
    $teacher_id,
    date('l'),
    CURRENT_ACADEMIC_YEAR
]);
$today_timetable = $timetable_stmt->fetchAll();

// Sidebar menu for teacher
$sidebar_menu = '
<ul class="space-y-2">
    <li>
        <a href="dashboard.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
    </li>
    <li>
        <a href="my_classes.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700">
            <i class="fas fa-chalkboard"></i>
            <span>My Classes</span>
        </a>
    </li>
    <li>
        <a href="students.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700">
            <i class="fas fa-user-graduate"></i>
            <span>Students</span>
        </a>
    </li>
    <li>
        <a href="attendance.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700">
            <i class="fas fa-clipboard-check"></i>
            <span>Attendance</span>
        </a>
    </li>
    <li>
        <a href="marks.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700">
            <i class="fas fa-chart-line"></i>
            <span>Marks Entry</span>
        </a>
    </li>
    <li>
        <a href="reports.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700">
            <i class="fas fa-file-alt"></i>
            <span>Reports</span>
        </a>
    </li>
    <li>
        <a href="timetable.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700">
            <i class="fas fa-calendar"></i>
            <span>Timetable</span>
        </a>
    </li>
    <li>
        <a href="documents.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700">
            <i class="fas fa-folder"></i>
            <span>Documents</span>
        </a>
    </li>
</ul>
';

include '../includes/header.php';
?>

<!-- Dashboard Content -->
<div class="space-y-6">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl p-6 text-white">
        <h1 class="text-2xl font-bold mb-2">Welcome back, <?php echo $_SESSION['full_name']; ?>!</h1>
        <p class="opacity-90">Ready to make a difference today?</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Classes -->
        <div class="dashboard-card bg-white rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">My Classes</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['total_classes']; ?></h3>
                </div>
                <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-chalkboard text-2xl text-blue-600"></i>
                </div>
            </div>
        </div>

        <!-- Total Subjects -->
        <div class="dashboard-card bg-white rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Modules</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['total_modules']; ?></h3>
                </div>
                <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-graduation-cap text-2xl text-green-600"></i>
                </div>
            </div>
        </div>

        <!-- Total Students -->
        <div class="dashboard-card bg-white rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Students</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['total_students']; ?></h3>
                </div>
                <div class="w-14 h-14 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-graduate text-2xl text-purple-600"></i>
                </div>
            </div>
        </div>

        <!-- Attendance Today -->
        <div class="dashboard-card bg-gradient-to-br from-orange-400 to-red-500 rounded-xl p-6 shadow-sm text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-white text-sm font-medium opacity-90">Attendance Today</p>
                    <h3 class="text-3xl font-bold mt-2"><?php echo $stats['attendance_marked_today']; ?></h3>
                </div>
                <div class="w-14 h-14 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <i class="fas fa-clipboard-check text-2xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <a href="attendance.php" class="text-sm text-white hover:underline">
                    Mark attendance →
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- My Modules & Classes -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">My Modules</h3>
                <a href="my_classes.php" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                    View all →
                </a>
            </div>
            <div class="p-6">
                <?php if (empty($assigned_classes)): ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-graduation-cap text-4xl mb-3 opacity-20"></i>
                        <p>No modules assigned yet.</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($assigned_classes as $class): ?>
                            <a href="my_classes.php?module=<?php echo $class['module_id']; ?>&class=<?php echo $class['id']; ?>"
                                class="block p-4 border border-gray-200 rounded-lg hover:border-indigo-500 hover:shadow-md transition">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($class['class_name']); ?></h4>
                                        <p class="text-sm text-indigo-600 font-medium"><?php echo htmlspecialchars($class['module_code']); ?></p>
                                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($class['module_name']); ?></p>
                                    </div>
                                    <i class="fas fa-chevron-right text-gray-400"></i>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Today's Timetable -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Today's Schedule</h3>
                <span class="text-sm text-gray-500"><?php echo date('l, F j, Y'); ?></span>
            </div>
            <div class="p-6">
                <?php if (empty($today_timetable)): ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-calendar-times text-4xl mb-3 opacity-20"></i>
                        <p>No classes scheduled for today.</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($today_timetable as $slot): ?>
                            <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-lg">
                                <div class="text-center">
                                    <div class="text-sm font-semibold text-indigo-600">
                                        <?php echo date('g:i A', strtotime($slot['start_time'])); ?>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <?php echo date('g:i A', strtotime($slot['end_time'])); ?>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($slot['subject_name']); ?></h4>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($slot['class_name']); ?></p>
                                    <?php if (!empty($slot['room'])): ?>
                                        <p class="text-xs text-gray-500 mt-1">
                                            <i class="fas fa-map-marker-alt mr-1"></i> <?php echo htmlspecialchars($slot['room']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="attendance.php" class="flex flex-col items-center justify-center p-6 border-2 border-gray-200 rounded-xl hover:border-indigo-500 hover:shadow-md transition">
                <i class="fas fa-clipboard-check text-3xl text-indigo-600 mb-2"></i>
                <span class="text-sm font-medium text-gray-700">Mark Attendance</span>
            </a>
            <a href="marks.php" class="flex flex-col items-center justify-center p-6 border-2 border-gray-200 rounded-xl hover:border-green-500 hover:shadow-md transition">
                <i class="fas fa-edit text-3xl text-green-600 mb-2"></i>
                <span class="text-sm font-medium text-gray-700">Enter Marks</span>
            </a>
            <a href="reports.php" class="flex flex-col items-center justify-center p-6 border-2 border-gray-200 rounded-xl hover:border-purple-500 hover:shadow-md transition">
                <i class="fas fa-file-alt text-3xl text-purple-600 mb-2"></i>
                <span class="text-sm font-medium text-gray-700">Generate Reports</span>
            </a>
            <a href="documents.php?action=upload" class="flex flex-col items-center justify-center p-6 border-2 border-gray-200 rounded-xl hover:border-orange-500 hover:shadow-md transition">
                <i class="fas fa-upload text-3xl text-orange-600 mb-2"></i>
                <span class="text-sm font-medium text-gray-700">Upload Document</span>
            </a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>