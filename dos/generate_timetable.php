<?php
ob_start();

/**
 * DOS - Timetable Generation
 * Auto-generate timetables based on module-teacher assignments
 */
require_once '../config/config.php';
requireLogin();

if (!hasRole('dos')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Generate Timetable';

$database = new Database();
$conn = $database->getConnection();
$current_user = $_SESSION['user_id'];

// Handle timetable generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_timetable'])) {
    try {
        $term = $_POST['term'] ?? '1';

        try {
            // Clear existing timetable for this term
            $delete_query = "DELETE FROM timetable_slots
                            WHERE term = ? AND academic_year = ?";
            $delete_stmt = $conn->prepare($delete_query);
            $delete_stmt->execute([$term, CURRENT_ACADEMIC_YEAR]);
        } catch (PDOException $e) {
            throw new Exception('Timetable system not yet initialized. Please contact administrator to run database setup.');
        }

        // Get all module-teacher assignments
        try {
            $assignments_query = "SELECT mt.*, m.module_name, m.total_hours, m.credits, t.id as teacher_id, t.full_name, c.class_name, c.id as class_id
                                 FROM module_teachers mt
                                 INNER JOIN modules m ON mt.module_id = m.id
                                 INNER JOIN users t ON mt.teacher_id = t.id
                                 INNER JOIN classes c ON mt.class_id = c.id
                                 WHERE mt.academic_year = ?
                                 ORDER BY m.module_name";
            $assignments_stmt = $conn->prepare($assignments_query);
            $assignments_stmt->execute([CURRENT_ACADEMIC_YEAR]);
            $assignments = $assignments_stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($assignments) === 0) {
                throw new Exception('No module assignments found. Please assign modules to teachers first.');
            }
        } catch (PDOException $e) {
            throw new Exception('Module assignment system not yet initialized. Please contact administrator to run database setup.');
        }

        // Days and time slots for generating schedule
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $time_slots = [
            ['start' => '08:00', 'end' => '10:00'],
            ['start' => '10:15', 'end' => '12:15'],
            ['start' => '13:00', 'end' => '15:00'],
            ['start' => '15:15', 'end' => '17:15']
        ];

        // Track occupied slots to avoid conflicts
        $occupied_teacher = [];
        $occupied_class = [];
        foreach ($days as $day) {
            foreach ($time_slots as $slot) {
                $occupied_teacher[$day][$slot['start']] = [];
                $occupied_class[$day][$slot['start']] = [];
            }
        }

        // Generate timetable slots
        try {
            $insert_query = "INSERT INTO timetable_slots (
                class_id, module_id, teacher_id, day_of_week, start_time, end_time,
                term, academic_year, hours_allocated, room, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $insert_stmt = $conn->prepare($insert_query);
        } catch (PDOException $e) {
            throw new Exception('Timetable system not yet initialized. Please contact administrator to run database setup.');
        }

        $room_counter = 1;
        $assigned_count = 0;

        foreach ($assignments as $assignment) {
            // Calculate how many 2-hour slots this module needs per week
            $slots_needed = ceil($assignment['total_hours'] / 10 / 2); // Assuming 10-week term

            $assigned_for_module = 0;
            $attempts = 0;
            $max_attempts = count($days) * count($time_slots);

            while ($assigned_for_module < $slots_needed && $attempts < $max_attempts) {
                $time_index = (int)($attempts % count($time_slots));
                $day_index = (int)(floor($attempts / count($time_slots)) % count($days));
                $day = $days[$day_index];
                $time = $time_slots[$time_index];

                $teacher_free = !in_array($assignment['teacher_id'], $occupied_teacher[$day][$time['start']]);
                $class_free = !in_array($assignment['class_id'], $occupied_class[$day][$time['start']]);

                if ($teacher_free && $class_free) {
                    $room = 'Room ' . $room_counter;
                    $room_counter = $room_counter % 10 + 1; // Cycle through 1-10

                    $insert_stmt->execute([
                        $assignment['class_id'],
                        $assignment['module_id'],
                        $assignment['teacher_id'],
                        $day,
                        $time['start'],
                        $time['end'],
                        $term,
                        CURRENT_ACADEMIC_YEAR,
                        2, // 2-hour blocks
                        $room,
                        $current_user
                    ]);

                    $occupied_teacher[$day][$time['start']][] = $assignment['teacher_id'];
                    $occupied_class[$day][$time['start']][] = $assignment['class_id'];

                    $assigned_for_module++;
                    $assigned_count++;
                }

                $attempts++;
            }

            if ($assigned_for_module < $slots_needed) {
                // Could not assign all slots, but continue
                error_log("Could not assign all slots for module " . $assignment['module_name']);
            }
        }

        $_SESSION['success'] = 'School timetable generated successfully. Assigned ' . $assigned_count . ' slots for ' . count($assignments) . ' module assignments.';
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
    ob_end_clean();
    header('Location: generate_timetable.php');
    exit();
}

