<?php

/**
 * DOS - Module Management (Enhanced Card-Based Layout)
 * Manage training modules and assign teachers to modules
 */
require_once '../config/config.php';
requireLogin();

if (!hasRole('dos')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Module Management';
$database = new Database();
$conn = $database->getConnection();

// Handle module assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_module'])) {
    try {
        $teacher_id = $_POST['teacher_id'] ?? null;
        $module_id = $_POST['module_id'] ?? null;
        $class_id = $_POST['class_id'] ?? null;

        if (!$teacher_id || !$module_id || !$class_id) {
            throw new Exception('All fields are required');
        }

        try {
            $check_query = "SELECT COUNT(*) FROM module_teachers 
                           WHERE teacher_id = ? AND module_id = ? AND class_id = ? AND academic_year = ?";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->execute([$teacher_id, $module_id, $class_id, CURRENT_ACADEMIC_YEAR]);

            if ($check_stmt->fetchColumn() > 0) {
                throw new Exception('This module is already assigned to this teacher for this class');
            }

            $insert_query = "INSERT INTO module_teachers (teacher_id, module_id, class_id, academic_year)
                            VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->execute([$teacher_id, $module_id, $class_id, CURRENT_ACADEMIC_YEAR]);

            $_SESSION['success'] = 'Module assigned successfully';
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'module_teachers') !== false || strpos($e->getMessage(), '1146') !== false) {
                throw new Exception('Module assignment system not yet initialized. Please contact administrator to run database setup at /database/init_extensions.php');
            }
            throw $e;
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
    header('Location: modules.php');
    exit();
}

// Handle module assignment removal
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['assignment_id'])) {
    try {
        $delete_query = "DELETE FROM module_teachers WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->execute([$_GET['assignment_id']]);

        $_SESSION['success'] = 'Module assignment removed';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error removing assignment: ' . $e->getMessage();
    }
    header('Location: modules.php');
    exit();
}

// Get all modules
$all_modules = [];
try {
    $modules_query = "SELECT id, module_code, module_name, description, level, credits, total_hours, status
                      FROM modules WHERE status = 'active' ORDER BY level, module_name";
    $modules_stmt = $conn->prepare($modules_query);
    $modules_stmt->execute();
    $all_modules = $modules_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $all_modules = [];
}

// Get all teachers
$teachers_query = "SELECT id, full_name FROM users WHERE role = 'teacher' AND status = 'active' ORDER BY full_name";
$teachers_stmt = $conn->prepare($teachers_query);
$teachers_stmt->execute();
$teachers = $teachers_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all classes
$classes_query = "SELECT id, class_name FROM classes WHERE academic_year = ? AND status = 'active' ORDER BY class_name";
$classes_stmt = $conn->prepare($classes_query);
$classes_stmt->execute([CURRENT_ACADEMIC_YEAR]);
$classes = $classes_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get current module assignments
$assignments = [];
try {
    $assignments_query = "SELECT mt.id, mt.academic_year, mt.hours_per_week,
                                 t.full_name as teacher_name, m.module_name, m.module_code,
                                 m.total_hours, m.credits, c.class_name
                          FROM module_teachers mt
                          INNER JOIN users t ON mt.teacher_id = t.id
                          INNER JOIN modules m ON mt.module_id = m.id
                          INNER JOIN classes c ON mt.class_id = c.id
                          WHERE mt.academic_year = ?
                          ORDER BY c.class_name, m.module_name";
    $assignments_stmt = $conn->prepare($assignments_query);
    $assignments_stmt->execute([CURRENT_ACADEMIC_YEAR]);
    $assignments = $assignments_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $assignments = [];
}

// Get statistics
$total_modules = count($all_modules);
$total_assignments = count($assignments);
$total_hours = array_sum(array_column($all_modules, 'total_hours'));
$total_credits = array_sum(array_column($all_modules, 'credits'));

