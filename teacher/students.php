<?php

/**
 * Teacher - Students
 * Shows students in teacher's assigned classes
 */
require_once '../config/config.php';
requireLogin();

if (!hasRole('teacher')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Students';

$database = new Database();
$conn = $database->getConnection();
$teacher_id = $_SESSION['user_id'];

// Fetch filters from query string
$searchTerm = trim($_GET['search'] ?? '');
$classFilter = $_GET['class'] ?? '';

// Get teacher's assigned classes
$teacher_classes_query = "SELECT DISTINCT class_id FROM teacher_subjects WHERE teacher_id = ? AND academic_year = ?";
$teacher_classes_stmt = $conn->prepare($teacher_classes_query);
$teacher_classes_stmt->execute([
    $teacher_id,
    CURRENT_ACADEMIC_YEAR
]);
$teacher_class_ids = $teacher_classes_stmt->fetchAll(PDO::FETCH_COLUMN);

if (empty($teacher_class_ids)) {
    $students = [];
    $classes = [];
} else {
    // Build query for students in teacher's classes
    $filters = [];
    $sqlConditions = ["students.class_id IN (" . str_repeat('?,', count($teacher_class_ids) - 1) . "?)"];

    if ($searchTerm !== '') {
        $sqlConditions[] = "(students.first_name LIKE ? OR students.last_name LIKE ? OR students.student_id LIKE ? OR students.admission_number LIKE ?)";
        $filters[] = "%{$searchTerm}%";
        $filters[] = "%{$searchTerm}%";
        $filters[] = "%{$searchTerm}%";
        $filters[] = "%{$searchTerm}%";
    }

    if ($classFilter !== '' && in_array($classFilter, $teacher_class_ids)) {
        $sqlConditions[] = 'students.class_id = ?';
        $filters[] = $classFilter;
    }

    $whereClause = 'WHERE ' . implode(' AND ', $sqlConditions) . ' AND students.status = \'active\'';

    // Pagination setup
    $perPage = RECORDS_PER_PAGE;
    $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
    $offset = ($page - 1) * $perPage;

    $countQuery = "SELECT COUNT(*) FROM students {$whereClause}";
    $countStmt = $conn->prepare($countQuery);
    $countStmt->execute(array_merge($teacher_class_ids, $filters));
    $totalRecords = (int) $countStmt->fetchColumn();
    $totalPages = max(1, (int) ceil($totalRecords / $perPage));

    // Main query
    $query = "SELECT students.*, classes.class_name
              FROM students
              INNER JOIN classes ON students.class_id = classes.id
              {$whereClause}
              ORDER BY classes.class_name, students.first_name, students.last_name
              LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($query);
    $all_params = array_merge($teacher_class_ids, $filters, [$perPage, $offset]);
    $stmt->execute($all_params);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch teacher's classes for dropdown
    $classQuery = "SELECT c.id, c.class_name
                   FROM classes c
                   WHERE c.id IN (" . str_repeat('?,', count($teacher_class_ids) - 1) . "?)
                   ORDER BY c.class_name";
    $classStmt = $conn->prepare($classQuery);
    $classStmt->execute($teacher_class_ids);
    $classes = $classStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Sidebar menu for teacher
$sidebar_menu = '
<ul class="space-y-2">
    <li><a href="dashboard.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
    <li><a href="my_classes.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chalkboard"></i><span>My Classes</span></a></li>
    <li><a href="students.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg"><i class="fas fa-user-graduate"></i><span>Students</span></a></li>
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
            <h1 class="text-2xl font-bold text-gray-800">My Students</h1>
            <p class="text-gray-500 text-sm">View students in your assigned classes.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="reports.php?type=students" class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-700 px-4 py-2 rounded-lg shadow-sm hover:bg-gray-100">
                <i class="fas fa-download"></i>
                <span>Export CSV</span>
            </a>
        </div>
    </div>

    <?php if (empty($teacher_class_ids)): ?>
        <div class="bg-white rounded-xl shadow-sm p-12">
            <div class="text-center text-gray-500">
                <i class="fas fa-user-graduate text-6xl mb-4 opacity-20"></i>
                <h3 class="text-lg font-medium mb-2">No Classes Assigned</h3>
                <p>You haven't been assigned any classes yet. Contact your administrator.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-2">Search</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" placeholder="Name, ID, admission number" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-2">Class</label>
                    <select name="class" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All My Classes</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['id']; ?>" <?php echo ($classFilter == $class['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($class['class_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Filter</button>
                    <a href="students.php" class="w-full bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-center hover:bg-gray-200">Reset</a>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Class</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Admission Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                    <i class="fas fa-user-graduate text-4xl mb-3 opacity-20"></i>
                                    <p class="mb-2">No student records found.</p>
                                    <?php if ($searchTerm || $classFilter): ?>
                                        <p class="text-sm">Try adjusting your search criteria.</p>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students as $student): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($student['student_id']); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        <div class="font-semibold text-gray-900"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></div>
                                        <div class="text-xs text-gray-500"><?php echo htmlspecialchars($student['admission_number']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($student['class_name']); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-500"><?php echo formatDate($student['admission_date']); ?></td>
                                    <td class="px-6 py-4 text-sm flex items-center gap-3">
                                        <a href="student_details.php?id=<?php echo $student['id']; ?>" class="text-indigo-600 hover:text-indigo-700" title="View Details"><i class="fas fa-eye"></i></a>
                                        <a href="marks.php?student_id=<?php echo $student['id']; ?>" class="text-blue-600 hover:text-blue-700" title="View Marks"><i class="fas fa-chart-line"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="px-6 py-4 bg-gray-50 flex items-center justify-between text-sm text-gray-600">
                    <span>Showing <?php echo min($totalRecords, $offset + 1); ?> to <?php echo min($totalRecords, $offset + $perPage); ?> of <?php echo $totalRecords; ?> students</span>
                    <div class="flex items-center gap-2">
                        <?php if ($page > 1): ?>
                            <a href="<?php echo buildPageLink($page - 1); ?>" class="px-3 py-1 rounded-lg bg-white border hover:bg-gray-100">Previous</a>
                        <?php endif; ?>
                        <span class="px-3 py-1 rounded-lg bg-indigo-100 text-indigo-700 font-medium">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
                        <?php if ($page < $totalPages): ?>
                            <a href="<?php echo buildPageLink($page + 1); ?>" class="px-3 py-1 rounded-lg bg-white border hover:bg-gray-100">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>

<?php
/**
 * Helper to rebuild page link with query parameters
 */
function buildPageLink($pageNumber)
{
    $params = $_GET;
    $params['page'] = $pageNumber;
    return 'students.php?' . http_build_query($params);
}
?>