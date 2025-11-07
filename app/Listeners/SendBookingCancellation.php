<?php

namespace App\Listeners;

use App\Events\BookingCancelled;
use App\Mail\BookingCancellationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendBookingCancellation implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * إنشاء المستمع.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * معالجة الحدث.
     *
     * @param  BookingCancelled  $event
     * @return void
     */
    public function handle(BookingCancelled $event)
    {
        // نتأكد أن المستخدم موجود قبل محاولة إرسال الإيميل
        if ($event->booking->user && $event->booking->user->email) {
            
            // إرسال كائن البريد الإلكتروني
            Mail::to($event->booking->user->email)
                ->send(new BookingCancellationMail($event->booking));
            
            \Log::info('Booking Cancellation email sent for Booking ID: ' . $event->booking->id);
        } else {
             \Log::warning('Could not send cancellation email: User or email address is missing for Booking ID: ' . $event->booking->id);
        }
    }
}