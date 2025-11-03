<?php

/**
 * DOS - Teacher Details
 * School Management System - APICUR TSS
 */
require_once '../config/config.php';
requireLogin();

if (!hasRole('dos')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Teacher Details';
$database = new Database();
$conn = $database->getConnection();

$teacher_id = $_GET['id'] ?? null;

if (!$teacher_id) {
    header('Location: teachers.php');
    exit();
}

// Get teacher info
$teacher_query = "SELECT * FROM users WHERE id = ? AND role = 'teacher'";
$teacher_stmt = $conn->prepare($teacher_query);
$teacher_stmt->execute([$teacher_id]);
$teacher = $teacher_stmt->fetch();

if (!$teacher) {
    header('Location: teachers.php');
    exit();
}

// Get teacher assignments
$assignments_query = "SELECT ts.*, c.class_name, c.class_level, m.module_name as subject_name
    FROM teacher_subjects ts
    JOIN classes c ON ts.class_id = c.id
    JOIN modules m ON ts.subject_id = m.id
    WHERE ts.teacher_id = ? AND ts.academic_year = ?
    ORDER BY c.class_name, m.module_name";

$assignments_stmt = $conn->prepare($assignments_query);
$assignments_stmt->execute([$teacher_id, CURRENT_ACADEMIC_YEAR]);
$assignments = $assignments_stmt->fetchAll();

// Get teacher's timetable
$timetable_query = "SELECT ts.*, m.module_name as subject_name, c.class_name
    FROM timetable_slots ts
    JOIN modules m ON ts.module_id = m.id
    JOIN classes c ON ts.class_id = c.id
    WHERE ts.teacher_id = ? AND ts.academic_year = ?
    ORDER BY FIELD(ts.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'), ts.start_time";

$timetable_stmt = $conn->prepare($timetable_query);
$timetable_stmt->execute([$teacher_id, CURRENT_ACADEMIC_YEAR]);
$timetable = $timetable_stmt->fetchAll();

$sidebar_menu = '
<ul class="space-y-2">
    <li><a href="dashboard.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
    <li><a href="timetable.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-calendar-alt"></i><span>Timetable</span></a></li>
    <li><a href="teachers.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg"><i class="fas fa-chalkboard-teacher"></i><span>Teachers</span></a></li>
    <li><a href="teacher_assignments.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-tasks"></i><span>Assignments</span></a></li>
    <li><a href="performance.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chart-line"></i><span>Performance</span></a></li>
    <li><a href="reports.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-file-alt"></i><span>Reports</span></a></li>
    <li><a href="classes.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-school"></i><span>Classes</span></a></li>
</ul>';

include '../includes/header.php';
?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div class="bg-gradient-to-r from-purple-500 to-pink-600 rounded-xl p-6 text-white flex-1 mr-4">
            <h1 class="text-2xl font-bold"><?php echo htmlspecialchars($teacher['full_name']); ?></h1>
            <p class="opacity-90">Teacher Details & Assignments</p>
        </div>
        <a href="teachers.php" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg">
            <i class="fas fa-arrow-left mr-2"></i>Back
        </a>
    </div>

    <!-- Teacher Info Card -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-sm font-medium text-gray-500 uppercase">Email</h3>
            <p class="text-lg font-semibold text-gray-800 mt-2"><?php echo htmlspecialchars($teacher['email']); ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-sm font-medium text-gray-500 uppercase">Phone</h3>
            <p class="text-lg font-semibold text-gray-800 mt-2"><?php echo $teacher['phone'] ?? 'Not provided'; ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-sm font-medium text-gray-500 uppercase">Status</h3>
            <p class="text-lg font-semibold mt-2">
                <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">
                    <?php echo ucfirst($teacher['status']); ?>
                </span>
            </p>
        </div>
    </div>

    <!-- Assignments -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Classes & Subjects</h2>
        <?php if (!empty($assignments)): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-700">Class</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-700">Level</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-700">Subject</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($assignments as $assignment): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-700"><?php echo $assignment['class_name']; ?></td>
                                <td class="px-4 py-3 text-gray-700"><?php echo $assignment['class_level']; ?></td>
                                <td class="px-4 py-3 text-gray-700"><?php echo $assignment['subject_name']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-500">No assignments yet.</p>
        <?php endif; ?>
    </div>

    <!-- Timetable -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Weekly Timetable</h2>
        <?php if (!empty($timetable)): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-700">Day</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-700">Time</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-700">Subject</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-700">Class</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-700">Room</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($timetable as $slot): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-700"><?php echo $slot['day_of_week']; ?></td>
                                <td class="px-4 py-3 text-gray-700">
                                    <?php echo date('H:i', strtotime($slot['start_time'])) . ' - ' . date('H:i', strtotime($slot['end_time'])); ?>
                                </td>
                                <td class="px-4 py-3 text-gray-700"><?php echo $slot['subject_name']; ?></td>
                                <td class="px-4 py-3 text-gray-700"><?php echo $slot['class_name']; ?></td>
                                <td class="px-4 py-3 text-gray-700"><?php echo $slot['room'] ?? '-'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-500">No timetable entries.</p>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>