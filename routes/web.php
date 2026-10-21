<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AdminController;

use App\Http\Controllers\PublicController;

// 1. PUBLIC WEBSITE ROUTES
Route::get('/', [PublicController::class, 'home'])->name('home');

Route::get('/about', function () {
    return view('public.about');
})->name('about');

Route::get('/contact', function () {
    return view('public.contact');
})->name('contact');

Route::get('/how-it-works', function () {
    return view('public.how-it-works');
})->name('how-it-works');

// Search & Details
Route::get('/search', [PublicController::class, 'search'])->name('properties.search');
Route::get('/property/{id}', [PublicController::class, 'details'])->name('properties.show');
Route::post('/property/{id}/book', [PublicController::class, 'book'])->name('properties.book')->middleware(['auth', 'role:tenant']);


// 2. AUTHENTICATION GUEST ROUTES
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');


// 3. AUTHENTICATED SHARED DASHBOARD
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile Settings
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile');
    Route::post('/profile', [App\Http\Controllers\ProfileController::class, 'update']);

    // Notifications Center
    Route::get('/notifications/mark-all-read', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'All notifications marked as read.');
    })->name('notifications.markAllRead');

    // Contract Download
    Route::get('/contracts/{id}/download', [ContractController::class, 'downloadPdf'])->name('contracts.download');
});


// 4. SUPER ADMIN MIDDLEWARE ROUTE GROUP
Route::middleware(['auth', 'role:superadmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/properties', [AdminController::class, 'properties'])->name('properties');
    Route::post('/properties/{id}/verify', [AdminController::class, 'verifyProperty'])->name('properties.verify');

    Route::get('/applications', [AdminController::class, 'applications'])->name('applications');

    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users/{id}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('users.toggle-status');
    Route::post('/users/{id}/verify-owner', [AdminController::class, 'verifyOwner'])->name('users.verify-owner');

    Route::get('/amenities', [AdminController::class, 'amenities'])->name('amenities');
    Route::post('/amenities', [AdminController::class, 'storeAmenity'])->name('amenities.store');
    Route::delete('/amenities/{id}', [AdminController::class, 'destroyAmenity'])->name('amenities.destroy');

    Route::get('/reports', [ReportController::class, 'adminReports'])->name('reports');
});


// 5. PROPERTY OWNER MIDDLEWARE ROUTE GROUP
Route::middleware(['auth', 'role:owner,agent', 'owner.profile'])->prefix('owner')->name('owner.')->group(function () {
    // Owner Onboarding
    Route::get('/onboarding', [App\Http\Controllers\Owner\OwnerOnboardingController::class, 'show'])->name('onboarding');
    Route::post('/onboarding/save-draft', [App\Http\Controllers\Owner\OwnerOnboardingController::class, 'saveDraft'])->name('onboarding.save-draft');
    Route::post('/onboarding/submit', [App\Http\Controllers\Owner\OwnerOnboardingController::class, 'submit'])->name('onboarding.submit');

    Route::get('/reports', [ReportController::class, 'ownerReports'])->name('reports');

    Route::get('/properties', [App\Http\Controllers\Owner\PropertyController::class, 'index'])->name('properties');
    Route::get('/properties/wizard', [App\Http\Controllers\Owner\PropertyWizardController::class, 'show'])->name('properties.wizard');
    Route::post('/properties/wizard/save-draft', [App\Http\Controllers\Owner\PropertyWizardController::class, 'saveDraft'])->name('properties.wizard.save-draft');
    Route::post('/properties/wizard/submit', [App\Http\Controllers\Owner\PropertyWizardController::class, 'submit'])->name('properties.wizard.submit');
    Route::get('/properties/create', [App\Http\Controllers\Owner\PropertyController::class, 'create'])->name('properties.create');
    Route::post('/properties', [App\Http\Controllers\Owner\PropertyController::class, 'store'])->name('properties.store');
    Route::get('/properties/{id}/edit', [App\Http\Controllers\Owner\PropertyController::class, 'edit'])->name('properties.edit');
    Route::put('/properties/{id}', [App\Http\Controllers\Owner\PropertyController::class, 'update'])->name('properties.update');
    Route::delete('/properties/images/{imageId}', [App\Http\Controllers\Owner\PropertyController::class, 'deleteImage'])->name('properties.images.destroy');

    // Property Dashboard & Management
    Route::get('/properties/{id}/dashboard', [App\Http\Controllers\Owner\PropertyDashboardController::class, 'index'])->name('properties.dashboard');
    Route::get('/properties/{id}/buildings-floors', [App\Http\Controllers\Owner\PropertyDashboardController::class, 'buildingsFloors'])->name('properties.buildings-floors');
    Route::post('/properties/{id}/buildings', [App\Http\Controllers\Owner\PropertyDashboardController::class, 'storeBuilding'])->name('properties.buildings.store');
    Route::post('/properties/{id}/floors', [App\Http\Controllers\Owner\PropertyDashboardController::class, 'storeFloor'])->name('properties.floors.store');
    Route::get('/properties/{id}/rooms-beds', [App\Http\Controllers\Owner\PropertyDashboardController::class, 'roomsBeds'])->name('properties.rooms-beds');
    Route::post('/properties/{id}/rooms', [App\Http\Controllers\Owner\PropertyDashboardController::class, 'storeRoom'])->name('properties.rooms.store');
    Route::post('/properties/{id}/beds', [App\Http\Controllers\Owner\PropertyDashboardController::class, 'storeBed'])->name('properties.beds.store');
    Route::get('/properties/{id}/staff', [App\Http\Controllers\Owner\PropertyDashboardController::class, 'staff'])->name('properties.staff');
    Route::post('/properties/{id}/staff', [App\Http\Controllers\Owner\PropertyDashboardController::class, 'storeStaff'])->name('properties.staff.store');
    Route::post('/properties/{id}/staff/{userId}/remove', [App\Http\Controllers\Owner\PropertyDashboardController::class, 'removeStaff'])->name('properties.staff.remove');

    // Assets routes
    Route::get('/properties/{id}/assets', [App\Http\Controllers\Owner\AssetController::class, 'index'])->name('properties.assets');
    Route::post('/properties/{id}/assets', [App\Http\Controllers\Owner\AssetController::class, 'store'])->name('properties.assets.store');
    Route::post('/properties/{id}/assets/handover', [App\Http\Controllers\Owner\AssetController::class, 'handover'])->name('properties.assets.handover');
    Route::post('/properties/{id}/assets/inspect', [App\Http\Controllers\Owner\AssetController::class, 'inspect'])->name('properties.assets.inspect');

    Route::get('/applications', [BookingController::class, 'ownerApplications'])->name('applications');
    Route::get('/applications/{id}', [BookingController::class, 'showApplication'])->name('applications.show');
    Route::post('/applications/{id}/process', [BookingController::class, 'processApplication'])->name('applications.process');

    Route::get('/contracts', [ContractController::class, 'ownerContracts'])->name('contracts');
    Route::get('/contracts/{id}', [ContractController::class, 'showContract'])->name('contracts.show');

    Route::get('/payments', [PaymentController::class, 'ownerPayments'])->name('payments');
    Route::post('/payments/{id}/verify', [PaymentController::class, 'verifyPayment'])->name('payments.verify');

    // Maintenance Request Routes
    Route::get('/maintenance', [MaintenanceController::class, 'ownerIndex'])->name('maintenance');
    Route::get('/maintenance/{id}', [MaintenanceController::class, 'showTicket'])->name('maintenance.show');
    Route::post('/maintenance/{id}/assign', [MaintenanceController::class, 'assignTicket'])->name('maintenance.assign');
    Route::post('/maintenance/{id}/resolve', [MaintenanceController::class, 'resolveTicket'])->name('maintenance.resolve');
});


