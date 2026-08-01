# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project overview

BruDMS/BruFlow ("Sewage Management System") is a Laravel 12 + Livewire application for Brunei's drainage/sewerage
department (JKR). It handles citizen sewage/drainage incident reporting, AI-assisted photo triage (Google Cloud
Vision + a custom classifier + Winston AI-image detection), operations work order management, digitized paper-form
archives (DMS), GIS/route-risk mapping, AWS SNS alerting, and a support chat between customers and admins.

Four roles drive almost all authorization: `super_admin`, `admin`, `operator` (a.k.a. "operations"), and `customer`.
Roles are Spatie `laravel-permission` roles, but `User::hasRole()` is overridden to also fall back to a plain
`users.role` string column (with `'operation'` normalized to `operator`) — always go through `$user->hasRole()` /
`isAdmin()` / `isOperator()` / `isCustomer()` rather than checking `role` or Spatie roles directly.

## Commands

```bash
composer setup       # install PHP+JS deps, copy .env, generate key, migrate, build assets
composer dev          # concurrently runs: php artisan serve, queue:listen, npm run dev (vite)
npm run dev           # vite dev server + `php artisan reverb:start` (websockets for support chat/notifications)
npm run build         # production asset build

composer lint         # pint --parallel (auto-fixes style)
composer test:lint    # pint --parallel --test (check only, no changes — this is what CI runs)
composer test         # config:clear + test:lint + php artisan test
./vendor/bin/pest                       # run full test suite (what CI actually invokes)
./vendor/bin/pest tests/Feature/Foo.php # run a single test file
./vendor/bin/pest --filter=test_name    # run a single test by name
```

CI (`.github/workflows/lint.yml`, `tests.yml`) runs Pint style-checking and the Pest suite against PHP 8.4/8.5 on
every push/PR to `develop`, `main`, `master`, `workos`. Tests run against sqlite in-memory (see `phpunit.xml`), with
queue/session/cache/broadcast drivers forced to sync/array/null so jobs and events run inline during tests.

There is no separate JS test runner or linter configured — Pint (Laravel's PHP-CS-Fixer wrapper, `pint.json` uses
the `laravel` preset) is the only enforced style tool.

## Architecture

### Report lifecycle (the core domain flow)

1. **Customer submits a report** via a multi-step wizard (`Customer\ReportController`: type → photo → preview →
   submit), creating a `Report` (status `pending`, `drainage_ai_verdict` unset).
2. **AI drainage scan** (`App\Services\Reports\CustomerReportDrainageScan`) runs Google Vision
   (`App\Services\AI\GoogleVisionService`) + `App\Services\AI\SewageClassifier` against the photo and stores a
   verdict: `drainage`, `needs_review`, or `not_drainage`. Triggered from admin customer-report screens
   (`AdminController::customerReportScanDrainage` / `RescanDrainage`), not automatically on submit.
3. **Admin triages** the customer report queue (`/admin/customer-reports`) and either sends it to operations
   (creates an `OperationsReport` linked via `operations_reports.customer_report_id`) or soft-deletes it with a
   recorded `deletion_reason` (see `Report::deletionReasonOptions()` — the customer sees a matching, shorter label).
4. **Operations manages `WorkOrder`s** against the `OperationsReport`, with photo uploads, status transitions,
   approval submission, and a PDF export.
5. Every mutating admin/operations action is expected to also write an `AuditLog` row via `App\Services\AuditLogger`
   (see `config/audit.php` for the taxonomy of `action` keys and area labels — extend this file, don't invent
   ad-hoc action strings, when adding new audited actions).

Reference codes (`Report::generateReferenceCode()`, format `FR SAL/{md}/{yy}(NNNN)`) are unique across `reports`,
`operations_reports.report_number`, `work_orders.work_order_number`, and `dms_archive_work_orders.work_order_number`
— always check uniqueness across all four tables (`Report::referenceCodeExists()`) before minting a new one.
Legacy/imported records use `RPT-`, `WO-`, `WO-ARC-` prefixes instead (`Report::isLegacyReference()`).

### Digitized paper archive (DMS)

Separate from the live report flow: `DmsPaperReport` / `DmsArchiveWorkOrder(+Photo)` represent scanned-in historical
paper forms, entered via Livewire forms (`App\Livewire\Operations\PaperReportForm`, `ArchiveWorkOrderForm`, backed
by the shared `App\Livewire\Concerns\HasDmsPaperReportForm` trait) at `operations/old-reports` and
`operations/old-work-orders` (mirrored under `admin/old-reports` / `admin/old-work-orders` for admin/super_admin).
`App\Services\Dms\DmsFormOcrService` does OCR-assisted field extraction from scanned forms. Domain vocab for these
forms (service groups, area conditions, problem types in Malay) lives in `config/dms_forms.php`.

### AI image pipeline — two parallel implementations

