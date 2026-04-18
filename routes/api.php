<?php

use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\WorkController;
use App\Http\Controllers\Api\TreeController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\RazorpayApiController;
use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\CustomerProjectTreeController;
use App\Http\Controllers\Api\CustomerWalletController;
use App\Http\Controllers\Api\TreePaymentController;
use App\Http\Controllers\Api\ProjectExportController;
use App\Http\Controllers\Api\SubscriptionApiController;
use Illuminate\Support\Facades\Route;


// Route::middleware('auth:sanctum')->group(function () {

    
//     Route::post('/payment/create-order', [TreePaymentController::class, 'createOrder']);

    
//     Route::post('/payment/verify', [TreePaymentController::class, 'verifyPayment']);
// });
// Route::middleware('auth:sanctum')->group(function () {
//     Route::post('/get_project_export_links', [ProjectExportController::class, 'get_project_export_links']);
//     Route::post('/user-subscriptions', [SubscriptionApiController::class, 'getUserSubscriptions']);
// });


Route::middleware('auth:sanctum')->group(function () {
    
    Route::post('/tree/add', [TreeController::class, 'new_tree_add']);
    // Get export links (role check + free 100 trees logic andar hai)
    Route::post('/get_project_export_links', [ProjectExportController::class, 'get_project_export_links']);

    // Price check (payment se pehle frontend ko info dene ke liye)
    Route::post('/payment/price-info', [TreePaymentController::class, 'getPriceInfo']);

    // Payment: Create Razorpay Order
    Route::post('/payment/create-order', [TreePaymentController::class, 'createOrder']);

    // Payment: Verify & Grant Lifetime Access
    Route::post('/payment/verify', [TreePaymentController::class, 'verifyPayment']);

    // User subscriptions (paid trees list)
    Route::post('/user-subscriptions', [SubscriptionApiController::class, 'getUserSubscriptions']);
});

// Route::middleware('auth:sanctum')->group(function () {

//     Route::get('/export/pdf/{project_id}', [ProjectExportController::class, 'downloadPdf'])
//         ->name('api.export.pdf');

//     Route::get('/export/excel/{project_id}', [ProjectExportController::class, 'downloadExcel'])
//         ->name('api.export.excel');

//     Route::get('/export/kml/{project_id}', [ProjectExportController::class, 'downloadKml'])
//         ->name('api.export.kml');

//     Route::get('/export/imgs/{project_id}', [ProjectExportController::class, 'downloadImgsZip'])
//         ->name('api.export.imgs');
// });

Route::post('/login', [LoginController::class, 'extra_login']);

// Step 1: Mobile daal kar OTP bhejne ke liye
Route::post('send-login-otp', [LoginController::class, 'send_otp']);

Route::post('send-register-otp', [LoginController::class, 'register_otp']);

Route::post('/get_tree_requirements', [LoginController::class, 'get_tree_requirements']);

// Step 2: OTP verify karke Login karne ke liye
Route::post('verify-otp', [LoginController::class, 'verifyOtp']);

// --- Public Routes (Moved Outside Middleware) ---
//Route::post('/create-order', [RazorpayApiController::class, 'createOrder']);
//Route::post('/verify-payment', [RazorpayApiController::class, 'verifyPayment']);
// ------------------------------------------------

//Route::middleware('auth:sanctum')->post('/user_register', [LoginController::class, 'user_register']);
Route::post('/user_register', [LoginController::class, 'user_register']);

Route::get('/states', [LoginController::class, 'state_list']);

Route::middleware('auth:sanctum')->get('/user', [LoginController::class, 'user']);
Route::middleware('auth:sanctum')->post('/logout', [LoginController::class, 'logout']);
//Route::middleware('auth:sanctum')->post('/change-password', [LoginController::class, 'changePassword']);
//Route::middleware('auth:sanctum')->get('/user/profile', [LoginController::class, 'profile']);
Route::middleware('auth:sanctum')->get('/users/{id}', [LoginController::class, 'getUserDetails']);
Route::middleware('auth:sanctum')->post('/upload-profile-image', [LoginController::class, 'uploadProfileImage']);


