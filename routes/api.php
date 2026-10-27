<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Auth\ProfileController;

use App\Http\Controllers\Api\V1\Public\PropertyController as PublicPropertyController;
use App\Http\Controllers\Api\V1\Public\LocationController;
use App\Http\Controllers\Api\V1\Public\InstitutionController;
use App\Http\Controllers\Api\V1\Public\AmenityController;

use App\Http\Controllers\Api\V1\Tenant\DashboardController as TenantDashboardController;
use App\Http\Controllers\Api\V1\Tenant\ApplicationController as TenantApplicationController;
use App\Http\Controllers\Api\V1\Tenant\ContractController as TenantContractController;
use App\Http\Controllers\Api\V1\Tenant\InvoiceController as TenantInvoiceController;
use App\Http\Controllers\Api\V1\Tenant\PaymentController as TenantPaymentController;
use App\Http\Controllers\Api\V1\Tenant\MaintenanceController as TenantMaintenanceController;
use App\Http\Controllers\Api\V1\Tenant\TenantAssetController;
use App\Http\Controllers\Api\V1\Tenant\ReviewController as TenantReviewController;
use App\Http\Controllers\Api\V1\Tenant\FavoriteController as TenantFavoriteController;

use App\Http\Controllers\Api\V1\Owner\DashboardController as OwnerDashboardController;
use App\Http\Controllers\Api\V1\Owner\PropertyController as OwnerPropertyController;
use App\Http\Controllers\Api\V1\Owner\BuildingController as OwnerBuildingController;
use App\Http\Controllers\Api\V1\Owner\FloorController as OwnerFloorController;
use App\Http\Controllers\Api\V1\Owner\RoomController as OwnerRoomController;
use App\Http\Controllers\Api\V1\Owner\BedController as OwnerBedController;
use App\Http\Controllers\Api\V1\Owner\ApplicationController as OwnerApplicationController;
use App\Http\Controllers\Api\V1\Owner\ContractController as OwnerContractController;
use App\Http\Controllers\Api\V1\Owner\PaymentController as OwnerPaymentController;
use App\Http\Controllers\Api\V1\Owner\MaintenanceController as OwnerMaintenanceController;
use App\Http\Controllers\Api\V1\Owner\StaffController as OwnerStaffController;
use App\Http\Controllers\Api\V1\Owner\AssetController as OwnerAssetController;
use App\Http\Controllers\Api\V1\Owner\ReportController as OwnerReportController;

use App\Http\Controllers\Api\V1\Manager\DashboardController as ManagerDashboardController;
use App\Http\Controllers\Api\V1\Manager\ApplicationController as ManagerApplicationController;
use App\Http\Controllers\Api\V1\Manager\ContractController as ManagerContractController;
use App\Http\Controllers\Api\V1\Manager\CheckinController as ManagerCheckinController;
use App\Http\Controllers\Api\V1\Manager\CheckoutController as ManagerCheckoutController;
use App\Http\Controllers\Api\V1\Manager\MaintenanceController as ManagerMaintenanceController;
use App\Http\Controllers\Api\V1\Manager\PaymentController as ManagerPaymentController;

use App\Http\Controllers\Api\V1\Notifications\NotificationController;

