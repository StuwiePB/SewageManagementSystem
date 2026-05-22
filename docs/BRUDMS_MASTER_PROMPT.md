# BruDMS — Master Product Prompt (Full System Reference)

**Use this document as context when asking Claude (or any AI) to explain BruDMS to investors, government agencies, private contractors, or the public.**

---

## Instructions for the AI reader

You are explaining **BruDMS (Brunei Drainage Management System)** — a Laravel web application for **drainage, sewage, and flood-related civic infrastructure** in **Brunei Darussalam**.

When the user asks why BruDMS is useful:

1. **Be accurate** — only describe features listed in this document.
2. **Tailor the audience** — government (JKR, municipal, disaster/public health), private (contractors, utilities, developers), investors (market, moat, scalability), or residents (simple benefits).
3. **Emphasize the closed loop**: public reports → admin triage → field operations → maps & statistics → accountability.
4. **Highlight Brunei-specific value**: monsoon rain, district/mukim geography, live weather, drainage risk zones, +673 phone signup, bilingual/Malay-friendly AI.
5. **Acknowledge maturity**: this is a strong operational platform / pilot-ready product; national deployment would need security review, PDPA, hosting, and integration with legacy systems.

---

## 1. What BruDMS is (one paragraph)

BruDMS is a **multi-role drainage management platform** that lets **residents report** blocked drains, sewage overflow, street pooling, manhole issues, odors, and damaged pipelines with **photos and GPS**; lets **administrators** review, run **AI checks**, and forward cases to **operations**; lets **field teams** manage **work orders**, crews, and **GIS maps** with **live weather** and **safe routing**; and provides **statistics, audit logs, archives, and alerts** for oversight. It is built for **Brunei** (map bounds, districts, risk zones, emergency contacts in AI).

---

## 2. User roles and access

| Role | Who | Entry after login |
|------|-----|-------------------|
| **Customer (civilian)** | Public residents | `/{name}/dashboard` — mobile-first UI |
| **Operator** | JKR / field operations staff | `/operations/dashboard` |
| **Admin** | Agency supervisors | `/admin/dashboard` |
| **Super admin** | System owner | Same as admin + staff creation, work order archive restore |

**Authentication**

- Signup/login at `/signup` and `/login` (combined glass UI).
- Customers typically register with **Brunei phone** (`+673`).
- Email binding and password reset flows exist for customers.
- Staff accounts created by super admin (admin, operator roles).
- Spatie roles: `customer`, `operator`, `admin`, `super_admin`, `crew_leader`.
- Locale toggle: English / Malay (`/locale/en`, `/locale/ms`).
- Light/dark theme (customer preferences saved per user; staff portals themed).

**Guest / explore**

- `/explore` — public dashboard preview of reports already linked to operations (no login).
- Guest report flow URLs redirect to login (report steps exist but require auth to submit).

---

## 3. End-to-end business workflow

```text
[Customer] Submit report (type → photo → map location → details → preview → submit)
    ↓
[Admin] Customer Reports queue — filter unsent/sent, AI drainage scan on photo
    ↓
[Admin] Send to Operations → creates/links OperationsReport
    ↓
[Ops/Admin] Work orders — assign crew, status, photos, PDF export
    ↓
[Customer] My History + Live Map — track status; public map shows sent reports
```

**Parallel paths**

- **AI Incidents**: photo upload → background Vision + sewage classifier → admin review queue → send to operations.
- **Old Reports / Old Work Orders**: digitized **paper/archive** records (DMS archive) for historical continuity.
- **Customer Support chat**: threaded messages between customer and admin.
- **SNS alerts** (optional): urgent reports, work orders, high-risk incidents → AWS SNS topic.

---

## 4. CUSTOMER PORTAL — every page and function

Base URL pattern: `/{profileSlug}/...` (slug from user name, e.g. `customer.user`).

**Navigation (mobile)**

- Home (dashboard)
- Chatbot (BruFlow / Ziqah AI)
- Bottom tabs / links to map, report, profile areas

