<?php

/**
 * Secretary - Register Student
 * Handles creation of new student records
 */
require_once '../config/config.php';
requireLogin();

if (!hasRole('secretary')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Register Student';

$database = new Database();
$conn = $database->getConnection();

$errors = [];
$successMessage = '';

// Preload active classes for dropdown
$classStmt = $conn->query('SELECT id, class_name FROM classes WHERE status = "active" ORDER BY class_name');
$classes = $classStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = sanitize($_POST['first_name'] ?? '');
    $middleName = sanitize($_POST['middle_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $dateOfBirth = $_POST['date_of_birth'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $classId = $_POST['class_id'] !== '' ? (int) $_POST['class_id'] : null;
    $admissionDate = $_POST['admission_date'] ?? '';
    $admissionNumber = sanitize($_POST['admission_number'] ?? '');
    $studentId = sanitize($_POST['student_id'] ?? '');
    $parentName = sanitize($_POST['parent_name'] ?? '');
    $parentPhone = sanitize($_POST['parent_phone'] ?? '');
    $parentEmail = sanitize($_POST['parent_email'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $medicalInfo = sanitize($_POST['medical_info'] ?? '');

    // Basic validation
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
    if ($studentId === '') {
        $errors['student_id'] = 'Student ID is required';
    }

    // Validate uniqueness of student ID and admission number
    if ($studentId !== '') {
        $stmt = $conn->prepare('SELECT COUNT(*) FROM students WHERE student_id = :student_id');
        $stmt->execute([':student_id' => $studentId]);
        if ((int) $stmt->fetchColumn() > 0) {
            $errors['student_id'] = 'Student ID is already in use';
        }
    }

    if ($admissionNumber !== '') {
        $stmt = $conn->prepare('SELECT COUNT(*) FROM students WHERE admission_number = :admission_number');
        $stmt->execute([':admission_number' => $admissionNumber]);
        if ((int) $stmt->fetchColumn() > 0) {
            $errors['admission_number'] = 'Admission number is already in use';
        }
    }

    // Handle file upload if provided
    $photoPath = null;
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
        } else {
            $errors['photo'] = 'Photo upload error. Code: ' . $photo['error'];
        }
    }

    if (empty($errors)) {
        try {
            $conn->beginTransaction();

            $insertQuery = 'INSERT INTO students (
                student_id, first_name, middle_name, last_name, date_of_birth, gender,
                class_id, admission_date, admission_number, parent_name, parent_phone,
                parent_email, address, medical_info, photo, status, created_by
            ) VALUES (
                :student_id, :first_name, :middle_name, :last_name, :date_of_birth, :gender,
                :class_id, :admission_date, :admission_number, :parent_name, :parent_phone,
                :parent_email, :address, :medical_info, :photo, :status, :created_by
            )';

            $stmt = $conn->prepare($insertQuery);
            $stmt->execute([
                ':student_id' => $studentId,
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
                ':status' => 'active',
                ':created_by' => $_SESSION['user_id']
            ]);

            $studentDbId = (int) $conn->lastInsertId();

            logActivity(
                $conn,
                $_SESSION['user_id'],
                'create',
                'student',
                $studentDbId,
                'Registered new student: ' . $studentId
            );

            $conn->commit();
            $successMessage = 'Student registered successfully!';

            // Reset form values
            $_POST = [];
            $photoPath = null;
        } catch (Exception $e) {
            $conn->rollBack();
            error_log('Student registration failed: ' . $e->getMessage());
            $errors['general'] = 'An unexpected error occurred. Please try again.';
        }
    }
}

// Sidebar menu
$sidebar_menu = '
<ul class="space-y-2">
    <li><a href="dashboard.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
    <li><a href="students.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700"><i class="fas fa-user-graduate"></i><span>Students</span></a></li>
    <li><a href="register_student.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg"><i class="fas fa-user-plus"></i><span>Register Student</span></a></li>
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
                <h1 class="text-2xl font-bold text-gray-800">Register New Student</h1>
                <p class="text-sm text-gray-500">Fill out the form below to add a new student to the system.</p>
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
                    <input type="text" name="student_id" value="<?php echo htmlspecialchars($_POST['student_id'] ?? ''); ?>" class="w-full border <?php echo isset($errors['student_id']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <?php if (isset($errors['student_id'])): ?>
                        <p class="text-xs text-red-500 mt-1"><?php echo $errors['student_id']; ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Admission Number <span class="text-red-500">*</span></label>
                    <input type="text" name="admission_number" value="<?php echo htmlspecialchars($_POST['admission_number'] ?? ''); ?>" class="w-full border <?php echo isset($errors['admission_number']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <?php if (isset($errors['admission_number'])): ?>
                        <p class="text-xs text-red-500 mt-1"><?php echo $errors['admission_number']; ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Class</label>
                    <select name="class_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['id']; ?>" <?php echo (($_POST['class_id'] ?? '') == $class['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($class['class_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">First Name <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" class="w-full border <?php echo isset($errors['first_name']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <?php if (isset($errors['first_name'])): ?>
                        <p class="text-xs text-red-500 mt-1"><?php echo $errors['first_name']; ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Middle Name</label>
                    <input type="text" name="middle_name" value="<?php echo htmlspecialchars($_POST['middle_name'] ?? ''); ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Last Name <span class="text-red-500">*</span></label>
                    <input type="text" name="last_name" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" class="w-full border <?php echo isset($errors['last_name']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <?php if (isset($errors['last_name'])): ?>
                        <p class="text-xs text-red-500 mt-1"><?php echo $errors['last_name']; ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth <span class="text-red-500">*</span></label>
                    <input type="date" name="date_of_birth" value="<?php echo htmlspecialchars($_POST['date_of_birth'] ?? ''); ?>" class="w-full border <?php echo isset($errors['date_of_birth']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <?php if (isset($errors['date_of_birth'])): ?>
                        <p class="text-xs text-red-500 mt-1"><?php echo $errors['date_of_birth']; ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Gender <span class="text-red-500">*</span></label>
                    <select name="gender" class="w-full border <?php echo isset($errors['gender']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Gender</option>
                        <option value="Male" <?php echo (($_POST['gender'] ?? '') === 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo (($_POST['gender'] ?? '') === 'Female') ? 'selected' : ''; ?>>Female</option>
                    </select>
                    <?php if (isset($errors['gender'])): ?>
                        <p class="text-xs text-red-500 mt-1"><?php echo $errors['gender']; ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Admission Date <span class="text-red-500">*</span></label>
                    <input type="date" name="admission_date" value="<?php echo htmlspecialchars($_POST['admission_date'] ?? ''); ?>" class="w-full border <?php echo isset($errors['admission_date']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <?php if (isset($errors['admission_date'])): ?>
                        <p class="text-xs text-red-500 mt-1"><?php echo $errors['admission_date']; ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Parent/Guardian Name</label>
                    <input type="text" name="parent_name" value="<?php echo htmlspecialchars($_POST['parent_name'] ?? ''); ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Parent/Guardian Phone</label>
                    <input type="text" name="parent_phone" value="<?php echo htmlspecialchars($_POST['parent_phone'] ?? ''); ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Parent/Guardian Email</label>
                    <input type="email" name="parent_email" value="<?php echo htmlspecialchars($_POST['parent_email'] ?? ''); ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Student Photo</label>
                    <input type="file" name="photo" accept="image/*" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <?php if (isset($errors['photo'])): ?>
                        <p class="text-xs text-red-500 mt-1"><?php echo $errors['photo']; ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                <textarea name="address" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Medical Information</label>
                <textarea name="medical_info" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"><?php echo htmlspecialchars($_POST['medical_info'] ?? ''); ?></textarea>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="students.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Register Student</button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Guidelines</h2>
        <ul class="list-disc list-inside text-sm text-gray-600 space-y-2">
            <li>Ensure the student ID and admission number are unique.</li>
            <li>Provide accurate parent/guardian contact information for communication.</li>
            <li>Date formats should follow the calendar input (YYYY-MM-DD).</li>
            <li>Student photos should be clear and in JPG/PNG format.</li>
        </ul>
    </div>
</div>

<?php include '../includes/footer.php'; ?>