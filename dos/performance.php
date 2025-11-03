<?php

/**
 * DOS - Student Performance & Attendance Monitoring (Updated)
 * School Management System - APICUR TSS
 */
require_once '../config/config.php';
requireLogin();

if (!hasRole('dos')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Student Performance';
$database = new Database();
$conn = $database->getConnection();

// Get all classes
$classes_query = "SELECT * FROM classes WHERE academic_year = ? AND status = 'active' ORDER BY class_level, class_name";
$classes_stmt = $conn->prepare($classes_query);
$classes_stmt->execute([CURRENT_ACADEMIC_YEAR]);
$classes = $classes_stmt->fetchAll();

$selected_class_id = $_GET['class_id'] ?? null;
$selected_term = $_GET['term'] ?? CURRENT_TERM;
$performance_data = [];
$attendance_data = [];
$class_info = null;

if ($selected_class_id) {
    // Get class info
    $class_stmt = $conn->prepare("SELECT * FROM classes WHERE id = ?");
    $class_stmt->execute([$selected_class_id]);
    $class_info = $class_stmt->fetch();

    // Get student performance data (marks and grades)
    $perf_query = "SELECT st.id, st.admission_number, st.first_name, st.last_name, 
        rc.average_marks, rc.grade, rc.position, COUNT(DISTINCT m.id) as assessments
        FROM students st
        LEFT JOIN report_cards rc ON st.id = rc.student_id AND rc.class_id = ? AND rc.academic_year = ? AND rc.term = ?
        LEFT JOIN marks m ON st.id = m.student_id AND m.class_id = ? AND m.academic_year = ? AND m.term = ?
        WHERE st.class_id = ? AND st.status = 'active'
        GROUP BY st.id
        ORDER BY rc.average_marks DESC, st.first_name ASC";

    $perf_stmt = $conn->prepare($perf_query);
    $perf_stmt->execute([
        $selected_class_id,
        CURRENT_ACADEMIC_YEAR,
        $selected_term,
        $selected_class_id,
        CURRENT_ACADEMIC_YEAR,
        $selected_term,
        $selected_class_id
    ]);
    $performance_data = $perf_stmt->fetchAll();

    // Get attendance data by student
    $attendance_query = "SELECT st.id, st.first_name, st.last_name,
        COUNT(CASE WHEN a.status = 'present' THEN 1 END) as days_present,
        COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as days_absent,
        COUNT(CASE WHEN a.status = 'late' THEN 1 END) as days_late,
        COUNT(DISTINCT a.date) as total_days
        FROM students st
        LEFT JOIN attendance a ON st.id = a.student_id
        WHERE st.class_id = ? AND st.status = 'active'
        GROUP BY st.id
        ORDER BY st.first_name ASC";

    $att_stmt = $conn->prepare($attendance_query);
    $att_stmt->execute([$selected_class_id]);
    $attendance_data = $att_stmt->fetchAll();

    // Create attendance lookup by student ID
    $attendance_lookup = [];
    foreach ($attendance_data as $att) {
        $attendance_lookup[$att['id']] = $att;
    }
}

// Get grade statistics
$grade_stats = [];
if ($selected_class_id) {
    $stats_query = "SELECT rc.grade, COUNT(*) as count
        FROM report_cards rc
        WHERE rc.class_id = ? AND rc.academic_year = ? AND rc.term = ?
        GROUP BY rc.grade
        ORDER BY rc.grade";

    $stats_stmt = $conn->prepare($stats_query);
    $stats_stmt->execute([$selected_class_id, CURRENT_ACADEMIC_YEAR, $selected_term]);
    $grade_stats = $stats_stmt->fetchAll();
}

$sidebar_menu = '
<ul class="space-y-2">
    <li><a href="dashboard.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
    <li><a href="classes.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-school"></i><span>Classes</span></a></li>
    <li><a href="teachers.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chalkboard-teacher"></i><span>Teachers</span></a></li>
    <li><a href="modules.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-cube"></i><span>Modules</span></a></li>
    <li><a href="generate_timetable.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-calendar-check"></i><span>Generate Timetable</span></a></li>
    <li><a href="timetable.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-calendar-alt"></i><span>View Timetable</span></a></li>
    <li><a href="teacher_assignments.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-tasks"></i><span>Assignments</span></a></li>
    <li><a href="performance.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg"><i class="fas fa-chart-line"></i><span>Performance</span></a></li>
    <li><a href="reports.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-file-alt"></i><span>Reports</span></a></li>
</ul>';

include '../includes/header.php';
?>

<div class="space-y-6">
    <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl p-6 text-white">
        <h1 class="text-3xl font-bold"><i class="fas fa-chart-line mr-3"></i>Student Performance & Attendance</h1>
        <p class="opacity-90">Monitor student academic performance and attendance by class and term</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"><i class="fas fa-school mr-1"></i>Select Class</label>
                <select onchange="document.location='?class_id=' + this.value + '&term=' + document.getElementById('term').value"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">-- Select a Class --</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?php echo $class['id']; ?>" <?php echo ($selected_class_id == $class['id']) ? 'selected' : ''; ?>>
                            <?php echo $class['class_name'] . ' (' . $class['class_level'] . ')'; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"><i class="fas fa-calendar mr-1"></i>Term</label>
                <select id="term" onchange="document.location='?class_id=' + (document.querySelector('select').value || '') + '&term=' + this.value"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="1" <?php echo ($selected_term == '1') ? 'selected' : ''; ?>>Term 1</option>
                    <option value="2" <?php echo ($selected_term == '2') ? 'selected' : ''; ?>>Term 2</option>
                    <option value="3" <?php echo ($selected_term == '3') ? 'selected' : ''; ?>>Term 3</option>
                </select>
            </div>

            <?php if ($selected_class_id): ?>
                <div class="flex items-end">
                    <button onclick="window.print()" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition">
                        <i class="fas fa-print mr-2"></i>Print Report
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($selected_class_id && $class_info): ?>

        <!-- Performance Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50">
                <h2 class="text-lg font-bold text-gray-800"><i class="fas fa-list mr-2 text-green-600"></i>Performance & Attendance Summary</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 border-b-2 border-gray-300">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 bg-gray-50">Rank</th>
                            <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 bg-gray-50">Student Name</th>
                            <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 bg-gray-50">Admission #</th>
                            <th class="px-6 py-4 text-center text-sm font-bold text-gray-700 bg-gray-50">Average %</th>
                            <th class="px-6 py-4 text-center text-sm font-bold text-gray-700 bg-gray-50">Grade</th>
                            <th class="px-6 py-4 text-center text-sm font-bold text-gray-700 bg-gray-50">Attendance</th>
                            <th class="px-6 py-4 text-center text-sm font-bold text-gray-700 bg-gray-50">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (count($performance_data) > 0): ?>
                            <?php $rank = 1;
                            foreach ($performance_data as $student):
                                $att = $attendance_lookup[$student['id']] ?? null;
                                $att_percent = 0;
                                if ($att && $att['total_days'] > 0) {
                                    $att_percent = round(($att['days_present'] / $att['total_days']) * 100, 2);
                                }
                            ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 text-gray-700 font-bold">
                                        <span class="inline-block bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-bold"><?php echo $rank++; ?></span>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-gray-900">
                                        <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-700"><?php echo $student['admission_number']; ?></td>
                                    <td class="px-6 py-4 text-center">
                                        <?php if ($student['average_marks']): ?>
                                            <span class="inline-block bg-blue-100 text-blue-800 px-4 py-2 rounded-full text-xs font-bold">
                                                <?php echo number_format($student['average_marks'], 2); ?>%
                                            </span>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-xs">--</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <?php if ($student['grade']): ?>
                                            <span class="inline-block bg-green-100 text-green-800 px-4 py-2 rounded-full text-xs font-bold">
                                                <?php echo $student['grade']; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-xs">--</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <?php if ($att): ?>
                                            <span class="inline-block <?php echo $att_percent >= 90 ? 'bg-green-100 text-green-800' : ($att_percent >= 75 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?> px-4 py-2 rounded-full text-xs font-bold">
                                                <?php echo $att_percent; ?>%
                                            </span>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-xs">--</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <?php
                                        $status = 'Pending';
                                        if ($student['average_marks'] && $student['grade']) {
                                            $status = 'Graded';
                                        }
                                        $status_color = $status == 'Graded' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
                                        ?>
                                        <span class="inline-block <?php echo $status_color; ?> px-3 py-1 rounded-full text-xs font-semibold">
                                            <?php echo $status; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-3xl mb-2 block opacity-50"></i>
                                    No student data found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Grade Distribution -->
        <?php if (!empty($grade_stats)): ?>
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4"><i class="fas fa-chart-pie mr-2 text-purple-600"></i>Grade Distribution</h2>
                <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                    <?php foreach ($grade_stats as $stat): ?>
                        <div class="bg-gradient-to-br from-purple-100 to-purple-50 rounded-lg p-4 text-center border border-purple-200">
                            <p class="text-3xl font-bold text-purple-600"><?php echo $stat['grade']; ?></p>
                            <p class="text-sm text-gray-600 mt-2 font-semibold"><?php echo $stat['count']; ?> <span class="font-normal">student<?php echo $stat['count'] != 1 ? 's' : ''; ?></span></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-8 text-center">
            <i class="fas fa-hand-pointer text-4xl text-blue-600 mb-3"></i>
            <p class="text-gray-700 font-medium">Select a class to view performance data</p>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>