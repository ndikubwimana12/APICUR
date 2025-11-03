<?php

/**
 * Secretary Dashboard
 * School Management System - APICUR TSS
 */
require_once '../config/config.php';
requireLogin();

// Check if user has secretary role
if (!hasRole('secretary')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Secretary Dashboard';

$database = new Database();
$conn = $database->getConnection();

// Get statistics
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM students WHERE status = 'active') as total_students,
    (SELECT COUNT(*) FROM students WHERE DATE(created_at) = CURDATE()) as new_students_today,
    (SELECT COUNT(*) FROM documents WHERE category = 'meeting') as meeting_documents,
    (SELECT COUNT(*) FROM students WHERE status = 'active' AND class_id IS NULL) as unassigned_students";
$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->execute();
$stats = $stats_stmt->fetch();

// Add applications stats if table exists
try {
    $app_query = "SELECT 
        (SELECT COUNT(*) FROM applications WHERE status = 'pending') as pending_applications,
        (SELECT COUNT(*) FROM applications WHERE status = 'accepted') as accepted_applications,
        (SELECT COUNT(*) FROM applications WHERE DATE(created_at) = CURDATE()) as new_applications_today";
    $app_stmt = $conn->prepare($app_query);
    $app_stmt->execute();
    $app_stats = $app_stmt->fetch();
    $stats = array_merge($stats, $app_stats);
} catch (Exception $e) {
    $stats['pending_applications'] = 0;
    $stats['accepted_applications'] = 0;
    $stats['new_applications_today'] = 0;
}

// Get recent students
$recent_students_query = "SELECT * FROM students ORDER BY created_at DESC LIMIT 5";
$recent_students_stmt = $conn->prepare($recent_students_query);
$recent_students_stmt->execute();
$recent_students = $recent_students_stmt->fetchAll();

// Sidebar menu for secretary
$sidebar_menu = '
<ul class="space-y-2">
    <li>
        <a href="dashboard.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
    </li>
    <li>
        <a href="applications.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 bg-gradient-to-r from-indigo-50 to-transparent border-l-4 border-indigo-500">
            <i class="fas fa-file-alt"></i>
            <span>Student Applications</span>
            <span class="ml-auto text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full font-semibold">NEW</span>
        </a>
    </li>
    <li>
        <a href="students.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700">
            <i class="fas fa-user-graduate"></i>
            <span>Students</span>
        </a>
    </li>
    <li>
        <a href="register_student.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700">
            <i class="fas fa-user-plus"></i>
            <span>Register Student</span>
        </a>
    </li>
    <li>
        <a href="documents.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700">
            <i class="fas fa-file-alt"></i>
            <span>Documents</span>
        </a>
    </li>
    <li>
        <a href="meetings.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700">
            <i class="fas fa-calendar-alt"></i>
            <span>Meetings</span>
        </a>
    </li>
    <li>
        <a href="reports.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700">
            <i class="fas fa-chart-bar"></i>
            <span>Reports</span>
        </a>
    </li>
</ul>
';

include '../includes/header.php';
?>

<!-- Dashboard Content -->
<div class="space-y-6">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl p-6 text-white">
        <h1 class="text-2xl font-bold mb-2">Welcome back, <?php echo $_SESSION['full_name']; ?>!</h1>
        <p class="opacity-90">Here's what's happening with your school today.</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Pending Applications -->
        <div class="dashboard-card bg-white rounded-xl p-6 shadow-sm border-t-4 border-indigo-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Pending Applications</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['pending_applications']; ?></h3>
                </div>
                <div class="w-14 h-14 bg-indigo-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-hourglass-half text-2xl text-indigo-600"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-indigo-600 font-semibold">+<?php echo $stats['new_applications_today']; ?></span>
                <span class="text-gray-500 ml-2">today</span>
            </div>
        </div>

        <!-- Total Students -->
        <div class="dashboard-card bg-white rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Students</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['total_students']; ?></h3>
                </div>
                <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-graduate text-2xl text-blue-600"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-green-600 font-semibold">+<?php echo $stats['new_students_today']; ?></span>
                <span class="text-gray-500 ml-2">registered today</span>
            </div>
        </div>

        <!-- Unassigned Students -->
        <div class="dashboard-card bg-white rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Unassigned</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['unassigned_students']; ?></h3>
                </div>
                <div class="w-14 h-14 bg-orange-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-2xl text-orange-600"></i>
                </div>
            </div>
            <div class="mt-4">
                <a href="students.php?filter=unassigned" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                    Assign to classes →
                </a>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="dashboard-card bg-gradient-to-br from-green-400 to-green-600 rounded-xl p-6 shadow-sm text-white">
            <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
            <div class="space-y-2">
                <a href="applications.php" class="block bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg px-4 py-2 text-sm transition font-semibold">
                    <i class="fas fa-inbox mr-2"></i> Review Apps
                </a>
                <a href="register_student.php" class="block bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg px-4 py-2 text-sm transition">
                    <i class="fas fa-plus mr-2"></i> Register Student
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Students -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800">Recently Registered Students</h3>
            <a href="students.php" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                View all →
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gender</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Admission Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($recent_students)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-user-graduate text-4xl mb-3 opacity-20"></i>
                                <p>No students registered yet.</p>
                                <a href="register_student.php" class="text-indigo-600 hover:text-indigo-700 font-medium mt-2 inline-block">
                                    Register your first student →
                                </a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_students as $student): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($student['student_id']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($student['gender']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500"><?php echo formatDate($student['admission_date']); ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        <?php echo ucfirst($student['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <a href="student_details.php?id=<?php echo $student['id']; ?>" class="text-indigo-600 hover:text-indigo-700 mr-3">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit_student.php?id=<?php echo $student['id']; ?>" class="text-blue-600 hover:text-blue-700">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>