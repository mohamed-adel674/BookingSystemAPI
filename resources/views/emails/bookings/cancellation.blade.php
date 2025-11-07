@component('mail::message')

# تم الغاء الحجز

We regret to inform you that your booking has been successfully **cancelled**.

### Cancelled Booking Details:

* **Booking ID:** \#{{ $booking->id }}
* **Resource:** {{ $booking->resource->name ?? 'N/A' }}
* **Start Time:** {{ $booking->start_time }}
* **End Time:** {{ $booking->end_time }}
* **Current Status:** **{{ ucfirst($booking->status) }}**

We understand that plans can change. If you need to make a new booking, please don't hesitate to use our system.

@component('mail::button', ['url' => url('/dashboard/bookings')])
Manage Bookings
@endcomponent

Thank you for using our services.

Regards,
The [Your System Name] Team

@endcomponent