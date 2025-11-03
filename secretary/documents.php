<?php

/**
 * Secretary - Documents Management
 * Upload and manage documents relevant to secretary role
 */
require_once '../config/config.php';
requireLogin();

if (!hasRole('secretary')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Documents';

$database = new Database();
$conn = $database->getConnection();

$errors = [];
$successMessage = '';

$categoryFilter = $_GET['category'] ?? '';
$searchTerm = trim($_GET['search'] ?? '');
$studentIdFilter = $_GET['student_id'] ?? '';

$categories = [
    'meeting' => 'Meeting',
    'pedagogical' => 'Pedagogical',
    'administrative' => 'Administrative',
    'policy' => 'Policy',
    'report' => 'Report',
    'other' => 'Other'
];

// Handle document upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_document') {
    $title = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $category = $_POST['category'] ?? '';
    $relatedClassId = $_POST['related_class_id'] !== '' ? (int) $_POST['related_class_id'] : null;
    $accessRoles = $_POST['access_roles'] ?? [];

    if ($title === '') {
        $errors['title'] = 'Title is required';
    }

    if (!array_key_exists($category, $categories)) {
        $errors['category'] = 'Please select a valid category';
    }

    $documentPath = null;
    if (!empty($_FILES['document']['name'])) {
        $file = $_FILES['document'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = array_merge(ALLOWED_DOCUMENT_TYPES, ALLOWED_IMAGE_TYPES);
            if (!in_array($file['type'], $allowedTypes, true)) {
                $errors['document'] = 'Unsupported file type';
            } elseif ($file['size'] > MAX_FILE_SIZE) {
                $errors['document'] = 'File exceeds 5MB limit';
            } else {
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'doc_' . time() . '_' . generateRandomString(8) . '.' . $extension;
                $destinationDir = UPLOAD_DIR . 'documents/';
                if (!is_dir($destinationDir)) {
                    mkdir($destinationDir, 0775, true);
                }
                $destination = $destinationDir . $filename;
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $documentPath = 'uploads/documents/' . $filename;
                } else {
                    $errors['document'] = 'Failed to upload file. Please try again.';
                }
            }
        } else {
            $errors['document'] = 'File upload error. Code: ' . $file['error'];
        }
    } else {
        $errors['document'] = 'Please select a file to upload';
    }

    if (empty($errors)) {
        try {
            $insertQuery = 'INSERT INTO documents (
                title, description, file_path, file_type, file_size, category,
                uploaded_by, access_roles, related_class_id, created_at
            ) VALUES (
                :title, :description, :file_path, :file_type, :file_size, :category,
                :uploaded_by, :access_roles, :related_class_id, NOW()
            )';

            $stmt = $conn->prepare($insertQuery);
            $stmt->execute([
                ':title' => $title,
                ':description' => $description,
                ':file_path' => $documentPath,
                ':file_type' => $_FILES['document']['type'],
                ':file_size' => $_FILES['document']['size'],
                ':category' => $category,
                ':uploaded_by' => $_SESSION['user_id'],
                ':access_roles' => json_encode($accessRoles),
                ':related_class_id' => $relatedClassId
            ]);

            logActivity(
                $conn,
                $_SESSION['user_id'],
                'upload',
                'document',
                (int) $conn->lastInsertId(),
                'Uploaded document: ' . $title
            );

            $successMessage = 'Document uploaded successfully!';
            $_POST = [];
        } catch (Exception $e) {
            error_log('Document upload failed: ' . $e->getMessage());
            $errors['general'] = 'An unexpected error occurred. Please try again.';
        }
    }
}

// Build query for document listing
$conditions = [];
$params = [];

if ($categoryFilter !== '' && array_key_exists($categoryFilter, $categories)) {
    $conditions[] = 'category = :category';
    $params[':category'] = $categoryFilter;
}

if ($searchTerm !== '') {
    $conditions[] = '(title LIKE :search OR description LIKE :search)';
    $params[':search'] = "%{$searchTerm}%";
}

if ($studentIdFilter !== '') {
    $conditions[] = 'related_class_id = (SELECT class_id FROM students WHERE id = :student_id)';
    $params[':student_id'] = $studentIdFilter;
}

$whereClause = '';
if (!empty($conditions)) {
    $whereClause = 'WHERE ' . implode(' AND ', $conditions);
}

$documentsQuery = "SELECT documents.*, users.full_name AS uploader
                   FROM documents
                   LEFT JOIN users ON documents.uploaded_by = users.id
                   {$whereClause}
                   ORDER BY documents.created_at DESC
                   LIMIT 30";

$documentsStmt = $conn->prepare($documentsQuery);
$documentsStmt->execute($params);
$documents = $documentsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch classes for filters
$classStmt = $conn->query('SELECT id, class_name FROM classes WHERE status = "active" ORDER BY class_name');
$classes = $classStmt->fetchAll(PDO::FETCH_ASSOC);

