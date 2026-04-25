<?php

use App\Http\Controllers\Admin\StatisticsController as AdminStatisticsController;
use App\Http\Controllers\Admin\SupportController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminWorkOrderController;
use App\Http\Controllers\Customer\ChatController;
use App\Http\Controllers\Customer\PreferenceController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\ReportController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\Operations\StatisticsController as OperationsStatisticsController;
use App\Http\Controllers\OperationsController;
use App\Http\Controllers\PasswordPanelController;
use App\Http\Controllers\WorkOrderController;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('guest.explore');
});

Route::get('/explore', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->hasRole(User::ROLE_CUSTOMER)) {
            return redirect()->route('customer.dashboard', ['name' => $user->profileSlug()]);
        }

        return redirect()->route('dashboard');
    }

    return view('r_customer.dashboard', [
        'reports' => Report::with('user')
            ->whereHas('operationsReport')
            ->latest()
            ->get(),
        'guestMode' => true,
    ]);
})->name('guest.explore');

Route::prefix('explore/report')->group(function () {
    $guestReportToLogin = fn () => redirect()->route('login');
    Route::get('/rproblem', $guestReportToLogin)->name('guest.report.rproblem');
    Route::get('/rpicture', $guestReportToLogin)->name('guest.report.rpicture');
    Route::get('/rlocation', $guestReportToLogin)->name('guest.report.rlocation');
    Route::get('/rdetails', $guestReportToLogin)->name('guest.report.rdetails');
    Route::get('/rpreview', $guestReportToLogin)->name('guest.report.rpreview');
    Route::post('/submit', $guestReportToLogin)->name('guest.report.submit');
});

Route::get('/signup', function () {
    return view('signup');
})->name('home');

Route::get('/login', function () {
    return view('signup', ['showLogin' => true]);
})->name('login');

Route::get('/auth/session', function () {
    if (! auth()->check()) {
        return response()->json(['logged_in' => false, 'user' => null]);
    }
    $user = auth()->user();

    return response()->json([
        'logged_in' => true,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role ?? null,
            'roles' => $user->getRoleNames(),
        ],
    ]);
})->name('auth.session');

Route::post('/password/check-customer-email', [PasswordPanelController::class, 'checkCustomerEmail'])
    ->name('password.check-customer-email');
Route::post('/password/update-customer', [PasswordPanelController::class, 'updateCustomerPassword'])
    ->name('password.update-customer');

