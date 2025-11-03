<?php

/**
 * Teacher - Documents Management
 * Upload and manage documents
 */
require_once '../config/config.php';
requireLogin();

if (!hasRole('teacher')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Documents';

$database = new Database();
$conn = $database->getConnection();
$teacher_id = $_SESSION['user_id'];

$message = '';
$messageType = '';

// Handle document upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_document'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $class_id = !empty($_POST['class_id']) ? $_POST['class_id'] : null;

    if (empty($title)) {
        $message = 'Document title is required.';
        $messageType = 'error';
    } elseif (empty($_FILES['document']['name'])) {
        $message = 'Please select a file to upload.';
        $messageType = 'error';
    } else {
        $file = $_FILES['document'];
        $allowed_types = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($file_extension, $allowed_types)) {
            $message = 'Invalid file type. Allowed types: PDF, DOC, DOCX, TXT, JPG, JPEG, PNG.';
            $messageType = 'error';
        } elseif ($file['size'] > 10 * 1024 * 1024) { // 10MB limit
            $message = 'File size too large. Maximum size is 10MB.';
            $messageType = 'error';
        } else {
            // Generate unique filename
            $filename = uniqid('doc_') . '_' . time() . '.' . $file_extension;
            $upload_path = '../uploads/documents/' . $filename;

            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Save to database
                $insert_query = "INSERT INTO documents (title, description, file_path, file_type, file_size, category, related_class_id, uploaded_by, created_at)
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                $insert_stmt = $conn->prepare($insert_query);
                $insert_stmt->execute([
                    $title,
                    $description,
                    $upload_path,
                    $file_extension,
                    $file['size'],
                    $category,
                    $class_id,
                    $teacher_id
                ]);

                $message = 'Document uploaded successfully.';
                $messageType = 'success';
            } else {
                $message = 'Failed to upload file.';
                $messageType = 'error';
            }
        }
    }
}

// Handle document deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $doc_id = $_GET['delete'];

    // Check if teacher owns the document
    $check_query = "SELECT file_path FROM documents WHERE id = ? AND uploaded_by = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->execute([$doc_id, $teacher_id]);
    $document = $check_stmt->fetch();

    if ($document) {
        // Delete file
        $file_path = $document['file_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Delete from database
        $delete_query = "DELETE FROM documents WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->execute([$doc_id]);

        $message = 'Document deleted successfully.';
        $messageType = 'success';
    } else {
        $message = 'Document not found or access denied.';
        $messageType = 'error';
    }
}

// Get documents uploaded by teacher
$searchTerm = trim($_GET['search'] ?? '');
$categoryFilter = $_GET['category'] ?? '';

$whereClause = "WHERE uploaded_by = ?";
$params = [$teacher_id];

if ($searchTerm !== '') {
    $whereClause .= " AND (title LIKE ? OR description LIKE ?)";
    $searchTerm = "%{$searchTerm}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($categoryFilter !== '') {
    $whereClause .= " AND category = ?";
    $params[] = $categoryFilter;
}

// Pagination
$perPage = RECORDS_PER_PAGE;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

$countQuery = "SELECT COUNT(*) FROM documents {$whereClause}";
$countStmt = $conn->prepare($countQuery);
$countStmt->execute($params);
$totalRecords = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalRecords / $perPage));

$query = "SELECT d.*, c.class_name FROM documents d
          LEFT JOIN classes c ON d.related_class_id = c.id
          {$whereClause}
          ORDER BY d.created_at DESC
          LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
$query_params = array_merge($params, [$perPage, $offset]);
$stmt->execute($query_params);
$documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get teacher's classes for upload form
$class_query = "SELECT DISTINCT c.id, c.class_name
                FROM teacher_subjects ts
                INNER JOIN classes c ON ts.class_id = c.id
                WHERE ts.teacher_id = ? AND ts.academic_year = ?
                ORDER BY c.class_name";
$class_stmt = $conn->prepare($class_query);
$class_stmt->execute([
    $teacher_id,
    CURRENT_ACADEMIC_YEAR
]);
$classes = $class_stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = [
    'lesson_plan' => 'Lesson Plan',
    'assignment' => 'Assignment',
    'exam' => 'Exam Paper',
    'notes' => 'Class Notes',
    'resource' => 'Resource',
    'other' => 'Other'
];

// Sidebar menu for teacher
$sidebar_menu = '
<ul class="space-y-2">
    <li><a href="dashboard.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
    <li><a href="my_classes.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chalkboard"></i><span>My Classes</span></a></li>
    <li><a href="students.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-user-graduate"></i><span>Students</span></a></li>
    <li><a href="attendance.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-clipboard-check"></i><span>Attendance</span></a></li>
    <li><a href="marks.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chart-line"></i><span>Marks Entry</span></a></li>
    <li><a href="reports.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-file-alt"></i><span>Reports</span></a></li>
    <li><a href="timetable.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-calendar"></i><span>Timetable</span></a></li>
    <li><a href="documents.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg"><i class="fas fa-folder"></i><span>Documents</span></a></li>
