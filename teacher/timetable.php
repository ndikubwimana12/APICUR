<?php

/**
 * Teacher - Timetable
 * View teacher's weekly schedule
 */
require_once '../config/config.php';
requireLogin();

if (!hasRole('teacher')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Timetable';

$database = new Database();
$conn = $database->getConnection();
$teacher_id = $_SESSION['user_id'];

// Get teacher's timetable
$timetable_query = "SELECT t.*, c.class_name, s.subject_name
                    FROM timetable t
                    INNER JOIN classes c ON t.class_id = c.id
                    INNER JOIN subjects s ON t.subject_id = s.id
                    WHERE t.teacher_id = ? AND t.academic_year = ?
                    ORDER BY FIELD(t.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), t.start_time";

$timetable_stmt = $conn->prepare($timetable_query);
$timetable_stmt->execute([
    $teacher_id,
    CURRENT_ACADEMIC_YEAR
]);
$timetable_entries = $timetable_stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize by day
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
$timetable_by_day = [];
foreach ($days as $day) {
    $timetable_by_day[$day] = array_filter($timetable_entries, function ($entry) use ($day) {
        return $entry['day_of_week'] === $day;
    });
}

// Sidebar menu for teacher
$sidebar_menu = '
<ul class="space-y-2">
    <li><a href="dashboard.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
    <li><a href="my_classes.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chalkboard"></i><span>My Classes</span></a></li>
    <li><a href="students.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-user-graduate"></i><span>Students</span></a></li>
    <li><a href="attendance.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-clipboard-check"></i><span>Attendance</span></a></li>
    <li><a href="marks.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chart-line"></i><span>Marks Entry</span></a></li>
    <li><a href="reports.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-file-alt"></i><span>Reports</span></a></li>
    <li><a href="timetable.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg"><i class="fas fa-calendar"></i><span>Timetable</span></a></li>
    <li><a href="documents.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-folder"></i><span>Documents</span></a></li>
</ul>';

include '../includes/header.php';
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">My Timetable</h1>
            <p class="text-gray-500 text-sm">View your weekly class schedule.</p>
        </div>
    </div>

    <?php if (empty($timetable_entries)): ?>
        <div class="bg-white rounded-xl shadow-sm p-12">
            <div class="text-center text-gray-500">
                <i class="fas fa-calendar-times text-6xl mb-4 opacity-20"></i>
                <h3 class="text-lg font-medium mb-2">No Timetable Available</h3>
                <p>Your timetable hasn't been set up yet. Contact your administrator.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php foreach ($days as $day): ?>
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 p-4">
                        <h3 class="text-lg font-bold text-white"><?php echo $day; ?></h3>
                        <p class="text-blue-100 text-sm"><?php echo count($timetable_by_day[$day]); ?> classes</p>
                    </div>

                    <div class="p-4">
                        <?php if (empty($timetable_by_day[$day])): ?>
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-calendar-day text-3xl mb-2 opacity-20"></i>
                                <p class="text-sm">No classes</p>
                            </div>
                        <?php else: ?>
                            <div class="space-y-3">
                                <?php foreach ($timetable_by_day[$day] as $slot): ?>
                                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                                        <div class="text-center min-w-0 flex-shrink-0">
                                            <div class="text-sm font-semibold text-indigo-600">
                                                <?php echo date('g:i A', strtotime($slot['start_time'])); ?>
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                <?php echo date('g:i A', strtotime($slot['end_time'])); ?>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-semibold text-gray-800 text-sm truncate">
                                                <?php echo htmlspecialchars($slot['subject_name']); ?>
                                            </h4>
                                            <p class="text-xs text-gray-600 truncate">
                                                <?php echo htmlspecialchars($slot['class_name']); ?>
                                            </p>
                                            <?php if (!empty($slot['room_name'])): ?>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                                    <?php echo htmlspecialchars($slot['room_name']); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Weekly Overview -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Weekly Overview</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                            <?php foreach ($days as $day): ?>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase"><?php echo substr($day, 0, 3); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php
                        // Get all unique time slots
                        $time_slots = [];
                        foreach ($timetable_entries as $entry) {
                            $time_key = $entry['start_time'] . '-' . $entry['end_time'];
                            if (!in_array($time_key, $time_slots)) {
                                $time_slots[] = $time_key;
                            }
                        }
                        sort($time_slots);

                        foreach ($time_slots as $time_slot):
                            list($start, $end) = explode('-', $time_slot);
                        ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    <?php echo date('g:i A', strtotime($start)); ?> - <?php echo date('g:i A', strtotime($end)); ?>
                                </td>
                                <?php foreach ($days as $day): ?>
                                    <td class="px-4 py-3 text-center">
                                        <?php
                                        $class_found = false;
                                        foreach ($timetable_by_day[$day] as $slot) {
                                            if ($slot['start_time'] === $start && $slot['end_time'] === $end) {
                                                echo '<div class="text-xs">';
                                                echo '<div class="font-medium text-gray-800">' . htmlspecialchars($slot['subject_name']) . '</div>';
                                                echo '<div class="text-gray-500">' . htmlspecialchars($slot['class_name']) . '</div>';
                                                if (!empty($slot['room_name'])) {
                                                    echo '<div class="text-gray-400">' . htmlspecialchars($slot['room_name']) . '</div>';
                                                }
                                                echo '</div>';
                                                $class_found = true;
                                                break;
                                            }
                                        }
                                        if (!$class_found) {
                                            echo '<span class="text-gray-300">-</span>';
                                        }
                                        ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>