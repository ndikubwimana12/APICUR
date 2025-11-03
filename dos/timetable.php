<?php

/**
 * DOS - Professional Timetable Management
 * Generate and view school timetable with intelligent period allocation
 */
require_once '../config/config.php';
requireLogin();

if (!hasRole('dos')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'School Timetable';
$database = new Database();
$conn = $database->getConnection();

$activeAcademicYear = defined('CURRENT_ACADEMIC_YEAR') ? CURRENT_ACADEMIC_YEAR : null;
$activeTerm = defined('CURRENT_TERM') ? CURRENT_TERM : null;
$originalAcademicYear = $activeAcademicYear;
$originalTerm = $activeTerm;
$academicContextNotice = '';

try {
    $needsFallback = empty($activeAcademicYear) || empty($activeTerm);

    if (!$needsFallback) {
        $availabilityStmt = $conn->prepare("SELECT COUNT(*) FROM timetable_slots WHERE academic_year = ? AND term = ?");
        $availabilityStmt->execute([$activeAcademicYear, $activeTerm]);
        $needsFallback = ((int)$availabilityStmt->fetchColumn() === 0);
    }

    if ($needsFallback) {
        $fallbackStmt = $conn->query("SELECT academic_year, term FROM timetable_slots ORDER BY academic_year DESC, term DESC LIMIT 1");
        $fallback = $fallbackStmt ? $fallbackStmt->fetch(PDO::FETCH_ASSOC) : null;

        if ($fallback) {
            $activeAcademicYear = $fallback['academic_year'];
            $activeTerm = $fallback['term'];

            if (!empty($originalAcademicYear) && !empty($originalTerm)) {
                if ($activeAcademicYear != $originalAcademicYear || $activeTerm != $originalTerm) {
                    $academicContextNotice = sprintf(
                        'No timetable entries found for academic year %s (term %s). Displaying most recent data for academic year %s (term %s).',
                        $originalAcademicYear,
                        $originalTerm,
                        $activeAcademicYear,
                        $activeTerm
                    );
                }
            } else {
                $academicContextNotice = sprintf(
                    'Default academic year or term was not set. Displaying most recent timetable data for academic year %s (term %s).',
                    $activeAcademicYear,
                    $activeTerm
                );
            }
        }
    }
} catch (Exception $e) {
    // Optional: log the exception without breaking page rendering
    error_log('Timetable academic year fallback error: ' . $e->getMessage());
}

// Define school schedule (8:00 AM - 4:40 PM)
$schedule = [
    ['slot' => '08:00-08:40', 'start' => '08:00', 'end' => '08:40', 'type' => 'period', 'label' => 'Period 1'],
    ['slot' => '08:40-09:20', 'start' => '08:40', 'end' => '09:20', 'type' => 'period', 'label' => 'Period 2'],
    ['slot' => '09:20-10:00', 'start' => '09:20', 'end' => '10:00', 'type' => 'period', 'label' => 'Period 3'],
    ['slot' => '10:00-10:20', 'start' => '10:00', 'end' => '10:20', 'type' => 'break', 'label' => 'Break'],
    ['slot' => '10:20-11:00', 'start' => '10:20', 'end' => '11:00', 'type' => 'period', 'label' => 'Period 4'],
    ['slot' => '11:00-11:40', 'start' => '11:00', 'end' => '11:40', 'type' => 'period', 'label' => 'Period 5'],
    ['slot' => '11:40-13:10', 'start' => '11:40', 'end' => '13:10', 'type' => 'lunch', 'label' => 'Lunch Break'],
    ['slot' => '13:10-13:50', 'start' => '13:10', 'end' => '13:50', 'type' => 'period', 'label' => 'Period 6'],
    ['slot' => '13:50-14:30', 'start' => '13:50', 'end' => '14:30', 'type' => 'period', 'label' => 'Period 7'],
    ['slot' => '14:30-15:10', 'start' => '14:30', 'end' => '15:10', 'type' => 'period', 'label' => 'Period 8'],
    ['slot' => '15:10-15:20', 'start' => '15:10', 'end' => '15:20', 'type' => 'break', 'label' => 'Break'],
    ['slot' => '15:20-16:00', 'start' => '15:20', 'end' => '16:00', 'type' => 'period', 'label' => 'Period 9'],
    ['slot' => '16:00-16:40', 'start' => '16:00', 'end' => '16:40', 'type' => 'period', 'label' => 'Period 10'],
];

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$view_type = $_GET['view'] ?? 'school';
$filter_id = $_GET['id'] ?? null;
$filter_type = $_GET['filter'] ?? 'class';

// Helper function to convert time to minutes
function timeToMinutes($time)
{
    list($h, $m) = explode(':', $time);
    return $h * 60 + $m;
}

// Helper function to get slots occupied by a module
function getSlotSpan($start_time, $end_time, $schedule)
{
    $start_mins = timeToMinutes($start_time);
    $end_mins = timeToMinutes($end_time);

    $occupied = [];
    foreach ($schedule as $idx => $slot) {
        if ($slot['type'] === 'break' || $slot['type'] === 'lunch') {
            continue;
        }

        $slot_start = timeToMinutes($slot['start']);
        $slot_end = timeToMinutes($slot['end']);

        if ($slot_start >= $start_mins && $slot_end <= $end_mins) {
            $occupied[] = $idx;
        }
    }

    return $occupied;
}

// Fetch timetable data based on view type
$timetable_data = [];

if ($view_type === 'school') {
    $query = "SELECT ts.*, m.module_name, m.module_code, m.credits, m.total_hours, 
              u.full_name as teacher_name, c.class_name
              FROM timetable_slots ts
              JOIN modules m ON ts.module_id = m.id
              JOIN users u ON ts.teacher_id = u.id
              JOIN classes c ON ts.class_id = c.id
              WHERE ts.academic_year = ? AND ts.term = ?
              ORDER BY ts.day_of_week, ts.start_time";
    $stmt = $conn->prepare($query);
    $stmt->execute([$activeAcademicYear, $activeTerm]);
} elseif ($view_type === 'teacher' && $filter_id) {
    $query = "SELECT ts.*, m.module_name, m.module_code, m.credits, m.total_hours, 
              u.full_name as teacher_name, c.class_name
              FROM timetable_slots ts
              JOIN modules m ON ts.module_id = m.id
              JOIN users u ON ts.teacher_id = u.id
              JOIN classes c ON ts.class_id = c.id
              WHERE ts.teacher_id = ? AND ts.academic_year = ? AND ts.term = ?
              ORDER BY ts.day_of_week, ts.start_time";
    $stmt = $conn->prepare($query);
    $stmt->execute([$filter_id, $activeAcademicYear, $activeTerm]);
} elseif ($view_type === 'class' && $filter_id) {
    $query = "SELECT ts.*, m.module_name, m.module_code, m.credits, m.total_hours, 
              u.full_name as teacher_name, c.class_name
              FROM timetable_slots ts
              JOIN modules m ON ts.module_id = m.id
              JOIN users u ON ts.teacher_id = u.id
              JOIN classes c ON ts.class_id = c.id
              WHERE ts.class_id = ? AND ts.academic_year = ? AND ts.term = ?
              ORDER BY ts.day_of_week, ts.start_time";
    $stmt = $conn->prepare($query);
    $stmt->execute([$filter_id, $activeAcademicYear, $activeTerm]);
} else {
    $stmt = $conn->prepare(
        "SELECT ts.*, m.module_name, m.module_code, m.credits, m.total_hours, u.full_name AS teacher_name, c.class_name
         FROM timetable_slots ts
         JOIN modules m ON ts.module_id = m.id
         JOIN users u ON ts.teacher_id = u.id
         JOIN classes c ON ts.class_id = c.id
         WHERE ts.academic_year = ? AND ts.term = ?
         ORDER BY ts.day_of_week, ts.start_time"
    );
    $stmt->execute([$activeAcademicYear, $activeTerm]);
}

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($rows) && !$needsFallback) {
    // Attempt to fetch most recent timetable entries as a final safeguard
    $fallbackRecentStmt = $conn->prepare(
        "SELECT ts.*, m.module_name, m.module_code, m.credits, m.total_hours, u.full_name AS teacher_name, c.class_name
         FROM timetable_slots ts
         JOIN modules m ON ts.module_id = m.id
         JOIN users u ON ts.teacher_id = u.id
         JOIN classes c ON ts.class_id = c.id
         ORDER BY ts.academic_year DESC, ts.term DESC, ts.day_of_week, ts.start_time
         LIMIT 1000"
    );
    $fallbackRecentStmt->execute();
    $rowsFallback = $fallbackRecentStmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($rowsFallback)) {
        $rows = $rowsFallback;
        $activeAcademicYear = $rowsFallback[0]['academic_year'] ?? $activeAcademicYear;
        $activeTerm = $rowsFallback[0]['term'] ?? $activeTerm;
        $academicContextNotice = $academicContextNotice ?: 'No timetable entries matched the selected filters. Displaying the most recent available timetable data.';
    }
}

