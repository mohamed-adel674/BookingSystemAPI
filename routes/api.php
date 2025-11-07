<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ResourceController;
use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\BookingController;



// Public routes (لا تحتاج مصادقة)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (تحتاج مصادقة باستخدام Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Resource Management (CRUD)
    Route::apiResource('resources', ResourceController::class);

    // Resource Management (CRUD) - متاح فقط للأدمن
    Route::middleware('is.admin')->group(function () {
        Route::apiResource('resources', ResourceController::class);

        // Availability Management
        Route::apiResource('availabilities', AvailabilityController::class)->except(['show']);
    });

    // Customer Routes (Booking Logic)
    // نقطة النهاية للحصول على المواعيد المتاحة
    Route::get('available-slots', [BookingController::class, 'getAvailableSlots']);

    // مسارات CRUD للحجوزات (الـ store هنا هو حجز جديد)
    Route::apiResource('bookings', BookingController::class)->except(['edit', 'create']);
});
