<?php

// app/Mail/BookingConfirmationMail.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Booking;

class BookingConfirmationMail extends Mailable implements ShouldQueue // <--- ShouldQueue: هام لعدم تأخير استجابة API
{
    use Queueable, SerializesModels;

    public Booking $booking;

    /**
     * إنشاء نسخة جديدة من الرسالة.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    /**
     * احصل على مغلف الرسالة (Subject and From).
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Booking Confirmation - Your Appointment Details',
        );
    }

    /**
     * احصل على تعريف محتوى الرسالة (الـ View).
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.bookings.confirmation', // سننشئ هذا الملف الآن
            with: [
                'booking' => $this->booking,
                'resourceName' => $this->booking->resource->name, // لضمان وجود اسم المورد
            ],
        );
    }
}