// Organize data by day and slot
$timetable_by_day = [];
foreach ($days as $day) {
    $timetable_by_day[$day] = [];
}

foreach ($rows as $slot) {
    $day = $slot['day_of_week'];
    if (!isset($timetable_by_day[$day])) {
        $timetable_by_day[$day] = [];
    }

    // Get the schedule slots occupied by this module
    $occupied = getSlotSpan($slot['start_time'], $slot['end_time'], $schedule);
    foreach ($occupied as $idx) {
        $time_slot = $schedule[$idx]['slot'];
        if (!isset($timetable_by_day[$day][$time_slot])) {
            $timetable_by_day[$day][$time_slot] = [];
        }
        $timetable_by_day[$day][$time_slot][] = $slot;
    }
}

// Get filter options
$classes = [];
$teachers = [];
$query_classes = "SELECT id, class_name FROM classes WHERE status = 'active' ORDER BY class_name";
$stmt_classes = $conn->prepare($query_classes);
$stmt_classes->execute();
$classes = $stmt_classes->fetchAll(PDO::FETCH_ASSOC);

$query_teachers = "SELECT DISTINCT u.id, u.full_name FROM users u 
                   JOIN timetable_slots ts ON u.id = ts.teacher_id 
                   WHERE u.role IN ('teacher', 'dos') ORDER BY u.full_name";
