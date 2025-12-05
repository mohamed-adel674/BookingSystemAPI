<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Resource;
use App\Models\Availability;
use App\Models\Booking;
use Carbon\Carbon;

class BookingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_available_slots()
    {
        $user = User::factory()->create();
        $resource = Resource::factory()->create();
        
        // Create availability for Monday (1)
        Availability::factory()->create([
            'resource_id' => $resource->id,
            'day_of_week' => 1, // Monday
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ]);

        // Next Monday
        $nextMonday = Carbon::now()->next(Carbon::MONDAY)->toDateString();

        $response = $this->actingAs($user, 'sanctum')->getJson("/api/available-slots?resource_id={$resource->id}&start_date={$nextMonday}&end_date={$nextMonday}&duration_minutes=60");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'slots' => [
                    $nextMonday => [
                        '*' => ['start_time', 'end_time', 'duration_minutes']
                    ]
                ]
            ]);
    }

    public function test_create_booking_success()
    {
        $user = User::factory()->create();
        $resource = Resource::factory()->create();
        
        // Availability for Monday
        Availability::factory()->create([
            'resource_id' => $resource->id,
            'day_of_week' => 1,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ]);

        $nextMonday = Carbon::now()->next(Carbon::MONDAY)->toDateString();
        $startTime = "{$nextMonday} 10:00:00";
        $endTime = "{$nextMonday} 11:00:00";

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/bookings', [
            'resource_id' => $resource->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'confirmed');

        $this->assertDatabaseHas('bookings', [
            'resource_id' => $resource->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);
    }

    public function test_create_booking_conflict()
    {
        $user = User::factory()->create();
        $resource = Resource::factory()->create();
        
        Availability::factory()->create([
            'resource_id' => $resource->id,
            'day_of_week' => 1,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ]);

        $nextMonday = Carbon::now()->next(Carbon::MONDAY)->toDateString();
        $startTime = "{$nextMonday} 10:00:00";
        $endTime = "{$nextMonday} 11:00:00";

        // Create initial booking
        Booking::create([
            'user_id' => $user->id,
            'resource_id' => $resource->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'confirmed',
        ]);

        // Try to create overlapping booking
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/bookings', [
            'resource_id' => $resource->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        $response->assertStatus(409);
    }

    public function test_user_can_list_bookings()
    {
        $user = User::factory()->create();
        $resource = Resource::factory()->create();
        
        Booking::create([
            'user_id' => $user->id,
            'resource_id' => $resource->id,
            'start_time' => Carbon::now()->addDay()->setHour(10),
            'end_time' => Carbon::now()->addDay()->setHour(11),
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/bookings');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'resource', 'start_time', 'end_time', 'status']]]);
    }

    public function test_user_can_view_booking()
    {
        $user = User::factory()->create();
        $resource = Resource::factory()->create();
        
        $booking = Booking::create([
            'user_id' => $user->id,
            'resource_id' => $resource->id,
            'start_time' => Carbon::now()->addDay()->setHour(10),
            'end_time' => Carbon::now()->addDay()->setHour(11),
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson("/api/bookings/{$booking->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $booking->id);
    }

    public function test_user_can_cancel_booking()
    {
        $user = User::factory()->create();
        $resource = Resource::factory()->create();
        
        $booking = Booking::create([
            'user_id' => $user->id,
            'resource_id' => $resource->id,
            'start_time' => Carbon::now()->addDay()->setHour(10),
            'end_time' => Carbon::now()->addDay()->setHour(11),
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($user, 'sanctum')->patchJson("/api/bookings/{$booking->id}", [
            'status' => 'cancelled'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'cancelled');
            
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
        ]);
    }
}
