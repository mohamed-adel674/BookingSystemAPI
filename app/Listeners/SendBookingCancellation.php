<?php

namespace App\Listeners;

use App\Events\BookingCancelled;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Mail\BookingCancellationMail;

class SendBookingCancellation implements ShouldQueue
{
    // ...
    public function handle(BookingCancelled $event)
    {
        $booking = $event->booking;
        Mail::to($booking->user->email)->send(new BookingCancellationMail($booking));
    }
}
