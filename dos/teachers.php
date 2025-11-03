<?php

/**
 * DOS - Teachers Management (Updated)
 * School Management System - APICUR TSS
 */
require_once '../config/config.php';
requireLogin();

if (!hasRole('dos')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Teachers Management';
$database = new Database();
$conn = $database->getConnection();

// Get all teachers with module assignments
$teachers_query = "SELECT u.*, 
    COUNT(DISTINCT ts.subject_id) as modules_assigned,
    COUNT(DISTINCT ts.class_id) as classes_assigned,
    GROUP_CONCAT(DISTINCT m.module_code SEPARATOR ', ') as module_codes
    FROM users u
    LEFT JOIN teacher_subjects ts ON u.id = ts.teacher_id AND ts.academic_year = ?
    LEFT JOIN modules m ON ts.subject_id = m.id
    WHERE u.role = 'teacher' AND u.status = 'active'
    GROUP BY u.id
    ORDER BY u.full_name";

$teachers_stmt = $conn->prepare($teachers_query);
$teachers_stmt->execute([CURRENT_ACADEMIC_YEAR]);
$teachers = $teachers_stmt->fetchAll();

$sidebar_menu = '
<ul class="space-y-2">
    <li><a href="dashboard.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
    <li><a href="classes.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-school"></i><span>Classes</span></a></li>
    <li><a href="teachers.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg"><i class="fas fa-chalkboard-teacher"></i><span>Teachers</span></a></li>
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
        <h1 class="text-3xl font-bold"><i class="fas fa-chalkboard-teacher mr-3"></i>Teachers Management</h1>
        <p class="opacity-90">Manage all active teachers and their module assignments</p>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Teachers</p>
                    <h3 class="text-4xl font-bold text-blue-600 mt-2"><?php echo count($teachers); ?></h3>
                </div>
                <i class="fas fa-users text-5xl text-blue-100"></i>
            </div>
        </div>

        <?php
        $total_assignments = 0;
        $total_modules = 0;
        foreach ($teachers as $t) {
            $total_assignments += $t['classes_assigned'];
            $total_modules += $t['modules_assigned'];
        }
        ?>

        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Assignments</p>
                    <h3 class="text-4xl font-bold text-green-600 mt-2"><?php echo $total_assignments; ?></h3>
                </div>
                <i class="fas fa-tasks text-5xl text-green-100"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Modules Covered</p>
                    <h3 class="text-4xl font-bold text-purple-600 mt-2"><?php echo $total_modules; ?></h3>
                </div>
                <i class="fas fa-cube text-5xl text-purple-100"></i>
            </div>
        </div>
    </div>

    <!-- Teachers Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-pink-50">
            <h2 class="text-lg font-bold text-gray-800"><i class="fas fa-list mr-2 text-purple-600"></i>Teachers List</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100 border-b-2 border-gray-300">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 bg-gray-50">Teacher Name</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 bg-gray-50">Email</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 bg-gray-50">Phone</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 bg-gray-50">Modules</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 bg-gray-50">Classes</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 bg-gray-50">Module Codes</th>
                        <th class="px-6 py-4 text-center text-sm font-bold text-gray-700 bg-gray-50">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (count($teachers) > 0): ?>
                        <?php foreach ($teachers as $teacher): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-400 to-pink-400 flex items-center justify-center text-white font-bold">
                                            <?php echo strtoupper(substr($teacher['full_name'], 0, 1)); ?>
                                        </div>
                                        <span class="font-semibold text-gray-900"><?php echo htmlspecialchars($teacher['full_name']); ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($teacher['email']); ?></td>
                                <td class="px-6 py-4 text-gray-600"><?php echo $teacher['phone'] ?? '<span class="text-gray-400">-</span>'; ?></td>
                                <td class="px-6 py-4">
                                    <span class="inline-block bg-blue-100 text-blue-800 px-4 py-2 rounded-full text-sm font-bold">
                                        <i class="fas fa-cube mr-1"></i><?php echo $teacher['modules_assigned']; ?> modules
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-block bg-green-100 text-green-800 px-4 py-2 rounded-full text-sm font-bold">
                                        <i class="fas fa-school mr-1"></i><?php echo $teacher['classes_assigned']; ?> classes
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600 max-w-xs truncate" title="<?php echo htmlspecialchars($teacher['module_codes'] ?? 'None'); ?>">
                                    <span class="text-xs"><?php echo htmlspecialchars(substr($teacher['module_codes'] ?? 'None', 0, 40)); ?></span>
                                </td>
                                <td class="px-6 py-4 text-center space-x-2">
                                    <a href="teacher_details.php?id=<?php echo $teacher['id']; ?>"
                                        class="inline-block text-blue-600 hover:text-blue-800 hover:bg-blue-100 px-3 py-2 rounded transition"
                                        title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="assign_teacher.php?id=<?php echo $teacher['id']; ?>"
                                        class="inline-block text-purple-600 hover:text-purple-800 hover:bg-purple-100 px-3 py-2 rounded transition"
                                        title="Assign Modules">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center">
                                <div class="text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-3 block opacity-50"></i>
                                    <p class="font-medium">No active teachers found</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Tips -->
    <div class="bg-blue-50 border-l-4 border-blue-600 rounded-xl p-6">
        <h3 class="font-bold text-blue-900 mb-3"><i class="fas fa-lightbulb mr-2"></i>Quick Tips</h3>
        <ul class="text-blue-800 space-y-2 text-sm">
            <li><i class="fas fa-check-circle mr-2"></i>Click the <strong>eye icon</strong> to view full teacher details and their complete profile</li>
            <li><i class="fas fa-check-circle mr-2"></i>Click the <strong>edit icon</strong> to assign modules and classes to teachers</li>
            <li><i class="fas fa-check-circle mr-2"></i>Teachers can be assigned multiple modules across different classes</li>
        </ul>
    </div>
</div>

<?php include '../includes/footer.php'; ?>