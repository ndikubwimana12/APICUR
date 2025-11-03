<?php

/**
 * Teacher - Marks Entry
 * Enter and manage student marks for assigned subjects
 */
require_once '../config/config.php';
requireLogin();

if (!hasRole('teacher')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Marks Entry';

$database = new Database();
$conn = $database->getConnection();
$teacher_id = $_SESSION['user_id'];

$message = '';
$messageType = '';

// Handle marks submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_marks'])) {
    $class_id = $_POST['class_id'];
    $subject_id = $_POST['subject_id'];
    $exam_type = $_POST['exam_type'];
    $marks_data = $_POST['marks'] ?? [];

    // Verify teacher is assigned to this class and subject
    $verify_query = "SELECT COUNT(*) FROM teacher_subjects
                     WHERE teacher_id = ? AND class_id = ?
                     AND subject_id = ? AND academic_year = ?";
    $verify_stmt = $conn->prepare($verify_query);
    $verify_stmt->execute([
        $teacher_id,
        $class_id,
        $subject_id,
        CURRENT_ACADEMIC_YEAR
    ]);

    if ($verify_stmt->fetchColumn() == 0) {
        $message = 'You are not assigned to this class and subject.';
        $messageType = 'error';
    } else {
        // Check if marks already exist for this assessment type
        $check_query = "SELECT COUNT(*) FROM marks
                        WHERE class_id = ? AND subject_id = ?
                        AND assessment_type = ? AND academic_year = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->execute([
            $class_id,
            $subject_id,
            $exam_type,
            CURRENT_ACADEMIC_YEAR
        ]);

        if ($check_stmt->fetchColumn() > 0) {
            // Update existing marks
            $update_query = "UPDATE marks SET marks = ?, entered_by = ?, updated_at = NOW()
                             WHERE student_id = ? AND class_id = ? AND subject_id = ?
                             AND assessment_type = ? AND academic_year = ?";
            $update_stmt = $conn->prepare($update_query);

            $success_count = 0;
            foreach ($marks_data as $student_id => $marks) {
                if ($marks !== '') {
                    $update_stmt->execute([
                        floatval($marks),
                        $teacher_id,
                        $student_id,
                        $class_id,
                        $subject_id,
                        $exam_type,
                        CURRENT_ACADEMIC_YEAR
                    ]);
                    $success_count++;
                }
            }
            $message = "Marks updated successfully for {$success_count} students.";
        } else {
            // Insert new marks
            $insert_query = "INSERT INTO marks (student_id, class_id, subject_id, assessment_type, marks, max_marks, academic_year, entered_by, created_at)
                             VALUES (?, ?, ?, ?, ?, 100, ?, ?, NOW())";
            $insert_stmt = $conn->prepare($insert_query);

            $success_count = 0;
            foreach ($marks_data as $student_id => $marks) {
                if ($marks !== '') {
                    $insert_stmt->execute([
                        $student_id,
                        $class_id,
                        $subject_id,
                        $exam_type,
                        floatval($marks),
                        CURRENT_ACADEMIC_YEAR,
                        $teacher_id
                    ]);
                    $success_count++;
                }
            }
            $message = "Marks entered successfully for {$success_count} students.";
        }
        $messageType = 'success';
    }
}

// Get teacher's assigned classes and subjects
$query = "SELECT DISTINCT c.id as class_id, c.class_name, s.id as subject_id, s.subject_name
          FROM teacher_subjects ts
          INNER JOIN classes c ON ts.class_id = c.id
          INNER JOIN subjects s ON ts.subject_id = s.id
          WHERE ts.teacher_id = :teacher_id AND ts.academic_year = :academic_year
          ORDER BY c.class_name, s.subject_name";

$stmt = $conn->prepare($query);
$stmt->execute([
    'teacher_id' => $teacher_id,
    'academic_year' => CURRENT_ACADEMIC_YEAR
]);
$assigned_subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get selected parameters
$selected_class_id = $_GET['class_id'] ?? ($assigned_subjects[0]['class_id'] ?? null);
$selected_subject_id = $_GET['subject_id'] ?? null;
$selected_exam_type = $_GET['exam_type'] ?? 'mid_term';

