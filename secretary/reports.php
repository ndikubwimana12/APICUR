<?php

/**
 * Secretary - Reports & Analytics
 * Provides quick data insights and export options
 */
require_once '../config/config.php';
requireLogin();

if (!hasRole('secretary')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Reports';

$database = new Database();
$conn = $database->getConnection();

// Overview statistics
$statsQuery = "SELECT
    (SELECT COUNT(*) FROM students) AS total_students,
    (SELECT COUNT(*) FROM students WHERE status = 'active') AS active_students,
    (SELECT COUNT(*) FROM students WHERE status = 'graduated') AS graduated_students,
    (SELECT COUNT(*) FROM students WHERE status = 'transferred') AS transferred_students,
    (SELECT COUNT(*) FROM students WHERE status = 'dropped') AS dropped_students,
    (SELECT COUNT(*) FROM documents WHERE category = 'meeting') AS total_meeting_docs,
    (SELECT COUNT(*) FROM meetings WHERE status = 'scheduled') AS upcoming_meetings,
    (SELECT COUNT(*) FROM meetings WHERE status = 'completed' AND meeting_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) AS meetings_last_30_days
";

$stats = $conn->query($statsQuery)->fetch(PDO::FETCH_ASSOC);

// Class distribution
$classDistributionQuery = "SELECT classes.class_name, COUNT(students.id) AS student_count
                            FROM classes
                            LEFT JOIN students ON students.class_id = classes.id
                            WHERE classes.status = 'active'
                            GROUP BY classes.id
                            ORDER BY classes.class_name";
$classDistribution = $conn->query($classDistributionQuery)->fetchAll(PDO::FETCH_ASSOC);

// Recent activity
$recentStudents = $conn->query("SELECT first_name, last_name, class_id, admission_date FROM students ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
$recentMeetings = $conn->query("SELECT title, meeting_date, meeting_type, status FROM meetings ORDER BY meeting_date DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

$sidebar_menu = '
<ul class="space-y-2">
    <li><a href="dashboard.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
    <li><a href="students.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-user-graduate"></i><span>Students</span></a></li>
    <li><a href="register_student.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-user-plus"></i><span>Register Student</span></a></li>
    <li><a href="documents.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-file-alt"></i><span>Documents</span></a></li>
    <li><a href="meetings.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-calendar-alt"></i><span>Meetings</span></a></li>
    <li><a href="reports.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg"><i class="fas fa-chart-bar"></i><span>Reports</span></a></li>
</ul>';

include '../includes/header.php';
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Reports & Analytics</h1>
            <p class="text-gray-500 text-sm">Quick insights into students, documents, and meetings.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="reports.php?download=students" class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-700 px-4 py-2 rounded-lg shadow-sm hover:bg-gray-100">
                <i class="fas fa-file-csv"></i>
                <span>Export Students CSV</span>
            </a>
            <a href="reports.php?download=meetings" class="inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg shadow-sm hover:bg-indigo-700">
                <i class="fas fa-download"></i>
                <span>Export Meetings</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-sm text-gray-500">Total Students</p>
            <h3 class="text-3xl font-bold text-gray-800 mt-1"><?php echo $stats['total_students']; ?></h3>
            <p class="text-xs text-gray-400 mt-2">Active: <?php echo $stats['active_students']; ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-sm text-gray-500">Student Status</p>
            <div class="mt-2 space-y-1 text-xs text-gray-500">
                <p>Graduated: <span class="font-semibold text-gray-800"><?php echo $stats['graduated_students']; ?></span></p>
                <p>Transferred: <span class="font-semibold text-gray-800"><?php echo $stats['transferred_students']; ?></span></p>
                <p>Dropped: <span class="font-semibold text-gray-800"><?php echo $stats['dropped_students']; ?></span></p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-sm text-gray-500">Meeting Documents</p>
            <h3 class="text-3xl font-bold text-gray-800 mt-1"><?php echo $stats['total_meeting_docs']; ?></h3>
            <p class="text-xs text-gray-400 mt-2">Files tagged as meeting minutes or agendas</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-sm text-gray-500">Meetings Overview</p>
            <div class="mt-2 space-y-1 text-xs text-gray-500">
                <p>Upcoming: <span class="font-semibold text-gray-800"><?php echo $stats['upcoming_meetings']; ?></span></p>
                <p>Completed (30 days): <span class="font-semibold text-gray-800"><?php echo $stats['meetings_last_30_days']; ?></span></p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Class Distribution</h2>
            <div class="space-y-3">
                <?php if (empty($classDistribution)): ?>
                    <p class="text-sm text-gray-500">No class data available.</p>
                <?php else: ?>
                    <?php foreach ($classDistribution as $row): ?>
                        <div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium text-gray-700"><?php echo htmlspecialchars($row['class_name']); ?></span>
                                <span class="text-gray-500"><?php echo $row['student_count']; ?> students</span>
                            </div>
                            <div class="mt-2 bg-gray-100 rounded-full h-2 overflow-hidden">
                                <div class="bg-indigo-500 h-full" style="width: <?php echo min(100, ($row['student_count'] / max(1, $stats['total_students'])) * 100); ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Recent Activity</h2>
            <div class="space-y-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-600 mb-2">Recent Students</h3>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <?php if (empty($recentStudents)): ?>
                            <li>No recent registrations.</li>
                        <?php else: ?>
                            <?php foreach ($recentStudents as $student): ?>
                                <li class="flex items-center justify-between">
                                    <span><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></span>
                                    <span class="text-xs text-gray-400"><?php echo formatDate($student['admission_date']); ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-600 mb-2">Recent Meetings</h3>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <?php if (empty($recentMeetings)): ?>
                            <li>No recent meetings.</li>
                        <?php else: ?>
                            <?php foreach ($recentMeetings as $meeting): ?>
                                <li class="flex items-center justify-between">
                                    <span><?php echo htmlspecialchars($meeting['title']); ?></span>
                                    <span class="text-xs text-gray-400"><?php echo formatDate($meeting['meeting_date']); ?> · <?php echo ucfirst($meeting['status']); ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Export & Integrations</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="border border-gray-100 rounded-lg p-4">
                <p class="font-semibold text-gray-800">Student List</p>
                <p class="text-xs text-gray-500 mt-1">Download full student registry for offline records.</p>
                <a href="reports.php?download=students" class="inline-block mt-3 text-indigo-600 hover:text-indigo-700 text-sm font-medium">Download CSV →</a>
            </div>
            <div class="border border-gray-100 rounded-lg p-4">
                <p class="font-semibold text-gray-800">Meeting Register</p>
                <p class="text-xs text-gray-500 mt-1">Summary of meetings scheduled and completed.</p>
                <a href="reports.php?download=meetings" class="inline-block mt-3 text-indigo-600 hover:text-indigo-700 text-sm font-medium">Download CSV →</a>
            </div>
            <div class="border border-gray-100 rounded-lg p-4">
                <p class="font-semibold text-gray-800">Document Index</p>
                <p class="text-xs text-gray-500 mt-1">List all uploaded documents with access roles.</p>
                <a href="reports.php?download=documents" class="inline-block mt-3 text-indigo-600 hover:text-indigo-700 text-sm font-medium">Download CSV →</a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>