### 4.1 Dashboard (`/{name}/dashboard`)

- Lists customer’s reports (via `Report::queryForCustomerDashboard`).
- Filter chips: **Active**, **In progress**, **Resolved** (and related statuses).
- Entry point to **submit new report** and view summary cards.
- Shows reports that are on the customer’s account.

### 4.2 Report submission flow (multi-step)

| Step | Route | Function |
|------|-------|----------|
| Problem type | `/{name}/rproblem` or `/report` | Choose one of **6 issue types**: Damaged pipelines, Clogged Drains, Street Pooling, Manhole issues, Sewage overflow, Odor complaint |
| Photo | `/{name}/rpicture` | Capture/upload photo (base64 to server on submit) |
| Location | `/{name}/rlocation` | Map picker; **must be inside Brunei bounds** |
| Details | `/{name}/rdetails` | Description, severity (urgent/non-urgent), reporter info |
| Preview | `/{name}/rpreview` | Review before submit |
| Submit | `POST /{name}/report/submit` | Creates `Report` with `reference_code`, `status=pending`, stores photo on public disk |

**Validation on submit**

- Brunei-only coordinates.
- Valid +673 phone when required.
- Optional anonymous preference (stored on user preferences).

### 4.3 My History (`/{name}/myhistory`)

- Reports visible to customer via `Report::queryForCustomerHistory`.
- Status tracking: pending, in progress, under review, resolved.
- Linked to operations when admin has sent report.

### 4.4 Live Map (`/{name}/livemap`)

- Full-screen **satellite map** (Esri imagery + labels).
- **District boundaries** GeoJSON overlay.
- **Customer reports** already sent to ops: color-coded markers (red pending, amber in progress, green resolved).
- Tap marker → bottom panel with photo, address, description, dates, prev/next between reports.
- **Filter box**: toggle visibility by status (active / in progress / resolved).
- **User location** marker (geolocation).
- **Weather widget** (top-right): live Brunei forecast, heavy/light rain %, refreshes GIS context.
- **Drainage risk zone circles**: **hidden for customers** (staff-only on admin/ops maps).
- **Safe route panel** (bottom-left):
  - Set start/end on map (or “My location”).
  - `POST /safe-route/analyze` — OSRM driving route + segment risk scoring (yellow = higher, green = lower).
  - Uses live weather + drainage data + nearby active reports in scoring (backend).
  - **Work order zones**: when routing, **red 100 m circles** around active work orders (customer-only visual).
- Does **not** show internal admin risk overlays; keeps map clean for public.

### 4.5 BruFlow GPT / Ziqah AI (`/{name}/brudmsgpt`)

- Chat UI with quick chips and persistent session (localStorage per user).
- `POST /customer/chat` — OpenAI-powered assistant **Ziqah**.

**AI capabilities (customer-facing)**

- How to use the app / report step-by-step guide.
- **Report status** lookups (reference codes).
- **Brunei weather** and how rain affects drains.
- **Drainage risk zones** (Kedayan, Damuan, Gadong, etc.) from config + live weather boost.
- **Nearby drainage alerts** within **5 km** for 6 issue types (`POST /customer/chat/nearby-alert`).
- **Urgency recommendation** (urgent vs non-urgent) with reasons.
- **Emergency contacts** (999, district police/ambulance/fire, Talian Darussalam 123, water 140).
- Optional **restricted mode** passwords for deeper DB read-only queries (staff-style tools gated).
- Suggests **“Report this issue”** button when conversation implies an active problem.
- Malay/English/Brunei mixed language support in prompts.

### 4.6 Statistics (`/{name}/custatistics`)

- **7-day bar chart** of reports (linked to operations).
- **Status breakdown** by day (pending / in progress / resolved).
- Public-facing analytics for transparency (aggregated).

### 4.7 General settings (`/{name}/general`)

- Links to: **Preferences**, **FAQ**, **Customer Support**.

### 4.8 Preferences (`/{name}/preference`)

