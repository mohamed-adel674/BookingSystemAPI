<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ResourceController;
use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AdminController;



// Auth routes (Authentication)
Route::prefix('auth')->middleware('throttle:5,1')->group(function () {
    // Public routes (لا تحتاج مصادقة)
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    // Protected routes (تحتاج مصادقة)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

// Services routes (خدمات)
Route::prefix('services')->group(function () {
    // Public - عرض الخدمات متاح للجميع
    Route::get('/', [ServiceController::class, 'index']);
    Route::get('/{id}', [ServiceController::class, 'show']);
    
    // Protected - إدارة الخدمات للأدمن فقط
    Route::middleware(['auth:sanctum', 'is.admin'])->group(function () {
        Route::post('/', [ServiceController::class, 'store']);
        Route::put('/{id}', [ServiceController::class, 'update']);
        Route::delete('/{id}', [ServiceController::class, 'destroy']);
    });
});

// Availability routes - البحث عن المواعيد المتاحة
Route::middleware('auth:sanctum')->group(function () {
    // GET /api/availability - البحث عن المواعيد المتاحة
    Route::get('/availability', [BookingController::class, 'getAvailableSlots']);
});

// Slots Management - إدارة فترات الإتاحة (للمشرف)
Route::middleware(['auth:sanctum', 'is.admin'])->group(function () {
    // POST /api/slots - إضافة فترة إتاحة جديدة
    Route::post('/slots', [AvailabilityController::class, 'store']);
    // PUT /api/slots/{id} - تعديل فترة إتاحة
    Route::put('/slots/{availability}', [AvailabilityController::class, 'update']);
});

// Protected routes (تحتاج مصادقة باستخدام Sanctum)
Route::middleware('auth:sanctum')->group(function () {

    // Resource Management (CRUD)
    Route::apiResource('resources', ResourceController::class);

    // Resource Management (CRUD) - متاح فقط للأدمن
    Route::middleware('is.admin')->group(function () {
        Route::apiResource('resources', ResourceController::class);

        // Availability Management
        Route::apiResource('availabilities', AvailabilityController::class)->except(['show']);
    });

    // Customer Routes (Booking Logic)
    // نقطة النهاية للحصول على المواعيد المتاحة (route بديل)
    Route::get('available-slots', [BookingController::class, 'getAvailableSlots']);

    // مسارات CRUD للحجوزات (الـ store هنا هو حجز جديد)
    Route::apiResource('bookings', BookingController::class)->except(['edit', 'create']);
    
    // مسارات إضافية للحجوزات
    Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel']);
    Route::put('bookings/{booking}', [BookingController::class, 'reschedule']); // تغيير الوقت
    Route::get('users/{userId}/bookings', [BookingController::class, 'userBookings']);
    
    // مسارات الأدمن للحجوزات
    Route::middleware('is.admin')->group(function () {
        Route::post('bookings/{booking}/confirm', [BookingController::class, 'confirm']);
    });
    
    // User Profile Management
    Route::get('profile', [UserController::class, 'profile']);
    Route::put('profile', [UserController::class, 'updateProfile']);
});

// Admin routes - لوحة التحكم
Route::middleware(['auth:sanctum', 'is.admin'])->prefix('admin')->group(function () {
    Route::get('statistics', [AdminController::class, 'statistics']);
    Route::get('users', [AdminController::class, 'users']);
    Route::get('bookings', [AdminController::class, 'allBookings']);
    Route::put('users/{userId}/role', [AdminController::class, 'updateUserRole']);
});