$stmt_teachers = $conn->prepare($query_teachers);
$stmt_teachers->execute();
$teachers = $stmt_teachers->fetchAll(PDO::FETCH_ASSOC);

// Get current selection info
$current_selection_name = '';
if ($filter_id) {
    if ($view_type === 'teacher') {
        $q = "SELECT full_name FROM users WHERE id = ?";
        $s = $conn->prepare($q);
        $s->execute([$filter_id]);
        $current_selection_name = $s->fetchColumn();
    } elseif ($view_type === 'class') {
        $q = "SELECT class_name FROM classes WHERE id = ?";
        $s = $conn->prepare($q);
        $s->execute([$filter_id]);
        $current_selection_name = $s->fetchColumn();
    }
}

// Color mapping for days


$day_header_colors = [
    'Monday' => 'bg-blue-500',
    'Tuesday' => 'bg-purple-500',
    'Wednesday' => 'bg-green-500',
    'Thursday' => 'bg-yellow-500',
    'Friday' => 'bg-pink-500',
];

// Setup sidebar menu for DOS
$sidebar_menu = '
<ul class="space-y-2">
    <li><a href="dashboard.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
    <li><a href="classes.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-school"></i><span>Classes</span></a></li>
    <li><a href="teachers.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chalkboard-teacher"></i><span>Teachers</span></a></li>
    <li><a href="modules.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-book"></i><span>Modules</span></a></li>
    <li><a href="generate_timetable.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-wand-magic-sparkles"></i><span>Generate Timetable</span></a></li>
    <li><a href="timetable.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg"><i class="fas fa-calendar-alt"></i><span>View Timetable</span></a></li>
    <li><a href="teacher_assignments.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-tasks"></i><span>Assignments</span></a></li>
    <li><a href="performance.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chart-line"></i><span>Performance</span></a></li>
    <li><a href="reports.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chart-bar"></i><span>Reports</span></a></li>
</ul>
';

include '../includes/header.php';
?>

<style>
    .timetable-container {
        display: grid;
        grid-template-columns: 100px repeat(5, 1fr);
        gap: 1px;
        background: #e5e7eb;
        padding: 1px;
    }

    .timetable-cell {
        background: white;
        padding: 12px;
        min-height: 80px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        border: 1px solid #e5e7eb;
    }

    .time-cell {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 600;
        text-align: center;
        align-items: center;
    }

    .day-header {
        font-weight: 700;
        color: white;
        text-align: center;
        padding: 16px 12px;
        align-items: center;
    }

    .module-cell {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        border-left: 4px solid #667eea;
    }

    .break-cell {
        background: #f3f4f6;
        color: #6b7280;
        font-size: 12px;
        font-weight: 500;
    }

    .lunch-cell {
        background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, rgba(34, 197, 94, 0.15) 100%);
        border-left: 4px solid #22c55e;
    }

    .module-name {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 4px;
    }

    .module-code {
        font-size: 11px;
        color: #667eea;
        font-weight: 500;
        margin-bottom: 4px;
    }

    .teacher-info {
        font-size: 12px;
        color: #4b5563;
        margin-bottom: 2px;
    }

    .room-info {
        font-size: 11px;
        color: #6b7280;
        background: #f3f4f6;
        padding: 2px 6px;
        border-radius: 3px;
        width: fit-content;
        margin-top: 4px;
    }

    .filter-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    @media print {

        .filter-section,
        .print-btn {
            display: none;
        }

        .timetable-container {
            gap: 0;
        }
    }
