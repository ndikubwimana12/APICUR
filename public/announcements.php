<!doctype html>
<html lang="en" class="h-full">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>APICUR TSS — Announcements</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="./styles.css" />
    <meta name="color-scheme" content="light dark" />
    <style>
        .mobile-menu {
            transform: translateX(100%);
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .mobile-menu.open {
            transform: translateX(0);
        }

        @media (max-width: 640px) {
            h1 {
                font-size: 1.5rem !important;
            }

            h2 {
                font-size: 1.125rem !important;
            }

            .announcement-card {
                gap: 1rem !important;
            }
        }
    </style>
</head>

<body class="min-h-screen bg-white text-gray-900 antialiased dark:bg-gray-950 dark:text-gray-100">
    <header
        class="sticky top-0 z-40 border-b bg-white/90 backdrop-blur supports-[backdrop-filter]:bg-white/60 dark:border-gray-800 dark:bg-gray-950/80">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <a href="./index.php" class="flex items-center gap-2 text-lg font-semibold">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded bg-indigo-600 text-white">A</span>
                <span class="hidden sm:inline">APICUR TSS — Musanze</span>
                <span class="sm:hidden">APICUR TSS</span>
            </a>
            <nav class="hidden gap-6 text-sm font-medium sm:flex">
                <a class="nav-link hover:text-indigo-600" href="./index.php">Home</a>
                <a class="nav-link hover:text-indigo-600" href="./about.php">About</a>
                <a class="nav-link active hover:text-indigo-600" href="./announcements.php">Announcements</a>
                <a class="nav-link hover:text-indigo-600" href="./contact.php">Contact</a>
            </nav>
            <button id="mobile-menu-button" class="md:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="mobile-menu fixed inset-x-0 top-full z-40 bg-white/95 backdrop-blur-md border-b border-gray-200/50 dark:bg-gray-950/95 dark:border-gray-800/50 md:hidden">
            <div class="mx-auto max-w-7xl px-4 py-6">
                <nav class="flex flex-col space-y-4">
                    <a class="hover:text-indigo-600 py-2" href="./index.php">Home</a>
                    <a class="hover:text-indigo-600 py-2" href="./about.php">About</a>
                    <a class="active text-indigo-600 py-2" href="./announcements.php">Announcements</a>
                    <a class="hover:text-indigo-600 py-2" href="./contact.php">Contact</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Header banner -->
    <section class="relative animated-gradient">
        <div class="pointer-events-none absolute inset-0 bg-grid"></div>
        <div class="mx-auto max-w-7xl px-4 py-8 sm:py-14 sm:px-6 lg:px-8">
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold tracking-tight">Announcements</h1>
            <p class="mt-3 max-w-3xl text-sm sm:text-base text-gray-700 dark:text-gray-200">News and updates from APICUR TSS in Musanze — events, deadlines, and opportunities.</p>
        </div>
    </section>

    <main class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-950 dark:to-purple-900 py-8 sm:py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Filters/Search (static) -->
            <div class="reveal mt-1 flex flex-col sm:flex-row flex-wrap items-center gap-2 sm:gap-3">
                <input type="search" placeholder="Search announcements" class="input w-full sm:max-w-xs text-sm sm:text-base" />
                <button class="button-secondary w-full sm:w-auto">Filter</button>
            </div>

            <div class="mt-8 space-y-4">
                <!-- Announcement card -->
                <article class="card announcement-card p-4 sm:p-6 transition reveal">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 sm:gap-4">
                        <div class="flex-1">
                            <h2 class="font-semibold text-base sm:text-lg">Admissions Open — Next Term</h2>
                            <p class="mt-1 text-xs sm:text-sm text-gray-600 dark:text-gray-300">Applications are open for O'Level and all technical programs (SD, Tourism, Building Construction, CSA). Visit the Contact page for guidance.</p>
                        </div>
                        <span class="badge blue text-xs sm:text-sm whitespace-nowrap">Admissions</span>
                    </div>
                    <p class="mt-3 text-xs text-gray-500">Posted on Sep 20, 2025</p>
                </article>

                <article class="card announcement-card p-4 sm:p-6 transition reveal">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 sm:gap-4">
                        <div class="flex-1">
                            <h2 class="font-semibold text-base sm:text-lg">Robotics & Innovation Lab</h2>
                            <p class="mt-1 text-xs sm:text-sm text-gray-600 dark:text-gray-300">Our new lab supports hands-on projects in Software Development and CSA. Clubs meet weekly after class.</p>
                        </div>
                        <span class="badge purple text-xs sm:text-sm whitespace-nowrap">Academics</span>
                    </div>
                    <p class="mt-3 text-xs text-gray-500">Posted on Sep 16, 2025</p>
                </article>

                <article class="card announcement-card p-4 sm:p-6 transition reveal">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 sm:gap-4">
                        <div class="flex-1">
                            <h2 class="font-semibold text-base sm:text-lg">Musanze Tourism Field Excursions</h2>
                            <p class="mt-1 text-xs sm:text-sm text-gray-600 dark:text-gray-300">Tourism students will participate in guided fieldwork across local attractions and hospitality venues.</p>
                        </div>
                        <span class="badge emerald text-xs sm:text-sm whitespace-nowrap">Community</span>
                    </div>
                    <p class="mt-3 text-xs text-gray-500">Posted on Sep 12, 2025</p>
                </article>
            </div>
        </div>
    </main>

    <footer class="border-t py-8 sm:py-10 dark:border-gray-900">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col items-center justify-between gap-4 sm:flex-row text-center sm:text-left">
                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">© <span id="year"></span> APICUR TSS, Musanze. All rights reserved.</p>
                <nav class="flex gap-3 sm:gap-4 text-xs sm:text-sm">
                    <a class="hover:text-indigo-600" href="./about.php">About</a>
                    <a class="hover:text-indigo-600" href="./announcements.php">Announcements</a>
                    <a class="hover:text-indigo-600" href="./contact.php">Contact</a>
                </nav>
            </div>
        </div>
    </footer>
    <script>
        document.getElementById('year').textContent = new Date().getFullYear();

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

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((e) => {
                if (e.isIntersecting) {
                    e.target.classList.add('is-visible');
                    observer.unobserve(e.target)
                }
            })
        }, {
            threshold: .15
        });
        document.querySelectorAll('.reveal').forEach((el) => observer.observe(el));
    </script>
</body>

</html>