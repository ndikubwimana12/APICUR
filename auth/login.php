<?php
session_start();
require_once '../config/config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect to dashboard based on role
    $role = $_SESSION['role'];
    header('Location: ' . getDashboardByRole($role));
    exit;
}

$error = '';
$success = '';

/**
 * Return the dashboard path based on user role
 */
function getDashboardByRole($role)
{
    switch (strtolower($role)) {
        case 'admin':
            return '../admin/dashboard.php';
        case 'accountant':
            return '../accountant/dashboard.php';
        case 'headteacher':
            return '../headteacher/dashboard.php';
        case 'discipline':
            return '../discipline/dashboard.php';
        case 'dos':
            return '../dos/dashboard.php';
        case 'secretary':
            return '../secretary/dashboard.php';
        case 'teacher':
            return '../teacher/dashboard.php';
        default:
            return '../auth/login.php'; // fallback
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $error = 'Please enter both username and password';
    } else {
        #try {
        $database = new Database();
        $conn = $database->getConnection();

        // Fetch active user by username or email
        $stmt = $conn->prepare("SELECT * FROM users WHERE (username = :username OR email = :email) AND status='active' LIMIT 1");
        $stmt->execute([
            ':username' => $username,
            ':email' => $username
        ]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true); // prevent session fixation
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];

            // Update last login
            $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
            $updateStmt->execute([':id' => $user['id']]);

            // Optional: log activity
            if (function_exists('logActivity')) {
                logActivity($conn, $user['id'], 'login', 'user', $user['id'], 'User logged in');
            }

            // Redirect to role-based dashboard
            header('Location: ' . getDashboardByRole($user['role']));
            exit;
        } else {
            $error = 'Invalid username or password';
        }
        // } catch (Exception $e) {
        //     $error = 'An error occurred. Please try again.';
        //     error_log($e->getMessage());
        // }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        * {
            font-family: 'Inter', sans-serif;
        }

        .login-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
        }

        .input-focus:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
        }

        .shape {
            position: absolute;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-20px) rotate(10deg);
            }
        }
    </style>
</head>

<body class="min-h-screen login-gradient flex items-center justify-center p-4">

    <!-- Floating Shapes -->
    <div class="floating-shapes">
        <div class="shape" style="top:10%;left:10%;width:100px;height:100px;background:white;border-radius:50%;"></div>
        <div class="shape"
            style="top:60%;right:10%;width:150px;height:150px;background:white;border-radius:30% 70% 70% 30% / 30% 30% 70% 70%;">
        </div>
        <div class="shape" style="bottom:20%;left:20%;width:80px;height:80px;background:white;border-radius:50%;"></div>
    </div>

    <!-- Login Container -->
    <div class="w-full max-w-md relative z-10">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-2xl shadow-lg mb-4">
                <span class="text-3xl font-black text-indigo-600">A</span>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2"><?php echo SITE_NAME; ?></h1>
            <p class="text-indigo-100">School Management System</p>
        </div>

        <div class="glass-effect rounded-2xl p-8 shadow-2xl">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Welcome Back</h2>

            <?php if ($error): ?>
                <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i> <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-6">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-1"></i> Username or Email
                    </label>
                    <input type="text" id="username" name="username" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none input-focus transition"
                        placeholder="Enter your username or email"
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-1"></i> Password
                    </label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none input-focus transition pr-12"
                            placeholder="Enter your password">
                        <button type="button" onclick="togglePassword()"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember"
                            class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-600">Remember me</span>
                    </label>
                    <a href="forgot_password.php"
                        class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">Forgot password?</a>
                </div>

                <button type="submit" class="w-full btn-login text-white font-semibold py-3 rounded-lg shadow-lg">
                    <i class="fas fa-sign-in-alt mr-2"></i> Sign In
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">Don't have an account?
                    <a href="register.php" class="text-indigo-600 hover:text-indigo-700 font-semibold">Register here</a>
                </p>
            </div>

            <div class="mt-4 text-center">
                <a href="../public/index.html" class="text-sm text-gray-500 hover:text-gray-700">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Homepage
                </a>
            </div>
        </div>

        <div class="mt-8 text-center text-white text-sm">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All
            rights reserved.</div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>

</body>

</html>