// Get generated timetables summary
$timetable_summary = [];
try {
    $timetable_summary_query = "SELECT ts.term,
                                COUNT(DISTINCT CONCAT(ts.day_of_week, ts.start_time)) as total_slots,
                                COUNT(DISTINCT ts.module_id) as total_modules,
                                COUNT(DISTINCT ts.class_id) as total_classes,
                                COUNT(DISTINCT ts.teacher_id) as total_teachers,
                                MAX(ts.created_at) as last_generated
                         FROM timetable_slots ts
                         WHERE ts.academic_year = ?
                         GROUP BY ts.term
                         ORDER BY ts.term";
    $summary_stmt = $conn->prepare($timetable_summary_query);
    $summary_stmt->execute([CURRENT_ACADEMIC_YEAR]);
    $timetable_summary = $summary_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // timetable_slots table doesn't exist yet, show empty summary
    $timetable_summary = [];
}

$sidebar_menu = '
<ul class="space-y-2">
    <li><a href="dashboard.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
    <li><a href="classes.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-school"></i><span>Classes</span></a></li>
    <li><a href="teachers.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chalkboard-teacher"></i><span>Teachers</span></a></li>
    <li><a href="modules.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-book"></i><span>Modules</span></a></li>
    <li><a href="generate_timetable.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg"><i class="fas fa-wand-magic-sparkles"></i><span>Generate Timetable</span></a></li>
    <li><a href="timetable.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-calendar-alt"></i><span>View Timetable</span></a></li>
    <li><a href="teacher_assignments.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-tasks"></i><span>Assignments</span></a></li>
    <li><a href="performance.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chart-line"></i><span>Performance</span></a></li>
    <li><a href="reports.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chart-bar"></i><span>Reports</span></a></li>
</ul>';

include '../includes/header.php';
?>

