<!doctype html>
<html lang="en" class="h-full">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>APICUR TSS — Admissions</title>
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

            input[type="text"],
            input[type="email"],
            textarea,
            select {
                font-size: 16px !important;
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
                <a class="nav-link text-gray-700 hover:text-indigo-600 dark:text-gray-300"
                    href="./programs.php">Programs</a>
                <a class="nav-link active text-gray-700 hover:text-indigo-600 dark:text-gray-300"
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
                    Join Our Community • Musanze, Rwanda
                </div>

                <h1 class="text-5xl font-black tracking-tight text-white sm:text-6xl lg:text-7xl">
                    Start Your
                    <span class="block bg-gradient-to-r from-yellow-300 to-orange-300 bg-clip-text text-transparent">
                        Journey
                    </span>
                    Here
                </h1>

                <p class="mt-8 text-xl leading-relaxed text-white/90 max-w-3xl mx-auto">
                    Begin your path to academic excellence and career success. Our admissions process is designed to
                    welcome passionate learners.
                </p>

                <div class="mt-12">
                    <a href="#application" class="btn-primary text-lg px-8 py-4">
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

    <section class="py-20 bg-gradient-to-br from-rose-50 to-red-50 dark:from-rose-950 dark:to-red-900">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white sm:text-4xl">Admissions Process</h2>
                <p class="mt-4 text-xl text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                    Simple steps to join our community of learners and innovators.
                </p>
            </div>

            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4 mb-16">
                <div class="text-center reveal">
                    <div
                        class="w-16 h-16 bg-indigo-500 rounded-full flex items-center justify-center mx-auto mb-4 text-white text-2xl font-bold">
                        1</div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Submit Application</h3>
                    <p class="text-gray-600 dark:text-gray-300">Complete our online application form with required
                        documents.</p>
                </div>
                <div class="text-center reveal">
                    <div
                        class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-4 text-white text-2xl font-bold">
                        2</div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Review Process</h3>
                    <p class="text-gray-600 dark:text-gray-300">Our admissions team reviews your application and
                        qualifications.</p>
                </div>
                <div class="text-center reveal">
                    <div
                        class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-4 text-white text-2xl font-bold">
                        3</div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Interview</h3>
                    <p class="text-gray-600 dark:text-gray-300">Schedule an interview to discuss your goals and fit with
                        our programs.</p>
                </div>
                <div class="text-center reveal">
                    <div
                        class="w-16 h-16 bg-purple-500 rounded-full flex items-center justify-center mx-auto mb-4 text-white text-2xl font-bold">
                        4</div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Welcome Aboard</h3>
                    <p class="text-gray-600 dark:text-gray-300">Receive your acceptance and prepare to start your
                        educational journey.</p>
                </div>
            </div>

            <div class="grid gap-12 lg:grid-cols-2">
                <div class="reveal">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Requirements</h3>
                    <div class="space-y-6">
                        <div class="glass-card p-6">
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Academic Requirements</h4>
                            <ul class="space-y-2 text-gray-600 dark:text-gray-300">
                                <li>• Previous academic transcripts</li>
                                <li>• Minimum grade requirements vary by program</li>
                                <li>• English proficiency for international students</li>
                            </ul>
                        </div>
                        <div class="glass-card p-6">
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Documents Needed</h4>
                            <ul class="space-y-2 text-gray-600 dark:text-gray-300">
                                <li>• Birth certificate</li>
                                <li>• Passport-sized photos</li>
                                <li>• Medical certificate</li>
                                <li>• Recommendation letters (optional)</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="reveal" id="application">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Application Form</h3>
                    <form class="space-y-6"
                        onsubmit="event.preventDefault(); alert('Application submitted successfully! We will contact you soon.'); this.reset();">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label for="firstName"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">First
                                    Name</label>
                                <input type="text" id="firstName" name="firstName" required
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent dark:bg-gray-800 dark:text-white">
                            </div>
                            <div>
                                <label for="lastName"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Last
                                    Name</label>
                                <input type="text" id="lastName" name="lastName" required
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent dark:bg-gray-800 dark:text-white">
                            </div>
                        </div>
                        <div>
                            <label for="email"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
                            <input type="email" id="email" name="email" required
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent dark:bg-gray-800 dark:text-white">
                        </div>
                        <div>
                            <label for="phone"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone</label>
                            <input type="tel" id="phone" name="phone" required
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent dark:bg-gray-800 dark:text-white">
                        </div>
                        <div>
                            <label for="program"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Preferred
                                Program</label>
                            <select id="program" name="program" required
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent dark:bg-gray-800 dark:text-white">
                                <option value="">Select a program</option>
                                <option value="olevel">O'Level Education</option>
                                <option value="software">Software Development</option>
                                <option value="tourism">Tourism</option>
                                <option value="construction">Building Construction</option>
                                <option value="computers">Computer Systems</option>
                            </select>
                        </div>
                        <div>
                            <label for="message"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Why do you want
                                to join APICUR TSS?</label>
                            <textarea id="message" name="message" rows="4"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent dark:bg-gray-800 dark:text-white"></textarea>
                        </div>
                        <button type="submit" class="btn-primary w-full">Submit Application</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 bg-gradient-to-br from-teal-50 to-cyan-50 dark:from-teal-950 dark:to-cyan-900">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">Frequently Asked Questions</h2>
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg reveal">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-2">When are applications due?</h3>
                        <p class="text-gray-600 dark:text-gray-300">We accept applications year-round, but early
                            submission is recommended for priority consideration.</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg reveal">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Are there scholarships available?
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300">Yes, we offer merit-based scholarships and financial
                            aid options. Contact our admissions office for details.</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg reveal">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-2">What is the class size?</h3>
                        <p class="text-gray-600 dark:text-gray-300">Our classes are small, typically 20-25 students,
                            ensuring personalized attention and support.</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg reveal">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Do you offer dormitories?</h3>
                        <p class="text-gray-600 dark:text-gray-300">Yes, we provide safe and comfortable dormitory
                            facilities for students from outside Musanze.</p>
                    </div>
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