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

// Landing Page
Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Credits Page (accessible to everyone)
Route::get('/credits', [\App\Http\Controllers\HomeController::class, 'credits'])->name('credits');

// DEBUG: Test slot generation (remove in production)
Route::get('/debug-slots/{organization}', function($organizationSlug) {
    $organization = \App\Models\Organization::where('slug', $organizationSlug)->firstOrFail();
    $service = $organization->services()->first();
    
    if (!$service) {
        return response()->json(['error' => 'No services found']);
    }
    
    $slotService = app(\App\Services\SlotGenerationService::class);
    $today = \Carbon\Carbon::today();
    $dayOfWeek = $today->dayOfWeek;
    
    $shifts = $organization->shifts()
        ->where('is_active', true)
        ->where(function($q) use ($dayOfWeek, $today) {
            $q->where(function($query) use ($dayOfWeek) {
                $query->where('is_recurring', true)
                      ->where('day_of_week', $dayOfWeek);
            })
            ->orWhere(function($query) use ($today) {
                $query->where('is_recurring', false)
                      ->whereDate('specific_date', $today);
            });
        })
        ->get();
    
    $allSlots = collect();
    $shiftDetails = [];
    
    foreach ($shifts as $shift) {
        $slots = $slotService->generateSlotsForShift($organization, $service, $shift, $today);
        $allSlots = $allSlots->merge($slots);
        
        $shiftDetails[] = [
            'id' => $shift->id,
            'start_time' => $shift->start_time,
            'end_time' => $shift->end_time,
            'user_id' => $shift->user_id,
            'day_of_week' => $shift->day_of_week,
            'is_recurring' => $shift->is_recurring,
            'slots_generated' => $slots->count(),
        ];
    }
    
    return response()->json([
        'date' => $today->toDateString(),
        'day_of_week' => $dayOfWeek,
        'service' => [
            'id' => $service->id,
            'name' => $service->name,
            'duration' => $service->duration,
        ],
        'shifts_found' => $shifts->count(),
        'shifts' => $shiftDetails,
        'total_slots_generated' => $allSlots->count(),
        'slots' => $allSlots->map(fn($s) => [
            'id' => $s->id ?? 'new',
            'start_time' => $s->start_time,
            'end_time' => $s->end_time,
            'staff_id' => $s->assigned_staff_id,
        ])->values(),
    ]);
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

    // Payment gateway settings
    Route::prefix('payment-settings')->name('payment-settings.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Superadmin\PaymentSettingsController::class, 'index'])->name('index');
        Route::get('/{gateway}/edit', [\App\Http\Controllers\Superadmin\PaymentSettingsController::class, 'edit'])->name('edit');
        Route::put('/{gateway}', [\App\Http\Controllers\Superadmin\PaymentSettingsController::class, 'update'])->name('update');
        Route::delete('/{gateway}/qr', [\App\Http\Controllers\Superadmin\PaymentSettingsController::class, 'deleteQr'])->name('delete-qr');
    });

    // Notification routes (for superadmin)
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [\App\Http\Controllers\NotificationController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\NotificationController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\NotificationController::class, 'store'])->name('store');
        Route::post('/{id}/mark-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('destroy');
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
        Route::prefix('{organization}/services')->name('services.')->scopedBindings()->group(function () {
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
            Route::get('/create', [\App\Http\Controllers\TeamMemberController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\TeamMemberController::class, 'store'])->name('store');
            Route::get('/{user}/edit', [\App\Http\Controllers\TeamMemberController::class, 'edit'])->name('edit');
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

        // Booking routes
        Route::prefix('{organization}/bookings')->name('bookings.')->group(function () {
            Route::get('/', [\App\Http\Controllers\BookingController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\BookingController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\BookingController::class, 'store'])->name('store');
            Route::get('/{booking}', [\App\Http\Controllers\BookingController::class, 'show'])->name('show');
            Route::get('/{booking}/edit', [\App\Http\Controllers\BookingController::class, 'edit'])->name('edit');
            Route::put('/{booking}', [\App\Http\Controllers\BookingController::class, 'update'])->name('update');
            Route::put('/{booking}/cancel', [\App\Http\Controllers\BookingController::class, 'cancel'])->name('cancel');
            Route::put('/{booking}/confirm', [\App\Http\Controllers\BookingController::class, 'confirm'])->name('confirm');
            Route::put('/{booking}/complete', [\App\Http\Controllers\BookingController::class, 'complete'])->name('complete');
            Route::delete('/{booking}', [\App\Http\Controllers\BookingController::class, 'destroy'])->name('destroy');
        });

        // Slot routes
        Route::prefix('{organization}/slots')->name('slots.')->group(function () {
            Route::get('/', [\App\Http\Controllers\SlotController::class, 'index'])->name('index');
            Route::post('/generate', [\App\Http\Controllers\SlotController::class, 'generate'])->name('generate');
            Route::post('/{slot}/toggle', [\App\Http\Controllers\SlotController::class, 'toggle'])->name('toggle');
            Route::put('/{slot}/update-status', [\App\Http\Controllers\SlotController::class, 'updateStatus'])->name('update-status');
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

        // Payment Gateway routes
        Route::prefix('{organization}/payment-gateways')->name('payment-gateways.')->group(function () {
            Route::get('/', [\App\Http\Controllers\PaymentGatewayController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\PaymentGatewayController::class, 'store'])->name('store');
            Route::put('/{paymentGateway}', [\App\Http\Controllers\PaymentGatewayController::class, 'update'])->name('update');
            Route::post('/{paymentGateway}/test', [\App\Http\Controllers\PaymentGatewayController::class, 'test'])->name('test');
            Route::post('/{paymentGateway}/toggle', [\App\Http\Controllers\PaymentGatewayController::class, 'toggle'])->name('toggle');
            Route::delete('/{paymentGateway}', [\App\Http\Controllers\PaymentGatewayController::class, 'destroy'])->name('destroy');
        });
    });
    
    // Invoice routes
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [\App\Http\Controllers\InvoiceController::class, 'index'])->name('index');
        Route::get('/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'show'])->name('show');
        Route::get('/{invoice}/download', [\App\Http\Controllers\InvoiceController::class, 'download'])->name('download');
        Route::post('/{invoice}/regenerate', [\App\Http\Controllers\InvoiceController::class, 'regenerate'])->name('regenerate');
        Route::post('/{invoice}/email', [\App\Http\Controllers\InvoiceController::class, 'email'])->name('email');
        Route::post('/bookings/{booking}/generate', [\App\Http\Controllers\InvoiceController::class, 'generateForBooking'])->name('generate.booking');
        Route::post('/subscriptions/{subscriptionPayment}/generate', [\App\Http\Controllers\InvoiceController::class, 'generateForSubscription'])->name('generate.subscription');
    });
    
    // Notification routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [\App\Http\Controllers\NotificationController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\NotificationController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\NotificationController::class, 'store'])->name('store');
        Route::post('/{id}/mark-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('destroy');
    });
});

