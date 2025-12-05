@component('mail::message')
# Booking Cancelled

Hello {{ $booking->user->name }},

We confirm that your booking for **{{ $booking->resource->name }}** has been **successfully cancelled**.

| Detail | Value |
| :--- | :--- |
| **Resource** | {{ $booking->resource->name }} |
| **Starts At** | {{ $booking->start_time->format('Y-m-d H:i A') }} |
| **Status** | Cancelled |

If this was an error, please contact support immediately.

Thank you,
{{ config('app.name') }}
@endcomponent