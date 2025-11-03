<?php

/**
 * DOS - Reports Management (Updated)
 * Report card generation and viewing
 */
require_once '../config/config.php';
requireLogin();

if (!hasRole('dos')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Reports';
$database = new Database();
$conn = $database->getConnection();

// Get all classes
$classes_query = "SELECT * FROM classes WHERE academic_year = ? AND status = 'active' ORDER BY class_level, class_name";
$classes_stmt = $conn->prepare($classes_query);
$classes_stmt->execute([CURRENT_ACADEMIC_YEAR]);
$classes = $classes_stmt->fetchAll();

$selected_class_id = $_GET['class_id'] ?? null;
$selected_term = $_GET['term'] ?? CURRENT_TERM;
$reports = [];

// Handle report card generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['action'] ?? null) === 'generate_reports')) {
    try {
        $class_id = $_POST['class_id'] ?? null;
        $term = $_POST['term'] ?? CURRENT_TERM;

        if (!$class_id) {
            throw new Exception('Please select a class');
        }

        // Get students in the class
        $students_query = "SELECT id FROM students WHERE class_id = ? AND status = 'active'";
        $students_stmt = $conn->prepare($students_query);
        $students_stmt->execute([$class_id]);
        $students = $students_stmt->fetchAll();

        $generated_count = 0;
        $conn->beginTransaction();

        foreach ($students as $student) {
            // Get average marks for this student
            $marks_query = "SELECT AVG(marks) as avg_marks FROM marks 
                          WHERE student_id = ? AND class_id = ? AND academic_year = ? AND term = ?";
            $marks_stmt = $conn->prepare($marks_query);
            $marks_stmt->execute([$student['id'], $class_id, CURRENT_ACADEMIC_YEAR, $term]);
            $marks_result = $marks_stmt->fetch();
            $average_marks = $marks_result['avg_marks'] ?? 0;

            // Determine grade based on average
            $grade = 'U'; // Ungraded
            if ($average_marks >= 80) $grade = 'A';
            elseif ($average_marks >= 70) $grade = 'B';
            elseif ($average_marks >= 60) $grade = 'C';
            elseif ($average_marks >= 50) $grade = 'D';
            elseif ($average_marks >= 40) $grade = 'E';
            else $grade = 'F';

            // Check if report card already exists
            $check_query = "SELECT id FROM report_cards WHERE student_id = ? AND class_id = ? AND academic_year = ? AND term = ?";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->execute([$student['id'], $class_id, CURRENT_ACADEMIC_YEAR, $term]);
            $existing = $check_stmt->fetch();

            if ($existing) {
                // Update existing report card
                $update_query = "UPDATE report_cards SET average_marks = ?, grade = ?, updated_at = NOW() 
                               WHERE student_id = ? AND class_id = ? AND academic_year = ? AND term = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->execute([$average_marks, $grade, $student['id'], $class_id, CURRENT_ACADEMIC_YEAR, $term]);
            } else {
                // Insert new report card
                $insert_query = "INSERT INTO report_cards (student_id, class_id, academic_year, term, average_marks, grade)
                               VALUES (?, ?, ?, ?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_query);
                $insert_stmt->execute([$student['id'], $class_id, CURRENT_ACADEMIC_YEAR, $term, $average_marks, $grade]);
                $generated_count++;
            }
        }

        // Update positions
        $position_query = "UPDATE report_cards SET position = (
            SELECT COUNT(*) FROM report_cards rc2 
            WHERE rc2.class_id = report_cards.class_id 
            AND rc2.academic_year = report_cards.academic_year 
            AND rc2.term = report_cards.term 
            AND rc2.average_marks >= report_cards.average_marks
        ) WHERE class_id = ? AND academic_year = ? AND term = ?";
        $position_stmt = $conn->prepare($position_query);
        $position_stmt->execute([$class_id, CURRENT_ACADEMIC_YEAR, $term]);

        $conn->commit();

        $_SESSION['success'] = 'Report cards generated/updated successfully for ' . count($students) . ' students!';
        header('Location: reports.php?class_id=' . $class_id . '&term=' . $term);
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error'] = 'Error generating report cards: ' . $e->getMessage();
        error_log($e->getMessage());
    }
}

