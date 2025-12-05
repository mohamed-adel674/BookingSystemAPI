<?php

namespace App\Events;

use App\Models\Booking;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * كائن الحجز الذي تم إنشاؤه.
     */
    public Booking $booking;

    /**
     * إنشاء نسخة جديدة من الحدث.
     */
    public function __construct(Booking $booking)
    {
        // يتم تحميل علاقتي المستخدم (User) والمورد (Resource) مسبقًا 
        // لتجنب الاستعلامات الإضافية في الـ Listener (N+1 Problem).
        $this->booking = $booking->load('user', 'resource'); 
    }
}