// Get students and existing marks for selected class/subject/exam
$students = [];
if ($selected_class_id && $selected_subject_id) {
    $student_query = "SELECT s.id, s.student_id, s.first_name, s.last_name, m.marks
                      FROM students s
                      LEFT JOIN marks m ON s.id = m.student_id AND m.class_id = ?
                      AND m.subject_id = ? AND m.assessment_type = ? AND m.academic_year = ?
                      WHERE s.class_id = ? AND s.status = 'active'
                      ORDER BY s.first_name, s.last_name";
    $student_stmt = $conn->prepare($student_query);
    $student_stmt->execute([
        $selected_class_id,
        $selected_subject_id,
        $selected_exam_type,
        CURRENT_ACADEMIC_YEAR,
        $selected_class_id
    ]);
    $students = $student_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Sidebar menu for teacher
$sidebar_menu = '
<ul class="space-y-2">
    <li><a href="dashboard.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
    <li><a href="my_classes.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chalkboard"></i><span>My Classes</span></a></li>
    <li><a href="students.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-user-graduate"></i><span>Students</span></a></li>
    <li><a href="attendance.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-clipboard-check"></i><span>Attendance</span></a></li>
    <li><a href="marks.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg"><i class="fas fa-chart-line"></i><span>Marks Entry</span></a></li>
    <li><a href="reports.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-file-alt"></i><span>Reports</span></a></li>
    <li><a href="timetable.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-calendar"></i><span>Timetable</span></a></li>
    <li><a href="documents.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-folder"></i><span>Documents</span></a></li>
</ul>';

include '../includes/header.php';
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Marks Entry</h1>
            <p class="text-gray-500 text-sm">Enter and update student marks for your subjects.</p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="bg-<?php echo $messageType === 'success' ? 'green' : 'red'; ?>-100 border border-<?php echo $messageType === 'success' ? 'green' : 'red'; ?>-400 text-<?php echo $messageType === 'success' ? 'green' : 'red'; ?>-700 px-4 py-3 rounded-lg">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($assigned_subjects)): ?>
        <div class="bg-white rounded-xl shadow-sm p-12">
            <div class="text-center text-gray-500">
                <i class="fas fa-chart-line text-6xl mb-4 opacity-20"></i>
                <h3 class="text-lg font-medium mb-2">No Classes Assigned</h3>
                <p>You haven't been assigned any classes yet. Contact your administrator.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-2">Select Class & Subject</label>
                    <select name="class_id" id="class_select" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <?php foreach ($assigned_subjects as $subject): ?>
                            <option value="<?php echo $subject['class_id']; ?>_<?php echo $subject['subject_id']; ?>"
                                <?php echo ($selected_class_id == $subject['class_id'] && $selected_subject_id == $subject['subject_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($subject['class_name'] . ' - ' . $subject['subject_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-2">Exam Type</label>
                    <select name="exam_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="mid_term" <?php echo $selected_exam_type === 'mid_term' ? 'selected' : ''; ?>>Mid Term</option>
                        <option value="final_term" <?php echo $selected_exam_type === 'final_term' ? 'selected' : ''; ?>>Final Term</option>
                        <option value="quiz" <?php echo $selected_exam_type === 'quiz' ? 'selected' : ''; ?>>Quiz</option>
                        <option value="assignment" <?php echo $selected_exam_type === 'assignment' ? 'selected' : ''; ?>>Assignment</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Load Students</button>
                </div>
            </form>

            <?php if (!empty($students)): ?>
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="class_id" value="<?php echo $selected_class_id; ?>">
                    <input type="hidden" name="subject_id" value="<?php echo $selected_subject_id; ?>">
                    <input type="hidden" name="exam_type" value="<?php echo $selected_exam_type; ?>">

                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            Enter <?php echo ucwords(str_replace('_', ' ', $selected_exam_type)); ?> Marks (Out of 100)
                        </h3>

                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Student ID</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Marks</th>
                                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Grade</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php foreach ($students as $student): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($student['student_id']); ?></td>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <input type="number" name="marks[<?php echo $student['id']; ?>]" value="<?php echo $student['marks_obtained'] ?? ''; ?>"
                                                    min="0" max="100" step="0.5" placeholder="0-100"
                                                    class="w-20 border border-gray-300 rounded px-2 py-1 text-center focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            </td>
                                            <td class="px-4 py-3 text-center text-sm font-medium">
                                                <?php
                                                $marks = $student['marks_obtained'] ?? '';
                                                if ($marks !== '') {
                                                    $marks = floatval($marks);
                                                    if ($marks >= 90) echo '<span class="text-green-600">A+</span>';
                                                    elseif ($marks >= 80) echo '<span class="text-green-600">A</span>';
                                                    elseif ($marks >= 70) echo '<span class="text-blue-600">B+</span>';
                                                    elseif ($marks >= 60) echo '<span class="text-blue-600">B</span>';
                                                    elseif ($marks >= 50) echo '<span class="text-yellow-600">C+</span>';
                                                    elseif ($marks >= 40) echo '<span class="text-yellow-600">C</span>';
                                                    else echo '<span class="text-red-600">F</span>';
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" name="save_marks" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 font-medium">
                            <i class="fas fa-save mr-2"></i> Save Marks
                        </button>
                    </div>
                </form>
            <?php elseif (isset($_GET['class_id'])): ?>
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-users text-4xl mb-3 opacity-20"></i>
                    <p>No students found in this class.</p>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    document.getElementById('class_select').addEventListener('change', function() {
        const [classId, subjectId] = this.value.split('_');
        const url = new URL(window.location);
        url.searchParams.set('class_id', classId);
        url.searchParams.set('subject_id', subjectId);
        window.location.href = url.toString();
    });
</script>

<?php include '../includes/footer.php'; ?>