// Get report cards for selected class
if ($selected_class_id) {
    $reports_query = "SELECT rc.*, s.first_name, s.last_name, s.admission_number, c.class_name
        FROM report_cards rc
        JOIN students s ON rc.student_id = s.id
        JOIN classes c ON rc.class_id = c.id
        WHERE rc.class_id = ? AND rc.academic_year = ? AND rc.term = ?
        ORDER BY rc.position ASC";

    $reports_stmt = $conn->prepare($reports_query);
    $reports_stmt->execute([$selected_class_id, CURRENT_ACADEMIC_YEAR, $selected_term]);
    $reports = $reports_stmt->fetchAll();
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
    <li><a href="performance.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chart-line"></i><span>Performance</span></a></li>
    <li><a href="reports.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg"><i class="fas fa-file-alt"></i><span>Reports</span></a></li>
</ul>';

include '../includes/header.php';
?>

<div class="space-y-6">
    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl p-6 text-white">
        <h1 class="text-3xl font-bold"><i class="fas fa-file-alt mr-3"></i>Academic Reports</h1>
        <p class="opacity-90">Generate and manage student report cards by class and term</p>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg flex items-center">
            <i class="fas fa-check-circle mr-2"></i> <?php echo $_SESSION['success'];
                                                        unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $_SESSION['error'];
                                                            unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <!-- Generate Report Cards -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-600 rounded-xl p-6">
        <h2 class="text-xl font-bold text-blue-900 mb-4"><i class="fas fa-magic mr-2"></i>Generate Report Cards</h2>
        <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Class</label>
                <select name="class_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Choose Class --</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?php echo $class['id']; ?>">
                            <?php echo htmlspecialchars($class['class_name']) . ' (' . $class['class_level'] . ')'; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Term</label>
                <select name="term" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="1">Term 1</option>
                    <option value="2">Term 2</option>
                    <option value="3">Term 3</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" name="action" value="generate_reports" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-bold transition">
                    <i class="fas fa-sync-alt mr-2"></i>Generate Report Cards
                </button>
            </div>
        </form>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4"><i class="fas fa-filter mr-2 text-indigo-600"></i>View Report Cards</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Class</label>
                <select onchange="document.location='?class_id=' + this.value + '&term=' + document.getElementById('term').value"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Select a Class --</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?php echo $class['id']; ?>" <?php echo ($selected_class_id == $class['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($class['class_name']) . ' (' . $class['class_level'] . ')'; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Term</label>
                <select id="term" onchange="document.location='?class_id=' + (document.querySelector('select').value || '') + '&term=' + this.value"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="1" <?php echo ($selected_term == '1') ? 'selected' : ''; ?>>Term 1</option>
                    <option value="2" <?php echo ($selected_term == '2') ? 'selected' : ''; ?>>Term 2</option>
                    <option value="3" <?php echo ($selected_term == '3') ? 'selected' : ''; ?>>Term 3</option>
                </select>
            </div>

            <?php if ($selected_class_id): ?>
                <div class="flex items-end">
                    <button onclick="window.print()" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-medium transition">
                        <i class="fas fa-print mr-2"></i>Print Report Cards
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($selected_class_id && !empty($reports)): ?>
        <!-- Report Cards Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-purple-50">
                <h2 class="text-lg font-bold text-gray-800"><i class="fas fa-list mr-2 text-indigo-600"></i>Report Cards - Term <?php echo $selected_term; ?></h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100 border-b-2 border-gray-300">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 bg-gray-50">Position</th>
                            <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 bg-gray-50">Student Name</th>
                            <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 bg-gray-50">Admission #</th>
                            <th class="px-6 py-4 text-center text-sm font-bold text-gray-700 bg-gray-50">Average %</th>
                            <th class="px-6 py-4 text-center text-sm font-bold text-gray-700 bg-gray-50">Grade</th>
                            <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 bg-gray-50">Comments</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($reports as $report): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <span class="inline-block bg-yellow-100 text-yellow-800 px-4 py-2 rounded-full text-sm font-bold">
                                        <i class="fas fa-medal mr-1"></i><?php echo $report['position'] ?? '-'; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-semibold text-gray-900">
                                    <?php echo htmlspecialchars($report['first_name'] . ' ' . $report['last_name']); ?>
                                </td>
                                <td class="px-6 py-4 text-gray-700"><?php echo $report['admission_number']; ?></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-block bg-blue-100 text-blue-800 px-4 py-2 rounded-full text-sm font-bold">
                                        <?php echo number_format($report['average_marks'], 2); ?>%
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-block bg-green-100 text-green-800 px-4 py-2 rounded-full text-sm font-bold">
                                        <?php echo $report['grade']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-700 text-xs max-w-xs">
                                    <?php
                                    $comment = $report['class_teacher_comment'] ?? '-';
                                    echo htmlspecialchars(substr($comment, 0, 50)) . (strlen($comment) > 50 ? '...' : '');
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
                <p class="text-gray-500 text-sm font-medium">Total Students</p>
                <h3 class="text-4xl font-bold text-blue-600 mt-2"><?php echo count($reports); ?></h3>
            </div>

            <?php
            $avg_class = 0;
            $top_student = isset($reports[0]) ? $reports[0] : null;
            $grade_a = 0;
            foreach ($reports as $r) {
                $avg_class += $r['average_marks'];
                if ($r['grade'] == 'A') $grade_a++;
            }
            $avg_class = count($reports) > 0 ? round($avg_class / count($reports), 2) : 0;
            ?>

            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
                <p class="text-gray-500 text-sm font-medium">Class Average</p>
                <h3 class="text-4xl font-bold text-green-600 mt-2"><?php echo $avg_class; ?>%</h3>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-purple-500">
                <p class="text-gray-500 text-sm font-medium">Grade A Count</p>
                <h3 class="text-4xl font-bold text-purple-600 mt-2"><?php echo $grade_a; ?></h3>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-orange-500">
                <p class="text-gray-500 text-sm font-medium">Top Student</p>
                <h3 class="text-lg font-bold text-orange-600 mt-2">
                    <?php echo $top_student ? htmlspecialchars(substr($top_student['first_name'], 0, 1) . '. ' . $top_student['last_name']) : '-'; ?>
                </h3>
            </div>
        </div>

    <?php elseif ($selected_class_id): ?>
        <div class="bg-yellow-50 border-2 border-yellow-200 rounded-xl p-8 text-center">
            <i class="fas fa-inbox text-4xl text-yellow-600 mb-3"></i>
            <p class="text-gray-700 font-medium">No report cards generated yet for this class and term</p>
            <p class="text-gray-600 text-sm mt-2">Use the "Generate Report Cards" section above to create them</p>
        </div>
    <?php else: ?>
        <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-8 text-center">
            <i class="fas fa-hand-pointer text-4xl text-blue-600 mb-3"></i>
            <p class="text-gray-700 font-medium">Select a class to view report cards</p>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>