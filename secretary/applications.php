<?php

/**
 * Secretary - Student Applications Review & Admission
 * Review applications and admit students to enrollment system
 */
require_once '../config/config.php';
requireLogin();

if (!hasRole('secretary')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Student Applications';

$database = new Database();
$conn = $database->getConnection();
$current_user = $_SESSION['user_id'];

$status_filter = $_GET['status'] ?? 'pending';
$search_term = trim($_GET['search'] ?? '');

// Handle admission action
if (isset($_GET['action']) && $_GET['action'] === 'admit' && isset($_GET['app_id']) && !empty($_GET['app_id'])) {
    try {
        $app_id = (int)$_GET['app_id'];

        // Get application details
        try {
            $app_query = "SELECT * FROM applications WHERE id = ?";
            $app_stmt = $conn->prepare($app_query);
            $app_stmt->execute([$app_id]);
            $application = $app_stmt->fetch(PDO::FETCH_ASSOC);

            if (!$application) {
                throw new Exception('Application not found');
            }
        } catch (PDOException $e) {
            throw new Exception('Applications system not yet initialized. Please run database setup first.');
        }

        // Get module details to find appropriate class
        $module = null;
        try {
            if (!empty($application['trade_module_id'])) {
                $module_query = "SELECT * FROM modules WHERE id = ?";
                $module_stmt = $conn->prepare($module_query);
                $module_stmt->execute([$application['trade_module_id']]);
                $module = $module_stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            // Module system not available, continue with level-based assignment
            $module = null;
        }

        // Find class matching the level
        $class_query = "SELECT id FROM classes 
                       WHERE class_level = ? AND academic_year = ? AND status = 'active' LIMIT 1";
        $class_stmt = $conn->prepare($class_query);
        $class_stmt->execute([str_replace('Level ', 'Level ', $application['level_applied']), CURRENT_ACADEMIC_YEAR]);
        $class = $class_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$class) {
            throw new Exception('No suitable class found for this level');
        }

        // Generate student ID
        $student_id = 'STU-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

        // Insert into students table
        $insert_query = "INSERT INTO students (
            student_id, first_name, middle_name, last_name, date_of_birth, gender,
            class_id, admission_date, admission_number, parent_name, parent_phone, 
            parent_email, address, status, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->execute([
            $student_id,
            $application['first_name'],
            $application['middle_name'],
            $application['last_name'],
            $application['date_of_birth'],
            $application['gender'],
            $class['id'],
            date('Y-m-d'),
            $student_id,
            $application['parent_name'],
            $application['parent_phone'],
            $application['parent_email'],
            $application['address'],
            'active',
            $current_user
        ]);

        // Update application status
        $update_query = "UPDATE applications 
                        SET status = 'admitted', admitted_date = NOW(), admitted_by = ?
                        WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->execute([$current_user, $app_id]);

        header('Location: applications.php?status=admitted&success=1');
        exit();
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

// Handle review update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['app_id'])) {
    try {
        $app_id = (int)$_POST['app_id'];
        $new_status = $_POST['status'] ?? 'under_review';
        $notes = trim($_POST['notes'] ?? '');

        $update_query = "UPDATE applications 
                        SET status = ?, reviewer_notes = ?, reviewed_by = ?, reviewed_at = NOW()
                        WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->execute([$new_status, $notes, $current_user, $app_id]);

        $_GET['message'] = 'Application updated successfully';
    } catch (Exception $e) {
        $_GET['error'] = 'Error: ' . $e->getMessage();
    }
}

// Pagination
$perPage = 20;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

// Build query
$where_conditions = [];
$params = [];

if ($status_filter) {
    $where_conditions[] = 'status = ?';
    $params[] = $status_filter;
}

if ($search_term) {
    $where_conditions[] = "(first_name LIKE ? OR last_name LIKE ? OR application_number LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $search = "%$search_term%";
    $params = array_merge($params, [$search, $search, $search, $search, $search]);
}

$where_clause = count($where_conditions) > 0 ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

$applications = [];
$total = 0;
$total_pages = 0;

try {
    $count_query = "SELECT COUNT(*) FROM applications $where_clause";
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->execute($params);
    $total = (int)$count_stmt->fetchColumn();
    $total_pages = max(1, (int)ceil($total / $perPage));

    $query = "SELECT a.*, m.module_name, m.level 
             FROM applications a
             LEFT JOIN modules m ON a.trade_module_id = m.id
             $where_clause
             ORDER BY a.created_at DESC
             LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($query);
    $exec_params = array_merge($params, [$perPage, $offset]);
    $stmt->execute($exec_params);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // applications table doesn't exist yet, show empty list
    $applications = [];
    $total = 0;
    $total_pages = 0;
}

$sidebar_menu = '
<ul class="space-y-2">
    <li><a href="dashboard.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
    <li><a href="students.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-user-graduate"></i><span>Students</span></a></li>
    <li><a href="applications.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg"><i class="fas fa-file-alt"></i><span>Applications</span></a></li>
    <li><a href="register_student.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-plus"></i><span>Register Student</span></a></li>
    <li><a href="documents.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-folder"></i><span>Documents</span></a></li>
    <li><a href="reports.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chart-bar"></i><span>Reports</span></a></li>
</ul>';

include '../includes/header.php';
?>

<div class="space-y-6">
    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl p-6 text-white">
        <h1 class="text-3xl font-bold">Student Applications</h1>
        <p class="opacity-90">Review and process student applications</p>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
            <i class="fas fa-check-circle mr-2"></i> Student admitted and added to enrollment system
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
            <i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php if ($status_filter === 'pending') echo 'selected'; ?>>Pending</option>
                        <option value="under_review" <?php if ($status_filter === 'under_review') echo 'selected'; ?>>Under Review</option>
                        <option value="accepted" <?php if ($status_filter === 'accepted') echo 'selected'; ?>>Accepted</option>
                        <option value="admitted" <?php if ($status_filter === 'admitted') echo 'selected'; ?>>Admitted</option>
                        <option value="rejected" <?php if ($status_filter === 'rejected') echo 'selected'; ?>>Rejected</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" name="search" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="Name, Email, Phone..." value="<?php echo htmlspecialchars($search_term); ?>">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
                        <i class="fas fa-search mr-2"></i> Search
                    </button>
                    <a href="applications.php" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Applications List -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <?php if (count($applications) > 0): ?>
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Application #</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Name</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Module</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Applied Date</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($applications as $app): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm font-medium text-gray-700">
                                <a href="#" onclick="viewApplication(<?php echo $app['id']; ?>)" class="text-blue-600 hover:underline">
                                    <?php echo htmlspecialchars($app['application_number']); ?>
                                </a>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <?php echo htmlspecialchars($app['module_name'] ?? 'N/A'); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <?php echo date('M d, Y', strtotime($app['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-3 py-1 rounded-full text-xs font-medium
                                    <?php
                                    if ($app['status'] === 'pending') echo 'bg-yellow-100 text-yellow-800';
                                    elseif ($app['status'] === 'under_review') echo 'bg-blue-100 text-blue-800';
                                    elseif ($app['status'] === 'accepted') echo 'bg-green-100 text-green-800';
                                    elseif ($app['status'] === 'admitted') echo 'bg-purple-100 text-purple-800';
                                    elseif ($app['status'] === 'rejected') echo 'bg-red-100 text-red-800';
                                    ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $app['status'])); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm space-x-2">
                                <?php if ($app['status'] !== 'admitted'): ?>
                                    <button onclick="openReviewModal(<?php echo htmlspecialchars(json_encode($app)); ?>)" class="text-blue-600 hover:text-blue-900 font-medium">
                                        <i class="fas fa-edit"></i> Review
                                    </button>
                                <?php endif; ?>
                                <?php if ($app['status'] === 'accepted'): ?>
                                    <a href="?action=admit&app_id=<?php echo $app['id']; ?>" class="text-green-600 hover:text-green-900 font-medium" onclick="return confirm('Admit this student?');">
                                        <i class="fas fa-check"></i> Admit
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="px-6 py-4 border-t flex justify-center gap-2">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search_term); ?>"
                            class="px-3 py-2 rounded <?php echo ($page === $i) ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="p-8 text-center text-gray-500">
                <i class="fas fa-inbox text-4xl mb-4 opacity-20"></i>
                <p>No applications found</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Review Modal -->
<div id="reviewModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full mx-4">
        <div class="p-6 border-b">
            <h2 class="text-2xl font-bold text-gray-800">Review Application</h2>
        </div>
        <form method="POST" class="p-6 space-y-4">
            <input type="hidden" name="app_id" id="app_id">

            <div id="appDetails"></div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Decision</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="under_review">Under Review</option>
                    <option value="accepted">Accept</option>
                    <option value="rejected">Reject</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Reviewer Notes</label>
                <textarea name="notes" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Add your comments here..."></textarea>
            </div>

            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeReviewModal()" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openReviewModal(app) {
        document.getElementById('app_id').value = app.id;
        document.getElementById('appDetails').innerHTML = `
        <div class="space-y-2 bg-gray-50 p-4 rounded-lg">
            <p><strong>Applicant:</strong> ${app.first_name} ${app.last_name}</p>
            <p><strong>Module:</strong> ${app.module_name || 'N/A'}</p>
            <p><strong>Applied:</strong> ${new Date(app.created_at).toLocaleDateString()}</p>
            <p><strong>Contact:</strong> ${app.phone}</p>
            <p><strong>Email:</strong> ${app.email || 'N/A'}</p>
        </div>
    `;
        document.getElementById('reviewModal').classList.remove('hidden');
    }

    function closeReviewModal() {
        document.getElementById('reviewModal').classList.add('hidden');
    }
</script>

<?php include '../includes/footer.php'; ?>