- **Appearance**: light / dark (saved to `preference_appearance`, applied site-wide).
- **Language**: English / Malay (`preference_language`).
- **Anonymous reporting** preference (`preference_anonymous`).
- `POST /customer/preference` saves choices.

### 4.9 Profile settings (`/{name}/profilesettings`)

- Update **name**, **phone**, **profile photo**.
- `POST /customer/profile/*` endpoints.

### 4.10 Email binding (`/{name}/email-bind-otp`)

- Bind email to phone-based account (OTP flow).

### 4.11 FAQ (`/{name}/faq`)

- Accordion help: what is BruDMS, reporting, Ziqah AI, live map, preferences, account, etc.

### 4.12 Customer Support (`/{name}/customersupport`)

- Hub for support options and announcements.

### 4.13 Contact Customer Support (`/{name}/contactcustomersupport`)

- Messaging UI; loads/sends via:
  - `GET /customer/support/messages`
  - `POST /customer/support/messages`
- Admin replies from **Admin → Customer chat**.

---

## 5. ADMIN CONSOLE — every module and function

Base: `/admin/...` — middleware `role:admin,super_admin`.

### 5.1 Overview / Dashboard (`/admin/dashboard`)

- **Total customer reports** (all `Report` records).
- **Reports today** (work orders created today).
- **Resolved** (completed work orders).
- **Work in progress** (active work order statuses).
- **Cancelled work orders**.
- **User counts**: civilians, admins, operators, crew leaders.
- **Charts**: 6-month trends (operations reports, maintenance work orders, resolved).
- **Pie chart**: work order status distribution.
- **Recent activity feed** (AI incidents, customer reports, crew events) with links.
- Quick links to **Customer Reports** and **Civilian Users**.

### 5.2 Customer Reports (`/admin/customer-reports`)

- **Queue** of all civilian-submitted reports.
- **Filters**: search (reference, address, reporter, phone, email), **sent vs unsent** to operations, status.
- **Bulk AI drainage scan**: Vision + classifier on photos not yet scanned (`drainage_ai_verdict`).
- Per report **actions**:
  - **Review** detail page
  - **Send to Operations** (creates/syncs `OperationsReport`)
  - **Delete**
- **Show page**: map, photo, AI verdict reason, reporter info, send/delete.

### 5.3 Civilian Users (`/admin/civilians`)

- List/search customer accounts.
- **View** profile and report history.
- **Activate / deactivate / delete** accounts.

### 5.4 AI Incidents (`/ai/incidents/dashboard` + `/admin/incidents/review`)

- **Upload** incident photo (`/incidents/create` → `ai/upload`).
- Background job: **AnalyzeIncidentImage** (sewage/drainage relevance).
- **Review queue**: incidents needing human review.
- **Send to operations** with optional admin message.
- **Delete** false positives.
- Dashboard lists all AI incidents.

### 5.5 GIS Map (`/admin/gis-map`)

Two views via `?view=`:

| View | Purpose |
|------|---------|
| **admin_ops** | Operations layer: active ops reports + work orders on map |
| **customer** | Preview of customer-facing report map |

**Map features (staff)**

- Satellite + terrain toggle (OpenTopoMap).
- **Drainage risk zones** (yellow/green circles, dashed high-risk rings).
- **Weather widget** — updates zone severity when heavy rain.
- **Safe route** tool (same OSRM + risk segmentation as customer).
- Report markers (R) and work order markers (W) with popups.
- Customer report layer for unsent/sent triage (admin_ops view).

### 5.6 Statistics (`/admin/statistics`)

- Date-range analytics for operations and work orders.
- **View** detailed breakdowns.
- **Export CSV** and **PDF**.

### 5.7 Work Orders (`/admin/work-orders`)

- Full CRUD: create, edit, show, delete.
- Fields: number, linked report, crew, type, priority, district/mukim, lat/lng, address, status lifecycle, notes.
- **Status updates** (pending → assigned → in_progress → on_the_way → on_site → completed, etc.).
- **Photo evidence** upload/delete on work orders.
- **PDF export** per work order.
- **Submit for approval** workflow step.
- **Archived** list (super admin): soft-deleted restore.