// Team invitation acceptance (public route)
Route::get('/invitations/accept/{token}', [\App\Http\Controllers\TeamInvitationController::class, 'accept'])->name('invitations.accept');

// Public widget route
Route::get('/widget/{slug}', [\App\Http\Controllers\WidgetController::class, 'show'])->name('widget.show');

// Widget Google OAuth routes
Route::get('/widget/{organization}/auth/google', [\App\Http\Controllers\WidgetAuthController::class, 'redirectToGoogle'])->name('widget.auth.google');
Route::get('/widget/auth/google/callback', [\App\Http\Controllers\WidgetAuthController::class, 'handleGoogleCallback'])->name('widget.auth.google.callback');

// API route for getting available slots (used by booking widget)
Route::get('/api/organizations/{organization}/services/{service}/available-slots', [\App\Http\Controllers\BookingController::class, 'getAvailableSlots'])->name('api.available-slots');

// Widget API routes (public)
Route::prefix('api/widget/{organization}')->name('api.widget.')->scopeBindings()->middleware(\App\Http\Middleware\HandleWidgetCors::class)->group(function () {
    // Auth routes
    Route::post('/auth/register', [\App\Http\Controllers\WidgetAuthController::class, 'register'])->name('auth.register');
    Route::post('/auth/login', [\App\Http\Controllers\WidgetAuthController::class, 'login'])->name('auth.login');
    Route::post('/auth/logout', [\App\Http\Controllers\WidgetAuthController::class, 'logout'])->middleware('auth:sanctum')->name('auth.logout');
    Route::get('/auth/user', [\App\Http\Controllers\WidgetAuthController::class, 'user'])->middleware('auth:sanctum')->name('auth.user');
    
    // Service and booking routes
    Route::get('/services', [\App\Http\Controllers\WidgetApiController::class, 'getServices'])->name('services');
    Route::get('/services/{service}/slots', [\App\Http\Controllers\WidgetApiController::class, 'getAvailableSlots'])->name('slots');
    Route::post('/bookings', [\App\Http\Controllers\WidgetApiController::class, 'createBooking'])->name('bookings');
    Route::post('/bookings/{booking}/bank-transfer', [\App\Http\Controllers\WidgetApiController::class, 'submitBankTransfer'])->name('bank-transfer');
    Route::post('/bookings/{booking}/payment', [\App\Http\Controllers\WidgetApiController::class, 'initiatePayment'])->name('payment.initiate');
    Route::get('/bookings/{booking}/payment/{payment}/success', [\App\Http\Controllers\WidgetApiController::class, 'paymentSuccess'])->name('payment.success');
    Route::get('/bookings/{booking}/payment/{payment}/failure', [\App\Http\Controllers\WidgetApiController::class, 'paymentFailure'])->name('payment.failure');
});

