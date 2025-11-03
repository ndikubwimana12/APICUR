<?php

/**
 * DOS - Teacher Assignments Overview (Updated)
 * School Management System - APICUR TSS
 */
require_once '../config/config.php';
requireLogin();

if (!hasRole('dos')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Teacher Assignments';
$database = new Database();
$conn = $database->getConnection();

// Get all module-teacher assignments
$assignments_query = "SELECT mt.*, u.full_name as teacher_name, c.class_name, c.class_level, m.module_name, m.module_code
    FROM module_teachers mt
    JOIN users u ON mt.teacher_id = u.id
    JOIN classes c ON mt.class_id = c.id
    JOIN modules m ON mt.module_id = m.id
    WHERE mt.academic_year = ?
    ORDER BY c.class_name, m.module_name, u.full_name";

$assignments_stmt = $conn->prepare($assignments_query);
$assignments_stmt->execute([CURRENT_ACADEMIC_YEAR]);
$assignments = $assignments_stmt->fetchAll();

// Group by class
$grouped_assignments = [];
$total_by_module = [];
$total_by_class = [];

foreach ($assignments as $assignment) {
    $class_key = $assignment['class_name'];
    if (!isset($grouped_assignments[$class_key])) {
        $grouped_assignments[$class_key] = [
            'class_level' => $assignment['class_level'],
            'class_id' => $assignment['class_id'],
            'modules' => []
        ];
        $total_by_class[$class_key] = 0;
    }
    $grouped_assignments[$class_key]['modules'][] = $assignment;
    $total_by_class[$class_key]++;

    // Count by module
    $module_key = $assignment['module_name'];
    if (!isset($total_by_module[$module_key])) {
        $total_by_module[$module_key] = 0;
    }
    $total_by_module[$module_key]++;
}

$sidebar_menu = '
<ul class="space-y-2">
    <li><a href="dashboard.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
    <li><a href="classes.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-school"></i><span>Classes</span></a></li>
    <li><a href="teachers.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chalkboard-teacher"></i><span>Teachers</span></a></li>
    <li><a href="modules.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-cube"></i><span>Modules</span></a></li>
    <li><a href="generate_timetable.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-calendar-check"></i><span>Generate Timetable</span></a></li>
    <li><a href="timetable.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-calendar-alt"></i><span>View Timetable</span></a></li>
    <li><a href="teacher_assignments.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg"><i class="fas fa-tasks"></i><span>Assignments</span></a></li>
    <li><a href="performance.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chart-line"></i><span>Performance</span></a></li>
    <li><a href="reports.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-file-alt"></i><span>Reports</span></a></li>
</ul>';

include '../includes/header.php';
?>

<div class="space-y-6">
    <div class="bg-gradient-to-r from-orange-400 to-red-500 rounded-xl p-6 text-white">
        <h1 class="text-3xl font-bold"><i class="fas fa-tasks mr-3"></i>Teacher Module Assignments</h1>
        <p class="opacity-90">Overview of all teacher-module-class assignments for Academic Year <?php echo CURRENT_ACADEMIC_YEAR; ?></p>
    </div>

    <!-- Summary Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Assignments</p>
                    <h3 class="text-4xl font-bold text-orange-600 mt-2"><?php echo count($assignments); ?></h3>
                </div>
                <i class="fas fa-tasks text-5xl text-orange-100"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Classes Covered</p>
                    <h3 class="text-4xl font-bold text-blue-600 mt-2"><?php echo count($grouped_assignments); ?></h3>
                </div>
                <i class="fas fa-school text-5xl text-blue-100"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Modules Assigned</p>
                    <h3 class="text-4xl font-bold text-green-600 mt-2"><?php echo count($total_by_module); ?></h3>
                </div>
                <i class="fas fa-cube text-5xl text-green-100"></i>
            </div>
        </div>

        <?php
        $unique_teachers = [];
        foreach ($assignments as $a) {
            $unique_teachers[$a['teacher_name']] = true;
        }
        ?>

        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Teachers</p>
                    <h3 class="text-4xl font-bold text-purple-600 mt-2"><?php echo count($unique_teachers); ?></h3>
                </div>
                <i class="fas fa-users text-5xl text-purple-100"></i>
            </div>
        </div>
    </div>

    <!-- Assignments by Class -->
    <?php if (count($assignments) > 0): ?>
        <?php foreach ($grouped_assignments as $class_name => $class_data): ?>
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-bold"><?php echo $class_name; ?></h2>
                            <p class="text-blue-100 text-sm">Level: <?php echo $class_data['class_level']; ?></p>
                        </div>
                        <div class="text-right">
                            <p class="text-3xl font-bold"><?php echo count($class_data['modules']); ?></p>
                            <p class="text-blue-100 text-sm">Modules</p>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100 border-b-2 border-gray-300">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 bg-gray-50">Module</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 bg-gray-50">Teacher Name</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 bg-gray-50">Status</th>
                                <th class="px-6 py-4 text-center text-sm font-bold text-gray-700 bg-gray-50">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($class_data['modules'] as $assignment): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-400 to-purple-400 flex items-center justify-center text-white font-bold text-sm">
                                                <?php echo strtoupper(substr($assignment['module_code'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($assignment['module_name']); ?></p>
                                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($assignment['module_code']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-orange-400 to-red-400 flex items-center justify-center text-white text-sm font-bold">
                                                <?php echo strtoupper(substr($assignment['teacher_name'], 0, 1)); ?>
                                            </div>
                                            <span class="font-medium text-gray-900"><?php echo htmlspecialchars($assignment['teacher_name']); ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">
                                            <i class="fas fa-check-circle mr-1"></i>Active
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="teacher_details.php?id=<?php echo $assignment['teacher_id']; ?>"
                                            class="inline-block text-blue-600 hover:text-blue-800 hover:bg-blue-100 px-3 py-2 rounded transition"
                                            title="View Teacher">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="bg-yellow-50 border-2 border-yellow-200 rounded-xl p-8 text-center">
            <i class="fas fa-inbox text-4xl text-yellow-600 mb-3"></i>
            <p class="text-gray-700 font-medium">No assignments found</p>
            <p class="text-gray-600 text-sm mt-2">
                <a href="assign_teacher.php" class="text-blue-600 hover:text-blue-800 font-semibold">Create assignments now</a>
            </p>
        </div>
    <?php endif; ?>

    <!-- Module Distribution Summary -->
    <?php if (count($total_by_module) > 0): ?>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4"><i class="fas fa-chart-bar mr-2 text-purple-600"></i>Module Distribution</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <?php foreach ($total_by_module as $module_name => $count): ?>
                    <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg p-4 border border-purple-200">
                        <p class="text-sm text-gray-600 font-medium">Assigned Teachers</p>
                        <p class="text-2xl font-bold text-purple-600 mt-2"><?php echo $count; ?></p>
                        <p class="text-xs text-gray-600 mt-2" title="<?php echo htmlspecialchars($module_name); ?>">
                            <?php echo htmlspecialchars(substr($module_name, 0, 30)); ?><?php echo strlen($module_name) > 30 ? '...' : ''; ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>