Route::prefix('v1')->group(function () {

    // 1. Health Ping
    Route::get('/ping', function () {
        return response()->json([
            'success' => true,
            'message' => 'Property Rental System API v1 is live.',
            'timestamp' => now()->toIso8601String(),
        ]);
    });

    // 2. Authentication & Guest Routes
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [RegisterController::class, 'register']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/profile', [ProfileController::class, 'show']);
            Route::put('/profile', [ProfileController::class, 'update']);
            Route::post('/avatar', [ProfileController::class, 'uploadAvatar']);
            Route::post('/password', [ProfileController::class, 'changePassword']);
            Route::post('/fcm-token', [AuthController::class, 'updateFcmToken']);
        });
    });

    // 3. Public Marketplace & Lookups
    Route::prefix('properties')->group(function () {
        Route::get('/', [PublicPropertyController::class, 'index']);
        Route::get('/featured', [PublicPropertyController::class, 'featured']);
        Route::get('/{id}', [PublicPropertyController::class, 'show']);
        Route::get('/{id}/availability', [PublicPropertyController::class, 'checkAvailability']);
    });

    Route::prefix('locations')->group(function () {
        Route::get('/regions', [LocationController::class, 'regions']);
        Route::get('/districts', [LocationController::class, 'districts']);
        Route::get('/wards', [LocationController::class, 'wards']);
        Route::get('/property-types', [LocationController::class, 'propertyTypes']);
        Route::get('/room-types', [LocationController::class, 'roomTypes']);
    });

    Route::get('/institutions', [InstitutionController::class, 'index']);
    Route::get('/amenities', [AmenityController::class, 'index']);
    Route::get('/plans', [\App\Http\Controllers\Api\V1\Public\PlanController::class, 'index']);

    // 4. Authenticated Notifications
    Route::middleware('auth:sanctum')->prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllRead']);
        Route::post('/{id}/read', [NotificationController::class, 'markRead']);
    });

    // 5. Tenant Routes
    Route::middleware(['auth:sanctum', 'role:tenant'])->prefix('tenant')->group(function () {
        Route::get('/dashboard', [TenantDashboardController::class, 'index']);

        // Applications
        Route::get('/applications', [TenantApplicationController::class, 'index']);
        Route::post('/applications', [TenantApplicationController::class, 'store']);
        Route::get('/applications/{id}', [TenantApplicationController::class, 'show']);
        Route::post('/applications/{id}/cancel', [TenantApplicationController::class, 'cancel']);

        // Contracts
        Route::get('/contracts', [TenantContractController::class, 'index']);
        Route::get('/contracts/{id}', [TenantContractController::class, 'show']);
        Route::post('/contracts/{id}/accept', [TenantContractController::class, 'accept']);
        Route::get('/contracts/{id}/pdf', [TenantContractController::class, 'downloadPdf']);

        // Invoices & Payments
        Route::get('/invoices', [TenantInvoiceController::class, 'index']);
        Route::get('/invoices/{id}', [TenantInvoiceController::class, 'show']);
        Route::get('/payments', [TenantPaymentController::class, 'index']);
        Route::get('/payments/{id}', [TenantPaymentController::class, 'show']);
        Route::post('/payments/{id}/submit', [TenantPaymentController::class, 'submitProof']);

        // Maintenance
        Route::get('/maintenance', [TenantMaintenanceController::class, 'index']);
        Route::post('/maintenance', [TenantMaintenanceController::class, 'store']);
        Route::get('/maintenance/{id}', [TenantMaintenanceController::class, 'show']);

        // Assets
        Route::get('/assets', [TenantAssetController::class, 'index']);
        Route::post('/assets/{handoverId}/confirm', [TenantAssetController::class, 'confirmHandover']);
        Route::post('/assets/personal', [TenantAssetController::class, 'storePersonal']);
        Route::delete('/assets/personal/{id}', [TenantAssetController::class, 'destroyPersonal']);

        // Reviews
        Route::post('/contracts/{id}/review', [TenantReviewController::class, 'store']);

        // Favorites
        Route::get('/favorites', [TenantFavoriteController::class, 'index']);
        Route::post('/favorites/{propertyId}/toggle', [TenantFavoriteController::class, 'toggle']);
    });

    // 6. Owner Routes
    Route::middleware(['auth:sanctum', 'role:owner,agent'])->prefix('owner')->group(function () {
        Route::get('/dashboard', [OwnerDashboardController::class, 'index']);
        Route::get('/subscription', [\App\Http\Controllers\Api\V1\Owner\SubscriptionController::class, 'show']);
        Route::post('/subscription/subscribe', [\App\Http\Controllers\Api\V1\Owner\SubscriptionController::class, 'subscribe']);

        // Properties & Layout
        Route::get('/properties', [OwnerPropertyController::class, 'index']);
        Route::post('/properties', [OwnerPropertyController::class, 'store']);
        Route::get('/properties/{id}', [OwnerPropertyController::class, 'show']);
        Route::put('/properties/{id}', [OwnerPropertyController::class, 'update']);
        Route::post('/properties/{id}/images', [OwnerPropertyController::class, 'uploadImages']);
        Route::post('/properties/{id}/primary-image/{imageId}', [OwnerPropertyController::class, 'setPrimaryImage']);
        Route::delete('/properties/{id}/images/{imageId}', [OwnerPropertyController::class, 'deleteImage']);

        // Buildings, Floors, Rooms, Beds
        Route::post('/properties/{propertyId}/buildings', [OwnerBuildingController::class, 'store']);
        Route::put('/buildings/{id}', [OwnerBuildingController::class, 'update']);
        Route::post('/buildings/{buildingId}/floors', [OwnerFloorController::class, 'store']);
        Route::post('/properties/{propertyId}/rooms', [OwnerRoomController::class, 'store']);
        Route::put('/rooms/{id}', [OwnerRoomController::class, 'update']);
        Route::post('/rooms/{roomId}/beds', [OwnerBedController::class, 'store']);
        Route::put('/beds/{id}', [OwnerBedController::class, 'update']);

        // Applications
        Route::get('/applications', [OwnerApplicationController::class, 'index']);
        Route::get('/applications/{id}', [OwnerApplicationController::class, 'show']);
        Route::post('/applications/{id}/process', [OwnerApplicationController::class, 'process']);

        // Contracts
        Route::get('/contracts', [OwnerContractController::class, 'index']);
        Route::get('/contracts/{id}', [OwnerContractController::class, 'show']);
        Route::get('/contracts/{id}/pdf', [OwnerContractController::class, 'downloadPdf']);

        // Payments
        Route::get('/payments', [OwnerPaymentController::class, 'index']);
        Route::get('/payments/{id}', [OwnerPaymentController::class, 'show']);
        Route::post('/payments/{id}/verify', [OwnerPaymentController::class, 'verify']);

        // Maintenance
        Route::get('/maintenance', [OwnerMaintenanceController::class, 'index']);
        Route::get('/maintenance/{id}', [OwnerMaintenanceController::class, 'show']);
        Route::post('/maintenance/{id}/assign', [OwnerMaintenanceController::class, 'assign']);
        Route::post('/maintenance/{id}/resolve', [OwnerMaintenanceController::class, 'resolve']);

        // Staff
        Route::get('/properties/{propertyId}/staff', [OwnerStaffController::class, 'index']);
        Route::post('/properties/{propertyId}/staff', [OwnerStaffController::class, 'store']);
        Route::delete('/properties/{propertyId}/staff/{userId}', [OwnerStaffController::class, 'destroy']);

        // Assets
        Route::get('/properties/{propertyId}/assets', [OwnerAssetController::class, 'index']);
        Route::post('/properties/{propertyId}/assets', [OwnerAssetController::class, 'store']);
        Route::post('/properties/{propertyId}/assets/handover', [OwnerAssetController::class, 'handover']);

        // Reports
        Route::get('/reports', [OwnerReportController::class, 'index']);
    });

    // 7. Manager Routes
    Route::middleware(['auth:sanctum', 'role:manager,receptionist'])->prefix('manager')->group(function () {
        Route::get('/dashboard', [ManagerDashboardController::class, 'index']);

        // Applications
        Route::get('/applications', [ManagerApplicationController::class, 'index']);
        Route::get('/applications/{id}', [ManagerApplicationController::class, 'show']);
        Route::post('/applications/{id}/process', [ManagerApplicationController::class, 'process']);

        // Contracts
        Route::get('/contracts', [ManagerContractController::class, 'index']);
        Route::get('/contracts/{id}', [ManagerContractController::class, 'show']);
        Route::get('/contracts/{id}/pdf', [ManagerContractController::class, 'downloadPdf']);

        // Check-ins & Check-outs
        Route::get('/checkins', [ManagerCheckinController::class, 'index']);
        Route::post('/checkins/{contractId}/checkin', [ManagerCheckinController::class, 'checkIn']);
        Route::get('/checkouts', [ManagerCheckoutController::class, 'index']);
        Route::post('/checkins/{contractId}/checkout', [ManagerCheckoutController::class, 'checkOut']);

        // Maintenance
        Route::get('/maintenance', [ManagerMaintenanceController::class, 'index']);
        Route::get('/maintenance/{id}', [ManagerMaintenanceController::class, 'show']);
        Route::post('/maintenance/{id}/assign', [ManagerMaintenanceController::class, 'assign']);
        Route::post('/maintenance/{id}/resolve', [ManagerMaintenanceController::class, 'resolve']);

        // Payments
        Route::get('/payments', [ManagerPaymentController::class, 'index']);
        Route::post('/payments/{id}/verify', [ManagerPaymentController::class, 'verify']);
    });
});
