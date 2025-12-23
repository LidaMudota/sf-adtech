<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\RedirectController;
use App\Http\Controllers\WebmasterController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::middleware('role:advertiser')->group(function () {
        Route::get('/offers', [OfferController::class, 'index'])->name('offers.index');
        Route::get('/offers/create', [OfferController::class, 'create'])->name('offers.create');
        Route::post('/offers', [OfferController::class, 'store'])->name('offers.store');
        Route::get('/offers/json', [OfferController::class, 'jsonList'])->name('offers.json');
        Route::get('/offers/{offer}/json', [OfferController::class, 'jsonShow'])->name('offers.json.show');
        Route::get('/offers/kanban', [OfferController::class, 'kanban'])->name('offers.kanban');
        Route::post('/offers/{offer}/status', [OfferController::class, 'updateStatus'])->name('offers.status');
        Route::get('/offers/{offer}', [OfferController::class, 'show'])->name('offers.show');
        Route::post('/offers/{offer}/deactivate', [OfferController::class, 'deactivate'])->name('offers.deactivate');
    });

    Route::middleware('role:webmaster')->group(function () {
        Route::get('/webmaster/subscriptions', [WebmasterController::class, 'subscriptions'])->name('webmaster.subscriptions');
        Route::get('/webmaster/offers', [WebmasterController::class, 'availableOffers'])->name('webmaster.offers');
        Route::post('/webmaster/offers/{offer}/subscribe', [WebmasterController::class, 'subscribe'])->name('webmaster.subscribe');
        Route::post('/webmaster/subscriptions/{subscription}/unsubscribe', [WebmasterController::class, 'unsubscribe'])->name('webmaster.unsubscribe');
        Route::get('/webmaster/subscriptions/{subscription}/stats', [WebmasterController::class, 'stats'])->name('webmaster.stats');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
        Route::post('/admin/users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
        Route::get('/admin/stats', [AdminController::class, 'stats'])->name('admin.stats');
    });
});

Route::get('/r/{token}', RedirectController::class)->name('redirect');
