<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingCancellationMail extends Mailable
{
    use Queueable, SerializesModels;
    
    /**
     * @var Booking
     */
    public $booking;

    /**
     * إنشاء نسخة جديدة من الرسالة.
     */
    public function __construct(Booking $booking)
    {
        // تحميل كائن الحجز لجعله متاحاً في الـ View
        $this->booking = $booking;
    }

    /**
     * الحصول على ظرف الرسالة (العنوان).
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            // عنوان البريد الإلكتروني
            subject: 'Booking Cancellation Notification (#' . $this->booking->id . ')',
        );
    }

    /**
     * الحصول على محتوى الرسالة (الـ View).
     */
    public function content(): Content
    {
        return new Content(
            // الإشارة إلى ملف الـ Markdown View
            markdown: 'emails.bookings.cancellation',
            // تمرير البيانات إلى الـ View
            with: [
                'booking' => $this->booking,
            ],
        );
    }
}