<?php

/**
 * Secretary - Meetings Management
 * Schedule and track meetings
 */
require_once '../config/config.php';
requireLogin();

if (!hasRole('secretary')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Meetings';

$database = new Database();
$conn = $database->getConnection();

$errors = [];
$successMessage = '';

$meetingTypes = [
    'parent' => 'Parent Meeting',
    'staff' => 'Staff Meeting',
    'board' => 'Board Meeting',
    'discipline' => 'Discipline Meeting',
    'other' => 'Other Meeting'
];

$meetingStatuses = [
    'scheduled' => 'Scheduled',
    'completed' => 'Completed',
    'cancelled' => 'Cancelled'
];

$filterStatus = $_GET['status'] ?? '';
$filterType = $_GET['type'] ?? '';

// Handle meeting creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_meeting') {
    $title = sanitize($_POST['title'] ?? '');
    $meetingType = $_POST['meeting_type'] ?? '';
    $meetingDate = $_POST['meeting_date'] ?? '';
    $startTime = $_POST['start_time'] ?? '';
    $endTime = $_POST['end_time'] ?? '';
    $location = sanitize($_POST['location'] ?? '');
    $participants = sanitize($_POST['participants'] ?? '');
    $agenda = sanitize($_POST['agenda'] ?? '');
    $notes = sanitize($_POST['notes'] ?? '');
    $status = $_POST['status'] ?? 'scheduled';

    if ($title === '') {
        $errors['title'] = 'Meeting title is required';
    }
    if (!array_key_exists($meetingType, $meetingTypes)) {
        $errors['meeting_type'] = 'Please select a valid meeting type';
    }
    if ($meetingDate === '') {
        $errors['meeting_date'] = 'Meeting date is required';
    }
    if ($startTime === '') {
        $errors['start_time'] = 'Start time is required';
    }
    if (!array_key_exists($status, $meetingStatuses)) {
        $status = 'scheduled';
    }

    if (empty($errors)) {
        try {
            $insertQuery = 'INSERT INTO meetings (
                title, meeting_type, meeting_date, start_time, end_time, location,
                participants, agenda, notes, status, created_by, created_at
            ) VALUES (
                :title, :meeting_type, :meeting_date, :start_time, :end_time, :location,
                :participants, :agenda, :notes, :status, :created_by, NOW()
            )';

            $stmt = $conn->prepare($insertQuery);
            $stmt->execute([
                ':title' => $title,
                ':meeting_type' => $meetingType,
                ':meeting_date' => $meetingDate,
                ':start_time' => $startTime,
                ':end_time' => $endTime,
                ':location' => $location,
                ':participants' => $participants,
                ':agenda' => $agenda,
                ':notes' => $notes,
                ':status' => $status,
                ':created_by' => $_SESSION['user_id']
            ]);

            logActivity(
                $conn,
                $_SESSION['user_id'],
                'create',
                'meeting',
                (int) $conn->lastInsertId(),
                'Scheduled meeting: ' . $title
            );

            $successMessage = 'Meeting scheduled successfully!';
            $_POST = [];
        } catch (Exception $e) {
            error_log('Meeting creation failed: ' . $e->getMessage());
            $errors['general'] = 'An unexpected error occurred. Please try again.';
        }
    }
}

// Build query for meeting listing
$conditions = [];
$params = [];

if ($filterStatus !== '' && array_key_exists($filterStatus, $meetingStatuses)) {
    $conditions[] = 'status = :status';
    $params[':status'] = $filterStatus;
}

if ($filterType !== '' && array_key_exists($filterType, $meetingTypes)) {
    $conditions[] = 'meeting_type = :meeting_type';
    $params[':meeting_type'] = $filterType;
}

$whereClause = '';
if (!empty($conditions)) {
    $whereClause = 'WHERE ' . implode(' AND ', $conditions);
}

