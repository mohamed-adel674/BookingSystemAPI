# 🚀 Booking System API (Integrated Reservation System)

This repository contains the Pure Backend API for a comprehensive reservation and scheduling system (e.g., booking halls, clinics, or services). It is built using the Laravel framework and utilizes Laravel Sanctum for secure API authentication.

## 🌟 Core FeaturesAPI 

Authentication (Sanctum): Secure registration, login, and token-based authentication for both Admin and Customer roles.Role-Based Authorization (Middleware): Clear separation of privileges between admin and customer users.Resource & Availability Management (CRUD): Full management of bookable items (Resources) and their weekly working hours/exceptions (Availabilities).Complex Scheduling Logic: Calculates truly available time slots, factoring in defined availability schedules and checking for existing booking overlaps (Conflict Check).Asynchronous Notifications (Queue/Events): Sends booking confirmations, cancellations, and reminders via email using Laravel's Queue system for fast API response times.Scheduled Jobs (Laravel Scheduler): Automatically dispatches reminders 24 hours prior to the appointment time.

## 🛠️ Setup and InstallationFollow these steps to get the project running on your local machine.
1. PrerequisitesPHP >= 8.2ComposerMySQL Database (or other supported by Laravel)Git2.
2.  Clone the RepositoryBashgit clone <YOUR_REPOSITORY_URL_HERE>
cd BookingSystemAPI
3. Install DependenciesBashcomposer install
4. Environment Configuration (.env)Create a copy of the .env.example file and name it .env:Bashcp .env.example .env
Configuration: Update your database connection details and email settings:مقتطف الرمزDB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=booking_api_db 
DB_USERNAME=root 
DB_PASSWORD=

MAIL_MAILER=log # Use 'smtp' for production email sending

# ... other MAIL settings
Generate Application Key:Bashphp artisan key:generate
5. Run MigrationsExecute the migrations to create all necessary database tables (Users, Resources, Availabilities, Bookings):Bashphp artisan migrate
6. Run Server and Queue WorkerStart the local server for the API:Bashphp artisan serve
The Queue Worker must be run separately to process emails and notifications:Bashphp artisan queue:work

# 🔑 API Endpoints and AuthorizationAll
API endpoints are prefixed with ```bash /api/ ```. For protected routes, you must send the Sanctum Token in the request header.
Header KeyHeader Value (Example)AuthorizationBearer <your_sanctum_token>1. Authentication RoutesMethodEndpointDescriptionPOST/api/registerRegister a new user (customer role is default).POST/api/loginLog in and receive a new Sanctum Token.POST/api/logoutLog out and revoke the current Token.2. Admin Management Routes (Requires admin Role)ResourceMethodEndpointDescriptionResourcesGET / POST / PUT / DELETE/api/resourcesFull CRUD for managing bookable resources.AvailabilitiesGET / POST / PUT / DELETE/api/availabilitiesFull CRUD for managing resource working schedules.3. Booking and Scheduling Logic (Authenticated Users)MethodEndpointDescriptionAvailabilityGET/api/available-slotsBookingPOST/api/bookingsBookingGET/api/bookingsBookingPATCH/api/bookings/{id}

# ⏰ Scheduler Configuration

To enable daily reminders (the  ```bash bookings:send-reminders ``` command ), you must set up a single
Cron Job on your production server to run the Laravel Scheduler every minute.


Cron Job Setup
   ```bash
(Production)Bash* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