// 6. PROPERTY MANAGER / RECEPTION MIDDLEWARE ROUTE GROUP
Route::middleware(['auth', 'role:manager,receptionist'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/properties', function () {
        return view('manager.properties.index');
    })->name('properties');

    Route::get('/applications', [BookingController::class, 'managerApplications'])->name('applications');
    Route::get('/applications/{id}', [BookingController::class, 'showApplication'])->name('applications.show');
    Route::post('/applications/{id}/process', [BookingController::class, 'processApplication'])->name('applications.process');

    Route::get('/contracts', [ContractController::class, 'managerContracts'])->name('contracts');
    Route::get('/contracts/{id}', [ContractController::class, 'showContract'])->name('contracts.show');

    Route::get('/checkins', [CheckinController::class, 'index'])->name('checkins');
    Route::post('/checkins/{id}/checkin', [CheckinController::class, 'checkIn'])->name('checkins.in');
    Route::post('/checkins/{id}/checkout', [CheckinController::class, 'checkOut'])->name('checkins.out');

    // Maintenance Request Routes
    Route::get('/maintenance', [MaintenanceController::class, 'managerIndex'])->name('maintenance');
    Route::get('/maintenance/{id}', [MaintenanceController::class, 'showTicket'])->name('maintenance.show');
    Route::post('/maintenance/{id}/assign', [MaintenanceController::class, 'assignTicket'])->name('maintenance.assign');
    Route::post('/maintenance/{id}/resolve', [MaintenanceController::class, 'resolveTicket'])->name('maintenance.resolve');
});


// 7. TENANT MIDDLEWARE ROUTE GROUP
Route::middleware(['auth', 'role:tenant'])->prefix('tenant')->name('tenant.')->group(function () {
    Route::get('/applications', [BookingController::class, 'tenantApplications'])->name('applications');

    Route::get('/contracts', [ContractController::class, 'tenantContracts'])->name('contracts');
    Route::get('/contracts/{id}', [ContractController::class, 'showContract'])->name('contracts.show');
    Route::post('/contracts/{id}/accept', [ContractController::class, 'acceptContract'])->name('contracts.accept');

    Route::get('/payments', [PaymentController::class, 'tenantPayments'])->name('payments');
    Route::post('/payments/{id}/submit', [PaymentController::class, 'submitPayment'])->name('payments.submit');

    Route::get('/maintenance', [MaintenanceController::class, 'tenantIndex'])->name('maintenance');
    Route::get('/maintenance/create', [MaintenanceController::class, 'tenantCreate'])->name('maintenance.create');
    Route::post('/maintenance', [MaintenanceController::class, 'tenantStore'])->name('maintenance.store');
    Route::post('/contracts/{id}/review', [ReviewController::class, 'store'])->name('contracts.review.store');

    // Tenant Asset Handover Confirmations & Personal Assets
    Route::get('/assets', [App\Http\Controllers\Tenant\TenantAssetController::class, 'index'])->name('assets');
    Route::post('/assets/{handoverId}/confirm', [App\Http\Controllers\Tenant\TenantAssetController::class, 'confirm'])->name('assets.confirm');
    Route::post('/assets/personal', [App\Http\Controllers\Tenant\TenantAssetController::class, 'storePersonal'])->name('assets.personal.store');
    Route::delete('/assets/personal/{id}', [App\Http\Controllers\Tenant\TenantAssetController::class, 'destroyPersonal'])->name('assets.personal.destroy');
});
