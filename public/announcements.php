<!doctype html>
<html lang="en" class="h-full">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>APICUR TSS — Announcements</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="./styles.css" />
    <meta name="color-scheme" content="light dark" />
</head>

<body class="min-h-screen bg-white text-gray-900 antialiased dark:bg-gray-950 dark:text-gray-100">
    <header
        class="sticky top-0 z-40 border-b bg-white/90 backdrop-blur supports-[backdrop-filter]:bg-white/60 dark:border-gray-800 dark:bg-gray-950/80">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <a href="./index.php" class="flex items-center gap-2 text-lg font-semibold">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded bg-indigo-600 text-white">A</span>
                APICUR TSS — Musanze
            </a>
            <nav class="hidden gap-6 text-sm font-medium sm:flex">
                <a class="nav-link hover:text-indigo-600" href="./index.php">Home</a>
                <a class="nav-link hover:text-indigo-600" href="./about.php">About</a>
                <a class="nav-link active hover:text-indigo-600" href="./announcements.php">Announcements</a>
                <a class="nav-link hover:text-indigo-600" href="./contact.php">Contact</a>
            </nav>
        </div>
    </header>

    <!-- Header banner -->
    <section class="relative animated-gradient">
        <div class="pointer-events-none absolute inset-0 bg-grid"></div>
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Announcements</h1>
            <p class="mt-3 max-w-3xl text-gray-700 dark:text-gray-200">News and updates from APICUR TSS in Musanze —
                events, deadlines, and opportunities.</p>
        </div>
    </section>

    <main class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <!-- Filters/Search (static) -->
        <div class="reveal mt-1 flex flex-wrap items-center gap-3">
            <input type="search" placeholder="Search announcements" class="input max-w-xs" />
            <button class="button-secondary">Filter</button>
        </div>

        <div class="mt-8 space-y-4">
            <!-- Announcement card -->
            <article class="card p-6 transition reveal">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="font-semibold">Admissions Open — Next Term</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Applications are open for O'Level and
                            all technical programs (SD, Tourism, Building Construction, CSA). Visit the Contact page for
                            guidance.</p>
                    </div>
                    <span class="badge blue">Admissions</span>
                </div>
                <p class="mt-3 text-xs text-gray-500">Posted on Sep 20, 2025</p>
            </article>

            <article class="card p-6 transition reveal">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="font-semibold">Robotics & Innovation Lab</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Our new lab supports hands-on projects
                            in Software Development and CSA. Clubs meet weekly after class.</p>
                    </div>
                    <span class="badge purple">Academics</span>
                </div>
                <p class="mt-3 text-xs text-gray-500">Posted on Sep 16, 2025</p>
            </article>

            <article class="card p-6 transition reveal">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="font-semibold">Musanze Tourism Field Excursions</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Tourism students will participate in
                            guided fieldwork across local attractions and hospitality venues.</p>
                    </div>
                    <span class="badge emerald">Community</span>
                </div>
                <p class="mt-3 text-xs text-gray-500">Posted on Sep 12, 2025</p>
            </article>
        </div>
    </main>

    <footer class="border-t py-10 dark:border-gray-900">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                <p class="text-sm text-gray-600 dark:text-gray-400">© <span id="year"></span> APICUR TSS, Musanze. All
                    rights reserved.</p>
                <nav class="flex gap-4 text-sm">
                    <a class="hover:text-indigo-600" href="./about.php">About</a>
                    <a class="hover:text-indigo-600" href="./announcements.php">Announcements</a>
                    <a class="hover:text-indigo-600" href="./contact.php">Contact</a>
                </nav>
            </div>
        </div>
    </footer>
    <script>
    document.getElementById('year').textContent = new Date().getFullYear();
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