Route::get('/locale/{lang}', function (string $lang) {
    if (in_array($lang, ['en', 'ms'], true)) {
        session(['locale' => $lang]);
    }

    return redirect()->back();
})->name('locale');

Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->hasRole(User::ROLE_SUPER_ADMIN) || $user->hasRole(User::ROLE_ADMIN)) {
        return redirect()->route('admin.dashboard');
    }
    if ($user->hasRole(User::ROLE_OPERATOR)) {
        return redirect()->route('operator.dashboard');
    }
    if ($user->hasRole(User::ROLE_CUSTOMER)) {
        return redirect()->route('customer.dashboard', ['name' => $user->profileSlug()]);
    }

    return redirect()->route('customer.dashboard', ['name' => $user->profileSlug()]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::prefix('operations')->name('operations.')->middleware(['auth', 'verified', 'role:operator'])->group(function () {
    Route::get('/dashboard', [OperationsController::class, 'dashboard'])->name('dashboard');
    Route::get('/reports', [OperationsController::class, 'reports'])->name('reports');
    Route::get('/map', [OperationsController::class, 'map'])->name('map');

    Route::get('/statistics', [OperationsStatisticsController::class, 'index'])->name('statistics.index');
    Route::get('/statistics/view', [OperationsStatisticsController::class, 'show'])->name('statistics.show');
    Route::get('/statistics/export/csv', [OperationsStatisticsController::class, 'exportCsv'])->name('statistics.export.csv');
    Route::get('/statistics/export/pdf', [OperationsStatisticsController::class, 'exportPdf'])->name('statistics.export.pdf');

    Route::get('/work-orders', [WorkOrderController::class, 'index'])->name('work-orders.index');
    Route::get('/work-orders/create', [WorkOrderController::class, 'create'])->name('work-orders.create');
    Route::post('/work-orders', [WorkOrderController::class, 'store'])->name('work-orders.store');
    Route::get('/work-orders/{workOrder}', [WorkOrderController::class, 'show'])->name('work-orders.show');
    Route::get('/work-orders/{workOrder}/pdf', [WorkOrderController::class, 'pdf'])->name('work-orders.pdf');
    Route::get('/work-orders/{workOrder}/edit', [WorkOrderController::class, 'edit'])->name('work-orders.edit');
    Route::put('/work-orders/{workOrder}', [WorkOrderController::class, 'update'])->name('work-orders.update');
    Route::patch('/work-orders/{workOrder}/status', [WorkOrderController::class, 'updateStatus'])->name('work-orders.update-status');
    Route::post('/work-orders/{workOrder}/submit-approval', [WorkOrderController::class, 'submitForApproval'])->name('work-orders.submit-approval');
    Route::post('/work-orders/{workOrder}/photos', [WorkOrderController::class, 'storePhoto'])->name('work-orders.photos.store');
    Route::delete('/work-orders/{workOrder}/photos/{photo}', [WorkOrderController::class, 'destroyPhoto'])->name('work-orders.photos.destroy');
});

Route::get('/operator/dashboard', fn () => redirect()->route('operations.dashboard'))
    ->middleware(['auth', 'verified', 'role:operator'])->name('operator.dashboard');

Route::middleware(['auth', 'verified', 'role:admin,super_admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/customer-reports', [AdminController::class, 'customerReports'])->name('admin.customer-reports.index');
    Route::get('/admin/customer-reports/unscanned-ids', [AdminController::class, 'customerReportsUnscannedIds'])->name('admin.customer-reports.unscanned-ids');
    Route::post('/admin/customer-reports/{report}/scan-drainage', [AdminController::class, 'customerReportScanDrainage'])->name('admin.customer-reports.scan-drainage');
    Route::get('/admin/customer-reports/{report}', [AdminController::class, 'customerReportShow'])->name('admin.customer-reports.show');
    Route::post('/admin/customer-reports/{report}/send-to-operations', [AdminController::class, 'customerReportSendToOperations'])->name('admin.customer-reports.send-to-operations');
    Route::delete('/admin/customer-reports/{report}', [AdminController::class, 'customerReportDestroy'])->name('admin.customer-reports.destroy');
    Route::get('/admin/incidents/review', [IncidentController::class, 'review'])->name('admin.incidents.review');
    Route::post('/admin/incidents/{incident}/send-to-operations', [IncidentController::class, 'sendToOperations'])->name('admin.incidents.send-to-operations');
    Route::post('/admin/incidents/{incident}/delete', [IncidentController::class, 'destroy'])->name('admin.incidents.destroy');
    Route::get('/admin/gis-map', [AdminController::class, 'gisMap'])->name('admin.gis-map');

    Route::get('/admin/statistics', [AdminStatisticsController::class, 'index'])->name('admin.statistics.index');
    Route::get('/admin/statistics/view', [AdminStatisticsController::class, 'show'])->name('admin.statistics.show');
    Route::get('/admin/statistics/export/csv', [AdminStatisticsController::class, 'exportCsv'])->name('admin.statistics.export.csv');
    Route::get('/admin/statistics/export/pdf', [AdminStatisticsController::class, 'exportPdf'])->name('admin.statistics.export.pdf');

    Route::get('/admin/work-orders', [AdminWorkOrderController::class, 'index'])->name('admin.work-orders.index');
    Route::get('/admin/work-orders/create', [AdminWorkOrderController::class, 'create'])->name('admin.work-orders.create');
    Route::post('/admin/work-orders', [AdminWorkOrderController::class, 'store'])->name('admin.work-orders.store');
    Route::get('/admin/work-orders/{workOrder}', [AdminWorkOrderController::class, 'show'])->name('admin.work-orders.show');
    Route::get('/admin/work-orders/{workOrder}/pdf', [AdminWorkOrderController::class, 'pdf'])->name('admin.work-orders.pdf');
    Route::get('/admin/work-orders/{workOrder}/edit', [AdminWorkOrderController::class, 'edit'])->name('admin.work-orders.edit');
    Route::put('/admin/work-orders/{workOrder}', [AdminWorkOrderController::class, 'update'])->name('admin.work-orders.update');
    Route::patch('/admin/work-orders/{workOrder}/status', [AdminWorkOrderController::class, 'updateStatus'])->name('admin.work-orders.update-status');
    Route::post('/admin/work-orders/{workOrder}/submit-approval', [AdminWorkOrderController::class, 'submitForApproval'])->name('admin.work-orders.submit-approval');
    Route::post('/admin/work-orders/{workOrder}/photos', [AdminWorkOrderController::class, 'storePhoto'])->name('admin.work-orders.photos.store');
    Route::delete('/admin/work-orders/{workOrder}/photos/{photo}', [AdminWorkOrderController::class, 'destroyPhoto'])->name('admin.work-orders.photos.destroy');

    Route::get('/admin/staff', [AdminController::class, 'staffDirectory'])->name('admin.staff.index');
    Route::get('/admin/staff/users/{user}', [AdminController::class, 'showStaffUser'])
        ->whereNumber('user')
        ->name('admin.staff.users.show');
    Route::post('/admin/staff/users/{user}/deactivate', [AdminController::class, 'deactivateStaffUser'])
        ->whereNumber('user')
        ->name('admin.staff.users.deactivate');
    Route::post('/admin/staff/users/{user}/activate', [AdminController::class, 'activateStaffUser'])
        ->whereNumber('user')
        ->name('admin.staff.users.activate');

    Route::get('/admin/workers/create', [AdminController::class, 'createWorker'])->name('admin.workers.create');
    Route::post('/admin/workers', [AdminController::class, 'storeWorker'])->name('admin.workers.store');

    Route::get('/ai/incidents/dashboard', [IncidentController::class, 'dashboard'])->name('ai.incidents.dashboard');
    Route::get('/incidents/create', [IncidentController::class, 'create'])->name('incidents.create');
    Route::post('/incidents', [IncidentController::class, 'store'])->name('incidents.store');
    Route::get('/incidents/{incident}/image', [IncidentController::class, 'showImage'])->name('incidents.image');

    Route::get('/admin/support', [SupportController::class, 'chat'])->name('admin.support.chat');
    Route::get('/admin/support/conversations', [SupportController::class, 'index'])->name('admin.support.conversations');
    Route::get('/admin/support/conversations/{userId}/messages', [SupportController::class, 'messages'])->name('admin.support.messages');
    Route::post('/admin/support/conversations/{userId}/reply', [SupportController::class, 'reply'])->name('admin.support.reply');
    Route::post('/admin/support/conversations/{userId}/terminate', [SupportController::class, 'terminate'])->name('admin.support.terminate');
});

