<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Report Details | Aqua Guard (Admin)</title>

  <link rel="stylesheet" href="../../../style.css" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/feather-icons"></script>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

  <style>
    :root{
      --bg-ice: #eef6ff;
      --brand-blue: #0a5ea8;
      --brand-blue-2: #0b74c9;
      --accent-teal: #12b3a6;
      --text-dark: #0f172a;
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

    .dashboard-wrapper{ min-height: 100vh; padding: 24px; }

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

    .badge{
      display:inline-flex;
      align-items:center;
      gap:.4rem;
      padding:.25rem .7rem;
      border-radius:999px;
      font-weight:700;
      font-size:.8rem;
      border:1px solid rgba(2,6,23,.08);
      background: rgba(255,255,255,.8);
    }
    .badge-received{ color:#0b74c9; background: rgba(11,116,201,.08); border-color: rgba(11,116,201,.18); }
    .badge-progress{ color:#b45309; background: rgba(245,158,11,.12); border-color: rgba(245,158,11,.22); }
    .badge-resolved{ color:#047857; background: rgba(16,185,129,.12); border-color: rgba(16,185,129,.22); }
    .badge-rejected{ color:#b91c1c; background: rgba(239,68,68,.10); border-color: rgba(239,68,68,.22); }

    /* Leaflet round corners */
    #detailMap { border-radius: 12px; overflow: hidden; }
  </style>
</head>

<body>
  <!-- Optional: use your existing topbar component if you want -->
  <!-- <nav-bar></nav-bar> -->

  <div class="flex dashboard-wrapper">
    <admin-sidebar></admin-sidebar>

    <main class="flex-1 p-8">
      <!-- Header Row -->
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">
        <div>
          <h1 class="text-3xl title-blue accent-underline">Report Details</h1>
          <p class="text-slate-600 mt-3">Review the submitted incident, verify details, and update its status.</p>
        </div>

        <div class="flex items-center gap-3">
          <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 rounded-lg bg-white border border-slate-200 shadow-sm text-slate-700 font-semibold hover:bg-slate-50">
            ← Back
          </a>

          <!-- Status badge (change class to match status) -->
          <span class="badge badge-progress">
            <i data-feather="clock" class="w-4 h-4"></i>
            In Progress
          </span>
        </div>
      </div>

      <!-- Top grid: Report info + Actions -->
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
        <!-- Report Summary -->
        <section class="card-soft p-6 xl:col-span-2">
          <div class="flex items-start justify-between gap-4">
            <div>
              <h2 class="text-xl font-semibold title-blue">#SSR-2023-142</h2>
              <p class="text-slate-600 mt-1">Category: <span class="font-semibold text-slate-800">Blockage</span></p>
            </div>

            <div class="text-right">
              <p class="text-sm text-slate-500">Submitted</p>
              <p class="font-semibold text-slate-800">Today, 10:42 AM</p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-6">
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
              <p class="text-sm text-slate-500 mb-1">Reporter</p>
              <p class="font-semibold text-slate-800">John Doe</p>
              <p class="text-slate-600 text-sm">john.doe@email.com</p>
              <p class="text-slate-600 text-sm">+673 812 3456</p>
            </div>

            <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
              <p class="text-sm text-slate-500 mb-1">Location</p>
              <p class="font-semibold text-slate-800">123 Main St</p>
              <p class="text-slate-600 text-sm">Lat: 51.5050, Lng: -0.0900</p>
              <p class="text-slate-600 text-sm">Zone: Central District</p>
            </div>

            <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 md:col-span-2">
              <p class="text-sm text-slate-500 mb-1">Description</p>
              <p class="text-slate-800">
                Water is backing up from drains and there’s a strong sewage smell near the sidewalk.
                Possible blockage under the manhole cover.
              </p>
            </div>
          </div>
        </section>

        <!-- Actions Panel -->
        <aside class="card-soft p-6">
          <h3 class="text-lg font-semibold title-blue mb-4">Admin Actions</h3>

          <div class="space-y-3">
            <button class="w-full px-4 py-3 rounded-xl font-semibold text-white bg-emerald-600 hover:bg-emerald-700 flex items-center justify-center gap-2">
              <i data-feather="check-circle" class="w-5 h-5"></i>
              Approve
            </button>

            <button class="w-full px-4 py-3 rounded-xl font-semibold text-white bg-amber-500 hover:bg-amber-600 flex items-center justify-center gap-2">
              <i data-feather="activity" class="w-5 h-5"></i>
              Mark In Progress
            </button>

            <button class="w-full px-4 py-3 rounded-xl font-semibold text-white bg-teal-600 hover:bg-teal-700 flex items-center justify-center gap-2">
              <i data-feather="tool" class="w-5 h-5"></i>
              Mark Resolved
            </button>

            <button class="w-full px-4 py-3 rounded-xl font-semibold text-white bg-red-600 hover:bg-red-700 flex items-center justify-center gap-2">
              <i data-feather="x-circle" class="w-5 h-5"></i>
              Reject
            </button>
          </div>

          <hr class="my-6 border-slate-200" />

          <label class="block text-sm font-semibold text-slate-700 mb-2">Internal Notes</label>
          <textarea class="w-full min-h-[120px] rounded-xl border border-slate-200 bg-white p-3 outline-none focus:ring-4 focus:ring-blue-200"
            placeholder="Add notes for your team (e.g., suspected cause, dispatch info, safety concerns)"></textarea>

          <button class="mt-3 w-full px-4 py-3 rounded-xl font-semibold bg-white border border-slate-200 hover:bg-slate-50">
            Save Notes
          </button>
        </aside>
      </div>

      <!-- Bottom grid: Photos + Map + Timeline -->
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Photos -->
        <section class="card-soft p-6 xl:col-span-1">
          <h3 class="text-lg font-semibold title-blue mb-4">Photos</h3>

          <div class="grid grid-cols-2 gap-3">
            <img src="http://static.photos/320x240" alt="Photo 1" class="rounded-xl object-cover w-full h-32 border border-slate-100" />
            <img src="http://static.photos/320x240" alt="Photo 2" class="rounded-xl object-cover w-full h-32 border border-slate-100" />
            <img src="http://static.photos/320x240" alt="Photo 3" class="rounded-xl object-cover w-full h-32 border border-slate-100" />
            <div class="rounded-xl border-2 border-dashed border-slate-200 h-32 flex items-center justify-center text-slate-500">
              + Add
            </div>
          </div>

          <p class="text-sm text-slate-500 mt-4">
            Tip: Add clear photos of the blockage/overflow and nearby landmarks for easy dispatch.
          </p>
        </section>

        <!-- Map -->
        <section class="card-soft p-6 xl:col-span-2">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold title-blue">Incident Location</h3>
            <button class="px-4 py-2 rounded-lg bg-white border border-slate-200 hover:bg-slate-50 font-semibold text-slate-700">
              Open Full Map
            </button>
          </div>

          <div id="detailMap" class="h-80"></div>
        </section>

        <!-- Timeline -->
        <section class="card-soft p-6 xl:col-span-3">
          <h3 class="text-lg font-semibold title-blue mb-4">Status Timeline</h3>

          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4">
              <div class="flex items-center gap-2 font-semibold text-slate-800">
                <i data-feather="inbox" class="w-4 h-4 text-blue-600"></i>
                Received
              </div>
              <p class="text-sm text-slate-600 mt-2">Today, 10:42 AM</p>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4">
              <div class="flex items-center gap-2 font-semibold text-slate-800">
                <i data-feather="check" class="w-4 h-4 text-emerald-600"></i>
                Approved
              </div>
              <p class="text-sm text-slate-600 mt-2">Today, 11:05 AM</p>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4">
              <div class="flex items-center gap-2 font-semibold text-slate-800">
                <i data-feather="clock" class="w-4 h-4 text-amber-500"></i>
                In Progress
              </div>
              <p class="text-sm text-slate-600 mt-2">Today, 12:10 PM</p>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 opacity-70">
              <div class="flex items-center gap-2 font-semibold text-slate-800">
                <i data-feather="tool" class="w-4 h-4 text-teal-600"></i>
                Resolved
              </div>
              <p class="text-sm text-slate-600 mt-2">Pending</p>
            </div>
          </div>
        </section>
      </div>
    </main>
  </div>

  <script>
    feather.replace();

    // Example coordinates (use your report coordinates here)
    const lat = 51.5050;
    const lng = -0.0900;

    const detailMap = L.map('detailMap').setView([lat, lng], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(detailMap);

    L.marker([lat, lng]).addTo(detailMap).bindPopup('Report: #SSR-2023-142');
  </script>
</body>
</html>