</style>

<main class="flex-1 overflow-y-auto p-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                    <i class="fas fa-calendar-alt text-indigo-600"></i>
                    School Timetable
                </h1>
                <p class="text-gray-600 mt-2">
                    Professional schedule for academic year <?php echo htmlspecialchars($activeAcademicYear); ?> (Term
                    <?php echo htmlspecialchars($activeTerm); ?>)
                </p>
                <?php if (!empty($academicContextNotice)): ?>
                    <p class="mt-2 text-sm text-yellow-600 bg-yellow-50 border border-yellow-200 rounded-md px-4 py-2">
                        <?php echo htmlspecialchars($academicContextNotice); ?>
                    </p>
                <?php endif; ?>
            </div>
            <button onclick="window.print()"
                class="print-btn bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition">
                <i class="fas fa-print mr-2"></i> Print
            </button>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section rounded-lg p-6 mb-8 text-white">
        <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
            <i class="fas fa-filter"></i> View Timetable
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2 opacity-90">View Type</label>
                <select id="viewType"
                    class="w-full px-4 py-2 rounded-lg bg-white bg-opacity-20 text-white placeholder-white placeholder-opacity-70 border border-white border-opacity-30 focus:outline-none"
                    onchange="updateTimetable()">
                    <option value="school" <?php echo $view_type === 'school' ? 'selected' : ''; ?>>All School Timetable
                    </option>
                    <option value="class" <?php echo $view_type === 'class' ? 'selected' : ''; ?>>Class Timetable
                    </option>
                    <option value="teacher" <?php echo $view_type === 'teacher' ? 'selected' : ''; ?>>Teacher Timetable
                    </option>
                </select>
            </div>

            <div id="classSelect" style="display: <?php echo $view_type === 'class' ? 'block' : 'none'; ?>;">
                <label class="block text-sm font-medium mb-2 opacity-90">Select Class</label>
                <select id="filterId"
                    class="w-full px-4 py-2 rounded-lg bg-white bg-opacity-20 text-white placeholder-white placeholder-opacity-70 border border-white border-opacity-30 focus:outline-none"
                    onchange="updateTimetable()">
                    <option value="">Choose a class...</option>
                    <?php foreach ($classes as $cls): ?>
                        <option value="<?php echo $cls['id']; ?>"
                            <?php echo $filter_id == $cls['id'] && $view_type === 'class' ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cls['class_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="teacherSelect" style="display: <?php echo $view_type === 'teacher' ? 'block' : 'none'; ?>;">
                <label class="block text-sm font-medium mb-2 opacity-90">Select Teacher</label>
                <select id="filterId"
                    class="w-full px-4 py-2 rounded-lg bg-white bg-opacity-20 text-white placeholder-white placeholder-opacity-70 border border-white border-opacity-30 focus:outline-none"
                    onchange="updateTimetable()">
                    <option value="">Choose a teacher...</option>
                    <?php foreach ($teachers as $teacher): ?>
                        <option value="<?php echo $teacher['id']; ?>"
                            <?php echo $filter_id == $teacher['id'] && $view_type === 'teacher' ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($teacher['full_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <?php if ($current_selection_name): ?>
            <div class="mt-4 text-sm opacity-90">
                Currently viewing: <strong><?php echo htmlspecialchars($current_selection_name); ?></strong>
            </div>
        <?php endif; ?>
    </div>


    <!-- Timetable Display -->
    <div class="bg-white rounded-lg shadow-lg overflow-x-auto">
        <?php if (count($rows) === 0): ?>
            <div class="p-8 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
                    <i class="fas fa-calendar-plus text-blue-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No Timetable Data Available</h3>
                <p class="text-gray-600 mb-4">The timetable is empty. Please generate a timetable first.</p>
                <a href="generate_timetable.php"
                    class="inline-block bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-wand-magic-sparkles mr-2"></i> Generate Timetable
                </a>
            </div>
        <?php else: ?>
            <table class="w-full border-collapse" style="min-width: 100%;">
                <thead>
                    <tr class="bg-gray-100 border-b-2 border-gray-300">
                        <th class="px-4 py-3 text-left font-semibold text-gray-900 border-r border-gray-300">Time</th>
                        <?php foreach ($days as $day): ?>
                            <th class="px-4 py-3 text-center font-semibold text-white <?php echo $day_header_colors[$day]; ?>"
                                style="min-width: 180px;">
                                <?php echo $day; ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($schedule as $idx => $slot): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td
                                class="px-4 py-3 font-semibold text-gray-900 bg-gray-50 border-r border-gray-300 whitespace-nowrap">
                                <div class="text-sm"><?php echo $slot['slot']; ?></div>
                                <div class="text-xs text-gray-600"><?php echo $slot['label']; ?></div>
                            </td>

                            <?php foreach ($days as $day): ?>
                                <td class="px-4 py-3 border border-gray-200 align-top" style="background-color: <?php
                                                                                                                if ($slot['type'] === 'break' || $slot['type'] === 'lunch') {
                                                                                                                    echo '#f9fafb';
                                                                                                                } else {
                                                                                                                    echo 'white';
                                                                                                                }
                                                                                                                ?>;">
                                    <?php if ($slot['type'] === 'break' || $slot['type'] === 'lunch'): ?>
                                        <div class="text-xs font-medium text-gray-500 text-center py-2">
                                            <?php echo ucfirst($slot['type']); ?>
                                        </div>
                                        <?php elseif (isset($timetable_by_day[$day][$slot['slot']])):
                                        $modules = $timetable_by_day[$day][$slot['slot']];
                                        foreach ($modules as $module):
                                        ?>
                                            <div class="bg-indigo-50 rounded p-2 border-l-4 border-indigo-600 mb-1">
                                                <div class="font-semibold text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($module['module_name']); ?>
                                                    (<?php echo htmlspecialchars($module['class_name']); ?>)
                                                </div>
                                                <div class="text-xs text-indigo-600 font-medium">
                                                    <?php echo htmlspecialchars($module['module_code']); ?>
                                                </div>
                                                <div class="text-xs text-gray-700 mt-1">
                                                    👨‍🏫 <?php echo htmlspecialchars($module['teacher_name']); ?>
                                                </div>
                                                <div class="text-xs text-gray-600 mt-1">
                                                    🏛️ Room <?php echo htmlspecialchars($module['room'] ?? 'TBA'); ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Statistics -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg p-6 shadow-sm border-l-4 border-indigo-500">
            <h3 class="text-gray-600 text-sm font-medium">Total Slots</h3>
            <p class="text-3xl font-bold text-indigo-600 mt-2"><?php echo count($rows); ?></p>
            <p class="text-xs text-gray-500 mt-1">Teaching periods assigned</p>
        </div>

        <div class="bg-white rounded-lg p-6 shadow-sm border-l-4 border-green-500">
            <h3 class="text-gray-600 text-sm font-medium">Days Scheduled</h3>
            <p class="text-3xl font-bold text-green-600 mt-2">5</p>
            <p class="text-xs text-gray-500 mt-1">Monday to Friday</p>
        </div>

        <div class="bg-white rounded-lg p-6 shadow-sm border-l-4 border-purple-500">
            <h3 class="text-gray-600 text-sm font-medium">Working Hours</h3>
            <p class="text-3xl font-bold text-purple-600 mt-2">8</p>
            <p class="text-xs text-gray-500 mt-1">08:00 - 16:00</p>
        </div>

        <div class="bg-white rounded-lg p-6 shadow-sm border-l-4 border-orange-500">
            <h3 class="text-gray-600 text-sm font-medium">Period Duration</h3>
            <p class="text-3xl font-bold text-orange-600 mt-2">40</p>
            <p class="text-xs text-gray-500 mt-1">Minutes per class</p>
        </div>
    </div>
</main>

<script>
    const viewSelect = document.getElementById('viewType');
    const classSelect = document.getElementById('classSelect');
    const teacherSelect = document.getElementById('teacherSelect');
    const filterIdSelect = document.getElementById('filterId');

    viewSelect.addEventListener('change', function() {
        if (this.value === 'class') {
            classSelect.style.display = 'block';
            teacherSelect.style.display = 'none';
        } else if (this.value === 'teacher') {
            classSelect.style.display = 'none';
            teacherSelect.style.display = 'block';
        } else {
            classSelect.style.display = 'none';
            teacherSelect.style.display = 'none';
        }
    });

    function updateTimetable() {
        const view = document.getElementById('viewType').value;
        const filterId = document.getElementById('filterId').value;

        if (view === 'school') {
            window.location.href = `?view=school`;
        } else if (filterId) {
            window.location.href = `?view=${view}&id=${filterId}`;
        } else {
            alert('Please select a ' + view);
        }
    }
</script>
</body>

</html>