<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\OrganizationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Superadmin authentication routes
Route::prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Auth\SuperadminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Auth\SuperadminAuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [\App\Http\Controllers\Auth\SuperadminAuthController::class, 'logout'])->name('logout');
});

// Superadmin protected routes
Route::middleware(['superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Superadmin\SuperadminDashboardController::class, 'index'])->name('dashboard');
    
    // Organization management
    Route::prefix('organizations')->name('organizations.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Superadmin\OrganizationManagementController::class, 'index'])->name('index');
        Route::get('/{organization}', [\App\Http\Controllers\Superadmin\OrganizationManagementController::class, 'show'])->name('show');
        Route::post('/{organization}/suspend', [\App\Http\Controllers\Superadmin\OrganizationManagementController::class, 'suspend'])->name('suspend');
        Route::post('/{organization}/activate', [\App\Http\Controllers\Superadmin\OrganizationManagementController::class, 'activate'])->name('activate');
        Route::delete('/{organization}', [\App\Http\Controllers\Superadmin\OrganizationManagementController::class, 'destroy'])->name('destroy');
    });
    
    // Subscription plans
    Route::resource('plans', \App\Http\Controllers\Superadmin\SubscriptionPlanController::class);
    
    // Subscription payments
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('/payments', [\App\Http\Controllers\Superadmin\SubscriptionPaymentController::class, 'index'])->name('payments');
        Route::get('/payments/{payment}', [\App\Http\Controllers\Superadmin\SubscriptionPaymentController::class, 'show'])->name('show');
        Route::post('/payments/{payment}/verify', [\App\Http\Controllers\Superadmin\SubscriptionPaymentController::class, 'verify'])->name('verify');
        Route::post('/payments/{payment}/reject', [\App\Http\Controllers\Superadmin\SubscriptionPaymentController::class, 'reject'])->name('reject');
    });
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Organization routes
    Route::prefix('organization')->name('organization.')->group(function () {
        Route::get('/setup', [OrganizationController::class, 'setup'])->name('setup');
        Route::post('/{organization}/complete-setup', [OrganizationController::class, 'completeSetup'])->name('complete-setup');
        Route::get('/{organization}', [OrganizationController::class, 'show'])->name('show');
        Route::get('/{organization}/edit', [OrganizationController::class, 'edit'])->name('edit');
        Route::put('/{organization}', [OrganizationController::class, 'update'])->name('update');
        Route::post('/{organization}/switch', [OrganizationController::class, 'switch'])->name('switch');
        
        // Service routes
        Route::prefix('{organization}/services')->name('services.')->group(function () {
            Route::get('/', [\App\Http\Controllers\ServiceController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\ServiceController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\ServiceController::class, 'store'])->name('store');
            Route::get('/{service}/edit', [\App\Http\Controllers\ServiceController::class, 'edit'])->name('edit');
            Route::put('/{service}', [\App\Http\Controllers\ServiceController::class, 'update'])->name('update');
            Route::delete('/{service}', [\App\Http\Controllers\ServiceController::class, 'destroy'])->name('destroy');
            Route::post('/{service}/toggle', [\App\Http\Controllers\ServiceController::class, 'toggleStatus'])->name('toggle');
        });
        
        // Team members routes
        Route::prefix('{organization}/team')->name('team.')->group(function () {
            Route::get('/', [\App\Http\Controllers\TeamMemberController::class, 'index'])->name('index');
            Route::put('/{user}', [\App\Http\Controllers\TeamMemberController::class, 'update'])->name('update');
            Route::delete('/{user}', [\App\Http\Controllers\TeamMemberController::class, 'destroy'])->name('destroy');
            Route::post('/{user}/reactivate', [\App\Http\Controllers\TeamMemberController::class, 'reactivate'])->name('reactivate');
        });
        
        // Team invitations routes
        Route::prefix('{organization}/invitations')->name('invitations.')->group(function () {
            Route::post('/', [\App\Http\Controllers\TeamInvitationController::class, 'store'])->name('store');
        });
        
        // Shifts routes
        Route::prefix('{organization}/shifts')->name('shifts.')->group(function () {
            Route::get('/', [\App\Http\Controllers\ShiftController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\ShiftController::class, 'store'])->name('store');
            Route::post('/bulk', [\App\Http\Controllers\ShiftController::class, 'bulkStore'])->name('bulk-store');
            Route::put('/{shift}', [\App\Http\Controllers\ShiftController::class, 'update'])->name('update');
            Route::delete('/{shift}', [\App\Http\Controllers\ShiftController::class, 'destroy'])->name('destroy');
        });
        
        // Payment gateway routes
        Route::prefix('{organization}/payment-gateways')->name('payment-gateways.')->group(function () {
            Route::get('/', [\App\Http\Controllers\PaymentGatewayController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\PaymentGatewayController::class, 'store'])->name('store');
            Route::post('/{gateway}/toggle', [\App\Http\Controllers\PaymentGatewayController::class, 'toggle'])->name('toggle');
            Route::post('/{gateway}/test', [\App\Http\Controllers\PaymentGatewayController::class, 'test'])->name('test');
            Route::delete('/{gateway}', [\App\Http\Controllers\PaymentGatewayController::class, 'destroy'])->name('destroy');
        });
        
        // Widget routes
        Route::prefix('{organization}/widget')->name('widget.')->group(function () {
            Route::get('/customize', [\App\Http\Controllers\WidgetController::class, 'customize'])->name('customize');
            Route::put('/update', [\App\Http\Controllers\WidgetController::class, 'update'])->name('update');
            Route::get('/embed', [\App\Http\Controllers\WidgetController::class, 'embed'])->name('embed');
            Route::get('/preview', [\App\Http\Controllers\WidgetController::class, 'preview'])->name('preview');
        });
    });
        
    // Booking routes (protected by role and subscription middleware)
    Route::middleware(['auth', 'role:admin,team_member,frontdesk', 'subscription'])->group(function () {
        Route::prefix('{organization}/bookings')->name('bookings.')->group(function () {
            Route::get('/', [\App\Http\Controllers\BookingController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\BookingController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\BookingController::class, 'store'])->name('store');
            Route::get('/{booking}', [\App\Http\Controllers\BookingController::class, 'show'])->name('show');
            Route::get('/{booking}/edit', [\App\Http\Controllers\BookingController::class, 'edit'])->name('edit');
            Route::put('/{booking}', [\App\Http\Controllers\BookingController::class, 'update'])->name('update');
            Route::delete('/{booking}', [\App\Http\Controllers\BookingController::class, 'destroy'])->name('destroy');
            Route::post('/{booking}/cancel', [\App\Http\Controllers\BookingController::class, 'cancel'])->name('cancel');
            Route::post('/{booking}/confirm', [\App\Http\Controllers\BookingController::class, 'confirm'])->name('confirm');
            Route::post('/{booking}/complete', [\App\Http\Controllers\BookingController::class, 'complete'])->name('complete');
        });
        
        // Slot routes
        Route::prefix('{organization}/slots')->name('slots.')->group(function () {
            Route::get('/', [\App\Http\Controllers\SlotController::class, 'index'])->name('index');
            Route::post('/generate', [\App\Http\Controllers\SlotController::class, 'generate'])->name('generate');
            Route::post('/{slot}/block', [\App\Http\Controllers\SlotController::class, 'block'])->name('block');
            Route::post('/{slot}/unblock', [\App\Http\Controllers\SlotController::class, 'unblock'])->name('unblock');
            Route::delete('/{slot}', [\App\Http\Controllers\SlotController::class, 'destroy'])->name('destroy');
        });
        
        // Reschedule routes
        Route::prefix('{organization}/reschedules')->name('reschedules.')->group(function () {
            Route::get('/bookings/{booking}/create', [\App\Http\Controllers\RescheduleController::class, 'create'])->name('create');
            Route::post('/bookings/{booking}', [\App\Http\Controllers\RescheduleController::class, 'store'])->name('store');
            Route::post('/{reschedule}/approve', [\App\Http\Controllers\RescheduleController::class, 'approve'])->name('approve');
            Route::post('/{reschedule}/reject', [\App\Http\Controllers\RescheduleController::class, 'reject'])->name('reject');
        });
        
        // Payment routes
        Route::prefix('{organization}/payments')->name('payments.')->group(function () {
            Route::get('/', [\App\Http\Controllers\PaymentController::class, 'index'])->name('index');
            Route::get('/bookings/{booking}', [\App\Http\Controllers\PaymentController::class, 'show'])->name('show');
            Route::post('/bookings/{booking}/initiate', [\App\Http\Controllers\PaymentController::class, 'initiate'])->name('initiate');
            Route::post('/bookings/{booking}/cash', [\App\Http\Controllers\PaymentController::class, 'processCash'])->name('cash');
        });
    });
    
    // Notification routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [\App\Http\Controllers\NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/mark-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('destroy');
    });
});

