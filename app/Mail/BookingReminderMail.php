<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// ... (use statements)
class BookingReminderMail extends Mailable implements ShouldQueue 
{
    // ... (هيكلية مشابهة للـ Mailable الأخرى)
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'REMINDER: Your Booking is Tomorrow!', // عنوان الرسالة
        );
    }
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.bookings.reminder',
            with: [
                'booking' => $this->booking,
                'resourceName' => $this->booking->resource->name,
            ],
        );
    }
}