Route::middleware(['auth', 'verified', 'role:super_admin'])->group(function () {
    Route::get('/admin/staff/users/create', [AdminController::class, 'createStaffUser'])->name('admin.staff.users.create');
    Route::post('/admin/staff/users', [AdminController::class, 'storeStaffUser'])->name('admin.staff.users.store');
    Route::get('/admin/staff/admins/create', fn () => redirect()->route('admin.staff.users.create', ['role' => 'admin']))->name('admin.staff.admins.create');
    Route::get('/admin/staff/operations/create', fn () => redirect()->route('admin.staff.users.create', ['role' => 'operator']))->name('admin.staff.operations.create');
});

Route::redirect('/admin/operators/create', '/admin/staff/users/create?role=operator');

Route::get('/customer/dashboard', fn () => redirect()->route('customer.dashboard', ['name' => auth()->user()->profileSlug()]))
    ->middleware(['auth', 'verified', 'role:customer']);
Route::get('/customer/brudmsgpt', fn () => redirect()->route('customer.brudmsgpt', ['name' => auth()->user()->profileSlug()]))
    ->middleware(['auth', 'verified', 'role:customer']);
Route::get('/customer/bruflowgpt', fn () => redirect()->route('customer.brudmsgpt', ['name' => auth()->user()->profileSlug()]))
    ->middleware(['auth', 'verified', 'role:customer']);
Route::get('/customer/general', fn () => redirect()->route('customer.general', ['name' => auth()->user()->profileSlug()]))
    ->middleware(['auth', 'verified', 'role:customer']);
Route::get('/customer/preference', fn () => redirect()->route('customer.preference', ['name' => auth()->user()->profileSlug()]))
    ->middleware(['auth', 'verified', 'role:customer']);
Route::get('/customer/faq', fn () => redirect()->route('customer.faq', ['name' => auth()->user()->profileSlug()]))
    ->middleware(['auth', 'verified', 'role:customer']);
