<?php

use App\Http\Controllers\Admin\BusinessPartnerController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\CredentialController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\InventoryItemController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WarehouseDashboardController;
use App\Http\Controllers\Admin\WarehouseLocationController;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
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

    $loginField = filter_var($credentials['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

    if (Auth::attempt([$loginField => $credentials['username'], 'password' => $credentials['password']], $request->boolean('remember'))) {
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'))->with('status', 'Authentication successful! Welcome, '.Auth::user()->name);
    }

    return back()->withErrors([
        'username' => 'Authentication error: Invalid credentials provided. Please verify your email/username and password.',
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

Route::get('/confirm-password', function () {
    return view('auth.confirm-password');
})->middleware('auth')->name('password.confirm');

Route::post('/confirm-password', function (Request $request) {
    $request->validate([
        'password' => ['required', 'current_password'],
    ]);

    $request->session()->passwordConfirmed();

    return redirect()->intended(route('admin.settings.security'));
})->middleware(['auth', 'throttle:6,1'])->name('password.confirm.store');

Route::get('/email/verify', function () {
    return redirect()->route('admin.settings.profile');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect()->route('admin.settings.profile')->with('status', 'Your email address has been verified.');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    if ($request->user()->hasVerifiedEmail()) {
        return redirect()->route('admin.settings.profile')->with('status', 'Your email address is already verified.');
    }

    $request->user()->sendEmailVerificationNotification();

    return redirect()->route('admin.settings.profile')->with('status', 'A verification email has been sent.');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::view('/inventory/dashboard', 'admin.inventory.dashboard')->name('inventory.dashboard');
    Route::resource('inventory', InventoryItemController::class)->only(['index', 'create', 'store']);
    Route::get('/warehouses/{location}', [WarehouseDashboardController::class, 'show'])->name('warehouses.show');
    Route::get('/reports', [DashboardController::class, 'reports'])->name('reports.index');
    Route::get('/reports/transactions', [DashboardController::class, 'transactionReport'])->name('reports.transactions');

    // SRF request, approval, and history
    Route::get('/srf/approved', [DashboardController::class, 'approvedSrf'])->name('srf.approved');
    Route::get('/srf/{srfNumber}/pdf', [DashboardController::class, 'srfPdf'])->name('srf.pdf');
    Route::post('/operations/installation/srf', [DashboardController::class, 'processInstallationSrf'])->name('operations.installation.srf');
    Route::post('/operations/installation/srf/{srf}/verify', [DashboardController::class, 'verifyInstallationSrf'])->name('operations.installation.srf.verify');

    // Notification Actions
    Route::post('/notifications/{notification}/read', [DashboardController::class, 'markNotificationRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [DashboardController::class, 'markAllNotificationsRead'])->name('notifications.markAllRead');

    Route::get('/settings/profile', [ProfileController::class, 'show'])->name('settings.profile');
    Route::put('/settings/profile', [ProfileController::class, 'update'])->name('settings.profile.update');
    Route::put('/settings/profile/account', [ProfileController::class, 'updateAccount'])->name('settings.profile.account');
    Route::get('/settings/security', [ProfileController::class, 'security'])
        ->middleware('password.confirm')
        ->name('settings.security');
    Route::put('/settings/security/password', [ProfileController::class, 'updatePassword'])
        ->middleware('password.confirm')
        ->name('settings.security.password');

    // Credentials & Access Control (Restricted to IT Administrators)
    Route::middleware('credentials.manage')->group(function () {
        Route::get('/settings/companies', [CompanyController::class, 'index'])->name('settings.companies');
        Route::post('/settings/companies', [CompanyController::class, 'store'])->name('settings.companies.store');
        Route::put('/settings/companies/{company}', [CompanyController::class, 'update'])->name('settings.companies.update');
        Route::delete('/settings/companies/{company}', [CompanyController::class, 'destroy'])->name('settings.companies.destroy');
        Route::get('/settings/departments', [DepartmentController::class, 'index'])->name('settings.departments');
        Route::post('/settings/departments', [DepartmentController::class, 'store'])->name('settings.departments.store');
        Route::put('/settings/departments/{department}', [DepartmentController::class, 'update'])->name('settings.departments.update');
        Route::delete('/settings/departments/{department}', [DepartmentController::class, 'destroy'])->name('settings.departments.destroy');
        Route::get('/settings/locations', [WarehouseLocationController::class, 'index'])->name('settings.locations');
        Route::post('/settings/locations', [WarehouseLocationController::class, 'store'])->name('settings.locations.store');
        Route::put('/settings/locations/{location}', [WarehouseLocationController::class, 'update'])->name('settings.locations.update');
        Route::delete('/settings/locations/{location}', [WarehouseLocationController::class, 'destroy'])->name('settings.locations.destroy');
        Route::get('/settings/partners', [BusinessPartnerController::class, 'index'])->name('settings.partners');
        Route::post('/settings/partners', [BusinessPartnerController::class, 'store'])->name('settings.partners.store');
        Route::put('/settings/partners/{partner}', [BusinessPartnerController::class, 'update'])->name('settings.partners.update');
        Route::delete('/settings/partners/{partner}', [BusinessPartnerController::class, 'destroy'])->name('settings.partners.destroy');

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
