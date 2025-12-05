<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// استخدام المسار الكامل لـ AdminMiddleware (الملف الوحيد الموجود لديك)
use App\Http\Middleware\AdminMiddleware; 

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        
        // تسجيل مسارات الـ API
        api: __DIR__.'/../routes/api.php', 
    )
    ->withMiddleware(function (Middleware $middleware): void {
        
        // 🚨 تصحيح نهائي: تسجيل الـ Alias لـ AdminMiddleware فقط 🚨
        $middleware->alias([
            // نستخدم 'is.admin' كـ Alias للدور الوحيد المتاح لدينا الآن
            'is.admin' => \App\Http\Middleware\AdminMiddleware::class, 
        ]);
        
        // تمت إزالة: 'role' => \App\Http\Middleware\CheckUserRole::class,
        
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();