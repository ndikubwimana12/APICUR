<?php

/**
 * Student Application Form
 * Allows students to apply to the school with their details, level, and trade choice
 */

require_once '../config/config.php';

$error = '';
$success = '';

// Get available modules by level
$database = new Database();
$conn = $database->getConnection();

$levels = ['Level 3', 'Level 4', 'Level 5'];
$selected_level = $_GET['level'] ?? '';
$modules = [];

if ($selected_level) {
    try {
        // Try modules table first
        $module_query = "SELECT id, module_code, module_name, module_title, total_hours, tuition_fee 
                         FROM modules 
                         WHERE level = ? AND status = 'active'
                         ORDER BY module_name";
        $module_stmt = $conn->prepare($module_query);
        $module_stmt->execute([$selected_level]);
        $modules = $module_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Fallback to subjects table if modules not available
        try {
            $module_query = "SELECT id, subject_code as module_code, subject_name as module_name, 
                             subject_name as module_title, total_hours, tuition_fee 
                             FROM subjects 
                             WHERE level = ? AND status = 'active'
                             ORDER BY subject_name";
            $module_stmt = $conn->prepare($module_query);
            $module_stmt->execute([$selected_level]);
            $modules = $module_stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e2) {
            $modules = [];
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $first_name = trim($_POST['first_name'] ?? '');
        $middle_name = trim($_POST['middle_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $dob = $_POST['date_of_birth'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $parent_name = trim($_POST['parent_name'] ?? '');
        $parent_phone = trim($_POST['parent_phone'] ?? '');
        $parent_email = trim($_POST['parent_email'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $level = $_POST['level'] ?? '';
        $module_id = $_POST['module_id'] ?? null;
        $previous_school = trim($_POST['previous_school'] ?? '');

        // Validation
        if (!$first_name || !$last_name || !$dob || !$gender || !$parent_name || !$parent_phone || !$level || !$module_id) {
            throw new Exception('Please fill in all required fields');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) && $email !== '') {
            throw new Exception('Invalid email format');
        }

        // Handle file uploads
        $result_slip_path = null;
        $qualification_path = null;

        if (isset($_FILES['result_slip']) && $_FILES['result_slip']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/applications/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_name = 'result_' . time() . '_' . uniqid() . '.' . pathinfo($_FILES['result_slip']['name'], PATHINFO_EXTENSION);
            if (move_uploaded_file($_FILES['result_slip']['tmp_name'], $upload_dir . $file_name)) {
                $result_slip_path = 'uploads/applications/' . $file_name;
            }
        }

        if (isset($_FILES['qualification_doc']) && $_FILES['qualification_doc']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/applications/';
            $file_name = 'doc_' . time() . '_' . uniqid() . '.' . pathinfo($_FILES['qualification_doc']['name'], PATHINFO_EXTENSION);
            if (move_uploaded_file($_FILES['qualification_doc']['tmp_name'], $upload_dir . $file_name)) {
                $qualification_path = 'uploads/applications/' . $file_name;
            }
        }

        // Generate application number
        $app_number = 'APP-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        // Insert application
        $application_saved = false;
        try {
            $insert_query = "INSERT INTO applications (
                application_number, first_name, middle_name, last_name, date_of_birth, gender, 
                email, phone, parent_name, parent_phone, parent_email, address, 
                level_applied, trade_module_id, result_slip_path, previous_school, 
                qualification_document_path, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->execute([
                $app_number,
                $first_name,
                $middle_name,
                $last_name,
                $dob,
                $gender,
                $email,
                $phone,
                $parent_name,
                $parent_phone,
                $parent_email,
                $address,
                $level,
                $module_id,
                $result_slip_path,
                $previous_school,
                $qualification_path,
                'pending'
            ]);
            $application_saved = true;
            $success = "Application submitted successfully! Your application number is: <strong>$app_number</strong>";
        } catch (PDOException $e) {
            // Try to save to legacy applications system or backup
            if (strpos($e->getMessage(), 'applications') !== false || strpos($e->getMessage(), '1146') !== false) {
                // Applications table doesn't exist - save to file backup with structured data
                @mkdir('../uploads/applications', 0755, true);
                $backup_file = '../uploads/applications/app_' . $app_number . '.json';
                $backup_data = json_encode([
                    'application_number' => $app_number,
                    'first_name' => $first_name,
                    'middle_name' => $middle_name,
                    'last_name' => $last_name,
                    'date_of_birth' => $dob,
                    'gender' => $gender,
                    'email' => $email,
                    'phone' => $phone,
                    'parent_name' => $parent_name,
                    'parent_phone' => $parent_phone,
                    'parent_email' => $parent_email,
                    'address' => $address,
                    'level_applied' => $level,
                    'trade_module_id' => $module_id,
                    'result_slip_path' => $result_slip_path,
                    'previous_school' => $previous_school,
                    'qualification_document_path' => $qualification_path,
                    'status' => 'pending',
                    'submitted_at' => date('Y-m-d H:i:s')
                ], JSON_PRETTY_PRINT);

                if (file_put_contents($backup_file, $backup_data)) {
                    $success = "Application submitted successfully! Your application number is: <strong>$app_number</strong><br><small>System is processing your application.</small>";
                    $application_saved = true;
                } else {
                    $error = 'Failed to submit application. Please try again or contact the school office.';
                }
            } else {
                $error = 'Error submitting application: ' . $e->getMessage();
            }
        }
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Application - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .mobile-menu {
            transform: translateX(100%);
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .mobile-menu.open {
            transform: translateX(0);
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="date"],
        select,
        textarea {
            font-size: 16px !important;
        }

        @media (max-width: 640px) {
            h1 {
                font-size: 1.875rem !important;
            }

            h3 {
                font-size: 1.125rem !important;
            }

            .container {
                padding: 0.5rem !important;
            }

            .max-w-2xl {
                max-width: 100% !important;
                padding: 0.5rem !important;
            }
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Navigation Header -->
    <header
        class="sticky top-0 z-40 border-b bg-white/90 backdrop-blur supports-[backdrop-filter]:bg-white/60 dark:border-gray-800 dark:bg-gray-950/80">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <a href="./index.php" class="flex items-center gap-2 text-lg font-semibold">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded bg-indigo-600 text-white">A</span>
                APICUR TSS
            </a>
            <nav class="hidden gap-6 text-sm font-medium sm:flex">
                <a class="hover:text-indigo-600" href="./index.php">Home</a>
                <a class="hover:text-indigo-600" href="./about.php">About</a>
                <a class="hover:text-indigo-600" href="./programs.php">Programs</a>
                <a class="hover:text-indigo-600" href="./admissions.php">Admissions</a>
                <a class="hover:text-indigo-600" href="./contact.php">Contact</a>
            </nav>
            <button id="mobile-menu-button" class="md:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                    </path>
                </svg>
            </button>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu"
            class="mobile-menu fixed inset-x-0 top-full z-40 bg-white/95 backdrop-blur-md border-b border-gray-200/50 dark:bg-gray-950/95 dark:border-gray-800/50 md:hidden">
            <div class="mx-auto max-w-7xl px-4 py-6">
                <nav class="flex flex-col space-y-4">
                    <a class="hover:text-indigo-600 py-2" href="./index.php">Home</a>
                    <a class="hover:text-indigo-600 py-2" href="./about.php">About</a>
                    <a class="hover:text-indigo-600 py-2" href="./programs.php">Programs</a>
                    <a class="hover:text-indigo-600 py-2" href="./admissions.php">Admissions</a>
                    <a class="hover:text-indigo-600 py-2" href="./contact.php">Contact</a>
                </nav>
            </div>
        </div>
    </header>

    <div
        class="container mx-auto px-4 py-8 bg-gradient-to-br from-lime-50 to-green-50 dark:from-lime-950 dark:to-green-900 min-h-screen">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 sm:p-8">
                <div class="text-center mb-8">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-white mb-2">Apply to
                        <?php echo SITE_NAME; ?></h1>
                    <p class="text-sm sm:text-base text-gray-600 dark:text-gray-300">Begin your journey in vocational
                        training</p>
                </div>

                <?php if ($success): ?>
                    <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded">
                        <i class="fas fa-check-circle mr-2"></i><?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                        <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <!-- Personal Information -->
                    <div class="border-b pb-6">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-4">Personal Information</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">First Name
                                    *</label>
                                <input type="text" name="first_name" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-base"
                                    placeholder="First name">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Middle Name</label>
                                <input type="text" name="middle_name"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Middle name">
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                            <input type="text" name="last_name" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Last name">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth *</label>
                                <input type="date" name="date_of_birth" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Gender *</label>
                                <select name="gender" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input type="email" name="email"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="your@email.com">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                                <input type="tel" name="phone" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Phone number">
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                            <textarea name="address" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Your residential address"></textarea>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Previous
                                School/Institution</label>
                            <input type="text" name="previous_school"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Name of previous school">
                        </div>
                    </div>

                    <!-- Parent/Guardian Information -->
                    <div class="border-b pb-6">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-4">Parent/Guardian Information
                        </h3>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Parent/Guardian Name *</label>
                            <input type="text" name="parent_name" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Full name">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                                <input type="tel" name="parent_phone" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Phone number">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input type="email" name="parent_email"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Email address">
                            </div>
                        </div>
                    </div>

                    <!-- Program Selection -->
                    <div class="border-b pb-6">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-4">Program Selection</h3>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Training Level *</label>
                            <div class="flex gap-3 flex-wrap">
                                <?php foreach ($levels as $level): ?>
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" name="level" value="<?php echo $level; ?>" required
                                            onchange="location.href='?level=<?php echo urlencode($level); ?>';"
                                            <?php if ($selected_level === $level) echo 'checked'; ?> class="mr-2">
                                        <span class="text-gray-700"><?php echo $level; ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <?php if ($selected_level && count($modules) > 0): ?>
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Trade/Module *</label>
                                <select name="module_id" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select a Trade/Module</option>
                                    <?php foreach ($modules as $module): ?>
                                        <option value="<?php echo $module['id']; ?>">
                                            <?php echo htmlspecialchars($module['module_name'] . ' (' . $module['module_code'] . ')'); ?>
                                            - <?php echo $module['total_hours']; ?> hours
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php elseif ($selected_level): ?>
                            <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded text-yellow-700">
                                <i class="fas fa-info-circle mr-2"></i> No programs available for selected level
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Document Upload -->
                    <div class="border-b pb-6">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-4">Required Documents</h3>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Result Slip/Transcript</label>
                            <input type="file" name="result_slip" accept=".pdf,.jpg,.jpeg,.png"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-xs text-gray-500 mt-1">PDF, JPG, or PNG (Max 5MB)</p>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Qualification Document</label>
                            <input type="file" name="qualification_doc" accept=".pdf,.jpg,.jpeg,.png"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-xs text-gray-500 mt-1">PDF, JPG, or PNG (Max 5MB)</p>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex gap-4">
                        <button type="submit"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition">
                            <i class="fas fa-paper-plane mr-2"></i> Submit Application
                        </button>
                        <a href="index.php"
                            class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-3 rounded-lg transition text-center">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', () => {
                mobileMenu.classList.toggle('open');
            });

            // Close menu when clicking on a link
            mobileMenu.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    mobileMenu.classList.remove('open');
                });
            });
        }
    </script>
</body>

</html>