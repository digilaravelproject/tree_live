<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\SupportController;
use App\Http\Controllers\Api\UserManagementController;
use App\Http\Controllers\Api\UserRatingController;
use App\Http\Controllers\Api\WorkController;
use App\Http\Controllers\Api\TreeController;
use App\Http\Controllers\Api\TreePaymentController;
use App\Http\Controllers\Api\ProjectExportController;
use App\Http\Controllers\Api\SubscriptionApiController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ==========================================
// 1. PUBLIC AUTH & ONBOARDING
// ==========================================
Route::post('/login', [LoginController::class, 'extra_login']);
Route::post('/send-login-otp', [LoginController::class, 'send_otp']);
Route::post('/send-register-otp', [LoginController::class, 'register_otp']);
Route::post('/verify-otp', [LoginController::class, 'verifyOtp']);
Route::post('/user_register', [LoginController::class, 'user_register']);

// ==========================================
// 2. PUBLIC SUPPORT & METADATA
// ==========================================
Route::get('/states', [SupportController::class, 'states']);
Route::post('/get_tree_requirements', [ProjectController::class, 'requirements']);

// ==========================================
// 3. PROTECTED ROUTES (Requires Sanctum Auth)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    
    // --- User Session ---
    Route::get('/user', [LoginController::class, 'user']);
    Route::post('/logout', [LoginController::class, 'logout']);
    
    // --- Profile & Management ---
    Route::get('/users/{id}', [UserManagementController::class, 'show']);
    Route::post('/upload-profile-image', [UserManagementController::class, 'updateProfile']);
    
    // --- Projects ---
    Route::get('/project/list', [ProjectController::class, 'index']);
    Route::post('/project_assign_officer', [LoginController::class, 'project_assign_officer']);
    
    // --- Ratings ---
    Route::post('/user/rating', [UserRatingController::class, 'store']);
    Route::get('/user/{user_id}/ratings', [LoginController::class, 'userRatings']);
    
    // --- Support ---
    Route::get('/faqs', [SupportController::class, 'faqs']);
    
    // --- Tree Operations ---
    Route::post('/tree/add', [TreeController::class, 'new_tree_add']);
    Route::get('/tree-list', [WorkController::class, 'tree_list']);
    Route::get('/tree/{id}', [WorkController::class, 'show']);
    Route::post('/tree/measure', [WorkController::class, 'calculate']);
    
    // --- Dashboard & Data Management ---
    Route::post('/dashboard', [TreeController::class, 'dashboard_count']);
    Route::get('/trees-add/{id}', [TreeController::class, 'index']);
    Route::post('/tree_in_project', [TreeController::class, 'tree_on_project_id']);
    Route::get('/tree-show/{id}', [TreeController::class, 'show']);
    Route::post('/trees-add', [TreeController::class, 'store']);
    Route::post('/tree-measure/{id}', [TreeController::class, 'update']);
    Route::delete('/measure-delete/{id}', [TreeController::class, 'destroy']);

    // --- Payments & Subscriptions ---
    Route::post('/payment/price-info', [TreePaymentController::class, 'getPriceInfo']);
    Route::post('/payment/create-order', [TreePaymentController::class, 'createOrder']);
    Route::post('/payment/verify', [TreePaymentController::class, 'verifyPayment']);
    Route::post('/user-subscriptions', [SubscriptionApiController::class, 'getUserSubscriptions']);

    // --- Export Access ---
    Route::post('/get_project_export_links', [ProjectExportController::class, 'get_project_export_links']);
    
    // --- Password Resets ---
    Route::post('password/send-otp', [ForgotPasswordController::class, 'sendResetOtp']);
    Route::post('password/verify-otp', [ForgotPasswordController::class, 'verifyOtp']);
    Route::post('password/reset', [ForgotPasswordController::class, 'resetPassword']);
});

// ==========================================
// 4. WORK & CONTENT (Protected)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/videos', [WorkController::class, 'index']);
    Route::get('/contacts', [WorkController::class, 'contact_list']);
    Route::get('/notes', [WorkController::class, 'notes_list']);
    Route::get('/privacy-policy', [WorkController::class, 'privacy_po']);
});

// ==========================================
// 5. EXPORT DOWNLOADS (GET Methods)
// ==========================================
Route::get('/export/pdf/{project_id}', [ProjectExportController::class, 'downloadPdf'])->name('api.export.pdf');
Route::get('/export/excel/{project_id}', [ProjectExportController::class, 'downloadExcel'])->name('api.export.excel');
Route::get('/export/kml/{project_id}', [ProjectExportController::class, 'downloadKml'])->name('api.export.kml');
Route::get('/export/imgs/{project_id}', [ProjectExportController::class, 'downloadImgsZip'])->name('api.export.imgs');
