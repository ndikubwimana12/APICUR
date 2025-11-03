<?php

/**
 * Head Teacher Dashboard
 * School Management System - APICUR TSS
 */
require_once '../config/config.php';
requireLogin();

if (!hasRole('head_teacher')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Head Teacher Dashboard';
$database = new Database();
$conn = $database->getConnection();

// Get comprehensive statistics
$stats = [];

// Total students
$query = "SELECT COUNT(*) as count FROM students WHERE status = 'active'";
$stats['total_students'] = $conn->query($query)->fetch()['count'];

// Total teachers
$query = "SELECT COUNT(*) as count FROM users WHERE role = 'teacher' AND status = 'active'";
$stats['total_teachers'] = $conn->query($query)->fetch()['count'];

// Total staff
$query = "SELECT COUNT(*) as count FROM users WHERE role IN ('secretary', 'dos', 'accountant', 'discipline_officer') AND status = 'active'";
$stats['total_staff'] = $conn->query($query)->fetch()['count'];

// Pending discipline cases
$query = "SELECT COUNT(*) as count FROM discipline_records WHERE status = 'pending'";
$stats['pending_discipline'] = $conn->query($query)->fetch()['count'];

// Recent activities
$activities_query = "SELECT al.*, u.full_name 
    FROM activity_logs al
    INNER JOIN users u ON al.user_id = u.id
    ORDER BY al.created_at DESC
    LIMIT 10";
$activities = $conn->query($activities_query)->fetchAll();

$sidebar_menu = '
<ul class="space-y-2">
    <li><a href="dashboard.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
    <li><a href="staff.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-users"></i><span>Staff Management</span></a></li>
    <li><a href="students.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-user-graduate"></i><span>Students</span></a></li>
    <li><a href="performance.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chart-line"></i><span>Performance</span></a></li>
    <li><a href="reports.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-file-alt"></i><span>Reports</span></a></li>
    <li><a href="discipline.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-gavel"></i><span>Discipline</span></a></li>
    <li><a href="approvals.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-check-circle"></i><span>Approvals</span></a></li>
</ul>';

include '../includes/header.php';
?>

<div class="space-y-6">
    <div class="bg-gradient-to-r from-green-500 to-teal-600 rounded-xl p-6 text-white">
        <h1 class="text-2xl font-bold mb-2">Head Teacher Dashboard - <?php echo $_SESSION['full_name']; ?></h1>
        <p class="opacity-90">School Overview and Management</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="dashboard-card bg-white rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Students</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['total_students']; ?></h3>
                </div>
                <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center"><i class="fas fa-user-graduate text-2xl text-blue-600"></i></div>
            </div>
        </div>
        <div class="dashboard-card bg-white rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Teachers</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['total_teachers']; ?></h3>
                </div>
                <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center"><i class="fas fa-chalkboard-teacher text-2xl text-green-600"></i></div>
            </div>
        </div>
        <div class="dashboard-card bg-white rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Staff Members</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['total_staff']; ?></h3>
                </div>
                <div class="w-14 h-14 bg-purple-100 rounded-full flex items-center justify-center"><i class="fas fa-users text-2xl text-purple-600"></i></div>
            </div>
        </div>
        <div class="dashboard-card bg-gradient-to-br from-red-400 to-pink-500 rounded-xl p-6 shadow-sm text-white">
            <p class="text-sm font-medium opacity-90">Pending Discipline</p>
            <h3 class="text-3xl font-bold mt-2"><?php echo $stats['pending_discipline']; ?></h3>
            <a href="discipline.php" class="text-sm hover:underline mt-2 inline-block">Review →</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-gray-800">Recent Activities</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <?php foreach (array_slice($activities, 0, 5) as $activity): ?>
                        <div class="flex items-start gap-3 pb-3 border-b last:border-0">
                            <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-user text-indigo-600 text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-gray-800"><strong><?php echo htmlspecialchars($activity['full_name']); ?></strong> <?php echo htmlspecialchars($activity['action']); ?></p>
                                <p class="text-xs text-gray-500 mt-1"><?php echo date('M j, Y g:i A', strtotime($activity['created_at'])); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-gray-800">Quick Actions</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4">
                    <a href="reports.php" class="flex flex-col items-center justify-center p-6 border-2 border-gray-200 rounded-xl hover:border-indigo-500 hover:shadow-md transition">
                        <i class="fas fa-file-alt text-3xl text-indigo-600 mb-2"></i>
                        <span class="text-sm font-medium text-gray-700 text-center">View Reports</span>
                    </a>
                    <a href="performance.php" class="flex flex-col items-center justify-center p-6 border-2 border-gray-200 rounded-xl hover:border-green-500 hover:shadow-md transition">
                        <i class="fas fa-chart-line text-3xl text-green-600 mb-2"></i>
                        <span class="text-sm font-medium text-gray-700 text-center">Performance</span>
                    </a>
                    <a href="staff.php" class="flex flex-col items-center justify-center p-6 border-2 border-gray-200 rounded-xl hover:border-purple-500 hover:shadow-md transition">
                        <i class="fas fa-users text-3xl text-purple-600 mb-2"></i>
                        <span class="text-sm font-medium text-gray-700 text-center">Manage Staff</span>
                    </a>
                    <a href="discipline.php" class="flex flex-col items-center justify-center p-6 border-2 border-gray-200 rounded-xl hover:border-red-500 hover:shadow-md transition">
                        <i class="fas fa-gavel text-3xl text-red-600 mb-2"></i>
                        <span class="text-sm font-medium text-gray-700 text-center">Discipline Cases</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>