@component('mail::message')
# Booking Confirmed

Hello {{ $booking->user->name }},

Your booking for **{{ $resourceName }}** has been successfully confirmed. Here are the details:

| Detail | Value |
| :--- | :--- |
| **Resource** | {{ $resourceName }} |
| **Starts At** | {{ $booking->start_time->format('Y-m-d H:i A') }} |
| **Ends At** | {{ $booking->end_time->format('Y-m-d H:i A') }} |
| **Status** | {{ $booking->status }} |

Please ensure you are present at the location on time.

@component('mail::button', ['url' => url('/profile/bookings')])
View Your Booking
@endcomponent

Thank you,
{{ config('app.name') }}
@endcomponent