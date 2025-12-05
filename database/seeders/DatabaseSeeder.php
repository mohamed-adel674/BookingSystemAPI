<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin User
        $admin = \App\Models\User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        // Create Regular Users
        $users = [];
        $users[] = \App\Models\User::create([
            'name' => 'Ahmed Mohamed',
            'email' => 'ahmed@example.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        $users[] = \App\Models\User::create([
            'name' => 'Fatima Ali',
            'email' => 'fatima@example.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        $users[] = \App\Models\User::create([
            'name' => 'Omar Hassan',
            'email' => 'omar@example.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        // Create Resources (Services, Halls, Clinics)
        $services = [];
        $services[] = \App\Models\Resource::create([
            'name' => 'Consultation Service',
            'description' => 'Professional consultation service',
            'type' => 'service',
            'capacity' => 1,
            'is_active' => true,
        ]);

        $services[] = \App\Models\Resource::create([
            'name' => 'Training Service',
            'description' => 'Personal training sessions',
            'type' => 'service',
            'capacity' => 5,
            'is_active' => true,
        ]);

        $halls = [];
        $halls[] = \App\Models\Resource::create([
            'name' => 'Conference Hall A',
            'description' => 'Large conference hall',
            'type' => 'hall',
            'capacity' => 100,
            'is_active' => true,
        ]);

        $halls[] = \App\Models\Resource::create([
            'name' => 'Meeting Room B',
            'description' => 'Small meeting room',
            'type' => 'hall',
            'capacity' => 20,
            'is_active' => true,
        ]);

        $clinics = [];
        $clinics[] = \App\Models\Resource::create([
            'name' => 'Dental Clinic',
            'description' => 'Professional dental care',
            'type' => 'clinic',
            'capacity' => 1,
            'is_active' => true,
        ]);

        // Create Availabilities for Resources
        $allResources = array_merge($services, $halls, $clinics);
        
        foreach ($allResources as $resource) {
            // Monday to Friday, 9 AM - 5 PM
            for ($day = 1; $day <= 5; $day++) {
                \App\Models\Availability::create([
                    'resource_id' => $resource->id,
                    'day_of_week' => $day,
                    'start_time' => '09:00:00',
                    'end_time' => '17:00:00',
                ]);
            }
        }

        // Create Sample Bookings
        $tomorrow = \Carbon\Carbon::tomorrow()->setHour(10)->setMinute(0);
        
        // Confirmed bookings
        \App\Models\Booking::create([
            'user_id' => $users[0]->id,
            'resource_id' => $services[0]->id,
            'start_time' => $tomorrow->copy()->format('Y-m-d H:i:s'),
            'end_time' => $tomorrow->copy()->addHour()->format('Y-m-d H:i:s'),
            'status' => 'confirmed',
        ]);

        \App\Models\Booking::create([
            'user_id' => $users[1]->id,
            'resource_id' => $halls[0]->id,
            'start_time' => $tomorrow->copy()->addDays(2)->setHour(14)->format('Y-m-d H:i:s'),
            'end_time' => $tomorrow->copy()->addDays(2)->setHour(16)->format('Y-m-d H:i:s'),
            'status' => 'confirmed',
        ]);

        // Pending booking
        \App\Models\Booking::create([
            'user_id' => $users[2]->id,
            'resource_id' => $clinics[0]->id,
            'start_time' => $tomorrow->copy()->addDays(3)->setHour(11)->format('Y-m-d H:i:s'),
            'end_time' => $tomorrow->copy()->addDays(3)->setHour(12)->format('Y-m-d H:i:s'),
            'status' => 'pending',
        ]);

        // Cancelled booking
        \App\Models\Booking::create([
            'user_id' => $users[0]->id,
            'resource_id' => $services[1]->id,
            'start_time' => $tomorrow->copy()->addDays(1)->setHour(15)->format('Y-m-d H:i:s'),
            'end_time' => $tomorrow->copy()->addDays(1)->setHour(16)->format('Y-m-d H:i:s'),
            'status' => 'cancelled',
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin credentials: admin@example.com / password');
        $this->command->info('User credentials: ahmed@example.com / password');
    }
}
