@component('mail::message')
# Booking Reminder

Hello {{ $booking->user->name }},

This is a friendly reminder that your booking for **{{ $resourceName }}** is scheduled for **tomorrow**.

| Detail | Value |
| :--- | :--- |
| **Resource** | {{ $resourceName }} |
| **Starts At** | {{ $booking->start_time->format('Y-m-d H:i A') }} |

Please be prepared for your scheduled time.

@component('mail::button', ['url' => url('/profile/bookings')])
Manage Booking
@endcomponent

Thank you,
{{ config('app.name') }}
@endcomponent