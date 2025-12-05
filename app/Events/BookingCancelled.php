<?php

namespace App\Events;

use App\Models\Booking;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingCancelled
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Booking
     */
    public Booking $booking;
    /**
     * إنشاء نسخة جديدة من الحدث.
     */
    public function __construct(Booking $booking)
    {
        // نحمل كائن الحجز ليكون متاحاً في المستمعين
        $this->booking = $booking->load('user', 'resource');
    }
}