Route::middleware('auth:sanctum')->get('/project/list', [LoginController::class, 'project_list']);
// Route::middleware('auth:sanctum')->get('/project/officer/{id}', [LoginController::class, 'project_assign_officer']);
Route::middleware('auth:sanctum')->post('project_assign_officer', [LoginController::class, 'project_assign_officer']);
Route::middleware('auth:sanctum')->post('/user/rating', [LoginController::class, 'user_rating']);
Route::middleware('auth:sanctum')->get('/user/{user_id}/ratings', [LoginController::class, 'userRatings']);
Route::middleware('auth:sanctum')->get('/faqs', [LoginController::class, 'faq_list']);
Route::middleware('auth:sanctum')->get('/videos', [WorkController::class, 'index']);
Route::middleware('auth:sanctum')->get('/contacts', [WorkController::class, 'contact_list']);
Route::middleware('auth:sanctum')->get('/notes', [WorkController::class, 'notes_list']);
Route::middleware('auth:sanctum')->get('/privacy-policy', [WorkController::class, 'privacy_po']);
Route::middleware('auth:sanctum')->get('/tree/{id}', [WorkController::class, 'show']);
Route::middleware('auth:sanctum')->get('/tree-list', [WorkController::class, 'tree_list']);
Route::middleware('auth:sanctum')->post('/tree/measure', [WorkController::class, 'calculate']);
Route::middleware('auth:sanctum')->post('/dashboard', [TreeController::class, 'dashboard_count']);
// Route::middleware('auth:sanctum')->get('/dashboard', [TreeController::class, 'dashboard_count']);
Route::post('/trees-add', [TreeController::class, 'store']);       // Create new tree (No Auth Required)

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/trees-add/{id}', [TreeController::class, 'index']);           // List all trees
    Route::post('/tree_in_project', [TreeController::class, 'tree_on_project_id']);
    // Route::get('/tree_in_project/{id}', [TreeController::class, 'tree_on_project_id']);
    Route::get('/tree-show/{id}', [TreeController::class, 'show']);       // Show one tree
    Route::post('/tree-measure/{id}', [TreeController::class, 'update']);
    Route::delete('/measure-delete/{id}', [TreeController::class, 'destroy']);

    Route::post('password/send-otp', [ForgotPasswordController::class, 'sendResetOtp']);
    Route::post('password/verify-otp', [ForgotPasswordController::class, 'verifyOtp']);
    Route::post('password/reset', [ForgotPasswordController::class, 'resetPassword']);
});



// 1. OPEN ROUTES (OTP Login)
Route::post('/customer/send-otp', [CustomerAuthController::class, 'sendOtp']);
Route::post('/customer/verify-otp', [CustomerAuthController::class, 'verifyOtp']);

// 2. PROTECTED ROUTES (Requires Login)
Route::middleware('auth:sanctum')->group(function () {

    // --- Profile ---
    Route::post('/customer/update-profile', [CustomerAuthController::class, 'updateProfile']);

    // --- Projects (Logic: extra_user) ---
    Route::post('/customer/create-project', [CustomerProjectTreeController::class, 'createProject']);
    Route::get('customer/projects/{id}', [CustomerProjectTreeController::class, 'getProject']);       // Edit (Get by ID)
    Route::put('customer/projects/{id}', [CustomerProjectTreeController::class, 'updateProject']);    // Update
    Route::get('/customer/get-projects', [CustomerProjectTreeController::class, 'getProjects']);

    // --- Trees (Logic: extra_usertree) ---
    Route::post('/customer/add-tree', [CustomerProjectTreeController::class, 'addTree']);
    Route::get('/customer/get-trees/{project_id}', [CustomerProjectTreeController::class, 'getTrees']);

    // --- Payment & Wallet (Razorpay) ---
    // Note: Ye alag controller hai taaki aapke purane Razorpay code se mix na ho
    Route::post('/customer/payment/create-order', [CustomerWalletController::class, 'createOrder']);
    Route::post('/customer/payment/verify', [CustomerWalletController::class, 'verifyPayment']);
});


// 2. The Download Routes (Using GET so they can be triggered by window.open or href)
// These have names so we can generate URLs in the controller
Route::get('/export/pdf/{project_id}', [ProjectExportController::class, 'downloadPdf'])->name('api.export.pdf');
Route::get('/export/excel/{project_id}', [ProjectExportController::class, 'downloadExcel'])->name('api.export.excel');
Route::get('/export/kml/{project_id}', [ProjectExportController::class, 'downloadKml'])->name('api.export.kml');
Route::get('/export/imgs/{project_id}', [ProjectExportController::class, 'downloadImgsZip'])->name('api.export.imgs');

