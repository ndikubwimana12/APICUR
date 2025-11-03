<?php

/**
 * DOS - Assign Teacher to Classes/Subjects
 * School Management System - APICUR TSS
 */
require_once '../config/config.php';
requireLogin();

if (!hasRole('dos')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Assign Teacher';
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

// Get all classes
$classes_query = "SELECT * FROM classes WHERE academic_year = ? AND status = 'active' ORDER BY class_level, class_name";
$classes_stmt = $conn->prepare($classes_query);
$classes_stmt->execute([CURRENT_ACADEMIC_YEAR]);
$classes = $classes_stmt->fetchAll();

// Get all subjects
$subjects_query = "SELECT * FROM subjects ORDER BY subject_name";
$subjects_stmt = $conn->prepare($subjects_query);
$subjects_stmt->execute();
$subjects = $subjects_stmt->fetchAll();

// Get current assignments
$current_query = "SELECT * FROM teacher_subjects WHERE teacher_id = ? AND academic_year = ?";
$current_stmt = $conn->prepare($current_query);
$current_stmt->execute([$teacher_id, CURRENT_ACADEMIC_YEAR]);
$current_assignments = $current_stmt->fetchAll();

// Handle assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = $_POST['class_id'] ?? null;
    $subject_id = $_POST['subject_id'] ?? null;

    if ($class_id && $subject_id) {
        try {
            // Check if already assigned
            $check_query = "SELECT COUNT(*) as count FROM teacher_subjects 
                WHERE teacher_id = ? AND class_id = ? AND subject_id = ? AND academic_year = ?";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->execute([$teacher_id, $class_id, $subject_id, CURRENT_ACADEMIC_YEAR]);
            $check = $check_stmt->fetch();

            if ($check['count'] > 0) {
                $error = "This assignment already exists!";
            } else {
                $insert_query = "INSERT INTO teacher_subjects (teacher_id, class_id, subject_id, academic_year)
                    VALUES (?, ?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_query);
                $insert_stmt->execute([$teacher_id, $class_id, $subject_id, CURRENT_ACADEMIC_YEAR]);

                $success = "Assignment added successfully!";

                // Refresh current assignments
                $current_stmt = $conn->prepare($current_query);
                $current_stmt->execute([$teacher_id, CURRENT_ACADEMIC_YEAR]);
                $current_assignments = $current_stmt->fetchAll();
            }
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Handle remove assignment
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['assignment_id'])) {
    try {
        $delete_query = "DELETE FROM teacher_subjects WHERE id = ? AND teacher_id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->execute([$_GET['assignment_id'], $teacher_id]);

        $success = "Assignment removed!";

        // Refresh
        $current_stmt = $conn->prepare($current_query);
        $current_stmt->execute([$teacher_id, CURRENT_ACADEMIC_YEAR]);
        $current_assignments = $current_stmt->fetchAll();
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

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
    <div class="flex justify-between items-center">
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl p-6 text-white flex-1 mr-4">
            <h1 class="text-2xl font-bold">Assign <?php echo htmlspecialchars($teacher['full_name']); ?></h1>
            <p class="opacity-90">Add classes and subjects for this teacher</p>
        </div>
        <a href="teachers.php" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg">
            <i class="fas fa-arrow-left mr-2"></i>Back
        </a>
    </div>

    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg"><?php echo $success; ?></div>
    <?php endif; ?>

    <!-- Add Assignment Form -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Add New Assignment</h2>
        <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Class</label>
                <select name="class_id" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Class</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?php echo $class['id']; ?>">
                            <?php echo $class['class_name'] . ' (' . $class['class_level'] . ')'; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                <select name="subject_id" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Subject</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?php echo $subject['id']; ?>"><?php echo $subject['subject_name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i>Add Assignment
                </button>
            </div>
        </form>
    </div>

    <!-- Current Assignments -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Current Assignments</h2>
        <?php if (!empty($current_assignments)): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-700">Class</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-700">Subject</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($current_assignments as $assignment):
                            // Get class and subject names
                            $class_info = $conn->query("SELECT * FROM classes WHERE id = " . $assignment['class_id'])->fetch();
                            $subject_info = $conn->query("SELECT * FROM subjects WHERE id = " . $assignment['subject_id'])->fetch();
                        ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-700"><?php echo $class_info['class_name']; ?></td>
                                <td class="px-4 py-3 text-gray-700"><?php echo $subject_info['subject_name']; ?></td>
                                <td class="px-4 py-3">
                                    <a href="?id=<?php echo $teacher_id; ?>&action=remove&assignment_id=<?php echo $assignment['id']; ?>"
                                        onclick="return confirm('Remove this assignment?')"
                                        class="text-red-600 hover:text-red-700">
                                        <i class="fas fa-trash"></i> Remove
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-500">No assignments yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>