Route::get('/customer/customersupport', fn () => redirect()->route('customer.customersupport', ['name' => auth()->user()->profileSlug()]))
    ->middleware(['auth', 'verified', 'role:customer']);
Route::get('/customer/contactcustomersupport', fn () => redirect()->route('customer.contactcustomersupport', ['name' => auth()->user()->profileSlug()]))
    ->middleware(['auth', 'verified', 'role:customer']);
Route::get('/customer/profilesettings', fn () => redirect()->route('customer.profilesettings', ['name' => auth()->user()->profileSlug()]))
    ->middleware(['auth', 'verified', 'role:customer']);
Route::get('/customer/report', fn () => redirect()->route('customer.report.type', ['name' => auth()->user()->profileSlug()]))
    ->middleware(['auth', 'verified', 'role:customer']);
Route::get('/customer/rproblem', fn () => redirect()->route('customer.rproblem', ['name' => auth()->user()->profileSlug()]))
    ->middleware(['auth', 'verified', 'role:customer']);
Route::get('/customer/rpicture', fn () => redirect()->route('customer.rpicture', ['name' => auth()->user()->profileSlug()]))
    ->middleware(['auth', 'verified', 'role:customer']);
Route::get('/customer/rlocation', fn () => redirect()->route('customer.rlocation', ['name' => auth()->user()->profileSlug()]))
    ->middleware(['auth', 'verified', 'role:customer']);
Route::get('/customer/rdetails', fn () => redirect()->route('customer.rdetails', ['name' => auth()->user()->profileSlug()]))
    ->middleware(['auth', 'verified', 'role:customer']);
Route::get('/customer/rpreview', fn () => redirect()->route('customer.rpreview', ['name' => auth()->user()->profileSlug()]))
    ->middleware(['auth', 'verified', 'role:customer']);
Route::get('/customer/livemap', fn () => redirect()->route('customer.livemap', ['name' => auth()->user()->profileSlug()]))
    ->middleware(['auth', 'verified', 'role:customer']);
Route::get('/customer/myhistory', fn () => redirect()->route('customer.myhistory', ['name' => auth()->user()->profileSlug()]))
    ->middleware(['auth', 'verified', 'role:customer']);
Route::get('/force-logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/explore');
});

Route::get('/debug-role', function () {
    if (! auth()->check()) {
        return response()->json(['error' => 'not logged in']);
    }
    $u = auth()->user();

    return response()->json([
        'id' => $u->id,
        'name' => $u->name,
        'email' => $u->email,
        'role_column' => $u->role ?? null,
        'spatie_roles' => $u->getRoleNames()->toArray(),
        'hasRole_operator' => $u->hasRole('operator'),
        'spatieHasRole_operator' => $u->spatieHasRole('operator'),
        'guard' => config('auth.defaults.guard'),
    ]);
});

