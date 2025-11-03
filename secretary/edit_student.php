<?php

/**
 * Secretary - Edit Student
 * Allows updating student information
 */
require_once '../config/config.php';
requireLogin();

if (!hasRole('secretary')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$studentIdParam = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($studentIdParam <= 0) {
    header('Location: students.php');
    exit();
}

$page_title = 'Edit Student';

$database = new Database();
$conn = $database->getConnection();

// Fetch student record
$studentStmt = $conn->prepare('SELECT * FROM students WHERE id = :id');
$studentStmt->execute([':id' => $studentIdParam]);
$student = $studentStmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    header('Location: students.php');
    exit();
}

// Active classes
$classStmt = $conn->query('SELECT id, class_name FROM classes WHERE status = "active" ORDER BY class_name');
$classes = $classStmt->fetchAll(PDO::FETCH_ASSOC);

$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = sanitize($_POST['first_name'] ?? '');
    $middleName = sanitize($_POST['middle_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $dateOfBirth = $_POST['date_of_birth'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $classId = $_POST['class_id'] !== '' ? (int) $_POST['class_id'] : null;
    $admissionDate = $_POST['admission_date'] ?? '';
    $admissionNumber = sanitize($_POST['admission_number'] ?? '');
    $studentUniqueId = sanitize($_POST['student_id'] ?? '');
    $parentName = sanitize($_POST['parent_name'] ?? '');
    $parentPhone = sanitize($_POST['parent_phone'] ?? '');
    $parentEmail = sanitize($_POST['parent_email'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $medicalInfo = sanitize($_POST['medical_info'] ?? '');
    $status = $_POST['status'] ?? 'active';

    if ($firstName === '') {
        $errors['first_name'] = 'First name is required';
    }
    if ($lastName === '') {
        $errors['last_name'] = 'Last name is required';
    }
    if ($dateOfBirth === '') {
        $errors['date_of_birth'] = 'Date of birth is required';
    }
    if (!in_array($gender, ['Male', 'Female'], true)) {
        $errors['gender'] = 'Please select a valid gender';
    }
    if ($admissionDate === '') {
        $errors['admission_date'] = 'Admission date is required';
    }
    if ($admissionNumber === '') {
        $errors['admission_number'] = 'Admission number is required';
    }
    if ($studentUniqueId === '') {
        $errors['student_id'] = 'Student ID is required';
    }

    // Uniqueness checks ignoring current student
    if ($studentUniqueId !== '') {
        $stmt = $conn->prepare('SELECT COUNT(*) FROM students WHERE student_id = :student_id AND id != :id');
        $stmt->execute([':student_id' => $studentUniqueId, ':id' => $studentIdParam]);
        if ((int) $stmt->fetchColumn() > 0) {
            $errors['student_id'] = 'Student ID is already in use';
        }
    }

    if ($admissionNumber !== '') {
        $stmt = $conn->prepare('SELECT COUNT(*) FROM students WHERE admission_number = :admission_number AND id != :id');
        $stmt->execute([':admission_number' => $admissionNumber, ':id' => $studentIdParam]);
        if ((int) $stmt->fetchColumn() > 0) {
            $errors['admission_number'] = 'Admission number is already in use';
        }
    }

    $statusOptions = ['active', 'graduated', 'transferred', 'dropped'];
    if (!in_array($status, $statusOptions, true)) {
        $status = 'active';
    }

    $photoPath = $student['photo'];

    if (!empty($_FILES['photo']['name'])) {
        $photo = $_FILES['photo'];
        if ($photo['error'] === UPLOAD_ERR_OK) {
            if (!in_array($photo['type'], ALLOWED_IMAGE_TYPES, true)) {
                $errors['photo'] = 'Invalid image type. Allowed: JPG, PNG, GIF';
            } elseif ($photo['size'] > MAX_FILE_SIZE) {
                $errors['photo'] = 'Photo exceeds 5MB limit';
            } else {
                $extension = pathinfo($photo['name'], PATHINFO_EXTENSION);
                $filename = 'student_' . time() . '_' . generateRandomString(8) . '.' . $extension;
                $destinationDir = UPLOAD_DIR . 'students/';
                if (!is_dir($destinationDir)) {
                    mkdir($destinationDir, 0775, true);
                }
                $destination = $destinationDir . $filename;
                if (move_uploaded_file($photo['tmp_name'], $destination)) {
                    $photoPath = 'uploads/students/' . $filename;
                } else {
                    $errors['photo'] = 'Failed to upload photo. Please try again.';
                }
            }
        } elseif ($photo['error'] !== UPLOAD_ERR_NO_FILE) {
            $errors['photo'] = 'Photo upload error. Code: ' . $photo['error'];
        }
    }

    if (empty($errors)) {
        try {
            $updateQuery = 'UPDATE students SET
                student_id = :student_id,
                first_name = :first_name,
                middle_name = :middle_name,
                last_name = :last_name,
                date_of_birth = :date_of_birth,
                gender = :gender,
                class_id = :class_id,
                admission_date = :admission_date,
                admission_number = :admission_number,
                parent_name = :parent_name,
                parent_phone = :parent_phone,
                parent_email = :parent_email,
                address = :address,
                medical_info = :medical_info,
                photo = :photo,
                status = :status
            WHERE id = :id';

            $stmt = $conn->prepare($updateQuery);
            $stmt->execute([
                ':student_id' => $studentUniqueId,
                ':first_name' => $firstName,
                ':middle_name' => $middleName,
                ':last_name' => $lastName,
                ':date_of_birth' => $dateOfBirth,
                ':gender' => $gender,
                ':class_id' => $classId,
                ':admission_date' => $admissionDate,
                ':admission_number' => $admissionNumber,
                ':parent_name' => $parentName,
                ':parent_phone' => $parentPhone,
                ':parent_email' => $parentEmail,
                ':address' => $address,
                ':medical_info' => $medicalInfo,
                ':photo' => $photoPath,
                ':status' => $status,
                ':id' => $studentIdParam
            ]);

            logActivity(
                $conn,
                $_SESSION['user_id'],
                'update',
                'student',
                $studentIdParam,
                'Updated student record: ' . $studentUniqueId
            );

            $successMessage = 'Student record updated successfully!';

            $student = array_merge($student, [
                'student_id' => $studentUniqueId,
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'last_name' => $lastName,
                'date_of_birth' => $dateOfBirth,
                'gender' => $gender,
                'class_id' => $classId,
                'admission_date' => $admissionDate,
                'admission_number' => $admissionNumber,
                'parent_name' => $parentName,
                'parent_phone' => $parentPhone,
                'parent_email' => $parentEmail,
                'address' => $address,
                'medical_info' => $medicalInfo,
                'photo' => $photoPath,
                'status' => $status
            ]);
        } catch (Exception $e) {
            error_log('Student update failed: ' . $e->getMessage());
            $errors['general'] = 'An unexpected error occurred. Please try again.';
        }
    }
}

$sidebar_menu = '
<ul class="space-y-2">
    <li><a href="dashboard.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
    <li><a href="students.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-user-graduate"></i><span>Students</span></a></li>
    <li><a href="register_student.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-user-plus"></i><span>Register Student</span></a></li>
    <li><a href="documents.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-file-alt"></i><span>Documents</span></a></li>
    <li><a href="meetings.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-calendar-alt"></i><span>Meetings</span></a></li>
    <li><a href="reports.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-chart-bar"></i><span>Reports</span></a></li>
</ul>';

include '../includes/header.php';
?>

<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Edit Student</h1>
                <p class="text-sm text-gray-500">Update the student's information below.</p>
            </div>
            <a href="students.php" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                <i class="fas fa-arrow-left mr-1"></i> Back to Students
            </a>
        </div>

        <?php if (!empty($successMessage)): ?>
            <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors['general'])): ?>
            <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo $errors['general']; ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Student ID <span class="text-red-500">*</span></label>
                    <input type="text" name="student_id" value="<?php echo htmlspecialchars($student['student_id']); ?>" class="w-full border <?php echo isset($errors['student_id']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <?php if (isset($errors['student_id'])): ?>
                        <p class="text-xs text-red-500 mt-1"><?php echo $errors['student_id']; ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Admission Number <span class="text-red-500">*</span></label>
                    <input type="text" name="admission_number" value="<?php echo htmlspecialchars($student['admission_number']); ?>" class="w-full border <?php echo isset($errors['admission_number']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <?php if (isset($errors['admission_number'])): ?>
                        <p class="text-xs text-red-500 mt-1"><?php echo $errors['admission_number']; ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Class</label>
                    <select name="class_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['id']; ?>" <?php echo ($student['class_id'] == $class['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($class['class_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">First Name <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" value="<?php echo htmlspecialchars($student['first_name']); ?>" class="w-full border <?php echo isset($errors['first_name']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <?php if (isset($errors['first_name'])): ?>
                        <p class="text-xs text-red-500 mt-1"><?php echo $errors['first_name']; ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Middle Name</label>
                    <input type="text" name="middle_name" value="<?php echo htmlspecialchars($student['middle_name']); ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Last Name <span class="text-red-500">*</span></label>
                    <input type="text" name="last_name" value="<?php echo htmlspecialchars($student['last_name']); ?>" class="w-full border <?php echo isset($errors['last_name']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <?php if (isset($errors['last_name'])): ?>
                        <p class="text-xs text-red-500 mt-1"><?php echo $errors['last_name']; ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth <span class="text-red-500">*</span></label>
                    <input type="date" name="date_of_birth" value="<?php echo htmlspecialchars($student['date_of_birth']); ?>" class="w-full border <?php echo isset($errors['date_of_birth']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <?php if (isset($errors['date_of_birth'])): ?>
                        <p class="text-xs text-red-500 mt-1"><?php echo $errors['date_of_birth']; ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Gender <span class="text-red-500">*</span></label>
                    <select name="gender" class="w-full border <?php echo isset($errors['gender']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Gender</option>
                        <option value="Male" <?php echo ($student['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($student['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                    </select>
                    <?php if (isset($errors['gender'])): ?>
                        <p class="text-xs text-red-500 mt-1"><?php echo $errors['gender']; ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Admission Date <span class="text-red-500">*</span></label>
                    <input type="date" name="admission_date" value="<?php echo htmlspecialchars($student['admission_date']); ?>" class="w-full border <?php echo isset($errors['admission_date']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <?php if (isset($errors['admission_date'])): ?>
                        <p class="text-xs text-red-500 mt-1"><?php echo $errors['admission_date']; ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Parent/Guardian Name</label>
                    <input type="text" name="parent_name" value="<?php echo htmlspecialchars($student['parent_name']); ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Parent/Guardian Phone</label>
                    <input type="text" name="parent_phone" value="<?php echo htmlspecialchars($student['parent_phone']); ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Parent/Guardian Email</label>
                    <input type="email" name="parent_email" value="<?php echo htmlspecialchars($student['parent_email']); ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Student Photo</label>
                    <input type="file" name="photo" accept="image/*" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <?php if (isset($student['photo']) && $student['photo']): ?>
                        <p class="text-xs text-gray-500 mt-1">Current photo: <a href="<?php echo BASE_URL . htmlspecialchars($student['photo']); ?>" target="_blank" class="text-indigo-600 hover:text-indigo-700">View</a></p>
                    <?php endif; ?>
                    <?php if (isset($errors['photo'])): ?>
                        <p class="text-xs text-red-500 mt-1"><?php echo $errors['photo']; ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                <textarea name="address" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"><?php echo htmlspecialchars($student['address']); ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Medical Information</label>
                <textarea name="medical_info" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"><?php echo htmlspecialchars($student['medical_info']); ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="active" <?php echo ($student['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                    <option value="graduated" <?php echo ($student['status'] === 'graduated') ? 'selected' : ''; ?>>Graduated</option>
                    <option value="transferred" <?php echo ($student['status'] === 'transferred') ? 'selected' : ''; ?>>Transferred</option>
                    <option value="dropped" <?php echo ($student['status'] === 'dropped') ? 'selected' : ''; ?>>Dropped</option>
                </select>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="students.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>