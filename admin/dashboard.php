<?php

/**
 * Admin Dashboard
 * School Management System - APICUR TSS
 */
require_once '../config/config.php';
requireLogin();

if (!hasRole('admin')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Admin Dashboard';
$database = new Database();
$conn = $database->getConnection();

// Get comprehensive statistics
$stats = [];
$query = "SELECT COUNT(*) as count FROM users WHERE status = 'active'";
$stats['total_users'] = $conn->query($query)->fetch()['count'];

$query = "SELECT COUNT(*) as count FROM students WHERE status = 'active'";
$stats['total_students'] = $conn->query($query)->fetch()['count'];

$query = "SELECT COUNT(*) as count FROM classes WHERE status = 'active'";
$stats['total_classes'] = $conn->query($query)->fetch()['count'];

$query = "SELECT COUNT(*) as count FROM activity_logs WHERE DATE(created_at) = CURDATE()";
$stats['today_activities'] = $conn->query($query)->fetch()['count'];

// Try to get applications (if table exists)
try {
    $query = "SELECT COUNT(*) as count FROM applications WHERE status = 'pending'";
    $stats['pending_applications'] = $conn->query($query)->fetch()['count'];
} catch (Exception $e) {
    $stats['pending_applications'] = 0;
}

// Try to get modules (if table exists)
try {
    $query = "SELECT COUNT(*) as count FROM modules WHERE status = 'active'";
    $stats['total_modules'] = $conn->query($query)->fetch()['count'];
} catch (Exception $e) {
    $stats['total_modules'] = 0;
}

// Try to get timetable slots (if table exists)
try {
    $query = "SELECT COUNT(*) as count FROM timetable_slots WHERE academic_year = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([CURRENT_ACADEMIC_YEAR]);
    $stats['timetable_slots'] = $stmt->fetch()['count'];
} catch (Exception $e) {
    $stats['timetable_slots'] = 0;
}

// Get users by role
$users_query = "SELECT role, COUNT(*) as count FROM users WHERE status = 'active' GROUP BY role";
$users_by_role = $conn->query($users_query)->fetchAll(PDO::FETCH_KEY_PAIR);

$sidebar_menu = '
<ul class="space-y-2">
    <li><a href="dashboard.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
    <li><a href="users.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-users"></i><span>User Management</span></a></li>
    <li><a href="students.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-user-graduate"></i><span>Students</span></a></li>
    <li><a href="classes.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-school"></i><span>Classes</span></a></li>
    <li><a href="subjects.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-book"></i><span>Subjects</span></a></li>
    <li><a href="../dos/modules.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-graduation-cap"></i><span>Training Modules</span><span class="ml-auto text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full font-semibold">NEW</span></a></li>
    <li><a href="reports.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chart-bar"></i><span>Reports</span></a></li>
    <li><a href="settings.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-cog"></i><span>Settings</span></a></li>
    <li><a href="activity_logs.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-history"></i><span>Activity Logs</span></a></li>
</ul>';

include '../includes/header.php';
?>

<div class="space-y-6">
    <div class="bg-gradient-to-r from-red-500 to-pink-600 rounded-xl p-6 text-white">
        <h1 class="text-2xl font-bold mb-2">Admin Dashboard - <?php echo $_SESSION['full_name']; ?></h1>
        <p class="opacity-90">System Administration and Management</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="dashboard-card bg-white rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Users</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['total_users']; ?></h3>
                </div>
                <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center"><i class="fas fa-users text-2xl text-blue-600"></i></div>
            </div>
        </div>
        <div class="dashboard-card bg-white rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Students</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['total_students']; ?></h3>
                </div>
                <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center"><i class="fas fa-user-graduate text-2xl text-green-600"></i></div>
            </div>
        </div>
        <div class="dashboard-card bg-white rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Classes</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['total_classes']; ?></h3>
                </div>
                <div class="w-14 h-14 bg-purple-100 rounded-full flex items-center justify-center"><i class="fas fa-school text-2xl text-purple-600"></i></div>
            </div>
        </div>
        <div class="dashboard-card bg-gradient-to-br from-orange-400 to-red-500 rounded-xl p-6 shadow-sm text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-white text-sm font-medium opacity-90">Pending Apps</p>
                    <h3 class="text-3xl font-bold mt-2"><?php echo $stats['pending_applications']; ?></h3>
                </div>
                <div class="w-14 h-14 bg-white bg-opacity-20 rounded-full flex items-center justify-center"><i class="fas fa-inbox text-2xl"></i></div>
            </div>
        </div>
        <div class="dashboard-card bg-white rounded-xl p-6 shadow-sm border-t-4 border-indigo-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Training Modules</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['total_modules']; ?></h3>
                </div>
                <div class="w-14 h-14 bg-indigo-100 rounded-full flex items-center justify-center"><i class="fas fa-graduation-cap text-2xl text-indigo-600"></i></div>
            </div>
        </div>
        <div class="dashboard-card bg-white rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Timetable Slots</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['timetable_slots']; ?></h3>
                </div>
                <div class="w-14 h-14 bg-pink-100 rounded-full flex items-center justify-center"><i class="fas fa-calendar-alt text-2xl text-pink-600"></i></div>
            </div>
        </div>
        <div class="dashboard-card bg-gradient-to-br from-cyan-400 to-blue-500 rounded-xl p-6 shadow-sm text-white">
            <p class="text-sm font-medium opacity-90">Today's Activities</p>
            <h3 class="text-3xl font-bold mt-2"><?php echo $stats['today_activities']; ?></h3>
            <a href="activity_logs.php" class="text-sm hover:underline mt-2 inline-block">View logs →</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Users by Role</h3>
            <div class="space-y-3">
                <?php
                $role_colors = [
                    'admin' => 'red',
                    'secretary' => 'blue',
                    'teacher' => 'green',
                    'dos' => 'purple',
                    'head_teacher' => 'indigo',
                    'accountant' => 'yellow',
                    'discipline_officer' => 'pink'
                ];
                foreach ($users_by_role as $role => $count):
                    $color = $role_colors[$role] ?? 'gray';
                ?>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-<?php echo $color; ?>-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-<?php echo $color; ?>-600"></i>
                            </div>
                            <span class="font-medium text-gray-700"><?php echo ucwords(str_replace('_', ' ', $role)); ?></span>
                        </div>
                        <span class="font-bold text-gray-800"><?php echo $count; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-2 gap-4">
                <a href="users.php?action=add" class="flex flex-col items-center justify-center p-6 border-2 border-gray-200 rounded-xl hover:border-indigo-500 hover:shadow-md transition">
                    <i class="fas fa-user-plus text-3xl text-indigo-600 mb-2"></i>
                    <span class="text-sm font-medium text-gray-700 text-center">Add User</span>
                </a>
                <a href="classes.php?action=add" class="flex flex-col items-center justify-center p-6 border-2 border-gray-200 rounded-xl hover:border-green-500 hover:shadow-md transition">
                    <i class="fas fa-plus-circle text-3xl text-green-600 mb-2"></i>
                    <span class="text-sm font-medium text-gray-700 text-center">Add Class</span>
                </a>
                <a href="subjects.php?action=add" class="flex flex-col items-center justify-center p-6 border-2 border-gray-200 rounded-xl hover:border-purple-500 hover:shadow-md transition">
                    <i class="fas fa-book-medical text-3xl text-purple-600 mb-2"></i>
                    <span class="text-sm font-medium text-gray-700 text-center">Add Subject</span>
                </a>
                <a href="settings.php" class="flex flex-col items-center justify-center p-6 border-2 border-gray-200 rounded-xl hover:border-orange-500 hover:shadow-md transition">
                    <i class="fas fa-cog text-3xl text-orange-600 mb-2"></i>
                    <span class="text-sm font-medium text-gray-700 text-center">Settings</span>
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>