// Setup sidebar menu for DOS
$sidebar_menu = '
<ul class="space-y-2">
    <li><a href="dashboard.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
    <li><a href="classes.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-school"></i><span>Classes</span></a></li>
    <li><a href="teachers.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chalkboard-teacher"></i><span>Teachers</span></a></li>
    <li><a href="modules.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg"><i class="fas fa-book"></i><span>Modules</span></a></li>
    <li><a href="assign_teacher.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-user-tie"></i><span>Assign Teachers</span></a></li>
    <li><a href="generate_timetable.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-wand-magic-sparkles"></i><span>Generate Timetable</span></a></li>
    <li><a href="timetable.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-calendar-alt"></i><span>View Timetable</span></a></li>
    <li><a href="reports.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chart-bar"></i><span>Reports</span></a></li>
    <li><a href="performance.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chart-line"></i><span>Performance</span></a></li>
</ul>
';

include '../includes/header.php';
?>

<style>
    .modules-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    @media (max-width: 640px) {
        .modules-grid {
            grid-template-columns: 1fr;
        }
    }

    .module-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid #e5e7eb;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
    }

    .module-card:hover {
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
        border-color: #667eea;
    }

    .module-card.level-1 {
        border-top: 4px solid #ef4444;
    }

    .module-card.level-2 {
        border-top: 4px solid #f97316;
    }

    .module-card.level-3 {
        border-top: 4px solid #eab308;
    }

    .module-card.level-4 {
        border-top: 4px solid #3b82f6;
    }

    .module-card.level-5 {
        border-top: 4px solid #8b5cf6;
    }

    .module-card-header {
        display: flex;
        align-items: start;
        justify-content: space-between;
        margin-bottom: 1rem;
    }

    .module-code-badge {
        display: inline-block;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .module-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin: 1rem 0;
        padding: 1rem 0;
        border-top: 1px solid #f3f4f6;
        border-bottom: 1px solid #f3f4f6;
    }

    .stat-item {
        text-align: center;
    }

    .stat-number {
        font-size: 1.5rem;
        font-weight: 700;
        color: #667eea;
    }

    .stat-label {
        font-size: 0.75rem;
        color: #6b7280;
        font-weight: 500;
        margin-top: 0.25rem;
    }

    .level-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .level-1 .level-badge {
        background: #fee2e2;
        color: #991b1b;
    }

    .level-2 .level-badge {
        background: #fed7aa;
        color: #92400e;
    }

    .level-3 .level-badge {
        background: #fef08a;
        color: #854d0e;
    }

    .level-4 .level-badge {
        background: #dbeafe;
        color: #1e40af;
    }

    .level-5 .level-badge {
        background: #ede9fe;
        color: #6d28d9;
    }

    .assignment-row {
        background: white;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        border: 1px solid #e5e7eb;
        transition: all 0.3s ease;
    }

    .assignment-row:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .assignment-row-header {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr auto;
        gap: 1rem;
        margin-bottom: 0.5rem;
    }

    @media (max-width: 768px) {
        .assignment-row-header {
            grid-template-columns: 1fr;
        }

        .modules-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<main class="flex-1 overflow-y-auto p-8">
    <div class="space-y-6 max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl p-6 text-white">
            <h1 class="text-3xl font-bold flex items-center gap-3">
                <i class="fas fa-cube"></i> Module Management
            </h1>
            <p class="opacity-90 mt-1">Manage training modules and assign teachers to classes</p>
        </div>

        <!-- Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
                <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success'];
                                                    unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error'];
                                                            unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg p-6 shadow-sm border-l-4 border-indigo-500">
                <h3 class="text-gray-600 text-sm font-medium">Total Modules</h3>
                <p class="text-3xl font-bold text-indigo-600 mt-2"><?php echo $total_modules; ?></p>
                <p class="text-xs text-gray-500 mt-1">Active modules</p>
            </div>

            <div class="bg-white rounded-lg p-6 shadow-sm border-l-4 border-blue-500">
                <h3 class="text-gray-600 text-sm font-medium">Total Credits</h3>
                <p class="text-3xl font-bold text-blue-600 mt-2"><?php echo $total_credits; ?></p>
                <p class="text-xs text-gray-500 mt-1">Credits available</p>
            </div>

            <div class="bg-white rounded-lg p-6 shadow-sm border-l-4 border-green-500">
                <h3 class="text-gray-600 text-sm font-medium">Total Hours</h3>
                <p class="text-3xl font-bold text-green-600 mt-2"><?php echo $total_hours; ?></p>
                <p class="text-xs text-gray-500 mt-1">Teaching hours</p>
            </div>

            <div class="bg-white rounded-lg p-6 shadow-sm border-l-4 border-purple-500">
                <h3 class="text-gray-600 text-sm font-medium">Assignments</h3>
                <p class="text-3xl font-bold text-purple-600 mt-2"><?php echo $total_assignments; ?></p>
                <p class="text-xs text-gray-500 mt-1">Active assignments</p>
            </div>
        </div>

        <!-- Assign Module Form -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <i class="fas fa-link text-indigo-600"></i> Assign Module to Teacher
            </h2>
            <form method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="hidden" name="assign_module" value="1">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Teacher</label>
                    <select name="teacher_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Teacher</option>
                        <?php foreach ($teachers as $teacher): ?>
                            <option value="<?php echo $teacher['id']; ?>">
                                <?php echo htmlspecialchars($teacher['full_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Module</label>
                    <select name="module_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Module</option>
                        <?php foreach ($all_modules as $module): ?>
                            <option value="<?php echo $module['id']; ?>">
                                <?php echo htmlspecialchars($module['module_name'] . ' (' . $module['module_code'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Class</label>
                    <select name="class_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['id']; ?>">
                                <?php echo htmlspecialchars($class['class_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition">
                        <i class="fas fa-plus mr-2"></i> Assign
                    </button>
                </div>
            </form>
        </div>

        <!-- Modules Grid -->
        <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                <i class="fas fa-th text-indigo-600"></i> All Modules
            </h2>
            <div class="modules-grid">
                <?php foreach ($all_modules as $module):
                    $level_num = str_replace('Level ', '', $module['level']);
                    $level_key = 'level-' . $level_num;
                ?>
                    <div class="module-card <?php echo $level_key; ?>">
                        <div class="module-card-header">
                            <div>
                                <h3 class="font-bold text-gray-900 text-sm"><?php echo htmlspecialchars($module['module_name']); ?></h3>
                            </div>
                            <span class="module-code-badge"><?php echo htmlspecialchars($module['module_code']); ?></span>
                        </div>

                        <div class="flex-1">
                            <p class="text-xs text-gray-600 leading-relaxed mb-3">
                                <?php echo htmlspecialchars(substr($module['description'] ?? 'No description', 0, 100)); ?>...
                            </p>
                        </div>

                        <div class="module-stats">
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $module['credits']; ?></div>
                                <div class="stat-label">Credits</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $module['total_hours']; ?></div>
                                <div class="stat-label">Hours</div>
                            </div>
                        </div>

                        <div class="level-badge text-center">
                            <?php echo htmlspecialchars($module['level']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Current Assignments -->
        <?php if (count($assignments) > 0): ?>
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-tasks text-indigo-600"></i> Current Assignments (<?php echo count($assignments); ?>)
                </h2>
                <div class="space-y-2">
                    <?php foreach ($assignments as $assign): ?>
                        <div class="assignment-row">
                            <div class="assignment-row-header">
                                <div>
                                    <div class="font-semibold text-gray-900"><?php echo htmlspecialchars($assign['module_name']); ?></div>
                                    <div class="text-xs text-gray-600">📚 <?php echo htmlspecialchars($assign['module_code']); ?></div>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900"><?php echo htmlspecialchars($assign['class_name']); ?></div>
                                    <div class="text-xs text-gray-600">👨‍🏫 <?php echo htmlspecialchars($assign['teacher_name']); ?></div>
                                </div>
                                <div class="space-y-1">
                                    <div class="text-xs font-semibold text-indigo-600">📊 <?php echo $assign['credits']; ?> Credits</div>
                                    <div class="text-xs font-semibold text-green-600">⏰ <?php echo $assign['total_hours']; ?> Hours</div>
                                </div>
                                <a href="?action=remove&assignment_id=<?php echo $assign['id']; ?>" onclick="return confirm('Remove this assignment?')"
                                    class="inline-block text-red-600 hover:text-red-800 hover:bg-red-100 px-3 py-2 rounded-lg text-sm transition">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-gray-50 rounded-lg p-8 text-center">
                <i class="fas fa-inbox text-4xl text-gray-400 mb-2 block"></i>
                <p class="text-gray-600 font-medium">No module assignments yet</p>
                <p class="text-gray-500 text-sm">Assign modules to teachers using the form above</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include '../includes/footer.php'; ?>