<div class="space-y-6">
    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl p-6 text-white">
        <h1 class="text-3xl font-bold">Timetable Generation</h1>
        <p class="opacity-90">Auto-generate class timetables for the academic term</p>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
            <i class="fas fa-check-circle mr-2"></i> <?php echo $_SESSION['success'];
                                                        unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
            <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $_SESSION['error'];
                                                            unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <!-- Generation Form -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">
            <i class="fas fa-magic mr-2 text-blue-600"></i> Generate School Timetable
        </h2>

        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Academic Term</label>
                <select name="term" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="1">Term 1</option>
                    <option value="2">Term 2</option>
                    <option value="3">Term 3</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" name="generate_timetable" value="1" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium">
                    <i class="fas fa-sync-alt mr-2"></i> Generate School Timetable
                </button>
            </div>
        </form>

        <div class="mt-6 p-4 bg-blue-50 border-l-4 border-blue-600 rounded text-blue-900">
            <i class="fas fa-info-circle mr-2"></i>
            <strong>Auto-generation Process:</strong>
            <ul class="ml-6 mt-2 list-disc text-sm space-y-1">
                <li>Generates timetable for all classes and assigned modules</li>
                <li>Assigns 2-hour slots for each module throughout the week</li>
                <li>Prevents teacher and class conflicts automatically</li>
                <li>Distributes modules across available time slots (08:00-17:15)</li>
                <li>Replaces existing timetable for the selected term</li>
            </ul>
        </div>
    </div>

    <!-- Timetable Status -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b">
            <h2 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-list-check mr-2 text-blue-600"></i> Timetable Status by Term
            </h2>
        </div>

        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Term</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Classes</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Modules</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Teachers</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Total Slots</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Last Generated</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php
                $terms = [1, 2, 3];
                foreach ($terms as $term_num):
                    $summary = null;
                    foreach ($timetable_summary as $s) {
                        if ($s['term'] == $term_num) {
                            $summary = $s;
                            break;
                        }
                    }
                ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm font-medium text-gray-700">
                            Term <?php echo $term_num; ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <?php echo $summary ? $summary['total_classes'] : 0; ?> classes
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <?php echo $summary ? $summary['total_modules'] : 0; ?> modules
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <?php echo $summary ? $summary['total_teachers'] : 0; ?> teachers
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <?php echo $summary ? $summary['total_slots'] : 0; ?> slots
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <?php echo $summary && $summary['last_generated'] ? date('M d, Y H:i', strtotime($summary['last_generated'])) : 'Never'; ?>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <?php if ($summary && $summary['total_slots'] > 0): ?>
                                <span class="px-3 py-1 rounded-full bg-green-100 text-green-800 text-xs font-medium">
                                    <i class="fas fa-check-circle mr-1"></i> Generated
                                </span>
                            <?php else: ?>
                                <span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-800 text-xs font-medium">
                                    <i class="fas fa-clock mr-1"></i> Pending
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Timetable Example -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">
            <i class="fas fa-calendar mr-2 text-blue-600"></i> Example Timetable Structure
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-300 px-4 py-2">Time</th>
                        <th class="border border-gray-300 px-4 py-2">Monday</th>
                        <th class="border border-gray-300 px-4 py-2">Tuesday</th>
                        <th class="border border-gray-300 px-4 py-2">Wednesday</th>
                        <th class="border border-gray-300 px-4 py-2">Thursday</th>
                        <th class="border border-gray-300 px-4 py-2">Friday</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border border-gray-300 px-4 py-2 font-medium bg-gray-50">08:00-10:00</td>
                        <td class="border border-gray-300 px-4 py-2 bg-blue-100">Module 1<br><small>Teacher A, Room 1</small></td>
                        <td class="border border-gray-300 px-4 py-2 bg-green-100">Module 2<br><small>Teacher B, Room 2</small></td>
                        <td class="border border-gray-300 px-4 py-2 bg-purple-100">Module 3<br><small>Teacher C, Room 3</small></td>
                        <td class="border border-gray-300 px-4 py-2 bg-blue-100">Module 1<br><small>Teacher A, Room 1</small></td>
                        <td class="border border-gray-300 px-4 py-2 bg-yellow-100">Module 4<br><small>Teacher D, Room 2</small></td>
                    </tr>
                    <tr>
                        <td class="border border-gray-300 px-4 py-2 font-medium bg-gray-50">10:15-12:15</td>
                        <td class="border border-gray-300 px-4 py-2 bg-green-100">Module 2<br><small>Teacher B, Room 2</small></td>
                        <td class="border border-gray-300 px-4 py-2 bg-purple-100">Module 3<br><small>Teacher C, Room 3</small></td>
                        <td class="border border-gray-300 px-4 py-2 bg-blue-100">Module 1<br><small>Teacher A, Room 1</small></td>
                        <td class="border border-gray-300 px-4 py-2 bg-yellow-100">Module 4<br><small>Teacher D, Room 2</small></td>
                        <td class="border border-gray-300 px-4 py-2 bg-green-100">Module 2<br><small>Teacher B, Room 3</small></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>