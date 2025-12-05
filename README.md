# 🚀 Booking System API

A comprehensive Laravel-based booking system API with complete resource management, user authentication, and admin dashboard.

## 🌟 Features

- **User Authentication** (Register, Login, Refresh Token, Logout)
- **Profile Management** (View & Update Profile)
- **Services Management** (CRUD with Search & Filtering)
- **Resource Management** (Halls, Clinics, Services)
- **Availability Management** (Time slots & Working hours)
- **Booking System** (Create, Reschedule, Cancel, Confirm)
- **Admin Dashboard** (Statistics, User Management, Booking Management)
- **Email Notifications** (Booking Created, Booking Cancelled)
- **Search & Filtering** (Services, Resources, Bookings)
- **Role-Based Authorization**: Clear separation of privileges between admin and customer users
- **Complex Scheduling Logic**: Calculates available time slots with conflict detection
- **Asynchronous Notifications**: Queue-based email system for fast API responses
- **Scheduled Jobs**: Automatic reminders 24 hours before appointments

## 📋 Requirements

- PHP >= 8.2
- Composer
- MySQL Database
- Laravel 11.x

## 🛠️ Installation

```bash
# Clone the repository
git clone https://github.com/mohamed-adel674/BookingSystemAPI.git
cd BookingSystemAPI

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Configure database in .env file
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=booking_system
DB_USERNAME=root
DB_PASSWORD=

# Run migrations and seed
php artisan migrate:fresh --seed

# Start the server
php artisan serve
```

## 🔑 Default Credentials

- **Admin**: admin@example.com / password
- **User**: ahmed@example.com / password

## 📚 API Endpoints

### Authentication
```
POST   /api/auth/register          Register new user
POST   /api/auth/login             User login
POST   /api/auth/refresh-token     Refresh access token
POST   /api/auth/logout            User logout
```

### User Profile
```
GET    /api/profile                Get user profile
PUT    /api/profile                Update profile
```

### Services
```
GET    /api/services               List all services (with search)
GET    /api/services/{id}          Get service details
POST   /api/services               Create service (Admin)
PUT    /api/services/{id}          Update service (Admin)
DELETE /api/services/{id}          Delete service (Admin)
```

### Availability & Slots
```
GET    /api/availability           Search available time slots
POST   /api/slots                  Create availability (Admin)
PUT    /api/slots/{id}             Update availability (Admin)
```

### Bookings
```
POST   /api/bookings               Create new booking
GET    /api/bookings               List user bookings
GET    /api/bookings/{id}          Get booking details
PUT    /api/bookings/{id}          Reschedule booking
POST   /api/bookings/{id}/cancel   Cancel booking
POST   /api/bookings/{id}/confirm  Confirm booking (Admin)
GET    /api/users/{userId}/bookings Get user bookings
```

### Admin Dashboard
```
GET    /api/admin/statistics       Dashboard statistics
GET    /api/admin/users            List all users
GET    /api/admin/bookings         All bookings (with filters)
PUT    /api/admin/users/{id}/role  Update user role
```

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/BookingApiTest.php

# Run with coverage
php artisan test --coverage
```

## 📧 Email Configuration

Update your `.env` file:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@booking.com
MAIL_FROM_NAME="${APP_NAME}"
```

For development, use **Mailtrap** or **MailHog**.  
For production, configure your actual SMTP provider.

## 🔒 Security Features

- **Sanctum Authentication**: Token-based authentication
- **Admin Middleware**: Protected admin routes
- **Conflict Detection**: Prevents double bookings
- **Availability Validation**: Ensures bookings within working hours
- **Authorization Checks**: User ownership verification
- **Rate Limiting**: API protection against abuse

## 📖 Database Schema

### Users
- id, name, email, password, is_admin, email_verified_at

### Resources
- id, name, description, type (hall/clinic/service), capacity, is_active

### Availabilities
- id, resource_id, day_of_week, start_time, end_time, date_from, date_to

### Bookings
- id, user_id, resource_id, start_time, end_time, status

## 🎯 Usage Examples

### Create Booking
```bash
curl -X POST http://localhost:8000/api/bookings \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "resource_id": 1,
    "start_time": "2025-12-10 10:00:00",
    "end_time": "2025-12-10 11:00:00"
  }'
```

### Search Services
```bash
curl -X GET "http://localhost:8000/api/services?search=consultation&is_active=1"
```

### Get Available Slots
```bash
curl -X GET "http://localhost:8000/api/availability?resource_id=1&start_date=2025-12-10&end_date=2025-12-15&duration_minutes=60" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 📂 Project Structure

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php
│   │   ├── BookingController.php
│   │   ├── ServiceController.php
│   │   ├── AvailabilityController.php
│   │   ├── ResourceController.php
│   │   ├── AdminController.php
│   │   └── UserController.php
│   ├── Middleware/
│   │   └── AdminMiddleware.php
│   └── Resources/
│       └── BookingResource.php
├── Models/
│   ├── User.php
│   ├── Resource.php
│   ├── Availability.php
│   └── Booking.php
├── Events/
│   ├── BookingCreated.php
│   └── BookingCancelled.php
└── Listeners/
    ├── SendBookingConfirmation.php
    └── SendBookingCancellation.php
```

## ⏰ Queue Worker & Scheduler

### Queue Worker
The Queue Worker must be run separately to process emails and notifications:
```bash
php artisan queue:work
```

### Scheduler Configuration
To enable daily reminders (the `bookings:send-reminders` command), you must set up a Cron Job on your production server to run the Laravel Scheduler every minute:

```bash
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

## 🤝 Contributing

1. Fork the project
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is open-sourced software licensed under the MIT license.

## 👨‍💻 Author

Booking System API - Laravel Backend by Mohamed Adel

## 📞 Support

For support, create an issue in the repository.
