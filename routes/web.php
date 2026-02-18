<?php

use App\Http\Controllers\analysesController;
use App\Http\Controllers\authController;
use App\Http\Controllers\dashboradController;
use App\Http\Controllers\Labo_dzController;
use App\Http\Controllers\messagesController;
use App\Http\Controllers\reservationsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Public Routes
Route::get('/', [Labo_dzController::class, 'index'])->name('home');
Route::post('/booking', [Labo_dzController::class, 'booking'])->name('booking');
Route::post('/message', [Labo_dzController::class, 'message'])->name('message');
Route::get('/analysis-info', [Labo_dzController::class, 'analysisInfo'])->name('analysis.info');
Route::get('/reservation/{id}/pdf', [\App\Http\Controllers\PdfController::class, 'generateReservationPdf'])->name('reservation.pdf');

// Authentication Routes
Route::middleware('guest:administrator')->group(function () {
    Route::get('/auth', [authController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/auth/login', [authController::class, 'administrator_login'])->name('auth.administrator');
});

Route::post('/auth/logout', [authController::class, 'logout'])->name('administrator.logout')->middleware('auth:administrator');

// Admin (Dashboard) Routes
Route::middleware('auth:administrator')->group(function () {
    // Dashboard Main
    Route::get('/dashboard', [dashboradController::class, 'dashboard'])->name('dashboard');

    // Reservations
    Route::prefix('dashboard/reservations')->group(function () {
        Route::get('/', [reservationsController::class, 'reservations'])->name('reservations');
        Route::get('/filter', [reservationsController::class, 'filterReservations'])->name('filter.reservations');
        Route::put('/{id}', [reservationsController::class, 'updateBookingStatus'])->name('admin.bookings.update');

        // Reservation Requests
        Route::get('/requests', [reservationsController::class, 'reservationRequests'])->name('reservation.requests');
        Route::post('/requests/{id}/confirm', [reservationsController::class, 'confirmRequest'])->name('reservation.requests.confirm');
        Route::post('/requests/{id}/reject', [reservationsController::class, 'rejectRequest'])->name('reservation.requests.reject');

        // Execution Eligibility Check
        Route::post('/{id}/check-eligibility', [reservationsController::class, 'checkExecutionEligibility'])->name('admin.bookings.check-eligibility');
        Route::get('/{id}/eligibility-check', [reservationsController::class, 'showEligibilityCheck'])->name('admin.bookings.eligibility.form');
        Route::post('/{id}/eligibility-check', [reservationsController::class, 'submitEligibilityCheck'])->name('admin.bookings.eligibility.submit');

        // Unified Reservation Eligibility Check
        Route::get('/{id}/full-eligibility', [reservationsController::class, 'showFullEligibilityCheck'])->name('admin.bookings.full-eligibility.form');
        Route::post('/{id}/full-eligibility', [reservationsController::class, 'submitFullEligibilityCheck'])->name('admin.bookings.full-eligibility.submit');
        Route::get('/{id}/eligibility-results', [reservationsController::class, 'showEligibilityResults'])->name('admin.bookings.eligibility.results');

        // Individual Analysis Status Update
        Route::put('/analysis/{id}/status', [reservationsController::class, 'updateAnalysisStatus'])->name('admin.bookings.analysis.status.update');
    });

    // Analyses
    Route::prefix('dashboard/analyses')->group(function () {
        Route::get('/', [analysesController::class, 'analyses'])->name('analyses');
        Route::get('/create', [analysesController::class, 'createAnalysis'])->name('analyses.create');
        Route::post('/', [analysesController::class, 'storeAnalysis'])->name('analyses.store');
        Route::get('/{id}/edit', [analysesController::class, 'editAnalysis'])->name('analyses.edit');
        Route::put('/{id}', [analysesController::class, 'updateAnalysis'])->name('analyses.update');
        Route::delete('/{id}', [analysesController::class, 'destroyAnalysis'])->name('analyses.destroy');
        Route::put('/{id}/toggle-availability', [analysesController::class, 'toggleAvailability'])->name('analyses.toggle-availability');
    });

    // Messages
    Route::prefix('dashboard/messages')->group(function () {
        Route::get('/', [messagesController::class, 'messages'])->name('messages');
        Route::post('/send', [messagesController::class, 'sendMessage'])->name('messages.send');
        Route::post('/send-result', [messagesController::class, 'sendResult'])->name('messages.send-result');
        Route::delete('/{id}', [messagesController::class, 'deleteMessage'])->name('messages.delete');
        Route::patch('/{id}/mark-as-read', [messagesController::class, 'markAsRead'])->name('messages.markAsRead');
    });
});
