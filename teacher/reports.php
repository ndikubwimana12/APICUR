<?php

/**
 * Teacher - Reports
 * Generate and view reports for classes, attendance, marks
 */
require_once '../config/config.php';
requireLogin();

if (!hasRole('teacher')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Reports';

$database = new Database();
$conn = $database->getConnection();
$teacher_id = $_SESSION['user_id'];

// Get teacher's assigned classes and subjects
$query = "SELECT DISTINCT c.id as class_id, c.class_name, s.id as subject_id, s.subject_name
          FROM teacher_subjects ts
          INNER JOIN classes c ON ts.class_id = c.id
          INNER JOIN subjects s ON ts.subject_id = s.id
          WHERE ts.teacher_id = :teacher_id AND ts.academic_year = :academic_year
          ORDER BY c.class_name, s.subject_name";

$stmt = $conn->prepare($query);
$stmt->execute([
    ':teacher_id' => $teacher_id,
    ':academic_year' => CURRENT_ACADEMIC_YEAR
]);
$assigned_subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle report generation
$report_type = $_GET['type'] ?? 'attendance';
$selected_class_id = $_GET['class_id'] ?? null;
$selected_subject_id = $_GET['subject_id'] ?? null;
$report_data = [];

if ($selected_class_id && $selected_subject_id) {
    if ($report_type === 'attendance') {
        // Attendance report
        $report_query = "SELECT s.student_id, s.first_name, s.last_name,
                                COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present_days,
                                COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent_days,
                                COUNT(CASE WHEN a.status = 'late' THEN 1 END) as late_days,
                                COUNT(a.id) as total_days,
                                ROUND((COUNT(CASE WHEN a.status = 'present' THEN 1 END) / COUNT(a.id)) * 100, 1) as attendance_percentage
                         FROM students s
                         LEFT JOIN attendance a ON s.id = a.student_id AND a.class_id = ?
                         WHERE s.class_id = ? AND s.status = 'active'
                         GROUP BY s.id
                         ORDER BY s.first_name, s.last_name";
        $report_stmt = $conn->prepare($report_query);
        $report_stmt->execute([
            $selected_class_id,
            $selected_class_id
        ]);
        $report_data = $report_stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($report_type === 'marks') {
        // Marks report
        $report_query = "SELECT s.student_id, s.first_name, s.last_name,
                                MAX(CASE WHEN m.assessment_type = 'formative' THEN m.marks END) as formative,
                                MAX(CASE WHEN m.assessment_type = 'continuous' THEN m.marks END) as continuous,
                                MAX(CASE WHEN m.assessment_type = 'exam' THEN m.marks END) as exam,
                                ROUND(AVG(m.marks), 1) as average_marks,
                                m.max_marks
                         FROM students s
                         LEFT JOIN marks m ON s.id = m.student_id AND m.class_id = ? AND m.subject_id = ? AND m.academic_year = ?
                         WHERE s.class_id = ? AND s.status = 'active'
                         GROUP BY s.id
                         ORDER BY s.first_name, s.last_name";
        $report_stmt = $conn->prepare($report_query);
        $report_stmt->execute([
            $selected_class_id,
            $selected_subject_id,
            CURRENT_ACADEMIC_YEAR,
            $selected_class_id
        ]);
        $report_data = $report_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Sidebar menu for teacher
$sidebar_menu = '
<ul class="space-y-2">
    <li><a href="dashboard.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
    <li><a href="my_classes.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chalkboard"></i><span>My Classes</span></a></li>
    <li><a href="students.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-user-graduate"></i><span>Students</span></a></li>
    <li><a href="attendance.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-clipboard-check"></i><span>Attendance</span></a></li>
    <li><a href="marks.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chart-line"></i><span>Marks Entry</span></a></li>
    <li><a href="reports.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg"><i class="fas fa-file-alt"></i><span>Reports</span></a></li>
    <li><a href="timetable.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-calendar"></i><span>Timetable</span></a></li>
    <li><a href="documents.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-folder"></i><span>Documents</span></a></li>
</ul>';

include '../includes/header.php';
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Reports</h1>
            <p class="text-gray-500 text-sm">Generate and view reports for your classes.</p>
        </div>
    </div>

    <?php if (empty($assigned_subjects)): ?>
        <div class="bg-white rounded-xl shadow-sm p-12">
            <div class="text-center text-gray-500">
                <i class="fas fa-file-alt text-6xl mb-4 opacity-20"></i>
                <h3 class="text-lg font-medium mb-2">No Classes Assigned</h3>
                <p>You haven't been assigned any classes yet. Contact your administrator.</p>
            </div>
        </div>
    <?php else: ?>
        <!-- Report Type Selection -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex flex-wrap gap-4 mb-6">
                <a href="?type=attendance" class="px-4 py-2 rounded-lg <?php echo $report_type === 'attendance' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?> transition">
                    Attendance Report
                </a>
                <a href="?type=marks" class="px-4 py-2 rounded-lg <?php echo $report_type === 'marks' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?> transition">
                    Marks Report
                </a>
            </div>

            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input type="hidden" name="type" value="<?php echo $report_type; ?>">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-2">Select Class & Subject</label>
                    <select name="class_id" id="class_select" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Class & Subject</option>
                        <?php foreach ($assigned_subjects as $subject): ?>
                            <option value="<?php echo $subject['class_id']; ?>_<?php echo $subject['subject_id']; ?>"
                                <?php echo ($selected_class_id == $subject['class_id'] && $selected_subject_id == $subject['subject_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($subject['class_name'] . ' - ' . $subject['subject_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Generate Report</button>
                    <?php if (!empty($report_data)): ?>
                        <a href="?type=<?php echo $report_type; ?>&class_id=<?php echo $selected_class_id; ?>&subject_id=<?php echo $selected_subject_id; ?>&export=csv"
                            class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">Export CSV</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <?php if (!empty($report_data)): ?>
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">
                        <?php echo ucfirst($report_type); ?> Report
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <?php if ($report_type === 'attendance'): ?>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Present</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Absent</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Late</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Total Days</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Attendance %</th>
                                <?php else: ?>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Mid Term</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Final Term</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Quiz</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Assignment</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Average</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($report_data as $row): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['student_id']); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>
                                    </td>
                                    <?php if ($report_type === 'attendance'): ?>
                                        <td class="px-6 py-4 text-sm text-center text-green-600 font-medium"><?php echo $row['present_days']; ?></td>
                                        <td class="px-6 py-4 text-sm text-center text-red-600 font-medium"><?php echo $row['absent_days']; ?></td>
                                        <td class="px-6 py-4 text-sm text-center text-yellow-600 font-medium"><?php echo $row['late_days']; ?></td>
                                        <td class="px-6 py-4 text-sm text-center"><?php echo $row['total_days']; ?></td>
                                        <td class="px-6 py-4 text-sm text-center">
                                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                                <?php
                                                $percentage = $row['attendance_percentage'] ?? 0;
                                                if ($percentage >= 90) echo 'bg-green-100 text-green-800';
                                                elseif ($percentage >= 75) echo 'bg-yellow-100 text-yellow-800';
                                                else echo 'bg-red-100 text-red-800';
                                                ?>">
                                                <?php echo $row['total_days'] > 0 ? $percentage . '%' : '-'; ?>
                                            </span>
                                        </td>
                                    <?php else: ?>
                                        <td class="px-6 py-4 text-sm text-center"><?php echo $row['mid_term'] ?? '-'; ?></td>
                                        <td class="px-6 py-4 text-sm text-center"><?php echo $row['final_term'] ?? '-'; ?></td>
                                        <td class="px-6 py-4 text-sm text-center"><?php echo $row['quiz'] ?? '-'; ?></td>
                                        <td class="px-6 py-4 text-sm text-center"><?php echo $row['assignment'] ?? '-'; ?></td>
                                        <td class="px-6 py-4 text-sm text-center font-medium">
                                            <span class="px-2 py-1 rounded-full text-xs
                                                <?php
                                                $avg = $row['average_marks'] ?? 0;
                                                if ($avg >= 80) echo 'bg-green-100 text-green-800';
                                                elseif ($avg >= 60) echo 'bg-blue-100 text-blue-800';
                                                elseif ($avg >= 40) echo 'bg-yellow-100 text-yellow-800';
                                                else echo 'bg-red-100 text-red-800';
                                                ?>">
                                                <?php echo $avg > 0 ? $avg : '-'; ?>
                                            </span>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php elseif (isset($_GET['class_id'])): ?>
            <div class="bg-white rounded-xl shadow-sm p-12">
                <div class="text-center text-gray-500">
                    <i class="fas fa-chart-bar text-4xl mb-3 opacity-20"></i>
                    <p>No data available for the selected criteria.</p>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
    document.getElementById('class_select').addEventListener('change', function() {
        const [classId, subjectId] = this.value.split('_');
        if (classId && subjectId) {
            const url = new URL(window.location);
            url.searchParams.set('class_id', classId);
            url.searchParams.set('subject_id', subjectId);
            window.location.href = url.toString();
        }
    });
</script>

<?php include '../includes/footer.php'; ?>