</ul>';

include '../includes/header.php';
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Document Management</h1>
            <p class="text-gray-500 text-sm">Upload and manage your teaching documents.</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="document.getElementById('uploadModal').classList.remove('hidden')"
                class="inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg shadow-sm hover:bg-indigo-700">
                <i class="fas fa-upload"></i>
                <span>Upload Document</span>
            </button>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="bg-<?php echo $messageType === 'success' ? 'green' : 'red'; ?>-100 border border-<?php echo $messageType === 'success' ? 'green' : 'red'; ?>-400 text-<?php echo $messageType === 'success' ? 'green' : 'red'; ?>-700 px-4 py-3 rounded-lg">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Filter</button>
                <a href="documents.php" class="w-full bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-center hover:bg-gray-200">Reset</a>
            </div>
        </form>
    </div>

    <!-- Documents List -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Document</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Class</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Size</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Upload Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($documents)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                <i class="fas fa-file-alt text-4xl mb-3 opacity-20"></i>
                                <p class="mb-2">No documents found.</p>
                                <button onclick="document.getElementById('uploadModal').classList.remove('hidden')"
                                    class="text-indigo-600 hover:text-indigo-700 font-medium">Upload your first document →</button>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($documents as $doc): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <i class="fas fa-file-<?php
                                                                $ext = $doc['file_type'];
                                                                if (in_array($ext, ['pdf'])) echo 'pdf text-red-500';
                                                                elseif (in_array($ext, ['doc', 'docx'])) echo 'word text-blue-500';
                                                                elseif (in_array($ext, ['jpg', 'jpeg', 'png'])) echo 'image text-green-500';
                                                                else echo 'alt text-gray-500';
                                                                ?> text-xl mr-3"></i>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($doc['title']); ?></div>
                                            <div class="text-xs text-gray-500"><?php echo htmlspecialchars($doc['original_filename']); ?></div>
                                            <?php if ($doc['description']): ?>
                                                <div class="text-xs text-gray-400 mt-1"><?php echo htmlspecialchars(substr($doc['description'], 0, 50)); ?><?php echo strlen($doc['description']) > 50 ? '...' : ''; ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                        <?php echo $categories[$doc['category']] ?? ucfirst($doc['category']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($doc['class_name'] ?? 'General'); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500"><?php echo formatFileSize($doc['file_size']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500"><?php echo formatDate($doc['created_at']); ?></td>
                                <td class="px-6 py-4 text-sm flex items-center gap-3">
                                    <a href="../uploads/documents/<?php echo $doc['filename']; ?>" target="_blank" class="text-indigo-600 hover:text-indigo-700" title="Download"><i class="fas fa-download"></i></a>
                                    <a href="?delete=<?php echo $doc['id']; ?>" onclick="return confirm('Are you sure you want to delete this document?')" class="text-red-600 hover:text-red-700" title="Delete"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="px-6 py-4 bg-gray-50 flex items-center justify-between text-sm text-gray-600">
                <span>Showing <?php echo min($totalRecords, $offset + 1); ?> to <?php echo min($totalRecords, $offset + $perPage); ?> of <?php echo $totalRecords; ?> documents</span>
                <div class="flex items-center gap-2">
                    <?php if ($page > 1): ?>
                        <a href="<?php echo buildPageLink($page - 1); ?>" class="px-3 py-1 rounded-lg bg-white border hover:bg-gray-100">Previous</a>
                    <?php endif; ?>
                    <span class="px-3 py-1 rounded-lg bg-indigo-100 text-indigo-700 font-medium">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
                    <?php if ($page < $totalPages): ?>
                        <a href="<?php echo buildPageLink($page + 1); ?>" class="px-3 py-1 rounded-lg bg-white border hover:bg-gray-100">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Upload Document</h3>
        </div>
        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-2">Title *</label>
                <input type="text" name="title" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-2">Description</label>
                <textarea name="description" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-2">Category</label>
                <select name="category" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <?php foreach ($categories as $key => $label): ?>
                        <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-2">Class (Optional)</label>
                <select name="class_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">General Document</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['class_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-2">File *</label>
                <input type="file" name="document" accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <p class="text-xs text-gray-500 mt-1">Allowed: PDF, DOC, DOCX, TXT, JPG, JPEG, PNG (Max 10MB)</p>
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="document.getElementById('uploadModal').classList.add('hidden')" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</button>
                <button type="submit" name="upload_document" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Upload</button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<?php
function buildPageLink($pageNumber)
{
    $params = $_GET;
    $params['page'] = $pageNumber;
    return 'documents.php?' . http_build_query($params);
}
?>