// Team invitation acceptance (public route)
Route::get('/invitations/accept/{token}', [\App\Http\Controllers\TeamInvitationController::class, 'accept'])->name('invitations.accept');

// Public widget route
Route::get('/widget/{slug}', [\App\Http\Controllers\WidgetController::class, 'show'])->name('widget.show');

// API route for getting available slots (used by booking widget)
Route::get('/api/organizations/{organization}/services/{service}/available-slots', [\App\Http\Controllers\BookingController::class, 'getAvailableSlots'])->name('api.available-slots');

// Widget API routes (public)
Route::prefix('api/widget/{organization}')->name('api.widget.')->group(function () {
    Route::get('/services', [\App\Http\Controllers\WidgetApiController::class, 'getServices'])->name('services');
    Route::get('/services/{service}/slots', [\App\Http\Controllers\WidgetApiController::class, 'getAvailableSlots'])->name('slots');
    Route::post('/bookings', [\App\Http\Controllers\WidgetApiController::class, 'createBooking'])->name('bookings');
});

// Widget analytics (protected)
Route::middleware('auth')->group(function () {
    Route::get('/api/widget/{organization}/analytics', [\App\Http\Controllers\WidgetApiController::class, 'getAnalytics'])->name('api.widget.analytics');
});

// Payment gateway callback routes (public)
Route::get('/payment/esewa/success/{payment}', [\App\Http\Controllers\PaymentController::class, 'esewaSuccess'])->name('payment.esewa.success');
Route::get('/payment/esewa/failure/{payment}', [\App\Http\Controllers\PaymentController::class, 'esewaFailure'])->name('payment.esewa.failure');
Route::get('/payment/khalti/callback/{payment}', [\App\Http\Controllers\PaymentController::class, 'khaltiCallback'])->name('payment.khalti.callback');
Route::post('/payment/stripe/webhook', [\App\Http\Controllers\PaymentController::class, 'stripeWebhook'])->name('payment.stripe.webhook');

// Google OAuth Routes
Route::get('auth/google', [AuthController::class, 'redirect'])->name('auth.google');
Route::get('auth/google/callback', [AuthController::class, 'callback'])->name('auth.google.callback');

require __DIR__.'/auth.php';
