<?php

/**
 * Accountant Dashboard
 * School Management System - APICUR TSS
 */
require_once '../config/config.php';
requireLogin();

if (!hasRole('accountant')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Accountant Dashboard';
$database = new Database();
$conn = $database->getConnection();

// Get financial statistics
$stats_query = "SELECT 
    SUM(amount_due) as total_due,
    SUM(amount_paid) as total_paid,
    SUM(amount_due - amount_paid) as total_pending,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
    COUNT(CASE WHEN status = 'overdue' THEN 1 END) as overdue_count
    FROM financial_records 
    WHERE academic_year = :academic_year";
$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->execute([':academic_year' => CURRENT_ACADEMIC_YEAR]);
$stats = $stats_stmt->fetch();

// Recent transactions
$transactions_query = "SELECT fr.*, s.first_name, s.last_name, s.student_id
    FROM financial_records fr
    INNER JOIN students s ON fr.student_id = s.id
    ORDER BY fr.created_at DESC
    LIMIT 10";
$transactions = $conn->query($transactions_query)->fetchAll();

$sidebar_menu = '
<ul class="space-y-2">
    <li><a href="dashboard.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
    <li><a href="fees.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-money-bill"></i><span>Fee Management</span></a></li>
    <li><a href="payments.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-credit-card"></i><span>Payments</span></a></li>
    <li><a href="receipts.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-receipt"></i><span>Receipts</span></a></li>
    <li><a href="reports.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chart-pie"></i><span>Financial Reports</span></a></li>
    <li><a href="students.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-user-graduate"></i><span>Student Accounts</span></a></li>
</ul>';

include '../includes/header.php';
?>

<div class="space-y-6">
    <div class="bg-gradient-to-r from-yellow-500 to-orange-600 rounded-xl p-6 text-white">
        <h1 class="text-2xl font-bold mb-2">Accountant Dashboard - <?php echo $_SESSION['full_name']; ?></h1>
        <p class="opacity-90">Financial Management and Fee Collection</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="dashboard-card bg-white rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Due</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-2">TSh <?php echo number_format($stats['total_due'] ?? 0); ?></h3>
                </div>
                <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center"><i class="fas fa-file-invoice text-2xl text-blue-600"></i></div>
            </div>
        </div>
        <div class="dashboard-card bg-white rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Paid</p>
                    <h3 class="text-2xl font-bold text-green-600 mt-2">TSh <?php echo number_format($stats['total_paid'] ?? 0); ?></h3>
                </div>
                <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center"><i class="fas fa-check-circle text-2xl text-green-600"></i></div>
            </div>
        </div>
        <div class="dashboard-card bg-white rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Pending</p>
                    <h3 class="text-2xl font-bold text-orange-600 mt-2">TSh <?php echo number_format($stats['total_pending'] ?? 0); ?></h3>
                </div>
                <div class="w-14 h-14 bg-orange-100 rounded-full flex items-center justify-center"><i class="fas fa-clock text-2xl text-orange-600"></i></div>
            </div>
        </div>
        <div class="dashboard-card bg-gradient-to-br from-red-400 to-pink-500 rounded-xl p-6 shadow-sm text-white">
            <p class="text-sm font-medium opacity-90">Overdue Accounts</p>
            <h3 class="text-3xl font-bold mt-2"><?php echo $stats['overdue_count'] ?? 0; ?></h3>
            <a href="fees.php?status=overdue" class="text-sm hover:underline mt-2 inline-block">View →</a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800">Recent Transactions</h3>
            <a href="payments.php" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">View all →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fee Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount Due</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paid</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($transactions as $trans): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-500"><?php echo formatDate($trans['created_at']); ?></td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($trans['first_name'] . ' ' . $trans['last_name']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-500"><?php echo ucfirst($trans['fee_type']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900">TSh <?php echo number_format($trans['amount_due']); ?></td>
                            <td class="px-6 py-4 text-sm text-green-600">TSh <?php echo number_format($trans['amount_paid']); ?></td>
                            <td class="px-6 py-4">
                                <?php
                                $status_colors = ['pending' => 'orange', 'partial' => 'yellow', 'paid' => 'green', 'overdue' => 'red'];
                                $color = $status_colors[$trans['status']] ?? 'gray';
                                ?>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-<?php echo $color; ?>-100 text-<?php echo $color; ?>-800">
                                    <?php echo ucfirst($trans['status']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>