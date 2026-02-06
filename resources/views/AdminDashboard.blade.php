<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard | Sewage Sentinel Squad</title>

  <link rel="stylesheet" href="../../../style.css" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/feather-icons"></script>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

  <style>
    :root{
      --bg-ice: #eef6ff;
      --nav-blue: #0b67b3;
      --brand-blue: #0a5ea8;
      --brand-blue-2: #0b74c9;
      --accent-teal: #12b3a6;
      --text-dark: #0f172a;
      --text-muted: #475569;

      --card: #ffffff;
      --card-border: #e6eef7;
      --shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
    }

    body{
      background: var(--bg-ice);
      background-image:
        radial-gradient(900px 400px at 20% 0%, rgba(11, 116, 201, 0.12), transparent 60%),
        radial-gradient(900px 400px at 80% 10%, rgba(18, 179, 166, 0.10), transparent 60%),
        radial-gradient(1200px 700px at 50% 100%, rgba(11, 116, 201, 0.07), transparent 65%);
      background-attachment: fixed;
      color: var(--text-dark);
    }

    .dashboard-wrapper{
      min-height: 100vh;
      padding: 24px;
    }

    .card-soft{
      background: var(--card);
      border: 1px solid var(--card-border);
      border-radius: 16px;
      box-shadow: var(--shadow);
    }

    .title-blue{
      color: var(--brand-blue);
      font-weight: 800;
      letter-spacing: -0.02em;
    }

    .accent-underline{
      position: relative;
      display: inline-block;
    }
    .accent-underline::after{
      content:"";
      display:block;
      height: 4px;
      width: 90px;
      margin-top: 10px;
      border-radius: 999px;
      background: var(--accent-teal);
    }

    /* Navbar (Aqua Guard style) */
    .topbar{
      background: linear-gradient(135deg, var(--brand-blue), var(--brand-blue-2));
    }
  </style>
</head>

<body>
  <!-- ONE Navbar Only (Tailwind) -->
  <nav class="topbar text-white shadow-md">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
      <a href="{{ route('dashboard') }}" 
   class="flex items-center gap-3 font-bold text-xl hover:opacity-90 transition-opacity">
  <span class="text-2xl">≋</span>
  Aqua Guard
</a>

      <div class="hidden md:flex items-center gap-8 font-semibold text-sm">
        <a href="#report" class="hover:opacity-100 opacity-90">Report Issue</a>
        <a href="#status" class="hover:opacity-100 opacity-90">Check Status</a>
        <a href="#information" class="hover:opacity-100 opacity-90">Information</a>
        <a href="#contact" class="hover:opacity-100 opacity-90">Contact</a>
      </div>
    </div>
  </nav>

  <div class="flex dashboard-wrapper">
    <admin-sidebar></admin-sidebar>

    <main class="flex-1 p-8">
      <h1 class="text-3xl title-blue accent-underline mb-8">Admin Dashboard</h1>

      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="card-soft p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-slate-500">Total Reports</p>
              <h2 class="text-3xl font-bold">142</h2>
            </div>
            <i data-feather="file-text" class="w-8 h-8 text-blue-600"></i>
          </div>
        </div>

        <div class="card-soft p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-slate-500">New Reports</p>
              <h2 class="text-3xl font-bold">24</h2>
            </div>
            <i data-feather="alert-circle" class="w-8 h-8 text-amber-500"></i>
          </div>
        </div>

        <div class="card-soft p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-slate-500">In Progress</p>
              <h2 class="text-3xl font-bold">18</h2>
            </div>
            <i data-feather="clock" class="w-8 h-8 text-yellow-500"></i>
          </div>
        </div>

        <div class="card-soft p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-slate-500">Resolved</p>
              <h2 class="text-3xl font-bold">76</h2>
            </div>
            <i data-feather="check-circle" class="w-8 h-8 text-emerald-500"></i>
          </div>
        </div>
      </div>

      <!-- Reports Queue -->
      <div class="card-soft p-6 mb-8">
        <h2 class="text-xl font-semibold title-blue mb-4">New Reports Queue</h2>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Report ID</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Photo</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Location</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Category</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Actions</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 bg-white">
              <tr>
                <td class="px-6 py-4 text-slate-700 font-medium">#SSR-2023-142</td>
                <td class="px-6 py-4">
                  <img src="http://static.photos/320x240" alt="Report photo" class="w-16 h-16 rounded-md object-cover" />
                </td>
                <td class="px-6 py-4 text-slate-600">123 Main St</td>
                <td class="px-6 py-4 text-slate-600">Blockage</td>
                <td class="px-6 py-4 text-slate-600">Today, 10:42 AM</td>
                <td class="px-6 py-4">
                  <button class="text-emerald-600 hover:text-emerald-800 mr-3 font-semibold">Approve</button>
                  <button class="text-red-600 hover:text-red-800 font-semibold">Reject</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Map -->
      <div class="card-soft p-6">
        <h2 class="text-xl font-semibold title-blue mb-4">Incidents Map</h2>
        <div id="map" class="h-96 rounded-md"></div>
      </div>
    </main>
  </div>

  <script>
    feather.replace();

    const map = L.map('map').setView([51.505, -0.09], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    L.marker([51.5, -0.09]).addTo(map).bindPopup('Blockage - 123 Main St');
    L.marker([51.51, -0.1]).addTo(map).bindPopup('Overflow - 456 Oak Ave');
  </script>
</body>
</html>
