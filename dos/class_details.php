<?php

/**
 * DOS - Class Details
 * School Management System - APICUR TSS
 */
require_once '../config/config.php';
requireLogin();

if (!hasRole('dos')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$class_id = $_GET['class_id'] ?? null;
if (!$class_id) {
    header('Location: classes.php');
    exit();
}

$database = new Database();
$conn = $database->getConnection();

// Get class details
$class_query = "SELECT c.*, u.full_name as class_teacher_name
                FROM classes c
                LEFT JOIN users u ON c.class_teacher_id = u.id
                WHERE c.id = ? AND c.academic_year = ? AND c.status = 'active'";
$class_stmt = $conn->prepare($class_query);
$class_stmt->execute([$class_id, CURRENT_ACADEMIC_YEAR]);
$class = $class_stmt->fetch();

if (!$class) {
    $_SESSION['error'] = "Class not found.";
    header('Location: classes.php');
    exit();
}

// Get students in class
$students_query = "SELECT * FROM students WHERE class_id = ? AND status = 'active' ORDER BY first_name, last_name";
$students_stmt = $conn->prepare($students_query);
$students_stmt->execute([$class_id]);
$students = $students_stmt->fetchAll();

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance_date'])) {
    $attendance_date = $_POST['attendance_date'];
    $marked_by = $_SESSION['user_id'];

    try {
        $conn->beginTransaction();

        foreach ($_POST['attendance'] as $student_id => $status) {
            // Check if attendance already exists for this date and student
            $check_query = "SELECT id FROM attendance WHERE student_id = ? AND date = ? AND class_id = ?";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->execute([$student_id, $attendance_date, $class_id]);
            $existing = $check_stmt->fetch();

            if ($existing) {
                // Update existing
                $update_query = "UPDATE attendance SET status = ?, remarks = ?, marked_by = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->execute([$status, $_POST['remarks'][$student_id] ?? '', $marked_by, $existing['id']]);
            } else {
                // Insert new
                $insert_query = "INSERT INTO attendance (student_id, class_id, date, status, remarks, marked_by) VALUES (?, ?, ?, ?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_query);
                $insert_stmt->execute([$student_id, $class_id, $attendance_date, $status, $_POST['remarks'][$student_id] ?? '', $marked_by]);
            }
        }

        $conn->commit();
        $_SESSION['success'] = "Attendance marked successfully for " . date('M d, Y', strtotime($attendance_date));
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error saving attendance: " . $e->getMessage();
    }
}

// Get attendance data for reports
$report_date = $_GET['report_date'] ?? date('Y-m-d');
$report_month = date('m', strtotime($report_date));
$report_year = date('Y', strtotime($report_date));

$attendance_query = "SELECT s.*,
    COUNT(CASE WHEN a.status = 'present' THEN 1 END) as days_present,
    COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as days_absent,
    COUNT(CASE WHEN a.status = 'late' THEN 1 END) as days_late,
    COUNT(DISTINCT a.date) as total_days
    FROM students s
    LEFT JOIN attendance a ON s.id = a.student_id AND MONTH(a.date) = ? AND YEAR(a.date) = ?
    WHERE s.class_id = ? AND s.status = 'active'
    GROUP BY s.id
    ORDER BY s.first_name, s.last_name";

$attendance_stmt = $conn->prepare($attendance_query);
$attendance_stmt->execute([$report_month, $report_year, $class_id]);
$attendance_data = $attendance_stmt->fetchAll();

// Get detailed attendance records
$detailed_query = "SELECT a.id, s.id as student_id, s.first_name, s.last_name, s.admission_number,
                  a.date, a.status, a.remarks
                  FROM attendance a
                  JOIN students s ON a.student_id = s.id
                  WHERE s.class_id = ? AND MONTH(a.date) = ? AND YEAR(a.date) = ?
                  ORDER BY s.first_name, s.last_name, a.date DESC";

