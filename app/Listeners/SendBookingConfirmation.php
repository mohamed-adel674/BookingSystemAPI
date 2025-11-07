<?php

namespace App\Listeners;

use App\Events\BookingCreated;
use Illuminate\Contracts\Queue\ShouldQueue; // <--- هام جداً لتشغيل الإرسال في طابور (Queue)
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingConfirmationMail; // تأكد من استيراد الـ Mailable

class SendBookingConfirmation implements ShouldQueue // <--- تطبيق واجهة ShouldQueue
{
    use InteractsWithQueue;

    /**
     * إنشاء المستمع.
     * لا يحتاج لـ Constructor في هذه الحالة.
     */
    public function __construct()
    {
        //
    }

    /**
     * التعامل مع الحدث.
     *
     * @param BookingCreated $event
     * @return void
     */
    public function handle(BookingCreated $event)
    {
        $booking = $event->booking;
        
        // إرسال الإيميل إلى المستخدم الذي قام بالحجز
        // يتم استخدام Mail::to لإرسال الـ Mailable الذي بدوره يقرأ بيانات الحجز
        Mail::to($booking->user->email)->send(new BookingConfirmationMail($booking));
    }
}