$sidebar_menu = '
<ul class="space-y-2">
    <li><a href="dashboard.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
    <li><a href="students.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-user-graduate"></i><span>Students</span></a></li>
    <li><a href="register_student.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-user-plus"></i><span>Register Student</span></a></li>
    <li><a href="documents.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg"><i class="fas fa-file-alt"></i><span>Documents</span></a></li>
    <li><a href="meetings.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-calendar-alt"></i><span>Meetings</span></a></li>
    <li><a href="reports.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chart-bar"></i><span>Reports</span></a></li>
</ul>';

include '../includes/header.php';
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Document Management</h1>
            <p class="text-gray-500 text-sm">Upload and organize meeting and administrative documents.</p>
        </div>
        <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" class="inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg shadow-sm hover:bg-indigo-700">
            <i class="fas fa-upload"></i>
            <span>Upload Document</span>
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
                <label class="block text-sm font-medium text-gray-600 mb-2">Search</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" placeholder="Title or description" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-2">Category</label>
                <select name="category" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $key => $label): ?>
                        <option value="<?php echo $key; ?>" <?php echo ($categoryFilter === $key) ? 'selected' : ''; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-2">Class</label>
                <select name="class" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Classes</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?php echo $class['id']; ?>" <?php echo (($studentIdFilter !== '' && $class['id'] == $student['class_id']) || ($_GET['class'] ?? '') == $class['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($class['class_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Filter</button>
                <a href="documents.php" class="w-full bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-center hover:bg-gray-200">Reset</a>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <?php if (empty($documents)): ?>
            <div class="bg-white rounded-xl shadow-sm p-10 text-center text-gray-500">
                <i class="fas fa-file-alt text-4xl opacity-30 mb-3"></i>
                <p>No documents found.</p>
                <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" class="mt-3 text-indigo-600 hover:text-indigo-700 font-medium">Upload your first document →</button>
            </div>
        <?php else: ?>
            <?php foreach ($documents as $document): ?>
                <div class="bg-white rounded-xl shadow-sm p-6 flex flex-col gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center text-lg">
                            <i class="fas fa-file"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($document['title']); ?></h3>
                            <p class="text-sm text-gray-500">Category: <?php echo ucfirst($document['category']); ?></p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600"><?php echo nl2br(htmlspecialchars($document['description'] ?? 'No description provided.')); ?></p>
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span><i class="fas fa-user mr-2"></i><?php echo htmlspecialchars($document['uploader'] ?? 'N/A'); ?></span>
                        <span><i class="far fa-clock mr-2"></i><?php echo formatDate($document['created_at'], DATETIME_FORMAT); ?></span>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="<?php echo BASE_URL . htmlspecialchars($document['file_path']); ?>" target="_blank" class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                            <i class="fas fa-arrow-up-right-from-square"></i> Open
                        </a>
                        <a href="<?php echo BASE_URL . htmlspecialchars($document['file_path']); ?>" download class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-700 text-sm font-medium">
                            <i class="fas fa-download"></i> Download
                        </a>
                    </div>
                    <div class="border-t pt-4 text-xs text-gray-500">
                        <p>Access Roles: <?php echo htmlspecialchars(implode(', ', json_decode($document['access_roles'] ?? '[]', true) ?: [])); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h2 class="text-lg font-semibold text-gray-800">Upload Document</h2>
            <button onclick="document.getElementById('uploadModal').classList.add('hidden')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="action" value="upload_document">
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
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category <span class="text-red-500">*</span></label>
                        <select name="category" class="w-full border <?php echo isset($errors['category']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $key => $label): ?>
                                <option value="<?php echo $key; ?>" <?php echo (($_POST['category'] ?? '') === $key) ? 'selected' : ''; ?>><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['category'])): ?>
                            <p class="text-xs text-red-500 mt-1"><?php echo $errors['category']; ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Related Class</label>
                        <select name="related_class_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">None</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>" <?php echo (($_POST['related_class_id'] ?? '') == $class['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($class['class_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Document File <span class="text-red-500">*</span></label>
                        <input type="file" name="document" class="w-full border <?php echo isset($errors['document']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <?php if (isset($errors['document'])): ?>
                            <p class="text-xs text-red-500 mt-1"><?php echo $errors['document']; ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Access Roles</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
                        <?php
                        $roles = ['admin', 'secretary', 'teacher', 'dos', 'head_teacher', 'accountant', 'discipline_officer'];
                        $selectedRoles = $_POST['access_roles'] ?? ['admin', 'secretary'];
                        foreach ($roles as $role): ?>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="access_roles[]" value="<?php echo $role; ?>" <?php echo in_array($role, $selectedRoles, true) ? 'checked' : ''; ?> class="rounded text-indigo-600 focus:ring-indigo-500">
                                <span class="capitalize"><?php echo str_replace('_', ' ', $role); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button type="button" onclick="document.getElementById('uploadModal').classList.add('hidden')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Upload Document</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            document.getElementById('uploadModal').classList.add('hidden');
        }
    });
</script>

<?php include '../includes/footer.php'; ?>