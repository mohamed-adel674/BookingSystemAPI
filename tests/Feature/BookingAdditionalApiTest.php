<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Resource;
use App\Models\Availability;
use App\Models\Booking;
use Carbon\Carbon;

class BookingAdditionalApiTest extends TestCase
{
    use RefreshDatabase;

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

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/bookings/{$booking->id}/cancel");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'cancelled');
    }

    public function test_admin_can_confirm_booking()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();
        $resource = Resource::factory()->create();
        
        $booking = Booking::create([
            'user_id' => $user->id,
            'resource_id' => $resource->id,
            'start_time' => Carbon::now()->addDay()->setHour(10),
            'end_time' => Carbon::now()->addDay()->setHour(11),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/bookings/{$booking->id}/confirm");

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'booking']);
    }

    public function test_non_admin_cannot_confirm_booking()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $resource = Resource::factory()->create();
        
        $booking = Booking::create([
            'user_id' => $user->id,
            'resource_id' => $resource->id,
            'start_time' => Carbon::now()->addDay()->setHour(10),
            'end_time' => Carbon::now()->addDay()->setHour(11),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/bookings/{$booking->id}/confirm");

        $response->assertStatus(403);
    }

    public function test_user_can_reschedule_booking()
    {
        $user = User::factory()->create();
        $resource = Resource::factory()->create();
        
        Availability::factory()->create([
            'resource_id' => $resource->id,
            'day_of_week' => Carbon::now()->addDay()->dayOfWeekIso,
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);
        
        $booking = Booking::create([
            'user_id' => $user->id,
            'resource_id' => $resource->id,
            'start_time' => Carbon::now()->addDay()->setHour(10)->setMinute(0)->format('Y-m-d H:i:s'),
            'end_time' => Carbon::now()->addDay()->setHour(11)->setMinute(0)->format('Y-m-d H:i:s'),
            'status' => 'confirmed',
        ]);

        $newStart = Carbon::now()->addDay()->setHour(14)->setMinute(0)->format('Y-m-d H:i:s');
        $newEnd = Carbon::now()->addDay()->setHour(15)->setMinute(0)->format('Y-m-d H:i:s');

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/bookings/{$booking->id}", [
                'start_time' => $newStart,
                'end_time' => $newEnd,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'start_time' => $newStart,
        ]);
    }

    public function test_user_can_view_their_bookings()
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

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/users/{$user->id}/bookings");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'resource']]]);
    }

    public function test_admin_can_view_any_user_bookings()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();
        $resource = Resource::factory()->create();
        
        Booking::create([
            'user_id' => $user->id,
            'resource_id' => $resource->id,
            'start_time' => Carbon::now()->addDay()->setHour(10),
            'end_time' => Carbon::now()->addDay()->setHour(11),
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/users/{$user->id}/bookings");

        $response->assertStatus(200);
    }

    public function test_user_cannot_view_other_user_bookings()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $response = $this->actingAs($user1, 'sanctum')
            ->getJson("/api/users/{$user2->id}/bookings");

        $response->assertStatus(403);
    }
}