$detailed_stmt = $conn->prepare($detailed_query);
$detailed_stmt->execute([$class_id, $report_month, $report_year]);
$detailed_attendance = $detailed_stmt->fetchAll();

$page_title = 'Class Details - ' . htmlspecialchars($class['class_name']);
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
        <!-- Back Button -->
        <div class="flex items-center gap-4">
            <a href="classes.php" class="flex items-center gap-2 text-teal-600 hover:text-teal-800 font-medium">
                <i class="fas fa-arrow-left"></i> Back to Classes
            </a>
        </div>

        <!-- Class Header -->
        <div class="bg-gradient-to-r from-teal-500 to-cyan-600 rounded-xl p-6 text-white">
            <h1 class="text-3xl font-bold"><i class="fas fa-school mr-3"></i><?php echo htmlspecialchars($class['class_name']); ?></h1>
            <p class="opacity-90"><?php echo htmlspecialchars($class['class_level']); ?> Level • Class Teacher: <?php echo htmlspecialchars($class['class_teacher_name'] ?: 'Not Assigned'); ?> • <?php echo count($students); ?> Students</p>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg flex items-center">
                <i class="fas fa-check-circle mr-2"></i> <?php echo $_SESSION['success'];
                                                            unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Navigation Tabs -->
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex space-x-1" role="tablist">
                <button onclick="showTab('students')" id="students-tab" class="tab-button active px-6 py-3 rounded-lg font-medium text-teal-600 bg-teal-50 border-b-2 border-teal-600">
                    <i class="fas fa-users mr-2"></i>Students
                </button>
                <button onclick="showTab('attendance')" id="attendance-tab" class="tab-button px-6 py-3 rounded-lg font-medium text-gray-600 hover:text-teal-600 hover:bg-teal-50">
                    <i class="fas fa-calendar-check mr-2"></i>Take Attendance
                </button>
                <button onclick="showTab('reports')" id="reports-tab" class="tab-button px-6 py-3 rounded-lg font-medium text-gray-600 hover:text-teal-600 hover:bg-teal-50">
                    <i class="fas fa-chart-bar mr-2"></i>Attendance Report
                </button>
            </div>
        </div>

        <!-- Students Tab -->
        <div id="students-content" class="tab-content">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
                    <h2 class="text-lg font-bold text-gray-800"><i class="fas fa-users mr-2 text-blue-600"></i>Class Students</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100 border-b-2 border-gray-300">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 bg-gray-50">Admission No.</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 bg-gray-50">Name</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 bg-gray-50">Gender</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 bg-gray-50">Date of Birth</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 bg-gray-50">Contact</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (count($students) > 0): ?>
                                <?php foreach ($students as $student): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 font-mono text-gray-900"><?php echo htmlspecialchars($student['admission_number']); ?></td>
                                        <td class="px-6 py-4 font-semibold text-gray-900"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                        <td class="px-6 py-4">
                                            <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold <?php echo $student['gender'] === 'Male' ? 'bg-blue-100 text-blue-800' : 'bg-pink-100 text-pink-800'; ?>">
                                                <?php echo $student['gender']; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-700"><?php echo date('M d, Y', strtotime($student['date_of_birth'])); ?></td>
                                        <td class="px-6 py-4 text-gray-700"><?php echo htmlspecialchars($student['phone'] ?: 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center">
                                        <div class="text-gray-500">
                                            <i class="fas fa-inbox text-4xl mb-3 block opacity-50"></i>
                                            <p class="font-medium">No students in this class</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Take Attendance Tab -->
        <div id="attendance-content" class="tab-content hidden">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-6"><i class="fas fa-calendar-check mr-2 text-green-600"></i>Mark Attendance</h2>

                <form method="POST" class="space-y-6">
                    <div class="flex gap-4 items-end">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Attendance Date</label>
                            <input type="date" name="attendance_date" value="<?php echo date('Y-m-d'); ?>" required
                                class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                        <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-2 rounded-lg transition font-medium">
                            <i class="fas fa-save mr-2"></i> Save Attendance
                        </button>
                    </div>

                    <?php if (count($students) > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-100 border-b-2 border-gray-300">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 bg-gray-50">Student</th>
                                        <th class="px-6 py-4 text-center text-sm font-bold text-gray-700 bg-gray-50">Status</th>
                                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 bg-gray-50">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php foreach ($students as $student): ?>
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-6 py-4 font-semibold text-gray-900">
                                                <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($student['admission_number']); ?></div>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <select name="attendance[<?php echo $student['id']; ?>]" required
                                                    class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                                                    <option value="present">Present</option>
                                                    <option value="absent">Absent</option>
                                                    <option value="late">Late</option>
                                                    <option value="excused">Excused</option>
                                                </select>
                                            </td>
                                            <td class="px-6 py-4">
                                                <input type="text" name="remarks[<?php echo $student['id']; ?>]" placeholder="Optional remarks"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-gray-500 py-8">
                            <i class="fas fa-inbox text-4xl mb-3 block opacity-50"></i>
                            <p class="font-medium">No students to mark attendance for</p>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Attendance Report Tab -->
        <div id="reports-content" class="tab-content hidden">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-bold text-gray-800"><i class="fas fa-chart-bar mr-2 text-purple-600"></i>Attendance Report</h2>
                    <div class="flex gap-2">
                        <select onchange="changeReportMonth(this.value)" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <?php
                            for ($i = 1; $i <= 12; $i++) {
                                $monthName = date('F', mktime(0, 0, 0, $i, 1));
                                $selected = ($i == $report_month) ? 'selected' : '';
                                echo "<option value='$i' $selected>$monthName</option>";
                            }
                            ?>
                        </select>
                        <select onchange="changeReportYear(this.value)" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <?php
                            $currentYear = date('Y');
                            for ($y = $currentYear - 2; $y <= $currentYear; $y++) {
                                $selected = ($y == $report_year) ? 'selected' : '';
                                echo "<option value='$y' $selected>$y</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100 border-b-2 border-gray-300">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 bg-gray-50">Student</th>
                                <th class="px-6 py-4 text-center text-sm font-bold text-gray-700 bg-gray-50">Present</th>
                                <th class="px-6 py-4 text-center text-sm font-bold text-gray-700 bg-gray-50">Absent</th>
                                <th class="px-6 py-4 text-center text-sm font-bold text-gray-700 bg-gray-50">Late</th>
                                <th class="px-6 py-4 text-center text-sm font-bold text-gray-700 bg-gray-50">Total Days</th>
                                <th class="px-6 py-4 text-center text-sm font-bold text-gray-700 bg-gray-50">Attendance %</th>
                                <th class="px-6 py-4 text-center text-sm font-bold text-gray-700 bg-gray-50">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($attendance_data as $student): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 font-semibold text-gray-900">
                                        <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-bold">
                                            <?php echo $student['days_present'] ?? 0; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-block bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-bold">
                                            <?php echo $student['days_absent'] ?? 0; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-block bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-bold">
                                            <?php echo $student['days_late'] ?? 0; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center font-medium text-gray-700"><?php echo $student['total_days'] ?? 0; ?></td>
                                    <td class="px-6 py-4 text-center">
                                        <?php
                                        $attendance_percent = 0;
                                        if ($student['total_days'] > 0) {
                                            $attendance_percent = round(($student['days_present'] / $student['total_days']) * 100, 2);
                                        }
                                        ?>
                                        <span class="inline-block <?php echo $attendance_percent >= 90 ? 'bg-green-100 text-green-800' : ($attendance_percent >= 75 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?> px-3 py-1 rounded-full text-sm font-bold">
                                            <?php echo $attendance_percent; ?>%
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button type="button" onclick="openDetailModal(<?php echo $student['id']; ?>)" class="inline-block bg-blue-100 text-blue-700 hover:bg-blue-200 px-3 py-2 rounded-lg text-sm font-medium transition">
                                            <i class="fas fa-eye mr-1"></i> Details
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Attendance Detail Modal -->
<div id="attendanceModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-2xl w-full max-h-[80vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="sticky top-0 bg-gradient-to-r from-blue-500 to-blue-600 text-white p-6 flex items-center justify-between">
            <h2 class="text-2xl font-bold flex items-center gap-2">
                <i class="fas fa-calendar-check"></i>
                <span id="modalStudentName"></span> - Attendance Details
            </h2>
            <button type="button" onclick="closeDetailModal()" class="text-white hover:bg-blue-700 p-2 rounded-lg transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <div id="attendanceDetailsList" class="space-y-3">
                <!-- Details will be populated by JavaScript -->
            </div>
        </div>
    </div>
</div>

<script>
    const attendanceData = <?php echo json_encode($detailed_attendance); ?>;

    function showTab(tabName) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(content => content.classList.add('hidden'));
        document.querySelectorAll('.tab-button').forEach(button => {
            button.classList.remove('active', 'text-teal-600', 'bg-teal-50', 'border-b-2', 'border-teal-600');
            button.classList.add('text-gray-600');
        });

        // Show selected tab
        document.getElementById(tabName + '-content').classList.remove('hidden');
        document.getElementById(tabName + '-tab').classList.add('active', 'text-teal-600', 'bg-teal-50', 'border-b-2', 'border-teal-600');
        document.getElementById(tabName + '-tab').classList.remove('text-gray-600');
    }

    function changeReportMonth(month) {
        const url = new URL(window.location);
        url.searchParams.set('report_date', '<?php echo $report_year; ?>-' + month + '-01');
        window.location = url;
    }

    function changeReportYear(year) {
        const url = new URL(window.location);
        url.searchParams.set('report_date', year + '-<?php echo $report_month; ?>-01');
        window.location = url;
    }

    function openDetailModal(studentId) {
        const student = attendanceData.find(record => record.student_id == studentId);
        if (!student) {
            alert('No attendance records found');
            return;
        }

        const studentRecords = attendanceData.filter(record => record.student_id == studentId);
        document.getElementById('modalStudentName').textContent = student.first_name + ' ' + student.last_name;

        let html = '';
        if (studentRecords.length === 0) {
            html = '<div class="text-center text-gray-500 py-8"><i class="fas fa-inbox text-4xl mb-2 block opacity-50"></i><p>No attendance records found</p></div>';
        } else {
            html = '<div class="overflow-x-auto"><table class="w-full"><thead class="bg-gray-100"><tr><th class="px-4 py-2 text-left text-sm font-bold">Date</th><th class="px-4 py-2 text-left text-sm font-bold">Status</th><th class="px-4 py-2 text-left text-sm font-bold">Remarks</th></tr></thead><tbody>';

            studentRecords.forEach(record => {
                const date = new Date(record.date).toLocaleDateString('en-US', {
                    weekday: 'short',
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
                const statusColor = record.status === 'present' ? 'bg-green-100 text-green-800' :
                    record.status === 'absent' ? 'bg-red-100 text-red-800' :
                    record.status === 'late' ? 'bg-yellow-100 text-yellow-800' :
                    'bg-gray-100 text-gray-800';

                html += `<tr class="border-b hover:bg-gray-50">
                <td class="px-4 py-3 text-sm">${date}</td>
                <td class="px-4 py-3"><span class="inline-block px-3 py-1 rounded-full text-xs font-semibold ${statusColor}">${record.status.charAt(0).toUpperCase() + record.status.slice(1)}</span></td>
                <td class="px-4 py-3 text-sm text-gray-700">${record.remarks ? record.remarks : '-'}</td>
            </tr>`;
            });

            html += '</tbody></table></div>';
        }

        document.getElementById('attendanceDetailsList').innerHTML = html;
        document.getElementById('attendanceModal').classList.remove('hidden');
    }

    function closeDetailModal() {
        document.getElementById('attendanceModal').classList.add('hidden');
    }

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDetailModal();
        }
    });
</script>

<?php include '../includes/footer.php'; ?>