### 5.8 Old Reports (`/admin/old-reports`)

- **DMS paper report** archive — digitized historical records.
- View detail, **scan** (OCR/AI where implemented).

### 5.9 Old Work Orders (`/admin/old-work-orders`)

- Archive work orders from paper era — read-only historical record.

### 5.10 Staff accounts (`/admin/staff`)

- Directory of admins and operators.
- **View** user, **activate/deactivate/delete**.
- **Reset password** (super admin flows).
- **Create staff user** (super admin): admin or operator roles.
- **Create worker** records linked to crews.

### 5.11 Customer chat (`/admin/support`)

- List conversations with customers.
- **Reply** to messages, **terminate** thread.
- Supports operational help desk use case.

### 5.12 Audit log (`/admin/audit-log`)

- System audit trail for accountability and compliance storytelling.

---

## 6. OPERATIONS PORTAL — every module and function

Base: `/operations/...` — middleware `role:operator`.

### 6.1 Dashboard (`/operations/dashboard`)

- Active incidents count (operations reports pending/in progress).
- **Recent incidents** list.
- **Pending work orders** table (priority sorted).
- **7-day incident chart**.
- **Reports sent today** (from customer pipeline when linked).

### 6.2 GIS Map (`/operations/map`)

- Same core GIS stack as admin:
  - Risk zones + weather + safe route + terrain.
  - Operations reports and work orders on map.
  - Toggle layers for field dispatch.

### 6.3 Reports (`/operations/reports`)

- Operations-side **incident/report list** from customer pipeline and internal creation.
- Status management for field response.

### 6.4 Old Reports (`/operations/old-reports`)

- List archived paper reports.
- **Upload** new paper report (Livewire `PaperReportForm`).
- Link to **add old work order** from report.

### 6.5 Work Orders (`/operations/work-orders`)

- **Index** — active work orders.
- **Create** — from report or manual (location, crew, priority, type).
- **Show / Edit** — full field workflow.
- **Status patch** API for quick updates.
- **Photos** — before/after site evidence.
- **PDF** export for crew briefings.
- **Submit for approval** when job done.
- **Delete** (soft delete).

### 6.6 Old Work Orders (`/operations/old-work-orders`)

- Archive index.
- **Add** archive work order (Livewire `ArchiveWorkOrderForm`) with photos.

### 6.7 Statistics (`/operations/statistics`)

- Ops-focused metrics and charts.
- **View** detail page.
- **Export CSV / PDF** for management reporting.

---

## 7. Shared technical systems (cross-cutting)

### 7.1 Data models (conceptual)

- **Report** — customer complaint (reference_code, problem_type, geo, photo, status).
- **OperationsReport** — ops incident record (may link `customer_report_id`).
- **WorkOrder** — field job (crew, priority, geo, status, photos).
- **Incident** — AI-uploaded image pipeline.
- **DmsPaperReport / DmsArchiveWorkOrder** — legacy archives.
- **User**, **Crew**, **Worker**, **Support messages**, **Audit logs**.

### 7.2 GIS & weather

- **`BruneiDrainageRiskService`** — configurable risk areas (`config/brunei_drainage_risk.php`), map payloads, weather boost in heavy rain.
- **`BruneiWeatherService`** — Open-Meteo API, Brunei timezone, heavy/light rain %, map widget.
- **`GET /api/gis/weather`** — polled by map pages.
- **`BruneiGisLayers`** (JS) — terrain, risk circles, high-risk dashed rings; `showRiskLayers: false` on customer livemap only.

### 7.3 Safe routing

- **`SafeRouteController`** + **`RouteRiskService`**.
- OSRM driving geometry (config `safe_route.php`).
- Segment colors: yellow (elevated risk), green (lower) — no red segments on route line.
- Scoring uses: risk zones, nearby active reports buffer, live rain %.

