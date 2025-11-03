<?php

/**
 * DOS - Classes Management (Updated)
 * School Management System - APICUR TSS
 */
require_once '../config/config.php';
requireLogin();

if (!hasRole('dos')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Classes Management';
$database = new Database();
$conn = $database->getConnection();

// Get all classes
$classes_query = "SELECT c.*, u.full_name as class_teacher_name, COUNT(DISTINCT s.id) as student_count
    FROM classes c
    LEFT JOIN users u ON c.class_teacher_id = u.id
    LEFT JOIN students s ON c.id = s.class_id AND s.status = 'active'
    WHERE c.academic_year = ? AND c.status = 'active'
    GROUP BY c.id
    ORDER BY c.class_level, c.class_name";

$classes_stmt = $conn->prepare($classes_query);
$classes_stmt->execute([CURRENT_ACADEMIC_YEAR]);
$classes = $classes_stmt->fetchAll();

// Group classes by department
$departments = [];
foreach ($classes as $class) {
    $parts = explode(' ', $class['class_name']);
    $department = end($parts); // Last part is department
    if (!isset($departments[$department])) {
        $departments[$department] = [];
    }
    $departments[$department][] = $class;
}



$sidebar_menu = '
<ul class="space-y-2">
    <li><a href="dashboard.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
    <li><a href="classes.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg"><i class="fas fa-school"></i><span>Classes</span></a></li>
    <li><a href="teachers.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chalkboard-teacher"></i><span>Teachers</span></a></li>
    <li><a href="modules.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-cube"></i><span>Modules</span></a></li>
    <li><a href="generate_timetable.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-calendar-check"></i><span>Generate Timetable</span></a></li>
    <li><a href="timetable.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-calendar-alt"></i><span>View Timetable</span></a></li>
    <li><a href="teacher_assignments.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-tasks"></i><span>Assignments</span></a></li>
    <li><a href="performance.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chart-line"></i><span>Performance</span></a></li>
    <li><a href="reports.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-file-alt"></i><span>Reports</span></a></li>
</ul>';

include '../includes/header.php';
?>

<main class="flex-1 overflow-y-auto p-8">
    <div class="space-y-6 max-w-7xl mx-auto">
        <div class="bg-gradient-to-r from-teal-500 to-cyan-600 rounded-xl p-6 text-white">
            <h1 class="text-3xl font-bold"><i class="fas fa-school mr-3"></i>Classes Management</h1>
            <p class="opacity-90">Manage classes, class teachers, and student attendance</p>
        </div>

        <!-- Department Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($departments as $dept_name => $dept_classes): ?>
                <div class="bg-white rounded-xl shadow-sm overflow-hidden border-l-4 border-teal-500">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-teal-50 to-cyan-50">
                        <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                            <i class="fas fa-building text-teal-600"></i>
                            <?php echo htmlspecialchars($dept_name); ?> Department
                        </h2>
                        <p class="text-sm text-gray-600"><?php echo count($dept_classes); ?> classes</p>
                    </div>

                    <div class="p-6">
                        <div class="space-y-3">
                            <?php foreach ($dept_classes as $class): ?>
                                <a href="class_details.php?class_id=<?php echo $class['id']; ?>"
                                    class="block bg-gradient-to-r from-blue-50 to-indigo-50 hover:from-blue-100 hover:to-indigo-100 p-4 rounded-lg transition-all duration-200 border border-blue-200 hover:border-blue-300 hover:shadow-md">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($class['class_name']); ?></h3>
                                            <p class="text-sm text-gray-600">
                                                <?php echo htmlspecialchars($class['class_teacher_name'] ?: 'No Teacher Assigned'); ?> •
                                                <?php echo $class['student_count']; ?> students
                                            </p>
                                        </div>
                                        <i class="fas fa-chevron-right text-blue-400"></i>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($departments)): ?>
            <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">No Classes Found</h3>
                <p class="text-gray-500">There are no active classes for the current academic year.</p>
            </div>
        <?php endif; ?>
    </div>
</main>



<?php include '../includes/footer.php'; ?>