There are **two overlapping sets of AI services**: `App\Services\{GoogleVisionService,SewageClassifier,
WinstonService,VisionAIService,VisionService}` and the newer, namespaced `App\Services\AI\{GoogleVisionService,
SewageClassifier,WinstonService}`. The `AI\` namespaced versions are what current code paths use (customer report
drainage scan, `App\Jobs\AI\AnalyzeIncidentImage`); the un-namespaced ones back the standalone `Incident` reporting
flow (`IncidentController`, `/incidents`, `/ai/incidents/dashboard`) and older call sites. When touching AI
classification logic, check both `config/ai.php` (drainage-relevance thresholds/keywords, used by `AI\
SewageClassifier`) and `config/sewage_sentinel.php` (a similarly-shaped but distinct threshold set — check which one
the service you're editing actually reads before changing numbers) — do not assume they're interchangeable.

`AnalyzeIncidentImage` (queued job) runs Vision → classifier → Winston (AI-generated-image detection) → on
high-risk verdicts, dispatches `App\Jobs\PublishSnsAlert` via `App\Services\Sns\SnsNotifier`, which formats
(`SnsAlertMessageFormatter`) and publishes through an `App\Contracts\SnsPublisher` implementation
(`AwsSnsPublisher` in production, `LogSnsPublisher` — see `App\Providers\SnsServiceProvider` for binding logic,
likely env-gated).

### Mapping / risk

`App\Services\BruneiWeatherService`, `BruneiDrainageRiskService`, `RouteRiskService`, `NearbyDrainageAlertService`
back the GIS map (`MapWeatherController`, `/api/gis/weather`) and "safe route" feature (`SafeRouteController`,
`POST /safe-route/analyze`, OSRM-based routing in `config/safe_route.php`). `config/brunei.php` holds Brunei's
districts/mukims, map center, and bounding box used for location validation — geo features are hard-scoped to
Brunei only.

### Auth, roles, and routing conventions

- Customer-facing routes are keyed by a `{name}` URL segment (the user's slugified name, `User::profileSlug()`),
  enforced by `EnsureCustomerNameInUrl` (`customer.name` middleware alias) so customers can't browse each other's
  slugged URLs. Always build customer links via `route('customer.xxx', ['name' => $user->profileSlug()])`, never
  hardcode `/customer/...` paths.
- `EnsureUserHasRole` is aliased as `role:` (e.g. `role:operator`, `role:admin,super_admin`) — the standard way
  routes gate by role, layered on top of `auth`/`verified`.
- `EnsureAccountIsActive` and `LogoutBeforeLoginSwitch` run on every web request (registered globally in
  `bootstrap/app.php`), so a deactivated `users.is_active` account is force-logged-out account-wide, and switching
  login identity mid-session is guarded against.
- Global middleware also includes `SetLocale` (session-based `en`/`ms` locale toggle via `GET /locale/{lang}`).
- Password reset/2FA/registration flow through **Laravel Fortify** (`App\Actions\Fortify\*`,
  `App\Providers\FortifyServiceProvider`) but signup/login views are custom (`signup.blade.php`, not Fortify's
  default views) — check `FortifyServiceProvider` before assuming a Fortify default is in effect.
- There's also a phone-based OTP path (`App\Services\PhoneOtpService`, `App\Support\AccountEmail`,
  `User::needsEmailBinding()`) for customers who sign up phone-only and later bind a real email.

### Support chat & realtime

Customer↔admin support chat (`SupportMessage` model, `Customer\ChatController`/`SupportController`,
`Admin\SupportController`) broadcasts over **Laravel Reverb** (self-hosted websockets, not Pusher-hosted — see
`REVERB_*` env vars) via `SupportMessageSent`/`SupportChatTerminated` events. `npm run dev` must run
`reverb:start` alongside Vite for realtime chat to work locally; `composer dev` starts the queue listener but not
Reverb — use `npm run dev` (or run `php artisan reverb:start` separately) when working on chat.

### Views

Blade views are organized by audience, not by MVC resource: `resources/views/r_customer/`, `r_operators/`,
`r_admin/`, plus shared `layouts/`, `components/`, `partials/`, and Livewire's own `views/livewire/`. When adding a
customer-facing page, follow the `r_customer` naming/layout conventions already there rather than introducing a new
top-level views folder.

### Other notable pieces

- `App\Services\ZiqahDatabaseBridge` backs "Ziqah", the customer-facing chat assistant (`Customer\ChatController`,
  widget script at `public/js/ziqah-widget.js`, embedded via `resources/views/layouts/customer.blade.php`) that is
  page-context-aware — check the PHP service and the widget JS together when changing either side.
- `Worker`/`WorkerAttendance`/`Crew` model staff attendance/crew assignment for the operations side, distinct from
  `User` (login accounts for admin/operator/customer roles).
