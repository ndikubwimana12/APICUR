<!doctype html>
<html lang="en" class="h-full">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>APICUR TSS — About</title>
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

            .gap-12 {
                gap: 1.5rem !important;
            }

            .py-20 {
                padding-top: 1.5rem !important;
                padding-bottom: 1.5rem !important;
            }

            .text-lg {
                font-size: 0.95rem !important;
            }

            .space-y-4 {
                gap: 0.75rem !important;
            }
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
                <a class="nav-link active text-gray-700 hover:text-indigo-600 dark:text-gray-300"
                    href="./about.php">About</a>
                <a class="nav-link text-gray-700 hover:text-indigo-600 dark:text-gray-300"
                    href="./programs.php">Programs</a>
                <a class="nav-link text-gray-700 hover:text-indigo-600 dark:text-gray-300"
                    href="./admissions.php">Admissions</a>
                <a class="nav-link text-gray-700 hover:text-indigo-600 dark:text-gray-300"
                    href="./contact.php">Contact</a>
            </nav>

            <div class="flex items-center gap-4">
                <a href="../auth/login.php" class="text-sm font-medium text-gray-700 hover:text-indigo-600 dark:text-gray-300 hidden sm:block">Staff Login</a>
                <a href="../auth/register.php" class="btn-primary hidden sm:block">Register</a>
                <a href="./admissions.php" class="btn-primary hidden sm:block">Apply Now</a>
                <button id="mobile-menu-button" class="md:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="mobile-menu fixed inset-x-0 top-full z-40 bg-white/95 backdrop-blur-md border-b border-gray-200/50 dark:bg-gray-950/95 dark:border-gray-800/50 md:hidden">
            <div class="mx-auto max-w-7xl px-4 py-6">
                <nav class="flex flex-col space-y-4">
                    <a class="nav-link text-gray-700 hover:text-indigo-600 dark:text-gray-300 py-2" href="./index.php">Home</a>
                    <a class="nav-link text-gray-700 hover:text-indigo-600 dark:text-gray-300 py-2" href="./about.php">About</a>
                    <a class="nav-link text-gray-700 hover:text-indigo-600 dark:text-gray-300 py-2" href="./programs.php">Programs</a>
                    <a class="nav-link text-gray-700 hover:text-indigo-600 dark:text-gray-300 py-2" href="./admissions.php">Admissions</a>
                    <a class="nav-link text-gray-700 hover:text-indigo-600 dark:text-gray-300 py-2" href="./contact.php">Contact</a>
                    <hr class="my-2 border-gray-200 dark:border-gray-700" />
                    <a class="nav-link text-gray-700 hover:text-indigo-600 dark:text-gray-300 py-2" href="../auth/login.php">Staff Login</a>
                    <a class="nav-link text-gray-700 hover:text-indigo-600 dark:text-gray-300 py-2" href="../auth/register.php">Staff Register</a>
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
                    About Us • Excellence in Education
                </div>

                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-7xl font-black tracking-tight text-white">
                    About
                    <span class="block bg-gradient-to-r from-yellow-300 to-orange-300 bg-clip-text text-transparent">
                        APICUR TSS
                    </span>
                </h1>

                <p class="mt-4 sm:mt-8 text-sm sm:text-lg md:text-xl leading-relaxed text-white/90 max-w-3xl mx-auto px-2">
                    APICUR TSS in Musanze blends strong O'Level education with career-focused technical training. We
                    develop confident, skilled graduates ready for further study or immediate employment.
                </p>

                <div class="mt-8 sm:mt-12">
                    <a href="./programs.php" class="btn-primary text-sm sm:text-lg px-6 sm:px-8 py-3 sm:py-4 inline-flex items-center justify-center">
                        Explore Programs
                        <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-950 dark:to-emerald-900">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-12 lg:grid-cols-2">
                <div class="reveal">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Our Mission</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-300 mb-8">
                        To empower students with practical knowledge, creativity, and character for success in a
                        fast-changing world.
                    </p>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center mr-4 mt-1">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Academic Excellence</h3>
                                <p class="text-gray-600 dark:text-gray-300">Rigorous curriculum designed to challenge
                                    and inspire</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center mr-4 mt-1">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Hands-on Technical Skills</h3>
                                <p class="text-gray-600 dark:text-gray-300">Practical training in cutting-edge
                                    technologies</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center mr-4 mt-1">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Community Partnerships</h3>
                                <p class="text-gray-600 dark:text-gray-300">Strong connections with industry and local
                                    community</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="reveal">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Our Values</h2>
                    <div class="grid gap-6">
                        <div class="glass-card p-6">
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 bg-indigo-500 rounded-lg flex items-center justify-center mr-4">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z">
                                        </path>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Curiosity & Innovation
                                </h3>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300">We encourage questioning, exploration, and
                                creative problem-solving in all our students.</p>
                        </div>
                        <div class="glass-card p-6">
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center mr-4">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                        </path>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Integrity &
                                    Responsibility</h3>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300">Building character through ethical
                                decision-making and accountability.</p>
                        </div>
                        <div class="glass-card p-6">
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center mr-4">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                        </path>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Collaboration & Service
                                </h3>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300">Working together and giving back to our
                                community.</p>
                        </div>
                        <div class="glass-card p-6">
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center mr-4">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Excellence & Growth</h3>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300">Continuous improvement and personal development
                                for all.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-950 dark:to-pink-900">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white sm:text-4xl">Leadership & Facilities</h2>
                <p class="mt-4 text-xl text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                    Dedicated professionals and world-class resources supporting student success.
                </p>
            </div>

            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg text-center reveal">
                    <div class="w-16 h-16 bg-indigo-500 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Head Teacher</h3>
                    <p class="text-gray-600 dark:text-gray-300">Experienced leadership guiding academics and operations
                        towards student success.</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg text-center reveal">
                    <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Technical Leads</h3>
                    <p class="text-gray-600 dark:text-gray-300">Industry-informed curriculum and labs across all
                        technical programs.</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg text-center reveal">
                    <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Modern Labs</h3>
                    <p class="text-gray-600 dark:text-gray-300">Up-to-date computer labs, workshop spaces, and
                        collaborative environments.</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg text-center reveal">
                    <div class="w-16 h-16 bg-purple-500 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Student Life</h3>
                    <p class="text-gray-600 dark:text-gray-300">Clubs, competitions, field visits, and mentorships
                        building confidence.</p>
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
                        <li><a href="./programs.php#olevel" class="hover:text-white">O'Level Education</a></li>
                        <li><a href="./programs.php#software" class="hover:text-white">Software Development</a></li>
                        <li><a href="./programs.php#tourism" class="hover:text-white">Tourism</a></li>
                        <li><a href="./programs.php#construction" class="hover:text-white">Building Construction</a>
                        </li>
                        <li><a href="./programs.php#computers" class="hover:text-white">Computer Systems</a></li>
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