Route::middleware(['auth', 'verified', 'role:customer', 'customer.name'])->group(function () {
    Route::get('/{name}', fn () => redirect()->route('customer.dashboard', ['name' => request()->route('name')]));
    Route::get('/{name}/dashboard', function () {
        $user = auth()->user();

        return view('r_customer.dashboard', [
            'reports' => Report::queryForCustomerDashboard($user)->get(),
        ]);
    })->name('customer.dashboard');
    Route::get('/{name}/brudmsgpt', fn () => view('r_customer.bruflowgpt'))->name('customer.brudmsgpt');
    Route::get('/{name}/bruflowgpt', fn () => redirect()->route('customer.brudmsgpt', ['name' => request()->route('name')]));
    Route::get('/{name}/general', fn () => view('r_customer.general'))->name('customer.general');
    Route::get('/{name}/faq', fn () => view('r_customer.faq'))->name('customer.faq');
    Route::get('/{name}/customersupport', fn () => view('r_customer.customersupport'))->name('customer.customersupport');
    Route::get('/{name}/contactcustomersupport', fn () => view('r_customer.contactcustomersupport'))->name('customer.contactcustomersupport');
    Route::get('/{name}/preference', function () {
        $user = auth()->user();

        return view('r_customer.preference', [
            'prefAppearance' => $user->preference_appearance ?? 'dark',
            'prefLanguage' => $user->preference_language ?? 'ms',
            'prefAnonymous' => $user->preference_anonymous ?? 'nonanonymous',
        ]);
    })->name('customer.preference');
    Route::get('/{name}/profilesettings', fn () => view('r_customer.profilesettings'))->name('customer.profilesettings');
    Route::get('/{name}/email-bind-otp', fn () => view('r_customer.email-bind-otp'))->name('customer.email.bind.otp');
    Route::get('/{name}/myhistory', function () {
        $user = auth()->user();

        return view('r_customer.myhistory', [
            'visibleReports' => Report::queryForCustomerHistory($user->id)->get(),
        ]);
    })->name('customer.myhistory');
    Route::get('/{name}/rproblem', fn () => view('r_customer.rproblem'))->name('customer.rproblem');
    Route::get('/{name}/rpicture', fn () => view('r_customer.rpicture'))->name('customer.rpicture');
    Route::get('/{name}/rlocation', fn () => view('r_customer.rlocation'))->name('customer.rlocation');
    Route::get('/{name}/rdetails', fn () => view('r_customer.rdetails'))->name('customer.rdetails');
    Route::get('/{name}/rpreview', fn () => view('r_customer.rpreview'))->name('customer.rpreview');
    Route::get('/{name}/livemap', function () {
        $reports = Report::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereHas('operationsReport')
            ->latest()
            ->get();

        return view('r_customer.livemap', [
            'reports' => $reports->map(fn ($r) => [
                'id' => $r->id,
                'problem_type' => $r->problem_type,
                'address' => $r->address,
                'latitude' => $r->latitude,
                'longitude' => $r->longitude,
                'status' => $r->status,
                'photo_url' => $r->photo_path ? Storage::url($r->photo_path) : null,
                'created_at' => $r->created_at->format('jS M Y'),
                'updated_at' => $r->updated_at->format('jS M Y'),
                'description' => $r->description ?? '',
            ]),
            'count_active' => $reports->where('status', 'pending')->count(),
            'count_in_progress' => $reports->whereIn('status', ['in_progress', 'under_review'])->count(),
            'count_resolved' => $reports->where('status', 'resolved')->count(),
        ]);
    })->name('customer.livemap');

    Route::get('/{name}/report', [ReportController::class, 'type'])->name('customer.report.type');
    Route::post('/{name}/report/type', [ReportController::class, 'storeType'])->name('customer.report.storeType');
    Route::get('/{name}/report/photo', [ReportController::class, 'photo'])->name('customer.report.photo');
    Route::post('/{name}/report/photo', [ReportController::class, 'storePhoto'])->name('customer.report.storePhoto');
    Route::get('/{name}/report/preview', [ReportController::class, 'preview'])->name('customer.report.preview');
    Route::post('/{name}/report/submit', [ReportController::class, 'submit'])->name('customer.report.submit');
    Route::post('/{name}/report/cancel', [ReportController::class, 'cancel'])->name('customer.report.cancel');
});

Route::post('/customer/chat', ChatController::class)
    ->middleware(['auth', 'verified', 'role:customer'])->name('customer.chat');

Route::get('/customer/support/messages', [App\Http\Controllers\Customer\SupportController::class, 'index'])
    ->middleware(['auth', 'verified', 'role:customer'])->name('customer.support.messages');
Route::post('/customer/support/messages', [App\Http\Controllers\Customer\SupportController::class, 'store'])
    ->middleware(['auth', 'verified', 'role:customer'])->name('customer.support.store');

Route::post('/customer/profile/photo', [ProfileController::class, 'updatePhoto'])
    ->middleware(['auth', 'verified', 'role:customer'])->name('customer.profile.photo.update');

Route::post('/customer/profile/name', [ProfileController::class, 'updateName'])
    ->middleware(['auth', 'verified', 'role:customer'])->name('customer.profile.name.update');

Route::post('/customer/profile/phone', [ProfileController::class, 'updatePhone'])
    ->middleware(['auth', 'verified', 'role:customer'])->name('customer.profile.phone.update');

Route::post('/customer/profile', [ProfileController::class, 'update'])
    ->middleware(['auth', 'verified', 'role:customer'])->name('customer.profile.update');

Route::post('/customer/profile/bind-email', [ProfileController::class, 'bindEmail'])
    ->middleware(['auth', 'verified', 'role:customer'])->name('customer.profile.bind-email');

Route::post('/customer/preference', [PreferenceController::class, 'update'])
    ->middleware(['auth', 'verified', 'role:customer'])->name('customer.preference.update');

require __DIR__.'/settings.php';
