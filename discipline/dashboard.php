<?php

/**
 * Discipline Officer Dashboard
 * School Management System - APICUR TSS
 */
require_once '../config/config.php';
requireLogin();

if (!hasRole('discipline_officer')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Discipline Dashboard';
$database = new Database();
$conn = $database->getConnection();

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total_cases,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_cases,
    COUNT(CASE WHEN status = 'resolved' THEN 1 END) as resolved_cases,
    COUNT(CASE WHEN incident_type = 'severe' AND status = 'pending' THEN 1 END) as severe_pending
    FROM discipline_records";
$stats = $conn->query($stats_query)->fetch();

// Recent cases
$cases_query = "SELECT dr.*, s.first_name, s.last_name, s.student_id, c.class_name
    FROM discipline_records dr
    INNER JOIN students s ON dr.student_id = s.id
    LEFT JOIN classes c ON s.class_id = c.id
    ORDER BY dr.created_at DESC
    LIMIT 10";
$recent_cases = $conn->query($cases_query)->fetchAll();

$sidebar_menu = '
<ul class="space-y-2">
    <li><a href="dashboard.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
    <li><a href="cases.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-gavel"></i><span>All Cases</span></a></li>
    <li><a href="new_case.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-plus-circle"></i><span>New Case</span></a></li>
    <li><a href="students.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-user-graduate"></i><span>Student Records</span></a></li>
    <li><a href="reports.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chart-bar"></i><span>Reports</span></a></li>
</ul>';

include '../includes/header.php';
?>

<div class="space-y-6">
    <div class="bg-gradient-to-r from-red-500 to-orange-600 rounded-xl p-6 text-white">
        <h1 class="text-2xl font-bold mb-2">Discipline Dashboard - <?php echo $_SESSION['full_name']; ?></h1>
        <p class="opacity-90">Student Discipline and Behavior Management</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="dashboard-card bg-white rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Cases</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['total_cases']; ?></h3>
                </div>
                <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center"><i class="fas fa-folder text-2xl text-blue-600"></i></div>
            </div>
        </div>
        <div class="dashboard-card bg-white rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Pending</p>
                    <h3 class="text-3xl font-bold text-orange-600 mt-2"><?php echo $stats['pending_cases']; ?></h3>
                </div>
                <div class="w-14 h-14 bg-orange-100 rounded-full flex items-center justify-center"><i class="fas fa-clock text-2xl text-orange-600"></i></div>
            </div>
        </div>
        <div class="dashboard-card bg-white rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Resolved</p>
                    <h3 class="text-3xl font-bold text-green-600 mt-2"><?php echo $stats['resolved_cases']; ?></h3>
                </div>
                <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center"><i class="fas fa-check-circle text-2xl text-green-600"></i></div>
            </div>
        </div>
        <div class="dashboard-card bg-gradient-to-br from-red-500 to-pink-600 rounded-xl p-6 shadow-sm text-white">
            <p class="text-sm font-medium opacity-90">Severe Cases</p>
            <h3 class="text-3xl font-bold mt-2"><?php echo $stats['severe_pending']; ?></h3>
            <a href="cases.php?type=severe&status=pending" class="text-sm hover:underline mt-2 inline-block">View →</a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800">Recent Discipline Cases</h3>
            <a href="cases.php" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">View all →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Class</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($recent_cases as $case): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-500"><?php echo formatDate($case['incident_date']); ?></td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($case['first_name'] . ' ' . $case['last_name']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($case['class_name'] ?? 'N/A'); ?></td>
                            <td class="px-6 py-4">
                                <?php
                                $type_colors = ['minor' => 'blue', 'major' => 'orange', 'severe' => 'red'];
                                $color = $type_colors[$case['incident_type']] ?? 'gray';
                                ?>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-<?php echo $color; ?>-100 text-<?php echo $color; ?>-800">
                                    <?php echo ucfirst($case['incident_type']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                $status_colors = ['pending' => 'orange', 'resolved' => 'green', 'escalated' => 'red'];
                                $color = $status_colors[$case['status']] ?? 'gray';
                                ?>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-<?php echo $color; ?>-100 text-<?php echo $color; ?>-800">
                                    <?php echo ucfirst($case['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <a href="case_details.php?id=<?php echo $case['id']; ?>" class="text-indigo-600 hover:text-indigo-700 mr-3"><i class="fas fa-eye"></i></a>
                                <a href="handle_case.php?id=<?php echo $case['id']; ?>" class="text-green-600 hover:text-green-700"><i class="fas fa-edit"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>