<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BillingDashboardController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\LabOrderController;
use App\Http\Controllers\Api\LabTestTypeController;
use App\Http\Controllers\Api\LaboratoryDashboardController;
use App\Http\Controllers\Api\MedicalRecordController;
use App\Http\Controllers\Api\MedicineController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\NotificationPreferenceController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\PharmacyDashboardController;
use App\Http\Controllers\Api\PrescriptionController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/email/send-code', [AuthController::class, 'sendVerificationCode']);
Route::post('/email/verify', [AuthController::class, 'verifyEmail']);

// Protected routes (Sanctum)
Route::middleware('auth:sanctum')->group(function () {

    // Auth / Profile
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Users directory
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);

    // Notification Preferences
    Route::get('/notification-preferences', [NotificationPreferenceController::class, 'show']);
    Route::put('/notification-preferences', [NotificationPreferenceController::class, 'update']);

    // Departments
    Route::apiResource('departments', DepartmentController::class)->except(['edit', 'create']);

    // Doctors
    Route::apiResource('doctors', DoctorController::class)->except(['edit', 'create']);
    Route::put('/doctors/{id}/schedule', [DoctorController::class, 'updateSchedule']);

    // Patients
    Route::apiResource('patients', PatientController::class)->except(['edit', 'create']);

    // Appointments
    Route::get('/appointments/today', [AppointmentController::class, 'today']);
    Route::get('/appointments/slots', [AppointmentController::class, 'availableSlots']);
    Route::apiResource('appointments', AppointmentController::class)->except(['edit', 'create']);
    Route::patch('/appointments/{id}/status', [AppointmentController::class, 'updateStatus']);

    // Medical Records
    Route::get('/medical-records/patients/{patientId}/timeline', [MedicalRecordController::class, 'timeline']);
    Route::apiResource('medical-records', MedicalRecordController::class)->except(['edit', 'create']);

    // Prescriptions
    Route::apiResource('prescriptions', PrescriptionController::class)->except(['edit', 'create']);
    Route::post('/prescriptions/{id}/dispense', [PrescriptionController::class, 'dispense']);
    Route::patch('/prescriptions/{id}/status', [PrescriptionController::class, 'updateStatus']);

    // Pharmacy - Medicines
    Route::apiResource('medicines', MedicineController::class)->except(['edit', 'create']);

    // Pharmacy - Categories
    Route::apiResource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);

    // Pharmacy - Suppliers
    Route::apiResource('suppliers', SupplierController::class)->only(['index', 'store', 'update', 'destroy']);

    // Pharmacy Dashboard
    Route::get('/pharmacy/dashboard', [PharmacyDashboardController::class, 'dashboard']);

    // Laboratory - Test Types
    Route::apiResource('test-types', LabTestTypeController::class)->only(['index', 'store', 'update', 'destroy']);

    // Laboratory - Orders
    Route::apiResource('lab-orders', LabOrderController::class)->only(['index', 'show', 'store', 'update']);
    Route::post('/lab-orders/{id}/status', [LabOrderController::class, 'updateStatus']);

    // Laboratory Dashboard
    Route::get('/laboratory/dashboard', [LaboratoryDashboardController::class, 'dashboard']);

    // Billing - Invoices
    Route::apiResource('invoices', InvoiceController::class)->except(['edit', 'create']);
    Route::post('/invoices/{id}/payments', [InvoiceController::class, 'recordPayment']);

    // Billing Dashboard
    Route::get('/billing/dashboard', [BillingDashboardController::class, 'dashboard']);

    // Staff
    Route::apiResource('staff', StaffController::class)->except(['edit', 'create']);

    // Notifications
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::delete('/notifications/all', [NotificationController::class, 'destroyAll']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);

    // Dashboard
    Route::get('/dashboard/admin', [DashboardController::class, 'admin']);
    Route::get('/dashboard/patient/{id}', [DashboardController::class, 'patient']);

    // Global Search
    Route::get('/search', SearchController::class);

    // Reports
    Route::get('/reports/data', [ReportController::class, 'getData']);
});
