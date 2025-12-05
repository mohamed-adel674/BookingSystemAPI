<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;// App/Console/Commands/SendBookingReminders.php

// ... (تأكد من استخدام الـ namespaces الصحيحة)
use App\Models\Booking;
use App\Mail\BookingReminderMail; // سننشئ هذا الـ Mailable
use Carbon\Carbon;

class SendBookingReminders extends Command
{
    protected $signature = 'bookings:send-reminders';
    protected $description = 'Sends booking reminders for appointments scheduled tomorrow.';

    public function handle()
    {
        $tomorrow = Carbon::tomorrow()->toDateString();
        $startWindow = Carbon::tomorrow()->addHours(6); // مثلاً التذكير لمن يبدأ حجزه بعد الساعة 6 صباحاً
        $endWindow = Carbon::tomorrow()->addHours(24);

        $bookings = Booking::where('status', 'confirmed')
                           ->whereDate('start_time', $tomorrow)
                           // يمكن تحديد نطاق زمني أدق إذا أردت إرسالها قبل 24 ساعة بالضبط
                           //->whereBetween('start_time', [$startWindow, $endWindow]) 
                           ->get();

        $this->info("Found {$bookings->count()} bookings for tomorrow.");

        foreach ($bookings as $booking) {
            // إرسال الإيميل مباشرة أو وضعه في الـ Queue
            Mail::to($booking->user->email)->queue(new BookingReminderMail($booking));
            $this->comment("Reminder queued for Booking #{$booking->id} ({$booking->user->email})");
        }

        return self::SUCCESS;
    }
}