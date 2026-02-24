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

// ==========================================================================
// 1. PUBLIC ROUTES
// ==========================================================================
Route::get('/', [Labo_dzController::class, 'index'])->name('home');
Route::post('/booking', [Labo_dzController::class, 'booking'])->name('booking');
Route::post('/message', [Labo_dzController::class, 'message'])->name('message');
Route::get('/analysis-info', [Labo_dzController::class, 'analysisInfo'])->name('analysis.info');

// ==========================================================================
// 2. PATIENT PORTAL (Visit & Session Based)
// ==========================================================================
Route::prefix('portal')->group(function () {
    Route::get('/', [\App\Http\Controllers\PatientPortalController::class, 'showLogin'])->name('patient.login');
    Route::post('/access', [\App\Http\Controllers\PatientPortalController::class, 'access'])->name('patient.access');
    Route::get('/dashboard', [\App\Http\Controllers\PatientPortalController::class, 'dashboard'])->name('patient.dashboard');
    Route::get('/download/{id}', [\App\Http\Controllers\PatientPortalController::class, 'downloadResult'])->name('patient.download');
    Route::post('/logout', [\App\Http\Controllers\PatientPortalController::class, 'logout'])->name('patient.logout');
});

// ==========================================================================
// 3. PHYSICIAN PORTAL (Guard: doctor)
// ==========================================================================
Route::prefix('physician')->group(function () {
    Route::middleware('guest:doctor')->group(function () {
        Route::get('/login', [\App\Http\Controllers\DoctorAuthController::class, 'showLoginForm'])->name('doctor.login');
        Route::post('/login', [\App\Http\Controllers\DoctorAuthController::class, 'login'])->name('doctor.login.submit');
    });

    Route::middleware('auth:doctor')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\DoctorDashboardController::class, 'dashboard'])->name('doctor.dashboard');
        Route::get('/patient-result/{id}', [\App\Http\Controllers\DoctorDashboardController::class, 'showPatientResult'])->name('doctor.patient.result');
        Route::post('/logout', [\App\Http\Controllers\DoctorAuthController::class, 'logout'])->name('doctor.logout');
    });
});

// ==========================================================================
// 4. ADMINISTRATIVE PANEL (Guard: administrator)
// ==========================================================================
// Admin Auth
Route::middleware('guest:administrator')->group(function () {
    Route::get('/auth', [authController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/auth/login', [authController::class, 'administrator_login'])->name('auth.administrator');
});

Route::post('/auth/logout', [authController::class, 'logout'])->name('administrator.logout')->middleware('auth:administrator');

// Admin Protected Area
Route::middleware('auth:administrator')->group(function () {
    Route::get('/dashboard', [dashboradController::class, 'dashboard'])->name('dashboard');

    // Reservations & Eligibility
    Route::prefix('dashboard/reservations')->group(function () {
        Route::get('/', [reservationsController::class, 'reservations'])->name('reservations');
        Route::get('/filter', [reservationsController::class, 'filterReservations'])->name('filter.reservations');
        Route::put('/{id}', [reservationsController::class, 'updateBookingStatus'])->name('admin.bookings.update');

        Route::get('/requests', [reservationsController::class, 'reservationRequests'])->name('reservation.requests');
        Route::post('/requests/{id}/confirm', [reservationsController::class, 'confirmRequest'])->name('reservation.requests.confirm');
        Route::post('/requests/{id}/reject', [reservationsController::class, 'rejectRequest'])->name('reservation.requests.reject');

        Route::post('/{id}/check-eligibility', [reservationsController::class, 'checkExecutionEligibility'])->name('admin.bookings.check-eligibility');
        Route::get('/{id}/eligibility-check', [reservationsController::class, 'showEligibilityCheck'])->name('admin.bookings.eligibility.form');
        Route::post('/{id}/eligibility-check', [reservationsController::class, 'submitEligibilityCheck'])->name('admin.bookings.eligibility.submit');
        Route::get('/{id}/full-eligibility', [reservationsController::class, 'showFullEligibilityCheck'])->name('admin.bookings.full-eligibility.form');
        Route::post('/{id}/full-eligibility', [reservationsController::class, 'submitFullEligibilityCheck'])->name('admin.bookings.full-eligibility.submit');
        Route::get('/{id}/eligibility-results', [reservationsController::class, 'showEligibilityResults'])->name('admin.bookings.eligibility.results');
        Route::get('/{id}/print-report', [reservationsController::class, 'printEligibilityReport'])->name('admin.bookings.eligibility.print');
        Route::put('/analysis/{id}/status', [reservationsController::class, 'updateAnalysisStatus'])->name('admin.bookings.analysis.status.update');
        Route::put('/{id}/referral', [reservationsController::class, 'updateReferral'])->name('admin.bookings.referral.update');
        Route::post('/{id}/notify', [reservationsController::class, 'notifyParticipants'])->name('admin.bookings.notify');
    });

    // Analyses & Protocol
    Route::prefix('dashboard/analyses')->group(function () {
        Route::get('/', [analysesController::class, 'analyses'])->name('analyses');
        Route::get('/create', [analysesController::class, 'createAnalysis'])->name('analyses.create');
        Route::post('/', [analysesController::class, 'storeAnalysis'])->name('analyses.store');
        Route::get('/{id}/edit', [analysesController::class, 'editAnalysis'])->name('analyses.edit');
        Route::put('/{id}', [analysesController::class, 'updateAnalysis'])->name('analyses.update');
        Route::delete('/{id}', [analysesController::class, 'destroyAnalysis'])->name('analyses.destroy');
        Route::put('/{id}/toggle-availability', [analysesController::class, 'toggleAvailability'])->name('analyses.toggle-availability');
    });

    Route::prefix('dashboard/eligibility')->group(function () {
        Route::get('/{analysis_id}', [\App\Http\Controllers\EligibilityController::class, 'manage'])->name('eligibility.manage');
        Route::post('/{analysis_id}/questions', [\App\Http\Controllers\EligibilityController::class, 'storeQuestion'])->name('eligibility.questions.store');
        Route::delete('/questions/{id}', [\App\Http\Controllers\EligibilityController::class, 'destroyQuestion'])->name('eligibility.questions.destroy');
        Route::post('/questions/{question_id}/options', [\App\Http\Controllers\EligibilityController::class, 'storeOption'])->name('eligibility.options.store');
        Route::delete('/options/{id}', [\App\Http\Controllers\EligibilityController::class, 'destroyOption'])->name('eligibility.options.destroy');
    });

    // CRM & Communications
    Route::prefix('dashboard/messages')->group(function () {
        Route::get('/', [messagesController::class, 'messages'])->name('messages');
        Route::post('/send', [messagesController::class, 'sendMessage'])->name('messages.send');
        Route::post('/send-result', [messagesController::class, 'sendResult'])->name('messages.send-result');
        Route::delete('/{id}', [messagesController::class, 'deleteMessage'])->name('messages.delete');
        Route::patch('/{id}/mark-as-read', [messagesController::class, 'markAsRead'])->name('messages.markAsRead');
    });
});

// Utilities
Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['ar', 'fr'])) {
        session(['locale' => $locale]);
    }
    return back();
})->name('lang.switch');

Route::get('/reservation/{id}/pdf', [\App\Http\Controllers\PdfController::class, 'generateReservationPdf'])->name('reservation.pdf');
