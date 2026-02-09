<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

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

Route::get('/locale/{lang}', function (string $lang) {
    if (in_array($lang, ['en', 'ms'], true)) {
        session(['locale' => $lang]);
    }
    return redirect()->back();
})->name('locale');

Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->hasRole(User::ROLE_ADMIN)) {
        return redirect()->route('admin.dashboard');
    }
    if ($user->hasRole(User::ROLE_OPERATOR)) {
        return redirect()->route('operator.dashboard');
    }
    if ($user->hasRole(User::ROLE_CUSTOMER)) {
        return redirect()->route('customer.dashboard');
    }
    return redirect()->route('customer.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/operator/dashboard', function () {
    return view('r_operators.dashboard');
})->middleware(['auth', 'verified', 'role:operator'])->name('operator.dashboard');

Route::get('/admin/dashboard', function () {
    return view('r_admin.dashboard');
})->middleware(['auth', 'verified', 'role:admin'])->name('admin.dashboard');

Route::get('/customer/dashboard', function () {
    return view('r_customer.home.dashboard');
})->middleware(['auth', 'verified', 'role:customer'])->name('customer.dashboard');

Route::get('/customer/reports/create', function () {
    return view('r_customer.reports.create');
})->middleware(['auth', 'verified', 'role:customer'])->name('customer.reports.create');

Route::get('/customer/map', function () {
    return view('r_customer.map.map');
})->middleware(['auth', 'verified', 'role:customer'])->name('customer.map');

Route::get('/customer/chatbot', function () {
    return view('r_customer.chatbot.chatbot');
})->middleware(['auth', 'verified', 'role:customer'])->name('customer.chatbot');

Route::get('/customer/profile', function () {
    return view('r_customer.profile.profile');
})->middleware(['auth', 'verified', 'role:customer'])->name('customer.profile');

require __DIR__.'/settings.php';
