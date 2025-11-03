<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Dashboard'; ?> - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        * {
            font-family: 'Inter', sans-serif;
        }

        .sidebar-link {
            transition: all 0.3s ease;
        }

        .sidebar-link:hover,
        .sidebar-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .dashboard-card {
            transition: all 0.3s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-lg overflow-y-auto hidden md:block">
            <div class="p-6 border-b">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-lg">
                        <span class="text-lg font-black">A</span>
                    </div>
                    <div>
                        <h1 class="font-bold text-lg">APICUR TSS</h1>
                        <p class="text-xs text-gray-500"><?php echo strtoupper(str_replace('_', ' ', $_SESSION['role'])); ?></p>
                    </div>
                </div>
            </div>

            <nav class="p-4">
                <?php echo $sidebar_menu ?? ''; ?>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            <header class="bg-white shadow-sm z-10">
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center gap-4">
                        <button class="md:hidden text-gray-500 hover:text-gray-700">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <h2 class="text-xl font-semibold text-gray-800"><?php echo $page_title ?? 'Dashboard'; ?></h2>
                    </div>

                    <div class="flex items-center gap-4">
                        <!-- Notifications -->
                        <button class="relative text-gray-500 hover:text-gray-700">
                            <i class="fas fa-bell text-xl"></i>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">3</span>
                        </button>

                        <!-- User Menu -->
                        <div class="relative">
                            <button class="flex items-center gap-3 hover:bg-gray-50 rounded-lg px-3 py-2" onclick="toggleUserMenu()">
                                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                                    <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
                                </div>
                                <div class="text-left hidden sm:block">
                                    <p class="text-sm font-semibold text-gray-700"><?php echo $_SESSION['full_name']; ?></p>
                                    <p class="text-xs text-gray-500"><?php echo $_SESSION['role']; ?></p>
                                </div>
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </button>

                            <!-- Dropdown -->
                            <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50">
                                <a href="<?php echo BASE_URL; ?>profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-user mr-2"></i> Profile
                                </a>
                                <a href="<?php echo BASE_URL; ?>settings.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-cog mr-2"></i> Settings
                                </a>
                                <hr class="my-2">
                                <a href="<?php echo BASE_URL; ?>auth/logout.php" class="block px-4 py-2 text-red-600 hover:bg-red-50">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">