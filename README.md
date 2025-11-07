#🚀 Booking System API (Integrated Reservation System)
This repository contains the Pure Backend API for a comprehensive reservation and scheduling system (e.g., booking halls, clinics, or services). It is built using Laravel and leverages Laravel Sanctum for API authentication.

🌟 Key Features
API Authentication (Sanctum): Secure registration, login, and token-based authentication for both Admin and Customer roles.

Role-Based Authorization: Clear separation of privileges using admin and customer Middleware.

Resource Management (CRUD): Full management of bookable items (halls, services).

Availability Scheduling: Define weekly working hours and specific date exceptions for each resource.

Complex Scheduling Logic: Calculates truly available time slots by factoring in defined availability and existing booking overlaps (Conflict Check).

Asynchronous Notifications (Queue/Events): Sends booking confirmations, cancellations, and reminders via email asynchronously to maintain fast API response times.

Scheduled Jobs (Laravel Scheduler): Automatically sends booking reminders 24 hours prior to the appointment time.
