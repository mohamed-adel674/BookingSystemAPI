<?php


namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route; // تأكد من استيراد Route
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    // ... (بقية الدالة register)

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. تعريف Rate Limiter للـ API
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // 2. تسجيل مسارات الـ API (المهمة)
        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/api.php'));

        // 3. تسجيل مسارات الويب (للاحتياط)
        Route::middleware('web')
            ->group(base_path('routes/web.php'));
    }
}