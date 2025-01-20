<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Software Documentation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <header class="text-center mb-12">
        <h1 class="text-4xl font-bold mb-4">Software Documentation</h1>
        <p class="text-lg text-gray-600 dark:text-gray-400">A comprehensive guide to using our software product.</p>
    </header>

    <!-- Main Content -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
        <!-- Sidebar Navigation -->
        <aside class="md:col-span-3">
            <nav class="sticky top-8">
                <ul class="space-y-2">
                    <li>
                        <a href="#introduction" class="block px-4 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700">Introduction</a>
                    </li>
                    <li>
                        <a href="#getting-started" class="block px-4 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700">Getting Started</a>
                    </li>
                    <li>
                        <a href="#features" class="block px-4 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700">Features</a>
                    </li>
                    <li>
                        <a href="#faq" class="block px-4 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700">FAQ</a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Documentation Content -->
        <main class="md:col-span-9">
            <!-- Introduction Section -->
            <section id="introduction" class="mb-12">
                <h2 class="text-3xl font-semibold mb-4">Introduction</h2>
                <p class="text-gray-700 dark:text-gray-300 mb-4">Welcome to the documentation for our software product. This guide will help you understand how to use the software effectively.</p>
                <p class="text-gray-700 dark:text-gray-300">Our software is designed to simplify your workflow and improve productivity.</p>
            </section>

            <!-- Getting Started Section -->
            <section id="getting-started" class="mb-12">
                <h2 class="text-3xl font-semibold mb-4">Getting Started</h2>
                <p class="text-gray-700 dark:text-gray-300 mb-4">Follow these steps to get started with the software:</p>
                <ol class="list-decimal list-inside space-y-2">
                    <li class="text-gray-700 dark:text-gray-300">Download and install the software.</li>
                    <li class="text-gray-700 dark:text-gray-300">Create an account or log in.</li>
                    <li class="text-gray-700 dark:text-gray-300">Explore the dashboard and settings.</li>
                </ol>
            </section>

            <!-- Features Section -->
            <section id="features" class="mb-12">
                <h2 class="text-3xl font-semibold mb-4">Features</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Feature Card -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                        <h3 class="text-xl font-semibold mb-2">Feature 1</h3>
                        <p class="text-gray-700 dark:text-gray-300">Description of Feature 1 and how it benefits the user.</p>
                    </div>
                    <!-- Feature Card -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                        <h3 class="text-xl font-semibold mb-2">Feature 2</h3>
                        <p class="text-gray-700 dark:text-gray-300">Description of Feature 2 and how it benefits the user.</p>
                    </div>
                </div>
            </section>

            <!-- FAQ Section -->
            <section id="faq">
                <h2 class="text-3xl font-semibold mb-4">Frequently Asked Questions</h2>
                <div class="space-y-4">
                    <!-- FAQ Item -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                        <h3 class="text-xl font-semibold mb-2">How do I reset my password?</h3>
                        <p class="text-gray-700 dark:text-gray-300">You can reset your password by clicking the "Forgot Password" link on the login page.</p>
                    </div>
                    <!-- FAQ Item -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                        <h3 class="text-xl font-semibold mb-2">Is there a mobile app?</h3>
                        <p class="text-gray-700 dark:text-gray-300">Yes, our software is available on both iOS and Android.</p>
                    </div>
                </div>
            </section>
        </main>
    </div>
</div>
</body>
</html>
