<?php

/**
 * DOS (Director of Studies) Dashboard
 * School Management System - APICUR TSS
 */
require_once '../config/config.php';
requireLogin();

if (!hasRole('dos')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'DOS Dashboard';
$database = new Database();
$conn = $database->getConnection();

// Get statistics
$stats = [
    'total_teachers' => 0,
    'total_classes' => 0,
    'timetable_slots' => 0,
    'teacher_assignments' => 0,
    'total_modules' => 0,
    'module_assignments' => 0,
    'generated_timetables' => 0
];

// Total teachers
$teachers_stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'teacher' AND status = 'active'");
$teachers_stmt->execute();
$teachers_result = $teachers_stmt->fetch();
$stats['total_teachers'] = $teachers_result['count'] ?? 0;

// Total classes
$classes_stmt = $conn->prepare("SELECT COUNT(*) as count FROM classes WHERE academic_year = ?");
$classes_stmt->execute([CURRENT_ACADEMIC_YEAR]);
$classes_result = $classes_stmt->fetch();
$stats['total_classes'] = $classes_result['count'] ?? 0;

// Timetable slots
$timetable_stmt = $conn->prepare("SELECT COUNT(*) as count FROM timetable WHERE academic_year = ?");
$timetable_stmt->execute([CURRENT_ACADEMIC_YEAR]);
$timetable_result = $timetable_stmt->fetch();
$stats['timetable_slots'] = $timetable_result['count'] ?? 0;

// Teacher assignments (old)
$assignments_stmt = $conn->prepare("SELECT COUNT(*) as count FROM teacher_subjects WHERE academic_year = ?");
$assignments_stmt->execute([CURRENT_ACADEMIC_YEAR]);
$assignments_result = $assignments_stmt->fetch();
$stats['teacher_assignments'] = $assignments_result['count'] ?? 0;

// New: Training modules (if table exists)
try {
    $modules_stmt = $conn->prepare("SELECT COUNT(*) as count FROM modules WHERE status = 'active'");
    $modules_stmt->execute();
    $modules_result = $modules_stmt->fetch();
    $stats['total_modules'] = $modules_result['count'] ?? 0;
} catch (Exception $e) {
    $stats['total_modules'] = 0;
}

// New: Module teacher assignments (if table exists)
try {
    $module_assign_stmt = $conn->prepare("SELECT COUNT(*) as count FROM module_teachers WHERE academic_year = ?");
    $module_assign_stmt->execute([CURRENT_ACADEMIC_YEAR]);
    $module_assign_result = $module_assign_stmt->fetch();
    $stats['module_assignments'] = $module_assign_result['count'] ?? 0;
} catch (Exception $e) {
    $stats['module_assignments'] = 0;
}

// New: Generated timetable slots (if table exists)
try {
    $gen_timetable_stmt = $conn->prepare("SELECT COUNT(*) as count FROM timetable_slots WHERE academic_year = ?");
    $gen_timetable_stmt->execute([CURRENT_ACADEMIC_YEAR]);
    $gen_timetable_result = $gen_timetable_stmt->fetch();
    $stats['generated_timetables'] = $gen_timetable_result['count'] ?? 0;
} catch (Exception $e) {
    $stats['generated_timetables'] = 0;
}

// Get teachers
$teachers_query = "SELECT u.*, COUNT(DISTINCT ts.class_id) as classes_count, COUNT(DISTINCT ts.subject_id) as subjects_count
    FROM users u
    LEFT JOIN teacher_subjects ts ON u.id = ts.teacher_id AND ts.academic_year = ?
    WHERE u.role = 'teacher' AND u.status = 'active'
    GROUP BY u.id
    ORDER BY u.full_name
    LIMIT 10";
$teachers_stmt = $conn->prepare($teachers_query);
$teachers_stmt->execute([CURRENT_ACADEMIC_YEAR]);
$teachers = $teachers_stmt->fetchAll();

$sidebar_menu = '
<ul class="space-y-2">
    <li><a href="dashboard.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
    <li><a href="classes.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-school"></i><span>Classes</span></a></li>
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

<div class="space-y-6">
    <div class="bg-gradient-to-r from-purple-500 to-pink-600 rounded-xl p-6 text-white">
        <h1 class="text-2xl font-bold mb-2">DOS Dashboard - <?php echo $_SESSION['full_name']; ?></h1>
        <p class="opacity-90">Manage teachers, timetables, and academic performance</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <a href="modules.php" class="dashboard-card bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition cursor-pointer border-t-4 border-indigo-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Training Modules</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['total_modules']; ?></h3>
                </div>
                <div class="w-14 h-14 bg-indigo-100 rounded-full flex items-center justify-center"><i class="fas fa-graduation-cap text-2xl text-indigo-600"></i></div>
            </div>
        </a>
        <a href="modules.php" class="dashboard-card bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition cursor-pointer border-t-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Module Assignments</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['module_assignments']; ?></h3>
                </div>
                <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center"><i class="fas fa-tasks text-2xl text-green-600"></i></div>
            </div>
        </a>
        <a href="generate_timetable.php" class="dashboard-card bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition cursor-pointer border-t-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Generated Timetables</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['generated_timetables']; ?></h3>
                </div>
                <div class="w-14 h-14 bg-purple-100 rounded-full flex items-center justify-center"><i class="fas fa-wand-magic-sparkles text-2xl text-purple-600"></i></div>
            </div>
        </a>
        <a href="teachers.php" class="dashboard-card bg-gradient-to-br from-orange-400 to-red-500 rounded-xl p-6 shadow-sm hover:shadow-md transition cursor-pointer text-white">
            <p class="text-sm font-medium opacity-90">Active Teachers</p>
            <h3 class="text-3xl font-bold mt-2"><?php echo $stats['total_teachers']; ?></h3>
            <p class="text-sm hover:underline mt-2 inline-block">View All →</p>
        </a>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <a href="modules.php" class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl p-6 text-white hover:shadow-lg transition">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-graduation-cap text-xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-sm">Manage Modules</h3>
                    <p class="text-xs opacity-90">Assign teachers</p>
                </div>
            </div>
        </a>
        <a href="generate_timetable.php" class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white hover:shadow-lg transition">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-wand-magic-sparkles text-xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-sm">Generate Timetable</h3>
                    <p class="text-xs opacity-90">Auto-schedule classes</p>
                </div>
            </div>
        </a>
        <a href="timetable.php" class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white hover:shadow-lg transition">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar-alt text-xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-sm">View Timetables</h3>
                    <p class="text-xs opacity-90">Check schedules</p>
                </div>
            </div>
        </a>
        <a href="performance.php" class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 text-white hover:shadow-lg transition">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-sm">Performance</h3>
                    <p class="text-xs opacity-90">Academic data</p>
                </div>
            </div>
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800">Teachers Overview (Top 10)</h3>
                <a href="teachers.php" class="text-blue-600 hover:text-blue-700 text-sm">View All →</a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teacher</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Classes</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subjects</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($teachers as $teacher): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($teacher['full_name']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($teacher['email']); ?></td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium">
                                    <?php echo $teacher['classes_count']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-block bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium">
                                    <?php echo $teacher['subjects_count']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm space-x-3">
                                <a href="teacher_details.php?id=<?php echo $teacher['id']; ?>" class="text-indigo-600 hover:text-indigo-700" title="View Details"><i class="fas fa-eye"></i></a>
                                <a href="assign_teacher.php?id=<?php echo $teacher['id']; ?>" class="text-blue-600 hover:text-blue-700" title="Assign Classes"><i class="fas fa-tasks"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>