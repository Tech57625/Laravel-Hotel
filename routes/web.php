<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChartController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomStatusController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransactionRoomReservationController;
use App\Http\Controllers\TypeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| AUTH (LOGIN / LOGOUT)
|--------------------------------------------------------------------------
*/

// login page
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// login submit
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

// logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (LOGIN REQUIRED)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // ✅ dashboard (IMPORTANT)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // activity
    Route::get('/activity-log', [ActivityController::class, 'index'])->name('activity-log.index');
    Route::get('/activity-log/all', [ActivityController::class, 'all'])->name('activity-log.all');

    // notification
    Route::view('/notification', 'notification.index')->name('notification.index');
    Route::get('/mark-all-as-read', [NotificationsController::class, 'markAllAsRead'])->name('notification.markAllAsRead');
    Route::get('/notification-to/{id}', [NotificationsController::class, 'routeTo'])->name('notification.routeTo');

    /*
    |--------------------------------------------------------------------------
    | ADMIN / SUPER (OPTIONAL - future use)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['checkRole:Super,Admin'])->group(function () {

        Route::resource('customer', CustomerController::class);
        Route::resource('type', TypeController::class);
        Route::resource('room', RoomController::class);
        Route::resource('roomstatus', RoomStatusController::class);
        Route::resource('transaction', TransactionController::class);
        Route::resource('facility', FacilityController::class);

        // image
        Route::post('/room/{room}/image/upload', [ImageController::class, 'store'])->name('image.store');
        Route::delete('/image/{image}', [ImageController::class, 'destroy'])->name('image.destroy');

        // payment
        Route::get('/payment', [PaymentController::class, 'index'])->name('payment.index');
        Route::get('/payment/{payment}/invoice', [PaymentController::class, 'invoice'])->name('payment.invoice');

        Route::get('/transaction/{transaction}/payment/create', [PaymentController::class, 'create'])->name('transaction.payment.create');
        Route::post('/transaction/{transaction}/payment/store', [PaymentController::class, 'store'])->name('transaction.payment.store');

        // reservation
        Route::name('transaction.reservation.')->group(function () {
            Route::get('/createIdentity', [TransactionRoomReservationController::class, 'createIdentity'])->name('createIdentity');
            Route::get('/pickFromCustomer', [TransactionRoomReservationController::class, 'pickFromCustomer'])->name('pickFromCustomer');
            Route::post('/storeCustomer', [TransactionRoomReservationController::class, 'storeCustomer'])->name('storeCustomer');
            Route::get('/{customer}/viewCountPerson', [TransactionRoomReservationController::class, 'viewCountPerson'])->name('viewCountPerson');
            Route::get('/{customer}/chooseRoom', [TransactionRoomReservationController::class, 'chooseRoom'])->name('chooseRoom');
            Route::get('/{customer}/{room}/{from}/{to}/confirmation', [TransactionRoomReservationController::class, 'confirmation'])->name('confirmation');
            Route::post('/{customer}/{room}/payDownPayment', [TransactionRoomReservationController::class, 'payDownPayment'])->name('payDownPayment');
        });

        // charts
        Route::get('/get-dialy-guest-chart-data', [ChartController::class, 'dailyGuestPerMonth']);
        Route::get('/get-dialy-guest/{year}/{month}/{day}', [ChartController::class, 'dailyGuest'])->name('chart.dailyGuest');
    });

    /*
    |--------------------------------------------------------------------------
    | SUPER ONLY
    |--------------------------------------------------------------------------
    */
    Route::middleware(['checkRole:Super'])->group(function () {
        Route::resource('user', UserController::class);
    });

    // user profile
    Route::resource('user', UserController::class)->only(['show']);
});


/*
|--------------------------------------------------------------------------
| PASSWORD RESET (GUEST)
|--------------------------------------------------------------------------
*/
Route::middleware(['guest'])->group(function () {

    Route::get('/forgot-password', fn () => view('auth.passwords.email'))->name('password.request');

    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');

    Route::get('/reset-password/{token}', fn ($token) =>
        view('auth.reset-password', ['token' => $token])
    )->name('password.reset');

    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});


/*
|--------------------------------------------------------------------------
| ROOT
|--------------------------------------------------------------------------
*/
Route::redirect('/', '/login');