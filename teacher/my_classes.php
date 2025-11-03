<?php

/**
 * Teacher - My Classes
 * Shows classes assigned to the teacher with subjects
 */
require_once '../config/config.php';
requireLogin();

if (!hasRole('teacher')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'My Classes';

$database = new Database();
$conn = $database->getConnection();
$teacher_id = $_SESSION['user_id'];

// Get assigned classes with subjects
$query = "SELECT DISTINCT c.id, c.class_name, c.class_level, c.section, c.capacity,
                 s.subject_name, s.id as subject_id, ts.academic_year,
                 COUNT(st.id) as student_count
          FROM teacher_subjects ts
          INNER JOIN classes c ON ts.class_id = c.id
          INNER JOIN subjects s ON ts.subject_id = s.id
          LEFT JOIN students st ON st.class_id = c.id AND st.status = 'active'
          WHERE ts.teacher_id = ?
          AND ts.academic_year = ?
          GROUP BY c.id, s.id
          ORDER BY c.class_level, c.class_name, s.subject_name";

$stmt = $conn->prepare($query);
$stmt->execute([
    $teacher_id,
    CURRENT_ACADEMIC_YEAR
]);
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group by class for display
$grouped_classes = [];
foreach ($classes as $class) {
    $class_key = $class['id'];
    if (!isset($grouped_classes[$class_key])) {
        $grouped_classes[$class_key] = [
            'id' => $class['id'],
            'class_name' => $class['class_name'],
            'class_level' => $class['class_level'],
            'section' => $class['section'],
            'capacity' => $class['capacity'],
            'student_count' => $class['student_count'],
            'subjects' => []
        ];
    }
    $grouped_classes[$class_key]['subjects'][] = [
        'id' => $class['subject_id'],
        'name' => $class['subject_name']
    ];
}

// Sidebar menu for teacher
$sidebar_menu = '
<ul class="space-y-2">
    <li><a href="dashboard.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
    <li><a href="my_classes.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg"><i class="fas fa-chalkboard"></i><span>My Classes</span></a></li>
    <li><a href="students.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-user-graduate"></i><span>Students</span></a></li>
    <li><a href="attendance.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-clipboard-check"></i><span>Attendance</span></a></li>
    <li><a href="marks.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chart-line"></i><span>Marks Entry</span></a></li>
    <li><a href="reports.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-file-alt"></i><span>Reports</span></a></li>
    <li><a href="timetable.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-calendar"></i><span>Timetable</span></a></li>
    <li><a href="documents.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-folder"></i><span>Documents</span></a></li>
</ul>';

include '../includes/header.php';
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">My Classes</h1>
            <p class="text-gray-500 text-sm">View and manage your assigned classes and subjects.</p>
        </div>
    </div>

    <?php if (empty($grouped_classes)): ?>
        <div class="bg-white rounded-xl shadow-sm p-12">
            <div class="text-center text-gray-500">
                <i class="fas fa-chalkboard text-6xl mb-4 opacity-20"></i>
                <h3 class="text-lg font-medium mb-2">No Classes Assigned</h3>
                <p>You haven't been assigned any classes yet for the current academic year.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($grouped_classes as $class): ?>
                <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 p-4">
                        <h3 class="text-lg font-bold text-white"><?php echo htmlspecialchars($class['class_name']); ?></h3>
                        <p class="text-blue-100 text-sm">
                            <?php echo htmlspecialchars($class['class_level']); ?>
                            <?php if ($class['section']): ?> - <?php echo htmlspecialchars($class['section']); ?><?php endif; ?>
                        </p>
                    </div>

                    <div class="p-4 space-y-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Students:</span>
                            <span class="font-semibold text-gray-800"><?php echo $class['student_count']; ?>/<?php echo $class['capacity']; ?></span>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-2">Subjects:</p>
                            <div class="flex flex-wrap gap-2">
                                <?php foreach ($class['subjects'] as $subject): ?>
                                    <span class="px-2 py-1 bg-indigo-100 text-indigo-700 text-xs rounded-full">
                                        <?php echo htmlspecialchars($subject['name']); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="flex gap-2 pt-2">
                            <a href="class_details.php?id=<?php echo $class['id']; ?>"
                                class="flex-1 bg-indigo-600 text-white text-center px-3 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
                                View Details
                            </a>
                            <a href="attendance.php?class_id=<?php echo $class['id']; ?>"
                                class="flex-1 bg-green-600 text-white text-center px-3 py-2 rounded-lg text-sm hover:bg-green-700 transition">
                                Mark Attendance
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>