### 7.4 AI & vision

- **Google Vision** + **SewageClassifier** on customer report photos (admin scan).
- **AnalyzeIncidentImage** job for AI incident uploads.
- **Ziqah** (ChatController) — GPT with Brunei domain prompt, DB tools (restricted), nearby scan.

### 7.5 Notifications (SNS)

- **`SnsNotifier`** — urgent customer reports, critical work orders, high-risk incidents.
- AWS SNS publish (or log driver for dev).
- Webhook controller for SNS subscription confirm.
- Artisan `sns:test-flows` for integration testing.

### 7.6 Theme & UX

- **`brudms-theme.css`** — CSS variables light/dark.
- **`customer-ui.css`** — mobile customer components.
- Customer: white/grey/light blue (light), cyan/dark (dark).

---

## 8. Why BruDMS is valuable — by audience

### 8.1 Brunei government (JKR, drainage, municipal, disaster management)

- **Single national queue** for citizen drainage complaints with GPS evidence.
- **Faster triage** via AI photo screening before dispatch.
- **Monsoon-ready planning** — weather + risk zones on staff maps.
- **District/mukim statistics** for budget and KPI reporting.
- **Audit trail** for parliamentary / public accountability.
- **Talian Darussalam alignment** — structured alternative to unstructured hotline chaos.
- **Public health** — sewage overflow and odor tracked geographically.

### 8.2 Private sector (contractors, utilities, facility managers)

- **Work order dispatch** with crew assignment and photo proof → **SLA billing**.
- **GIS dispatch map** reduces drive time; safe route avoids active work sites (customer view) and risk areas (staff).
- **Archive module** for legacy contract continuity.
- **White-label potential** — same stack for estates, industrial parks, campuses.

### 8.3 Investors

- **Market**: Brunei civic infra + expandable to Borneo coastal municipalities with similar rain/drainage issues.
- **Moat**: Brunei-specific risk dataset + integrated AI + ops workflow + GIS in one product (not generic ticketing).
- **Revenue models**: government SaaS license, per-dispatch contractor seats, API/alerting, premium analytics.
- **ESG / climate narrative**: flood resilience, public health, data-driven infrastructure.
- **Tech stack**: Laravel 12, Leaflet, Open-Meteo, OSRM, Vision API, SNS — integratable and auditable.

### 8.4 Public / residents

- **Easy reporting** from phone with photo and map.
- **Transparency** — history and live map show government is responding.
- **AI helper (Ziqah)** — answers in plain language, weather/risk awareness, nearby issue warnings.
- **Safer travel** during rain via optional safe route on live map.

---

## 9. Sample prompts you can give Claude

Copy one of these after pasting this document:

**Investor pitch**

> Using the BruDMS master prompt above, write a 2-minute investor pitch covering problem, solution, moat, market (Brunei + ASEAN civic infra), and revenue models.

**Government proposal**

> Using the BruDMS master prompt above, draft a 1-page proposal to Brunei JKR explaining how BruDMS improves monsoon drainage response, citizen trust, and KPI reporting. Mention AI triage, GIS, and work orders.

**Private contractor**

> Using the BruDMS master prompt above, explain why a drainage maintenance contractor would pay for BruDMS ops portal — focus on work orders, map, PDF, and crew workflow.

**Feature walkthrough**

> Using the BruDMS master prompt above, give a minute-by-minute demo script for customer report submission → admin send to ops → ops work order → customer live map status.

**Compare to status quo**

> Using the BruDMS master prompt above, compare BruDMS to phone hotlines + spreadsheets + WhatsApp groups. List 10 concrete advantages.

---

## 10. Product name and branding

- **BruDMS** — Brunei Drainage Management System.
- **Ziqah** / **BruFlow GPT** — customer AI assistant brand in UI.
- Primary colours: light mode white/grey/light blue; dark mode cyan-on-navy (theme variables).

---

*Document generated from codebase routes, controllers, views, and services. Update when major features ship.*
