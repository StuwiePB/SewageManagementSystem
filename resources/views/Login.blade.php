<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sewage Sentinel Squad 🚽</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body class="bg-gray-100">
    <nav-bar></nav-bar>
    
    <main class="container mx-auto px-4 py-8">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-800 mb-4">Sewage Sentinel Squad 🚽</h1>
            <p class="text-xl text-gray-600">Report, track, and resolve sewage issues in your community</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center mb-4">
                    <i data-feather="user" class="text-blue-500 mr-3"></i>
                    <h2 class="text-2xl font-semibold">Civilian</h2>
                </div>
                <p class="text-gray-600 mb-6">Report sewage issues in your area without needing to login</p>
                <a href="/report.html" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md inline-block transition-colors">Report Issue</a>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center mb-4">
                    <i data-feather="shield" class="text-green-500 mr-3"></i>
                    <h2 class="text-2xl font-semibold">Admin</h2>
                </div>
                <p class="text-gray-600 mb-6">Manage and assign reported sewage issues to operations team</p>
                <a href="/admin/login.html" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md inline-block transition-colors">Admin Login</a>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center mb-4">
                    <i data-feather="tool" class="text-orange-500 mr-3"></i>
                    <h2 class="text-2xl font-semibold">Operations</h2>
                </div>
                <p class="text-gray-600 mb-6">View assigned cases and update status of repairs</p>
                <a href="/operations/login.html" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-md inline-block transition-colors">Operations Login</a>
            </div>
        </div>
    </main>

    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; 2023 Sewage Sentinel Squad. All rights reserved.</p>
            <p class="mt-2 text-gray-400">Keeping our communities clean and safe</p>
        </div>
    </footer>

    <script src="components/navbar.js"></script>
    <script>feather.replace();</script>
<script src="https://huggingface.co/deepsite/deepsite-badge.js"></script>
</body>
</html>