<!doctype html>
<html lang="en" class="h-full">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>APICUR TSS — Programs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="color-scheme" content="light dark" />
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

    * {
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    :root {
        --brand-50: #eef2ff;
        --brand-100: #e0e7ff;
        --brand-300: #a5b4fc;
        --brand-400: #818cf8;
        --brand-500: #6366f1;
        --brand-600: #4f46e5;
        --brand-700: #4338ca;
        --brand-800: #3730a3;
        --brand-900: #312e81;
        --surface: 255, 255, 255;
        --surface-dark: 3, 7, 18;
    }

    .hero-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        position: relative;
        overflow: hidden;
    }

    .hero-gradient::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.9) 0%, rgba(118, 75, 162, 0.9) 100%);
        animation: gradientShift 20s ease infinite;
    }

    @keyframes gradientShift {

        0%,
        100% {
            opacity: 0.9;
        }

        50% {
            opacity: 0.7;
        }
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 24px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .dark .glass-card {
        background: rgba(15, 23, 42, 0.9);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .glass-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 35px 70px -12px rgba(0, 0, 0, 0.15);
    }

    .program-card {
        background: white;
        border-radius: 20px;
        border: 1px solid #e5e7eb;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .program-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--brand-500), var(--brand-600));
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .program-card:hover::before {
        transform: scaleX(1);
    }

    .program-card:hover {
        transform: translateY(-12px);
        box-shadow: 0 25px 60px -12px rgba(79, 70, 229, 0.15);
        border-color: var(--brand-200);
    }

    .dark .program-card {
        background: #1e293b;
        border-color: #334155;
    }

    .floating-elements {
        position: absolute;
        width: 100%;
        height: 100%;
        pointer-events: none;
    }

    .floating-shape {
        position: absolute;
        opacity: 0.1;
        animation: float 6s ease-in-out infinite;
    }

    .floating-shape:nth-child(1) {
        top: 10%;
        left: 10%;
        animation-delay: 0s;
    }

    .floating-shape:nth-child(2) {
        top: 20%;
        right: 10%;
        animation-delay: 2s;
    }

    .floating-shape:nth-child(3) {
        bottom: 20%;
        left: 15%;
        animation-delay: 4s;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0px) rotate(0deg);
        }

        50% {
            transform: translateY(-20px) rotate(5deg);
        }
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--brand-600) 0%, var(--brand-700) 100%);
        border: none;
        border-radius: 50px;
        padding: 16px 32px;
        color: white;
        font-weight: 600;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 10px 30px -12px rgba(79, 70, 229, 0.4);
        position: relative;
        overflow: hidden;
    }

    .btn-primary::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }

    .btn-primary:hover::before {
        left: 100%;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 20px 40px -12px rgba(79, 70, 229, 0.6);
    }

    .nav-link {
        position: relative;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .nav-link::after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 50%;
        width: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--brand-500), var(--brand-600));
        border-radius: 2px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        transform: translateX(-50%);
    }

    .nav-link.active::after,
    .nav-link:hover::after {
        width: 100%;
    }

    .reveal {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .reveal.is-visible {
        opacity: 1;
        transform: translateY(0);
    }

    @media (prefers-reduced-motion: reduce) {
        * {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
    }

    .mobile-menu {
        transform: translateX(100%);
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .mobile-menu.open {
        transform: translateX(0);
    }

    /* Mobile Responsive Improvements */
    @media (max-width: 640px) {
        .hero-gradient {
            min-h-auto;
            padding-top: 100px;
            padding-bottom: 40px;
        }

        .hero-gradient h1 {
            font-size: 2rem !important;
            line-height: 1.2;
        }

        .floating-elements {
            display: none;
        }

        .btn-primary,
        .btn-secondary {
            width: 100%;
            padding: 12px 20px !important;
            font-size: 14px !important;
        }

        .grid {
            gap: 1rem !important;
        }

        h2 {
            font-size: 1.75rem !important;
        }

        h3 {
            font-size: 1.125rem !important;
        }

        .gap-8 {
            gap: 1rem !important;
        }

        .py-20 {
            padding-top: 1.5rem !important;
            padding-bottom: 1.5rem !important;
        }

        .text-lg {
            font-size: 0.95rem !important;
        }

        .program-card {
            padding: 1.25rem !important;
        }
    }

    .mobile-menu {
        transform: translateX(100%);
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .mobile-menu.open {
        transform: translateX(0);
    }

    @media (max-width: 768px) {
        .mobile-menu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease;
            position: fixed;
            top: 100%;
            width: 100%;
            z-index: 40;
        }

        .mobile-menu.open {
            max-height: 500px;
            overflow-y: auto;
            transform: none;
        }

        .nav-link::after {
            bottom: -4px;
            height: 2px;
        }
    }
    </style>
</head>

<body class="min-h-screen bg-gray-50 text-gray-900 antialiased dark:bg-gray-950 dark:text-gray-100">
    <header
        class="fixed top-0 w-full z-50 bg-white/90 backdrop-blur-md border-b border-gray-200/50 dark:bg-gray-950/90 dark:border-gray-800/50">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <a href="./index.php" class="flex items-center gap-3 text-xl font-bold">
                <div
                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-lg">
                    <span class="text-lg font-black">A</span>
                </div>
                <div class="hidden sm:block">
                    <div class="text-gray-900 dark:text-white">APICUR TSS</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">Excellence in Education</div>
                </div>
            </a>

            <nav class="hidden gap-8 text-sm font-medium md:flex">
                <a class="nav-link text-gray-700 hover:text-indigo-600 dark:text-gray-300" href="./index.php">Home</a>
                <a class="nav-link text-gray-700 hover:text-indigo-600 dark:text-gray-300" href="./about.php">About</a>
                <a class="nav-link active text-gray-700 hover:text-indigo-600 dark:text-gray-300"
                    href="./programs.php">Programs</a>
                <a class="nav-link text-gray-700 hover:text-indigo-600 dark:text-gray-300"
                    href="./admissions.php">Admissions</a>
                <a class="nav-link text-gray-700 hover:text-indigo-600 dark:text-gray-300"
                    href="./contact.php">Contact</a>
            </nav>

            <div class="flex items-center gap-4">
                <a href="../auth/login.php"
                    class="text-sm font-medium text-gray-700 hover:text-indigo-600 dark:text-gray-300 hidden sm:block">Staff
                    Login</a>
                <a href="../auth/register.php" class="btn-primary hidden sm:block">Register</a>
                <a href="./admissions.php" class="btn-primary hidden sm:block">Apply Now</a>
                <button id="mobile-menu-button"
                    class="md:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu"
            class="mobile-menu fixed inset-x-0 top-full z-40 bg-white/95 backdrop-blur-md border-b border-gray-200/50 dark:bg-gray-950/95 dark:border-gray-800/50 md:hidden">
            <div class="mx-auto max-w-7xl px-4 py-6">
                <nav class="flex flex-col space-y-4">
                    <a class="nav-link text-gray-700 hover:text-indigo-600 dark:text-gray-300 py-2"
                        href="./index.php">Home</a>
                    <a class="nav-link text-gray-700 hover:text-indigo-600 dark:text-gray-300 py-2"
                        href="./about.php">About</a>
                    <a class="nav-link text-gray-700 hover:text-indigo-600 dark:text-gray-300 py-2"
                        href="./programs.php">Programs</a>
                    <a class="nav-link text-gray-700 hover:text-indigo-600 dark:text-gray-300 py-2"
                        href="./admissions.php">Admissions</a>
                    <a class="nav-link text-gray-700 hover:text-indigo-600 dark:text-gray-300 py-2"
                        href="./contact.php">Contact</a>
                    <hr class="my-2 border-gray-200 dark:border-gray-700" />
                    <a class="nav-link text-gray-700 hover:text-indigo-600 dark:text-gray-300 py-2"
                        href="../auth/login.php">Staff Login</a>
                    <a class="nav-link text-gray-700 hover:text-indigo-600 dark:text-gray-300 py-2"
                        href="../auth/register.php">Staff Register</a>
                    <a href="./admissions.php" class="btn-primary text-center mt-4">Apply Now</a>
                </nav>
            </div>
        </div>
    </header>

    <section class="hero-gradient min-h-screen flex items-center pt-20">
        <div class="floating-elements">
            <div class="floating-shape w-20 h-20 bg-white rounded-full"></div>
            <div class="floating-shape w-16 h-16 bg-indigo-200 rounded-lg"></div>
            <div class="floating-shape w-24 h-24 bg-purple-200 rounded-full"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <div
                    class="inline-flex items-center rounded-full bg-white/20 backdrop-blur-sm px-4 py-2 text-sm font-medium text-white mb-8">
                    <span class="h-2 w-2 bg-green-400 rounded-full mr-2"></span>
                    Academic Excellence • Musanze, Rwanda
                </div>

                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-7xl font-black tracking-tight text-white">
                    Our
                    <span class="block bg-gradient-to-r from-yellow-300 to-orange-300 bg-clip-text text-transparent">
                        Programs
                    </span>
                </h1>

                <p
                    class="mt-4 sm:mt-8 text-sm sm:text-lg md:text-xl leading-relaxed text-white/90 max-w-3xl mx-auto px-2">
                    Discover a range of educational pathways designed to prepare you for success in academics,
                    technology, and beyond.
                </p>

                <div class="mt-8 sm:mt-12">
                    <a href="./admissions.php"
                        class="btn-primary text-sm sm:text-lg px-6 sm:px-8 py-3 sm:py-4 inline-flex items-center justify-center">
                        Apply Now
                        <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 bg-gradient-to-br from-cyan-50 to-blue-50 dark:from-cyan-950 dark:to-blue-900">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white sm:text-4xl">Academic Programs</h2>
                <p class="mt-4 text-xl text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                    Comprehensive education from O'Level to specialized technical training.
                </p>
            </div>

            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                <div class="program-card p-8 reveal">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-indigo-500 rounded-lg flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                </path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">O'Level Education</h3>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 mb-6">
                        Strong foundation in core subjects with a focus on critical thinking, creativity, and academic
                        excellence.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                        <li>• Comprehensive curriculum</li>
                        <li>• Experienced faculty</li>
                        <li>• Holistic development</li>
                    </ul>
                </div>

                <div class="program-card p-8 reveal">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Software Development</h3>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 mb-6">
                        Learn modern programming languages, web development, and software engineering principles.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                        <li>• Full-stack development</li>
                        <li>• Project-based learning</li>
                        <li>• Industry-relevant skills</li>
                    </ul>
                </div>

                <div class="program-card p-8 reveal">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064">
                                </path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Tourism</h3>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 mb-6">
                        Explore the tourism industry with training in hospitality, customer service, and cultural
                        tourism.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                        <li>• Hospitality management</li>
                        <li>• Cultural studies</li>
                        <li>• Practical experience</li>
                    </ul>
                </div>

                <div class="program-card p-8 reveal">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                </path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Building Construction</h3>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 mb-6">
                        Hands-on training in construction techniques, safety standards, and project management.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                        <li>• Construction skills</li>
                        <li>• Safety protocols</li>
                        <li>• Blueprint reading</li>
                    </ul>
                </div>

                <div class="program-card p-8 reveal">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z">
                                </path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Computer Systems</h3>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 mb-6">
                        Master computer hardware, networking, and system administration for IT careers.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                        <li>• Hardware maintenance</li>
                        <li>• Network configuration</li>
                        <li>• System security</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-gray-900 text-white py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-8 md:grid-cols-4">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white">
                            <span class="text-lg font-black">A</span>
                        </div>
                        <div>
                            <div class="font-bold">APICUR TSS</div>
                            <div class="text-sm text-gray-400">Excellence in Education</div>
                        </div>
                    </div>
                    <p class="text-gray-400 text-sm">
                        Shaping futures through quality education and technical training in Musanze, Rwanda.
                    </p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Programs</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="#olevel" class="hover:text-white">O'Level Education</a></li>
                        <li><a href="#software" class="hover:text-white">Software Development</a></li>
                        <li><a href="#tourism" class="hover:text-white">Tourism</a></li>
                        <li><a href="#construction" class="hover:text-white">Building Construction</a></li>
                        <li><a href="#computers" class="hover:text-white">Computer Systems</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="./about.php" class="hover:text-white">About Us</a></li>
                        <li><a href="./admissions.php" class="hover:text-white">Admissions</a></li>
                        <li><a href="./contact.php" class="hover:text-white">Contact</a></li>
                        <li><a href="./announcements.php" class="hover:text-white">Announcements</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Contact Info</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li>Musanze, Rwanda</li>
                        <li>Phone: (555) 123-4567</li>
                        <li>Email: info@apicur.tss.rw</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-sm text-gray-400">
                <p>&copy; 2024 APICUR TSS. All rights reserved.</p>
            </div>
        </div>
    </footer>

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

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((e) => {
            if (e.isIntersecting) {
                e.target.classList.add('is-visible');
                observer.unobserve(e.target);
            }
        });
    }, {
        threshold: 0.15
    });
    document.querySelectorAll('.reveal').forEach((el) => observer.observe(el));
    </script>
</body>

</html>