$meetingsQuery = "SELECT * FROM meetings {$whereClause} ORDER BY meeting_date DESC, start_time DESC LIMIT 30";
$meetingsStmt = $conn->prepare($meetingsQuery);
$meetingsStmt->execute($params);
$meetings = $meetingsStmt->fetchAll(PDO::FETCH_ASSOC);

$sidebar_menu = '
<ul class="space-y-2">
    <li><a href="dashboard.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
    <li><a href="students.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-user-graduate"></i><span>Students</span></a></li>
    <li><a href="register_student.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-user-plus"></i><span>Register Student</span></a></li>
    <li><a href="documents.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-file-alt"></i><span>Documents</span></a></li>
    <li><a href="meetings.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg"><i class="fas fa-calendar-alt"></i><span>Meetings</span></a></li>
    <li><a href="reports.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chart-bar"></i><span>Reports</span></a></li>
</ul>';

include '../includes/header.php';
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Meetings</h1>
            <p class="text-gray-500 text-sm">Schedule and track parent, staff, and board meetings.</p>
        </div>
        <button onclick="document.getElementById('meetingModal').classList.remove('hidden')" class="inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg shadow-sm hover:bg-indigo-700">
            <i class="fas fa-calendar-plus"></i>
            <span>Schedule Meeting</span>
        </button>
    </div>

    <?php if (!empty($successMessage)): ?>
        <div class="p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded">
            <i class="fas fa-check-circle mr-2"></i>
            <?php echo $successMessage; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors['general'])): ?>
        <div class="p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <?php echo $errors['general']; ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-2">Meeting Type</label>
                <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Types</option>
                    <?php foreach ($meetingTypes as $key => $label): ?>
                        <option value="<?php echo $key; ?>" <?php echo ($filterType === $key) ? 'selected' : ''; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-2">Status</label>
                <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Statuses</option>
                    <?php foreach ($meetingStatuses as $key => $label): ?>
                        <option value="<?php echo $key; ?>" <?php echo ($filterStatus === $key) ? 'selected' : ''; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Filter</button>
                <a href="meetings.php" class="w-full bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-center hover:bg-gray-200">Reset</a>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <?php if (empty($meetings)): ?>
            <div class="bg-white rounded-xl shadow-sm p-10 text-center text-gray-500">
                <i class="fas fa-calendar-alt text-4xl opacity-30 mb-3"></i>
                <p>No meetings found.</p>
                <button onclick="document.getElementById('meetingModal').classList.remove('hidden')" class="mt-3 text-indigo-600 hover:text-indigo-700 font-medium">Schedule your first meeting →</button>
            </div>
        <?php else: ?>
            <?php foreach ($meetings as $meeting): ?>
                <div class="bg-white rounded-xl shadow-sm p-6 flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($meeting['title']); ?></h3>
                            <p class="text-sm text-gray-500"><?php echo $meetingTypes[$meeting['meeting_type']] ?? 'Meeting'; ?></p>
                        </div>
                        <span class="px-3 py-1 text-xs font-semibold rounded-full <?php
                                                                                    $statusClass = [
                                                                                        'scheduled' => 'bg-blue-100 text-blue-800',
                                                                                        'completed' => 'bg-green-100 text-green-800',
                                                                                        'cancelled' => 'bg-red-100 text-red-800'
                                                                                    ];
                                                                                    echo $statusClass[$meeting['status']] ?? 'bg-gray-100 text-gray-800';
                                                                                    ?>"><?php echo ucfirst($meeting['status']); ?></span>
                    </div>
                    <div class="text-sm text-gray-600 space-y-2">
                        <p><i class="far fa-calendar mr-2"></i><?php echo formatDate($meeting['meeting_date']); ?> (<?php echo $meeting['start_time']; ?> - <?php echo $meeting['end_time']; ?>)</p>
                        <p><i class="fas fa-map-marker-alt mr-2"></i><?php echo htmlspecialchars($meeting['location'] ?: 'To be confirmed'); ?></p>
                        <p><i class="fas fa-users mr-2"></i><?php echo htmlspecialchars($meeting['participants'] ?: 'Participants TBD'); ?></p>
                        <?php if (!empty($meeting['agenda'])): ?>
                            <p><i class="fas fa-list mr-2"></i><?php echo nl2br(htmlspecialchars($meeting['agenda'])); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($meeting['notes'])): ?>
                        <div class="bg-gray-50 p-3 rounded-lg text-sm text-gray-500">
                            <p class="font-semibold text-gray-700 mb-1">Notes:</p>
                            <?php echo nl2br(htmlspecialchars($meeting['notes'])); ?>
                        </div>
                    <?php endif; ?>
                    <div class="flex items-center gap-3">
                        <a href="documents.php?category=meeting" class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                            <i class="fas fa-file-alt"></i> Related Documents
                        </a>
                        <a href="#" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-700 text-sm font-medium">
                            <i class="fas fa-user-check"></i> Mark Attendance
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Schedule Meeting Modal -->
<div id="meetingModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h2 class="text-lg font-semibold text-gray-800">Schedule Meeting</h2>
            <button onclick="document.getElementById('meetingModal').classList.add('hidden')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <form method="POST" class="space-y-6">
                <input type="hidden" name="action" value="create_meeting">
                <?php if (!empty($errors)): ?>
                    <div class="p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                        <i class="fas fa-exclamation-circle mr-2"></i> Please fix the errors below and try again.
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" class="w-full border <?php echo isset($errors['title']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <?php if (isset($errors['title'])): ?>
                            <p class="text-xs text-red-500 mt-1"><?php echo $errors['title']; ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Meeting Type <span class="text-red-500">*</span></label>
                        <select name="meeting_type" class="w-full border <?php echo isset($errors['meeting_type']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select Type</option>
                            <?php foreach ($meetingTypes as $key => $label): ?>
                                <option value="<?php echo $key; ?>" <?php echo (($_POST['meeting_type'] ?? '') === $key) ? 'selected' : ''; ?>><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['meeting_type'])): ?>
                            <p class="text-xs text-red-500 mt-1"><?php echo $errors['meeting_type']; ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date <span class="text-red-500">*</span></label>
                        <input type="date" name="meeting_date" value="<?php echo htmlspecialchars($_POST['meeting_date'] ?? ''); ?>" class="w-full border <?php echo isset($errors['meeting_date']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <?php if (isset($errors['meeting_date'])): ?>
                            <p class="text-xs text-red-500 mt-1"><?php echo $errors['meeting_date']; ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Start Time <span class="text-red-500">*</span></label>
                        <input type="time" name="start_time" value="<?php echo htmlspecialchars($_POST['start_time'] ?? ''); ?>" class="w-full border <?php echo isset($errors['start_time']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <?php if (isset($errors['start_time'])): ?>
                            <p class="text-xs text-red-500 mt-1"><?php echo $errors['start_time']; ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">End Time</label>
                        <input type="time" name="end_time" value="<?php echo htmlspecialchars($_POST['end_time'] ?? ''); ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                        <input type="text" name="location" value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Participants</label>
                        <input type="text" name="participants" value="<?php echo htmlspecialchars($_POST['participants'] ?? ''); ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <p class="text-xs text-gray-400 mt-1">Example: Parents of Standard 5, DOS, Head Teacher</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Agenda</label>
                    <textarea name="agenda" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"><?php echo htmlspecialchars($_POST['agenda'] ?? ''); ?></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <?php foreach ($meetingStatuses as $key => $label): ?>
                            <option value="<?php echo $key; ?>" <?php echo (($_POST['status'] ?? 'scheduled') === $key) ? 'selected' : ''; ?>><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button type="button" onclick="document.getElementById('meetingModal').classList.add('hidden')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Save Meeting</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            document.getElementById('meetingModal').classList.add('hidden');
        }
    });
</script>

<?php include '../includes/footer.php'; ?>