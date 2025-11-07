<?php

namespace App\Providers;

use App\Events\BookingCreated;
use App\Listeners\SendBookingConfirmation;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * مصفوفة التعيين للحدث والمستمعين.
     * Events registered for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // ربط حدث إنشاء الحجز (BookingCreated) بمستمع إرسال التأكيد (SendBookingConfirmation)
        BookingCreated::class => [
            SendBookingConfirmation::class,
        ],
    ];

    /**
     * تسجيل أي خدمات للتطبيق.
     * Register any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * تحديد ما إذا كان يجب اكتشاف الأحداث تلقائيًا.
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}