<?php

/**
 * Secretary - Students Management
 * Displays student list with filters and actions
 */
require_once '../config/config.php';
requireLogin();

if (!hasRole('secretary')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Students';

$database = new Database();
$conn = $database->getConnection();

// Fetch filters from query string
$searchTerm = trim($_GET['search'] ?? '');
$classFilter = $_GET['class'] ?? '';
$statusFilter = $_GET['status'] ?? '';

$filters = [];
$sqlConditions = [];

if ($searchTerm !== '') {
    $sqlConditions[] = "(students.first_name LIKE :search OR students.last_name LIKE :search OR students.student_id LIKE :search OR students.admission_number LIKE :search)";
    $filters[':search'] = "%{$searchTerm}%";
}

if ($classFilter !== '') {
    $sqlConditions[] = 'students.class_id = :class_id';
    $filters[':class_id'] = $classFilter;
}

if ($statusFilter !== '') {
    $sqlConditions[] = 'students.status = :status';
    $filters[':status'] = $statusFilter;
}

$whereClause = '';
if (!empty($sqlConditions)) {
    $whereClause = 'WHERE ' . implode(' AND ', $sqlConditions);
}

// Pagination setup
$perPage = RECORDS_PER_PAGE;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

$countQuery = "SELECT COUNT(*) FROM students {$whereClause}";
$countStmt = $conn->prepare($countQuery);
$countStmt->execute($filters);
$totalRecords = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalRecords / $perPage));

// Main query
$query = "SELECT students.*, classes.class_name
          FROM students
          LEFT JOIN classes ON students.class_id = classes.id
          {$whereClause}
          ORDER BY students.created_at DESC
          LIMIT :limit OFFSET :offset";

$stmt = $conn->prepare($query);
foreach ($filters as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch classes for dropdown
$classStmt = $conn->query('SELECT id, class_name FROM classes WHERE status = "active" ORDER BY class_name');
$classes = $classStmt->fetchAll(PDO::FETCH_ASSOC);

$statuses = [
    'active' => 'Active',
    'graduated' => 'Graduated',
    'transferred' => 'Transferred',
    'dropped' => 'Dropped'
];

// Sidebar
$sidebar_menu = '
<ul class="space-y-2">
    <li><a href="dashboard.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
    <li><a href="students.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg"><i class="fas fa-user-graduate"></i><span>Students</span></a></li>
    <li><a href="register_student.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-user-plus"></i><span>Register Student</span></a></li>
    <li><a href="documents.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-file-alt"></i><span>Documents</span></a></li>
    <li><a href="meetings.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-calendar-alt"></i><span>Meetings</span></a></li>
    <li><a href="reports.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chart-bar"></i><span>Reports</span></a></li>
</ul>';

include '../includes/header.php';
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Student Management</h1>
            <p class="text-gray-500 text-sm">View, search, and manage student records.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="register_student.php" class="inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg shadow-sm hover:bg-indigo-700">
                <i class="fas fa-user-plus"></i>
                <span>Register Student</span>
            </a>
            <a href="reports.php?type=students" class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-700 px-4 py-2 rounded-lg shadow-sm hover:bg-gray-100">
                <i class="fas fa-download"></i>
                <span>Export CSV</span>
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-2">Search</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" placeholder="Name, ID, admission number" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-2">Class</label>
                <select name="class" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Classes</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?php echo $class['id']; ?>" <?php echo ($classFilter == $class['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($class['class_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-2">Status</label>
                <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Statuses</option>
                    <?php foreach ($statuses as $key => $label): ?>
                        <option value="<?php echo $key; ?>" <?php echo ($statusFilter === $key) ? 'selected' : ''; ?>><?php echo $label; ?></option>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                <i class="fas fa-user-graduate text-4xl mb-3 opacity-20"></i>
                                <p class="mb-2">No student records found.</p>
                                <a href="register_student.php" class="text-indigo-600 hover:text-indigo-700 font-medium">Register a new student →</a>
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
                                <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($student['class_name'] ?? 'Unassigned'); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500"><?php echo formatDate($student['admission_date']); ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full
                                        <?php
                                        $statusClass = [
                                            'active' => 'bg-green-100 text-green-800',
                                            'graduated' => 'bg-blue-100 text-blue-800',
                                            'transferred' => 'bg-yellow-100 text-yellow-800',
                                            'dropped' => 'bg-red-100 text-red-800'
                                        ];
                                        echo $statusClass[$student['status']] ?? 'bg-gray-100 text-gray-800';
                                        ?>"><?php echo ucfirst($student['status']); ?></span>
                                </td>
                                <td class="px-6 py-4 text-sm flex items-center gap-3">
                                    <a href="student_details.php?id=<?php echo $student['id']; ?>" class="text-indigo-600 hover:text-indigo-700" title="View Details"><i class="fas fa-eye"></i></a>
                                    <a href="edit_student.php?id=<?php echo $student['id']; ?>" class="text-blue-600 hover:text-blue-700" title="Edit"><i class="fas fa-edit"></i></a>
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