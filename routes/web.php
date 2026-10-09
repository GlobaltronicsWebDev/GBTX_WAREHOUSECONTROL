<?php

use App\Http\Controllers\Admin\CredentialController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('admin.dashboard');
    }

    return view('welcome');
})->name('home');

Route::get('/login', function () {
    if (Auth::check()) {
        return redirect()->route('admin.dashboard');
    }

    return view('welcome');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'username' => 'required',
        'password' => 'required',
    ]);

    $loginField = filter_var($credentials['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'email';

    if (Auth::attempt([$loginField => $credentials['username'], 'password' => $credentials['password']], $request->boolean('remember'))) {
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'))->with('status', 'Authentication successful! Welcome, '.Auth::user()->name);
    }

    return back()->withErrors([
        'username' => 'Authentication error: Invalid credentials provided. Please verify your email and password.',
    ])->withInput($request->only('username'));
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
})->name('logout');

Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email']);

    $status = Password::sendResetLink(
        $request->only('email')
    );

    return $status === Password::RESET_LINK_SENT
        ? back()->with('status', __($status))
        : back()->withErrors(['email' => __($status)]);
})->name('password.email');

Route::get('/reset-password/{token}', function (Request $request, string $token) {
    return view('auth.reset-password', [
        'token' => $token,
        'email' => $request->query('email'),
    ]);
})->name('password.reset');

Route::post('/reset-password', function (Request $request) {
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8|confirmed',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, string $password) {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
        }
    );

    return $status === Password::PASSWORD_RESET
        ? redirect()->route('login')->with('status', __($status))
        : back()->withErrors(['email' => __($status)]);
})->name('password.update');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Warehouse Inventory Management (Accessible by Warehouse Admins & IT Admins)
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/export', [InventoryController::class, 'export'])->name('inventory.export');
    Route::get('/inventory/sample-csv', [InventoryController::class, 'sampleCsv'])->name('inventory.sample-csv');
    Route::post('/inventory/import', [InventoryController::class, 'import'])->name('inventory.import');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::put('/inventory/{item}', [InventoryController::class, 'update'])->name('inventory.update');
    Route::delete('/inventory/{item}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
    Route::post('/inventory/{item}/reserve', [InventoryController::class, 'reserve'])->name('inventory.reserve');

    // Warehouse Operational Workflow Actions (Interactive Floor Operations)
    Route::post('/operations/dispatch', [DashboardController::class, 'dispatchOrder'])->name('operations.dispatch');
    Route::post('/operations/cycle-count', [DashboardController::class, 'cycleCount'])->name('operations.cycle-count');
    Route::post('/operations/return-item', [DashboardController::class, 'returnItem'])->name('operations.return');

    // Installation Project Workflow Actions (Official Flowchart Functional Execution)
    Route::get('/srf/approved', [DashboardController::class, 'approvedSrf'])->name('srf.approved');
    Route::get('/srf/{srfNumber}/pdf', [DashboardController::class, 'srfPdf'])->name('srf.pdf');
    Route::post('/operations/installation/srf', [DashboardController::class, 'processInstallationSrf'])->name('operations.installation.srf');
    Route::post('/operations/installation/srf/{srf}/verify', [DashboardController::class, 'verifyInstallationSrf'])->name('operations.installation.srf.verify');
    Route::post('/operations/installation/sto', [DashboardController::class, 'processInstallationSto'])->name('operations.installation.sto');
    Route::post('/operations/installation/dr', [DashboardController::class, 'processInstallationDr'])->name('operations.installation.dr');
    Route::post('/operations/installation/return', [DashboardController::class, 'processInstallationReturn'])->name('operations.installation.return');

    // Notification Actions
    Route::post('/notifications/{notification}/read', [DashboardController::class, 'markNotificationRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [DashboardController::class, 'markAllNotificationsRead'])->name('notifications.markAllRead');

    // Credentials & Access Control (Restricted to IT Administrators)
    Route::middleware('credentials.manage')->group(function () {
        // User & Credential management
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // Role management
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

        // Admin Credentials & Profile Settings
        Route::get('/settings/credentials', [CredentialController::class, 'index'])->name('settings.credentials');
        Route::put('/settings/credentials/profile', [CredentialController::class, 'updateProfile'])->name('settings.credentials.profile');
        Route::put('/settings/credentials/password', [CredentialController::class, 'updatePassword'])->name('settings.credentials.password');
    });
});