// Customer Dashboard Routes (for logged-in customers)
Route::middleware('auth')->prefix('customer')->name('customer.')->group(function () {
    Route::get('/bookings', [\App\Http\Controllers\CustomerBookingController::class, 'index'])->name('bookings');
    Route::put('/bookings/{booking}/cancel', [\App\Http\Controllers\CustomerBookingController::class, 'cancel'])->name('bookings.cancel');
});

// Invoice routes
Route::middleware('auth')->group(function () {
    Route::get('/invoices/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/download', [\App\Http\Controllers\InvoiceController::class, 'download'])->name('invoices.download');
});

// Public Booking Routes (Customer-facing)
Route::prefix('book/{slug}')->name('public.booking.')->group(function () {
    Route::get('/', [\App\Http\Controllers\PublicBookingController::class, 'index'])->name('index');
    Route::get('/services/{service}', [\App\Http\Controllers\PublicBookingController::class, 'showService'])->name('service');
    Route::get('/services/{service}/slots', [\App\Http\Controllers\PublicBookingController::class, 'getAvailableSlots'])->name('slots');
    Route::get('/services/{service}/slots/{slot}/book', [\App\Http\Controllers\PublicBookingController::class, 'showBookingForm'])->name('form');
    Route::post('/bookings', [\App\Http\Controllers\PublicBookingController::class, 'store'])->name('store');
    Route::get('/bookings/{booking}/confirmation', [\App\Http\Controllers\PublicBookingController::class, 'confirmation'])->name('confirmation');
});

// Customer Bookings Routes (Authenticated customers)
Route::middleware('auth')->prefix('my-bookings')->name('my-bookings.')->group(function () {
    Route::get('/', [\App\Http\Controllers\CustomerBookingController::class, 'index'])->name('index');
    Route::get('/{booking}', [\App\Http\Controllers\CustomerBookingController::class, 'show'])->name('show');
    Route::post('/{booking}/cancel', [\App\Http\Controllers\CustomerBookingController::class, 'cancel'])->name('cancel');
});


// Widget analytics (protected)
Route::middleware('auth')->group(function () {
    Route::get('/api/widget/{organization}/analytics', [\App\Http\Controllers\WidgetApiController::class, 'getAnalytics'])->name('api.widget.analytics');
});

// Payment gateway callback routes (public)
Route::get('/payment/esewa/success/{payment}', [\App\Http\Controllers\PaymentController::class, 'esewaSuccess'])->name('payment.esewa.success');
Route::get('/payment/esewa/failure/{payment}', [\App\Http\Controllers\PaymentController::class, 'esewaFailure'])->name('payment.esewa.failure');
Route::get('/payment/khalti/callback/{payment}', [\App\Http\Controllers\PaymentController::class, 'khaltiCallback'])->name('payment.khalti.callback');

// Stripe payment routes
Route::post('/stripe/checkout', [\App\Http\Controllers\StripePaymentController::class, 'createCheckout'])->middleware('auth')->name('stripe.checkout');
Route::get('/stripe/success', [\App\Http\Controllers\StripePaymentController::class, 'success'])->middleware('auth')->name('stripe.success');
Route::post('/stripe/webhook', [\App\Http\Controllers\StripePaymentController::class, 'webhook'])->name('stripe.webhook');

// Subscription payment submission routes (for new registrations)
Route::middleware(['auth'])->prefix('subscription')->name('subscription.')->group(function () {
    Route::get('/', [\App\Http\Controllers\SubscriptionUpgradeController::class, 'index'])->name('index');
    Route::get('/payment', [\App\Http\Controllers\SubscriptionPaymentSubmissionController::class, 'show'])->name('payment.show');
    Route::post('/payment', [\App\Http\Controllers\SubscriptionPaymentSubmissionController::class, 'submit'])->name('payment.submit');
    Route::get('/payment/skip', [\App\Http\Controllers\SubscriptionPaymentSubmissionController::class, 'skip'])->name('payment.skip');
    Route::post('/upgrade', [\App\Http\Controllers\SubscriptionUpgradeController::class, 'upgrade'])->name('upgrade');
});

// Google OAuth Routes
Route::get('auth/google', [AuthController::class, 'redirect'])->name('auth.google');
Route::get('auth/google/callback', [AuthController::class, 'callback'])->name('auth.google.callback');

require __DIR__.'/auth.php';
